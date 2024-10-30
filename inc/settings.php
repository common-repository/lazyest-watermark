<?php
class LazyestWatermarkSettings {
	
	var $settings;
	
	function __construct( $settings ) {
		$this->settings = $settings;
	}
	
	function display() {
		global $lg_gallery, $wp_version;
		$preview_width = $lg_gallery->get_option( 'pictwidth' );
		$preview_height = $lg_gallery->get_option( 'pictheight' );
		$watermark_width = ( lazyest_watermark()->options['position'] == 'coverimage' ) ? $preview_width : round( $preview_width / 3 );
		$watermark_height = round( $preview_height / 3 );
		?>
		<div class="wrap">
			<?php screen_icon( 'watermark' ); ?>
      <h2><?php echo esc_html_e( 'Add Watermarks to your images', 'lazyest-watermark' ); ?></h2>      
			<?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?> 
			<div id="poststuff" class="metabox-holder has-right-sidebar">
			<?php else : ?>
			<div id="poststuff" class="metabox-holder">
			<?php endif; ?>
				<form id="lazyest-watermark" method="post" action="admin.php">
					<?php wp_nonce_field( 'lazyest_watermark' );  ?>
					<input type="hidden" name="action" value="lazyest-watermark" />
					<?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?> 
						<?php $this->sidebar( $this->settings ) ?>         		
          	<div id="post-body">
         	<?php else : ?>
						<div id="post-body" class="metabox-holder columns-2">		              	
         		<?php $this->sidebar( $this->settings ) ?>
         	<?php endif; ?>	
						<div id="post-body-content">
							<fieldset>
							<legend><?php esc_html__( 'Watermark Settings', 'lazyest-watermark' ); ?></legend>
							<?php $this->enable_settings() ?>
							<?php $this->watermark_settings() ?>
							</fieldset>
						</div>
					</div>
				</form>	
			</div>					 
			</div>	
			<div id="example_cell" style="height:<?php echo $lg_gallery->get_option( 'pictheight') ?>px; width:<?php echo $lg_gallery->get_option( 'pictwidth' ) ?>px;">								
				<img id="watermark_example" src="<?php echo plugins_url( 'images/preview.jpg', lazyest_watermark()->plugin_file ); ?>" alt="<?php esc_html_e( 'Watermark', 'lazyest-watermark' ) ?>" />
				<img id="image_example" src="<?php echo plugins_url( 'images/preview.jpg', lazyest_watermark()->plugin_file ); ?>" alt="<?php esc_html_e( 'Image', 'lazyest-watermark' ) ?>" style="height: <?php echo $preview_height; ?>px; width:<?php echo $preview_width; ?>px;" />																	
			</div>			
		<?php
	}
	
	
	
	// settings page boxes
	
	function enable_settings() {
		?>
		<div class="postbox">
			<h3><?php esc_html_e( 'Enable Watermarks', 'lazyest-watermark' ); ?></h3>
			<div class="inside">
				<p class="meta-options">
					<label for="lgw-enable" class="selectit">
						<input type="checkbox" id="lgw-enable" name="lazyest-watermark[enable]" value="1" <?php checked( '1', lazyest_watermark()->options['enable'] ); ?> />
						<?php esc_html_e( 'Enable watermarks on your images', 'lazyest-watermark' ); ?>
					</label>
				</p>
			</div>			
		</div>
		<?php
	}
	
	function _selected_td( $position ) {
		if ( $position == lazyest_watermark()->options['position'] )
			echo ' class="selected"';
	}
	
	function watermark_settings() {	
		global $lg_gallery, $lazyest_maps;
		
		$options = lazyest_watermark()->options;		
		$options['text'] = str_replace( "|", "\n", $options['text'] );
		$allowed_sizes = apply_filters( 'lazyest_watermark_allowed_sizes', array( 8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36, 48 ) );
		$sizes_select = '';
		foreach( $allowed_sizes as $size ) {
			$sizes_select .= sprintf( "<option value='%d'%s>%d</option>\n", 
				$size,
				( $options['textsize'] == $size ) ? " selected='selected'" : "",
				$size 
			);
		}
		$opacity_select = '';		
		$all_icons = array('academy','activities','airport','amusement','aquarium','art-gallery','atm','baby','bank-dollar','bank-euro','bank-intl','bank-pound','bank-yen','bar','barber','beach','beer','bicycle','books','bowling','bus','cafe','camping','car-dealer','car-rental','car-repair','casino','caution','cemetery-grave','cemetery-tomb','cinema','civic-building','computer','corporate','courthouse','fire','flag','floral','helicopter','home','info','landslide','legal','location','locomotive','medical','mobile','motorcycle','music','parking','pet','petrol','phone','picnic','postal','repair','restaurant','sail','school','scissors','ship','shoppingbag','shoppingcart','ski','snack','snow','sport','star','swim','taxi','train','truck','wc-female','wc-male','wc','wheelchair');
		$icon_select = '';
		foreach( $all_icons as $icon ) { 
			$icon_select .= sprintf( "<option value='%s'%s>%s</option>\n", 
				$icon,
				( $options['iconname'] == $icon ) ? " selected='selected'" : "",
				$icon 
			);			
		} 
		$icon_sizes = array( 12, 16, 24 );
		$iconsizes_select = '';
		foreach( $icon_sizes as $size ) {
			$iconsizes_select .= sprintf( "<option value='%d'%s>%d</option>\n", 
				$size,
				( $options['iconsize'] == $size ) ? " selected='selected'" : "",
				$size 
			);
		}
		$font_size_style = ( $options['type'] == 'd_fnote' ) ? 'style="display:none"' : '';
		$transparency_select = '';
		for ( $transparency = 100; $transparency >= 0; $transparency = $transparency - 10 ) {
			$transparency_select .= sprintf( "<option value='%d'%s>%d%%</option>\n",
				$transparency,
				$transparency == $options['transparency']  ? " selected='selected'" : "",
				100 - $transparency
			);
		}
		
		$maps_warning = isset( $lazyest_maps ) ? '<p class="description">' . esc_html__( 'Watermarking your originals will remove Geo Data from your images. You cannot re-read Geo Data in your Folder Manager screen', 'lazyest-watermark' ) . '</p>' : '';
		?>
		<div id="lgw-settings" class="postbox"<?php if ( ! lazyest_watermark()->options['enable'] ) echo ' style="display:none"' ?>>
		<h3><?php esc_html_e( 'Watermark Settings', 'lazyest-watermark' ); ?></h3>
			<div class="inside">
				<table class="form-table">
					<tbody>					
						<tr>
							<th>
								<?php esc_html_e( 'Watermark', 'lazyest-watermark' ); ?>
							</th>
							<td>
								<select id="watermark_watermark" name="lazyest-watermark[watermark]" >
									<option value="slide" <?php selected( 'slide', $options['watermark'] ) ?>><?php esc_html_e( 'Slides', 'lazyest-watermark' ) ?></option>
									<option value="both" <?php selected( 'both', $options['watermark'] ) ?>><?php esc_html_e( 'Original images and Slides', 'lazyest-watermark' ) ?></option>
								</select>
								<?php echo $maps_warning; ?>							
							</td>
						</tr>
						<tr>
							<th>
								<?php esc_html_e( 'Type of watermark ', 'lazyest-watermark' ) ?>
							</th>
							<td>
								<select id="watermark_type" name="lazyest-watermark[type]" >
									<option value="d_text_outline" <?php selected( 'd_text_outline', $options['type'] ) ?>><?php esc_html_e( 'Outlined text', 'lazyest-watermark' ) ?></option>
									<option value="d_simple_text_icon" <?php selected( 'd_simple_text_icon', $options['type'] ) ?>><?php esc_html_e( 'Text line with icon', 'lazyest-watermark' ) ?></option>
									<option value="d_fnote" <?php selected( 'd_fnote', $options['type'] ) ?>><?php esc_html_e( 'Funny note', 'lazyest-watermark' ) ?></option>
								</select>
								<a id="preview_button" class="button" href="#" title="<?php esc_html_e( 'Show an example', 'lazyest-watermark' ); ?>"><?php esc_html_e( 'Preview', 'lazyest-watermark' ); ?></a>
							</td>
						</tr>
						<tr id="position_tr">
							<th>
								<?php esc_html_e( 'Watermark position', 'lazyest-watermark' ) ?>
							</th>
							<td>
								<table id="watermark_position"<?php $this->_selected_td( 'coverimage' )?>>
									<tbody>
										<tr>
											<td<?php $this->_selected_td( 'topleft' )?>>																						
												<input type="radio" name="lazyest-watermark[position]" value="topleft" <?php checked( 'topleft', $options['position'] ); ?> />
												<?php esc_html_e( 'Top Left', 'lazyest-watermark' ); ?>
											</td>
											<td></td>
											<td<?php $this->_selected_td( 'topright' )?>>
												<input type="radio" name="lazyest-watermark[position]" value="topright" <?php checked( 'topright', $options['position'] ); ?> />																						
												<?php esc_html_e( 'Top Right', 'lazyest-watermark' ); ?>
											</td>
										</tr>
										<tr>
											<td<?php $this->_selected_td( 'middleleft' )?>>																						
												<input type="radio" name="lazyest-watermark[position]" value="middleleft" <?php checked( 'middleleft', $options['position'] ); ?> />
												<?php esc_html_e( 'Middle Left', 'lazyest-watermark' ); ?>
											</td>
											<td<?php $this->_selected_td( 'centered' )?>>
												<input type="radio" name="lazyest-watermark[position]" value="centered" <?php checked( 'centered', $options['position'] ); ?> />
												<?php esc_html_e( 'Centered', 'lazyest-watermark' ); ?>												
											</td>
											<td<?php $this->_selected_td( 'middleright' )?>>																						
												<input type="radio" name="lazyest-watermark[position]" value="middleright" <?php checked( 'middleright', $options['position'] ); ?> />
												<?php esc_html_e( 'Middle Right', 'lazyest-watermark' ); ?>
											</td>	
										</tr>
										<tr>
											<td<?php $this->_selected_td( 'bottomleft' )?>>
												<input type="radio" name="lazyest-watermark[position]" value="bottomleft" <?php checked( 'bottomleft', $options['position'] ); ?> />
												<?php esc_html_e( 'Bottom Left', 'lazyest-watermark' ); ?>
											</td>
											<td></td>
											<td<?php $this->_selected_td( 'bottomright' )?>>
												<input type="radio" name="lazyest-watermark[position]" value="bottomright" <?php checked( 'bottomright', $options['position'] ); ?> />
												<?php esc_html_e( 'Bottom Right', 'lazyest-watermark' ); ?>												
											</td>
										</tr>
									</tbody>
								</table>
								<div id="cover_image">
									<input type="radio" name="lazyest-watermark[position]" value="coverimage" <?php checked( 'coverimage', $options['position'] ); ?> />
									<?php esc_html_e( 'Cover Image', 'lazyest-watermark' ); ?>
								</div>
							</td>
						</tr>
						<tr>
							<th>
								<?php esc_html_e( 'Watermark transparency', 'lazyes-watermark' ); ?>
							</th>
							<td>
								<select id="watermark_transparency" name="lazyest-watermark[transparency]">
									<?php echo $transparency_select; ?>
								</select>
								<span class="description"><?php esc_html_e( 'Transparency from 0% (opaque) to 100% (invisible).', 'lazyest-watermark' );  ?></span>
							</td>
						</tr>
						<tr>
							<th>
								<?php esc_html_e( 'Watermark text', 'lazyest-watermark' ); ?>
							</th>
							<td>
								<textarea id="watermark_text" name="lazyest-watermark[text]"><?php echo $options['text']; ?></textarea>
							</td>
						</tr>
						<tr>
							<th>
								<?php esc_html_e( 'Text options', 'lazyest-watermark' ); ?> 
							</th>
							<td>
								<div id="text_options">
								<p id="font_size_p"<?php echo $font_size_style; ?>>
									<label><?php esc_html_e( 'Font size', 'lazyest-watermark' ); ?> 
										<select id="watermark_textsize" name="lazyest-watermark[textsize]">
											<?php echo $sizes_select; ?>
										</select><?php esc_html_e( 'pixels', 'lazyest-watermark' ); ?>
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Text color', 'lazyest-watermark' ) ?>
							  		<input class="colorpicker" type="text" maxlength="7" id="watermark_color" name="lazyest-watermark[color]" size="6" value="<?php echo $options['color']; ?>" />
							  		<div id="textcolorpicker"></div>
							  	</label>
								</p>
								</div>
							</td>
						</tr>
						<tr class="xtra_options" id="d_text_outline"<?php if ( 'd_text_outline' != $options['type'] ) echo ' style="display:none"' ?>>
							<th>
								<?php esc_html_e( 'Outlined text options', 'lazyest-watermark' ); ?>
							</th>
							<td>
								<p>
									<label><?php esc_html_e( 'Alignment', 'lazyest-watermark' ) ?>
										<select id="watermark_textalignment" name="lazyest-watermark[textalignment]">
											<option value="l" <?php selected( 'l', $options['textalignment'] ) ?>><?php esc_html_e( 'Left', 'lazyest-watermark' ) ?></option>
											<option value="h" <?php selected( 'h', $options['textalignment'] ) ?>><?php esc_html_e( 'Centered', 'lazyest-watermark' ) ?></option>
											<option value="r" <?php selected( 'r', $options['textalignment'] ) ?>><?php esc_html_e( 'Right', 'lazyest-watermark' ) ?></option>
										</select>
									</label>       
								</p>
								<p>
									<label><?php esc_html_e( 'Weight ', 'lazyest-watermark' ) ?>
										<select id="watermark_textweight" name="lazyest-watermark[textweight]">
											<option value="_" <?php selected( '_', $options['textweight'] ) ?>><?php esc_html_e( 'Normal', 'lazyest-watermark' ) ?></option>
											<option value="b" <?php selected( 'b', $options['textweight'] ) ?>><?php esc_html_e( 'Bold', 'lazyest-watermark' ) ?></option>
										</select>
									</label>
								</p>
								<p>
									<?php esc_html_e( 'Outline color ', 'lazyest-watermark' ) ?>
									<input class="colorpicker" type="text" maxlength="7" id="watermark_outlinecolor" name="lazyest-watermark[outlinecolor]" size="6" value="<?php echo $options['outlinecolor']; ?>" />
									<div id="outlinecolorpicker"></div> 									
								</p>
							</td>
						</tr>
						<tr class="xtra_options" id="d_simple_text_icon"<?php if ( 'd_simple_text_icon' != $options['type'] ) echo ' style="display:none"' ?>>
							<th>
								<?php esc_html_e( 'Text line with icon options', 'lazyest-watermark' ); ?>
							</th>
							<td>
								<p>
									<label><?php esc_html_e( 'Icon position', 'lazyest-watermark' ) ?>
										<select id="watermark_iconposition" name="lazyest-watermark[iconposition]">
											<option value="_below" <?php selected( '_below', $options['iconposition'] ) ?>><?php esc_html_e( 'Below text', 'lazyest-watermark' ) ?></option>
											<option value="_above" <?php selected( '_above', $options['iconposition'] ) ?>><?php esc_html_e( 'Above text', 'lazyest-watermark' ) ?></option>
											<option value="_left" <?php selected( '_left', $options['iconposition'] ) ?>><?php esc_html_e( 'Left of the text', 'lazyest-watermark' ) ?></option>
											<option value="_right" <?php selected( '_right', $options['iconposition'] ) ?>><?php esc_html_e( 'Right of the text', 'lazyest-watermark' ) ?></option>
										</select>
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Icon', 'lazyest-watermark' ); ?>
										<select id="watermark_iconname" name="lazyest-watermark[iconname]">
											<?php echo $icon_select; ?>
										</select>
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Icon size', 'lazyest-watermark' ) ?>
										<select id="watermark_iconsize" name="lazyest-watermark[iconsize]">
											<?php echo $iconsizes_select; ?>
										</select><?php esc_html_e( 'pixels', 'lazyest-watermark' ); ?>
									</label>
								</p>
								<p>
									<label><?php esc_html_e( 'Border color ', 'lazyest-watermark' ) ?>
									<input class="colorpicker"  type="text" maxlength="7" id="watermark_bordercolor" name="lazyest-watermark[bordercolor]" size="6" value="<?php echo $options['bordercolor']; ?>" />
									<div id="bordercolorpicker"></div> 		
									</label>
								</p>
							</td>
						</tr>
						<tr class="xtra_options" id="d_fnote"<?php if ( 'd_fnote' != $options['type'] ) echo ' style="display:none"' ?>>
							<th>
								<?php esc_html_e( 'Funny note options', 'lazyest-watermark' ); ?>
							</th>
							<td>
								<p>
									<label><?php esc_html_e( 'Size', 'lazyest-watermark' ); ?>
										<select id="watermark_notesize" name="lazyest-watermark[notesize]">
											<option value="1" <?php selected( '1', $options['notesize'] ) ?>><?php esc_html_e( 'Large', 'lazyest-watermark' ) ?></option>
											<option value="2" <?php selected( '2', $options['notesize'] ) ?>><?php esc_html_e( 'Small', 'lazyest-watermark' ) ?></option>
										</select>
									</label>
								</p>
								<p>	
									<label><?php esc_html_e( 'Type', 'lazyest-watermark' ); ?>
										<select id="watermark_notetype" name="lazyest-watermark[notetype]">
											<option value="arrow_d" <?php selected( 'arrow_d', $options['notetype'] ) ?>><?php esc_html_e( 'Down Arrow', 'lazyest-watermark' ) ?></option>
											<option value="balloon" <?php selected( 'balloon', $options['notetype'] ) ?>><?php esc_html_e( 'Comics Balloon', 'lazyest-watermark' ) ?></option>
											<option value="pinned_c" <?php selected( 'pinned_c', $options['notetype'] ) ?>><?php esc_html_e( 'Pinned Paper', 'lazyest-watermark' ) ?></option>
											<option value="sticky_y" <?php selected( 'sticky_y', $options['notetype'] ) ?>><?php esc_html_e( 'Sticky Note', 'lazyest-watermark' ) ?></option>
											<option value="taped_y" <?php selected( 'taped_y', $options['notetype'] ) ?>><?php esc_html_e( 'Taped Paper', 'lazyest-watermark' ) ?></option>
											<option value="thought" <?php selected( 'thought', $options['notetype'] ) ?>><?php esc_html_e( 'Thought Balloon', 'lazyest-watermark' ) ?></option>                        
										</select>
									</label>
								</p>												
							</td>
						</tr>
					</tbody>
				</table>
			</div>		
		</div>
		<?php
	}
	
	function sidebar( $settings ) {
  	global $wp_version;
		?>
    <?php if ( version_compare( $wp_version, '3.4', '<' ) ) : ?> 
	    <div id="side-info-column" class="inner-sidebar">
	  <?php else : ?>
	    <div id="postbox-container-1" class="postbox-container">
    <?php endif; ?>
      <div id="side-sortables" class="meta-box-sortables">
        <?php $settings->aboutbox(); ?>
        <?php $this->submitbox(); ?>
      </div>
    </div>
    <?php		
	}
	
	function submitbox() {
		?>
		<div id="submitdiv" class="postbox">
		<h3 class="hndle"><span><?php esc_html_e( 'Lazyest Watermark', 'lazyest-watermark' ) ?></span></h3>
		<div id="version" class="misc-pub-section">               
      <div class="versions">
        <p><span id="ls-version-message"><strong><?php echo esc_html_e( 'Version', 'lazyest-watermark' ); ?></strong> <?php echo lazyest_watermark()->version(); ?></span></p>
      </div>
    </div>
    <div class="misc-pub-section misc-pub-section-last">
      <p><a id="back_link" href="admin.php?page=lazyest-gallery" title="<?php esc_html_e( 'Back to Lazyest Gallery Settings', 'lazyest-watermark' ) ?>"><?php esc_html_e( 'Back to Lazyest Gallery Settings', 'lazyest-watermark' ) ?></a></p>            
    </div>     
		<div id="major-publishing-actions">       
      <div id="publishing-action">
        <input class="button-primary" type="submit" name="lazyest-watermark[update]" value="<?php	esc_html_e( 'Save Changes', 'lazyest-watermark' )	?>" />
      </div> 
      <div class="clear"></div>
    </div>
		</div>
		<?php
	}
	
} // LazyestWatermarkSettings
?>