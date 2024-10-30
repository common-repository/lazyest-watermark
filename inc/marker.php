<?php

/**
 * LazyestWaterMarker
 * 
 * @package Lazyest Gallery
 * @subpackage Lazyest Watermark
 * @author Marcel Brinkkemper
 * @copyright 2011 Brimosoft
 * @version 0.2.1
 * @access public
 */
class LazyestWaterMarker {
	
	protected $watermark;
	
	/**
	 * LazyestWaterMarker::__construct()
	 * 
	 * @uses add_filter()
	 * @return void
	 */
	function __construct() {
		$options = lazyest_watermark()->options;
							
		switch( $options['type'] ) {
			case 'd_text_outline' :
				$params = sprintf( 'chst=d_text_outline&chld=%s|%d|%s|%s|%s|%s',
					substr( $options['color'], 1 ),
					$options['textsize'],
					$options['textalignment'], 
					substr( $options['outlinecolor'], 1 ),
					$options['textweight'],
					rawurlencode( str_replace( '|', ' ', $options['text'] ) )
				); 
				break;
			case 'd_simple_text_icon' :
				$params = sprintf( 'chst=d_simple_text_icon%s&chld=%s|%d|%s|%s|%s|%s|%s',
					$options['iconposition'],
					rawurlencode( str_replace( '|', ' ', $options['text'] ) ),
					$options['textsize'],
					substr( $options['color'], 1 ),
					$options['iconname'],
					$options['iconsize'],
					substr( $options['color'], 1 ),
					substr( $options['bordercolor'], 1 )
				);
				break;
			case 'd_fnote' :
				$params = sprintf( 'chst=d_fnote&chld=%s|%s|%s|%s|%s',
					$options['notetype'],
					$options['notesize'],
					substr( $options['color'], 1 ),
					$options['textalignment'],
					str_replace( '%7C', '|', rawurlencode( $options['text'] ) )
				);
				break;
			default: 				
				$params = '';	
				break;
		}	
		// get the watermark resource once
		if ( file_exists( lazyest_watermark()->watermark_image ) ) { 
			$this->watermark = @imagecreatefrompng( lazyest_watermark()->watermark_image );
		} else {			
			$this->watermark = @imagecreatefrompng( 'http://chart.apis.google.com/chart?' . $params );
		}
		
		if ( $this->watermark ) {
			imagealphablending( $this->watermark, true);
			imagesavealpha( $this->watermark, true );
			
			if ( ! file_exists( lazyest_watermark()->watermark_image ) )
				imagepng( $this->watermark, lazyest_watermark()->watermark_image );
			
			add_filter( 'lazyest_image_found', array( &$this, 'lazyest_image_found' ), 100 );	
			add_filter( 'lazyest_imageresized', array( &$this, 'lazyest_imageresized' ), 10, 3 );					
		}		
	}
	
	/**
	 * LazyestWaterMarker::__destruct()
	 * 
	 * @return void
	 */
	function __destruct() {		
		if ( is_resource( $this->watermark ) )
	  	imagedestroy( $this->watermark );		
	}
	
	/**
	 * LazyestWaterMarker::mark_image()
	 * Adds watermark to image resource
	 * 
	 * @param resource $picture to be watermarked
	 * @return resource
	 */
	function mark_image( $picture ) {
		global $lg_gallery;			
		
		$transparency = 90;	
			
		$o_width = imagesx( $picture );
		$o_height = imagesy( $picture );
		$w_width = imagesx( $this->watermark );
		$w_height = imagesy( $this->watermark );
		$c_width = $w_width;
		$c_height = $w_height;		
				
		$xpos = $ypos = 0;
		switch( lazyest_watermark()->options['position'] ) {
			case 'topright' :
				$xpos = $o_width - $w_width; 
				break;
			case 'middleleft' :
				$ypos = round( ( $o_height - $w_height ) / 2 );
				break;			
			case 'coverimage' :	
				$w_aspect = $w_width / $w_height;
				$o_aspect = $o_height / $o_width;
				if ( $o_aspect > $w_aspect ) {
					$c_width = round( $o_height / $w_height * $w_width );
					$c_height = $o_height;
				} else {
					$c_height = round( $o_width / $w_width * $w_height );
					$c_width = $o_width;
				}
				$ypos = round( ( $o_height - $c_height ) / 2 );
				$xpos = round( ( $o_width - $c_width ) / 2 );
				break;
			case 'centered' :
				$ypos = round( ( $o_height - $w_height ) / 2 );
				$xpos = round( ( $o_width - $w_width ) / 2 );
				break;
			case 'middleright' :				
				$ypos = round( ( $o_height - $w_height ) / 2 );
				$xpos = $o_width - $w_width;
				break;
			case 'bottomleft' :
				$ypos = $o_height - $w_height;
				break;
			case 'bottomright' :
				$ypos = $o_height - $w_height;
				$xpos = $o_width - $w_width;
				break;	
		}
									
		if ( 'coverimage' == lazyest_watermark()->options['position'] ) {			
			$cut = imagecreatetruecolor( $c_width, $c_height );
			imagecopy( $cut, $picture, 0, 0, $xpos, $ypos, $c_width, $c_height );
			imagecopyresampled( $cut, $this->watermark, 0, 0, 0, 0, $c_width, $c_height, $w_width, $w_height ); 
			imagecopymerge( $picture, $cut, $xpos, $ypos, 0, 0, $c_width, $c_height, $transparency );
		} else {
			$cut = imagecreatetruecolor( $w_width, $w_height );
			imagecopy( $cut, $picture, 0, 0, $xpos, $ypos, $w_width , $w_height );
			imagecopy( $cut, $this->watermark, 0, 0, 0, 0, $w_width, $w_height ); 
			imagecopymerge( $picture, $cut, $xpos, $ypos, 0, 0, $w_width, $w_height, $transparency );
		}
		imagedestroy( $cut );
		return $picture;							
	}
	
	/**
	 * LazyestWaterMarker::mark_file()
	 * Adds watermark to image file
	 * 
	 * @uses wp_load_image()
	 * @param string $image_location image path in file system
	 * @return bool succes/failure
	 */
	function mark_file( $image_location ) {
		global $lg_gallery;
		
		$original = wp_load_image( $image_location );
    if ( !is_resource( $original ) ) {
    	trigger_error( $image, E_USER_WARNING );
    	return false;
		}		
		list( $o_width, $o_height, $o_type ) = @getimagesize( $image_location );			 	
		
		$watermarked = $this->mark_image( $original );
		
		if ( IMAGETYPE_PNG == $o_type && function_exists( 'imageistruecolor' ) && ! imageistruecolor( $original ) )
			imagetruecolortopalette( $watermarked, false, imagecolorstotal( $original ) );
		
		if ( is_resource( $watermarked ) ) {
			rename( $image_location, $image_location . '.bak' );
			$path = pathinfo( $image_location );
  		switch ( strtolower(  $path['extension'] ) ) {
  	  	case 'jpeg':
  	  	case 'jpg':
  	    	imagejpeg( $watermarked, $image_location, $lg_gallery->get_option( 'resample_quality' ) );
  	    	break;
  	  	case 'gif':
  	    	imagegif( $watermarked, $image_location );
  	    	break;
  	  	case 'png':
  	    	imagepng( $watermarked, $image_location );
  	   	 break;  	   	 
  		}
 			imagedestroy( $watermarked );
 			
		  if ( is_resource( $original ) )
				imagedestroy( $original );
  	
  		if ( file_exists( $image_location ) ) {
  			unlink( $image_location . '.bak' );
  			return true;
			}
  		else {
  			rename( $image_location  . '.bak', $image_location );
  			return false;	
  		}	
    }
    return false;
	}	
		
	/**
	 * LazyestWaterMarker::lazyest_image_found()
	 * Adds watermark to new found files
	 * 
	 * @param LazyestImage $image
	 * @return LazyestImage
	 */
	function lazyest_image_found( $image ) {
		global $lg_gallery;
		
		// add watermark to original
		if ( 'slide' != lazyest_watermark()->options['watermark'] )
			if ( $this->mark_file( $image->original() ) ) 
				$image->extra_fields['watermark'] = 'original';
								
		return $image;
	}
	
	/**
	 * LazyestWaterMarker::lazyest_imageresized()
	 * Adds watermarked to new resized images  (slides)
	 * 
	 * @param resource $resized
	 * @param int $width
	 * @param int $height
	 * @return resource
	 */
	function lazyest_imageresized( $resized, $width, $height ) {
		global $lg_gallery;
		
		// don't watermark thumbnails
		if ( ( $width <= $lg_gallery->get_option( 'thumbwidth' ) ) || ( $height <= $lg_gallery->get_option( 'thumbheight' ) ) )
			return $resized;
			
		// only watermark if orginal is not watermarked	
		if ( 'slide' == lazyest_watermark()->options['watermark'] )		
			$resized = $this->mark_image( $resized );
		return $resized;	
	}
	
	/**
	 * LazyestWaterMarker::add_watermark()
	 * Add watermark to image on AJAX request
	 * 
	 * @return void
	 */
	function add_watermark() {
		global $lg_gallery;
		$folder = utf8_decode( rawurldecode( $_POST['folder'] ) );
		if ( ! file_exists( $lg_gallery->root . $folder ) )
			return '-1';
			
	}
}
	
?>