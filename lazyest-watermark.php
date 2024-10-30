<?php
/*
Plugin Name: Lazyest Watermark
Plugin URI: http://brimosoft.nl/lazyest/plugin/
Description: Watermarks to Images in Lazyest Gallery 
Date: December 2012
Author: Brimosoft
Author URI: http://brimosoft.nl
Version: 0.4.0
License: GNU GPLv2
*/
 
 

/**
 * LazyestWatermark
 * 
 * @package Lazyest Gallery
 * @subpackage Lazyest Watermark
 * @author Marcel Brinkkemper
 * @copyright 2011-2012 Marcel Brinkkemper
 * @version 0.4.0
 * @access public
 */
class LazyestWatermark {
	
	var $options;
	var $plugin_file;
	var $marker;
	var $watermark_image;
	
	private static $instance;
	
	/**
	 * LazyestWatermark::__construct()
	 * 
	 * @return void
	 */
	function __construct() {}
	
	// lazyest-watermark core functions
	
	static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new LazyestWatermark;
			self::$instance->init();
		}
		return self::$instance;
	}
		
	function init() {		
		$options = get_option( 'lazyest-watermark' );		
		$this->options = $options ? $options : $this->defaults();
		$this->plugin_file =  __FILE__ ;
		load_plugin_textdomain( 'lazyest-maps', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		$uploads         = wp_upload_dir();
		$watermark_dir   = $uploads['basedir'] . '/lazyest-watermark';
		if ( ! file_exists( $watermark_dir ) )
			wp_mkdir_p( $watermark_dir );
		$this->watermark_image = $watermark_dir . '/watermark.png';
		$this->filters();	
	}
	
	function filters() {
		// wordpress hooks
		register_uninstall_hook(  __FILE__, array( 'LazyestWatermark', 'uninstall' ) );
		register_activation_hook( __FILE__, array( &$this, 'activation'            ) );
		
		// wordpress actions and filters		
		add_action( 'admin_action_lazyest-watermark',                    array( $this, 'do_action' ) );		
		add_action( 'admin_notices',                                     array( $this, 'admin_notices' ) );
		add_action( 'admin_print_scripts-settings_page_lazyest-gallery', array( $this, 'manager_js' ) );		
		add_action( 'admin_print_styles-settings_page_lazyest-gallery',  array( $this, 'manager_css' ) );
			
		if ( $this->options['enable'] )
			add_action( 'admin_print_scripts-toplevel_page_lazyest-filemanager', array( &$this, 'manager_js' ) );
		
		// lazyest-gallery actions and filters				
		add_action( 'lazyest-gallery-settings_slides',   array( $this, 'settings_slides' ) );
		add_action( 'lazyest-gallery-settings_pages',    array( $this, 'settings_page'   ) );	
		add_action( 'lazyest_ready',                     array( $this, 'lazyest_ready'   ) );	
//		if ( $this->options['enable'] )
//			add_filter( 'lazyest_gallery_edit_image', array( &$this, 'lazyest_gallery_edit_image' ), 10, 2 );	
	}
	
	function defaults() {
		return array(
			'enable'        => 1,
			'mark'          => 'slide',
			'type'          => 'd_simple_text_icon',
			'position'      => 'bottomright',
			'text'          => get_bloginfo( 'name' ),
			'textsize'      => 16,
			'color'         => '#e8e8e8',
			'zposition'     => 'ontop',
			'textalignment' => 'l',
			'textweight'    => '_',
			'outlinecolor'  => '#ffffff',
			'iconposition'  => '_left',
			'iconname'      => 'computer',
			'iconsize'      => 16,
			'bordercolor'   => '#ffffff',
			'notesize'      => 1,
			'notetype'      => 'taped_y',
			'transparency'  => 50
		);
	}
	
	function activation() {
		$this->upgrade();
	}
	
	function uninstall() {
		if ( __FILE__ != WP_UNINSTALL_PLUGIN )
  		return;
  	delete_option( 'lazyest-watermark' );	
	}
	
	function upgrade() {
		update_option( 'lazyest-watermark', $this->options );
	}
	
	// wordpress actions and filters
	
	function do_action() {	
		$redirect = admin_url( 'admin.php?page=lazyest-gallery&subpage=lazyest-watermark' );
		$nonce = $_POST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'lazyest_watermark' ) ) {
			$options = isset( $_POST['lazyest-watermark'] ) ? $_POST['lazyest-watermark'] : $this->options;
			$options['text'] = str_replace( "\r\n", "|", $options['text'] );
			if ( !isset( $options['enable'] ) )
				$options['enable'] = 0;
			update_option( 'lazyest-watermark', $options );
			set_transient( 'lazyest_watermark_notice', 'updated', 30 );
			
			if ( file_exists( $this->watermark_image ) )
				@unlink( $this->watermark_image );
		}		
		wp_redirect( $redirect ); 
    exit();
	}
	
	function admin_notices() {
		$notice = get_transient( 'lazyest_watermark_notice' );
			if ( $notice && ( 'updated' ==  $notice ) ){
				$message = esc_html__( 'Watermark settings saved', 'lazyest-watermark' );
				echo "<div class='updated'><p><strong>$message</strong></p></div>";
				delete_transient( 'lazyest_watermark_notice' );
			}
	}
	
	function manager_css() {
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_style( 'farbtastic' );	
		wp_enqueue_style( 'watermark_admin', plugins_url( 'css/admin.css',  __FILE__ ) );
	}
		
	function manager_js() {
		$ext = ( defined('WP_DEBUG') && WP_DEBUG ) ? 'dev.js' : 'js';
		wp_enqueue_script( 'watermark-admin', plugins_url( "js/admin.$ext",  __FILE__ ), array( 'jquery', 'wp-pointer', 'farbtastic'), '0.1', true );
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		if ( ! in_array( 'lazyest_watermark', $dismissed  ) ){ 
			wp_enqueue_script( 'watermark-pointer', plugins_url( "js/pointer.$ext",  __FILE__ ), array( 'jquery', 'wp-pointer' ), '0.1', true );
			wp_localize_script( 'watermark-pointer', 'waterMark', $this->localize() );
		}
	}
	
	function localize() {		 
		return array(
			'content' => sprintf('<h3>%s</h3><p>%s<br/>%s</p>',
				esc_html__( 'Watermark Options',                         'lazyest-watermark' ),
				esc_html__( 'You have just installed Lazyest Watermark', 'lazyest-watermark' ),
				esc_html__( 'Use this button to make your marks',        'lazyest-watermark' ) 
			)
		);		
	}
	
	// lazyest-gallery actions and filters
	
	// settings page
	
	function settings_slides() {
		?>
		<tr>
      <th scope="row"><?php esc_html_e( 'Watermark', 'lazyest-watermark' ); ?></th>
      <td id="lazyest_watermark_settings_button" >
        <p><a class="button" href="admin.php?page=lazyest-gallery&amp;subpage=lazyest-watermark"><?php esc_html_e( 'Watermark Settings' ) ?></a></p>
        <p><?php esc_html_e( 'Add a watermark to your images', 'lazyest-watermark' ) ?></p>
      </td>  
    </tr>
		<?php
	}
	
	function settings_page( $settings ) {
		if ( ! isset( $_REQUEST['subpage'] ) || 'lazyest-watermark' != $_REQUEST['subpage'] )
			return;
		$settings->other_page = true;	
		require_once( plugin_dir_path( __FILE__ ) . 'inc/settings.php' );
		$lazyest_watermark_settings = new LazyestWatermarkSettings( $settings );
		$lazyest_watermark_settings->display();
		unset( $lazyest_watermark_settings ); 	
	}
	
	// watermark functionality
	
	function lazyest_ready() {
		lg_add_extrafield( 'watermark', '', 'image', false ); 
						
		if ( isset ( $this->options['enable'] ) && $this->options['enable'] ) {
			require_once( plugin_dir_path( __FILE__ ) . 'inc/marker.php' );
			$this->marker = new LazyestWaterMarker();						
		}
	}		
	
	function lazyest_gallery_edit_image( $extra_html, $image ) {
		global $lg_gallery;
		$do_watermark = ( $this->options['watermark'] == 'both' ) && ( $image->extra_fields['watermark'] ==  '' );
		if ( ! $do_watermark )
		$do_watermark = ( $this->options['watermark'] == 'both' ) && ( $image->extra_fields['watermark'] ==  'orginal' ) && ( 'TRUE' == $lg_gallery->get_option( 'enable_slides_cache' ) );
		if ( ! $do_watermark )
			$do_watermark = ( $this->options['watermark'] == 'slide' ) && ( $image->extra_fields['watermark'] ==  '' ) && ( 'TRUE' == $lg_gallery->get_option( 'enable_slides_cache' ) ); 
		if ( $do_watermark ) {	
			$extra_html .= sprintf( '<p><a class="watermarker button" id="%s" class="button" href="%s">%s</a>',
				'watermark-' . $image->form_name(),
				'watermark-' . $image->form_name(),			
				esc_html__( 'Add Watermark', 'lazyest-watermark' ) 
			);
		}
		return $extra_html;
	}
	
	// misc functions
	
	function version() {		  	
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_data = get_plugin_data( __FILE__ );
  	return $plugin_data['Version'];
	}
}

function lazyest_watermark() {
	return LazyestWatermark::instance();
}
lazyest_watermark();

?>