<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.niroma.net
 * @since      1.0.0
 *
 * @author    Niroma
 */

?>
<div class="wrap"> 
	<h2><?php _e( 'WP Image Optimizer', $this->plugin_text_domain ); ?></h2>
	<?php 
        $total = $this->get_files_sum();
		if ($total > 0) {
			$optimized = $this->get_optimized_files_sum();
			$nonoptimized = $total - $optimized;
			$optimizedPercent = round($optimized / $total * 100,2);
		}
    ?>
    <div id="col-container">
    	<?php 	/*global $wpdb;	
		
		/*$attachments = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )  WHERE ({$wpdb->posts}.post_mime_type LIKE 'image%' AND  {$wpdb->posts}.post_type = 'attachment') AND ({$wpdb->postmeta}.meta_key = 'is_optimized' AND CAST({$wpdb->postmeta}.meta_value AS CHAR) NOT IN ('1')) OR {$wpdb->postmeta}.meta_key != 'is_optimized';"); */
		/*$attachments = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ) WHERE {$wpdb->posts}.post_mime_type LIKE 'image%' AND  {$wpdb->posts}.post_type = 'attachment' AND ( {$wpdb->postmeta}.meta_key != 'is_optimized' OR ({$wpdb->postmeta}.meta_key = 'is_optimized' AND CAST({$wpdb->postmeta}.meta_value AS CHAR) NOT IN ('1')));"); 
		var_dump($attachments);*/
		?>
        
        <div id="col-left">
            <div class="col-wrap">
                <div class="card">
                    <h3 class="title"><?php _e( 'Optimized Images', $this->plugin_text_domain ); ?></h3>
                    <div class="clear"></div>
                    <?php if ($total > 0) { ?>
                        <div id="percentCircle" class="c100 p<?php echo (int) $optimizedPercent; ?> big">
                            <span id="percentCircleValue"><?php echo $optimizedPercent; ?>%</span>
                            <div class="slice">
                                <div class="bar"></div>
                                <div class="fill"></div>
                            </div>
                        </div>
                        <div class="clear"></div>
                            <p><?php echo __( 'We found', $this->plugin_text_domain ) .' '. $total .' '.  __( 'images in your media library', $this->plugin_text_domain ); ?></p>
                            <p>
                            <?php  
                            if ($optimizedPercent != 100) echo '<p id="wpio-nonopti-row">'. __( 'You need to run the bulk optimizer as', $this->plugin_text_domain ) .' <span id="wpio-nonopti">'. $nonoptimized .'</span> '. __( 'files need an optimization.', $this->plugin_text_domain ) .'</p>';
                            else echo "<b>". __( 'Congratulations ! All images are optimised', $this->plugin_text_domain ) ." :)</b>"; 
                            ?>
                            </p>
                        <div class="clear"></div>
                        <div id="bulkOptimizeButtons">
                            <button id="bulkOptimizeAllFiles" type="button" class="button-secondary action"><?php _e( 'Optimize ALL files now', $this->plugin_text_domain ); ?></button>
                            <?php if ($optimizedPercent != 100) echo'<button id="bulkOptimizeFiles" type="button" class="button-primary action">'. __( 'Optimize ALL unoptimized files now', $this->plugin_text_domain ) .'</button>'; ?>
                        </div>
                        <div id="bulkOptimize" style="display:none">
                            <div id="bulkOptimizeOutputProgress" class="meter">
                                <div class="percentHolder"><div id="bulkOptimizeOutputProgressPercent" class="showpercent"></div></div> 
                                <span style="width:0%"></span>
                            </div>
                            <p id="bulkOptimizeOutputNotice"></p>
                            <p id="bulkOptimizeWarning"><b> <?php _e( 'PLEASE NOTE : DO NOT CLOSE THIS WINDOW OR OPERATION WILL BE ABORTED', $this->plugin_text_domain ); ?></b></p>
                        </div>
                    
                    <?php } else echo "<p>No files detected yet !</p>"; ?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div id="col-right">
			<div class="col-wrap">
				<div class="card">
                        <h3 class="title"> <?php _e( 'Compatibility Check', $this->plugin_text_domain ); ?></h3>
                        <div class="clear"></div>
                        <?php if (!$this->opt_valid_os) {
							echo '<p><span class="dashicons dashicons-no"></span> '. __( 'We are sorry, your OS is not compatible with', $this->plugin_text_domain ) .' '. $this->plugin_name .'</p>';
						} else { ?>
                        	<ul>
								<li><span class="dashicons dashicons-yes"></span> <?php _e( 'Compatible OS detected', $this->plugin_text_domain ); ?> : <?php echo PHP_OS; ?></li>
                                 <?php if ($this->opt_skip_check) { ?>
                                    <li><span class="dashicons dashicons-warning"></span> <?php _e( 'Littleutils check is disabled', $this->plugin_text_domain ); ?></li>
                                 <?php } else { ?>
                                    <li><?php echo $this->opt_gif  ? '<span class="dashicons dashicons-yes"></span> OPT-GIF '. __( 'is installed', $this->plugin_text_domain ) : '<span class="dashicons dashicons-no"></span> OPT-GIF '. __( 'is missing', $this->plugin_text_domain ); ?></li>
                                    <li><?php echo $this->opt_png  ? '<span class="dashicons dashicons-yes"></span> OPT-PNG '. __( 'is installed', $this->plugin_text_domain ) : '<span class="dashicons dashicons-no"></span> OPT-PNG '. __( 'is missing', $this->plugin_text_domain ); ?></li>
                                    <li><?php echo $this->opt_jpg  ? '<span class="dashicons dashicons-yes"></span> OPT-JPG '. __( 'is installed', $this->plugin_text_domain ) : '<span class="dashicons dashicons-no"></span> OPT-JPG '. __( 'is missing', $this->plugin_text_domain ); ?></li>
                                <?php }?>
								<li><?php echo $this->opt_exec_enable  ? '<span class="dashicons dashicons-yes"></span> Exec '. __( 'is enabled', $this->plugin_text_domain ) : '<span class="dashicons dashicons-no"></span> Exec '. __( 'is disabled', $this->plugin_text_domain ) ; ?></li>
								<li><?php echo function_exists('getimagesize')  ? '<span class="dashicons dashicons-yes"></span> getimagesize '. __( 'found', $this->plugin_text_domain ) : '<span class="dashicons dashicons-no"></span> getimagesize '. __( 'is missing', $this->plugin_text_domain ); ?></li>
								<li><?php echo function_exists('mime_content_type')  ? '<span class="dashicons dashicons-yes"></span> mime_content_type '. __( 'found', $this->plugin_text_domain ) : '<span class="dashicons dashicons-no"></span> mime_content_type '. __( 'is missing', $this->plugin_text_domain ); ?></li>
                            </ul>
						<?php } ?>
                        <div class="clear"></div>
				</div>
			</div>
        </div>
    </div>
    <div class="clear"></div>
    <div class="welcome-panel">
    	<h3 class="title"><?php _e( 'Optimizer Settings', $this->plugin_text_domain ); ?></h3>
        <div class="clear"></div>
        	<form name="<?php echo $this->plugin_name; ?>_settings" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="<?php echo $this->plugin_name; ?>_skip_check" />
							<?php _e( 'Skip littleutils check ?', $this->plugin_text_domain ); ?>
                        </label>
                    </th>
                    <td>								
                        <div class="slider-checkbox">
                            <input type="checkbox" id="<?php echo $this->plugin_name; ?>_skip_check" name="<?php echo $this->plugin_name; ?>_skip_check" value="true"<?php if(get_option($this->plugin_name .'_skip_check') == TRUE) : ?> checked="true" <?php endif;?> />
                            <span class="label"><?php _e( 'Disable littleutils check', $this->plugin_text_domain ); ?></span>
  						</div>
                        <p  id="<?php echo $this->plugin_name; ?>_skip_check-description" class="description">
							<?php _e( 'WP Image Optimizer performs several checks to make sure your system is capable of optimizing images.', $this->plugin_text_domain ); ?><br  />
							<?php _e( 'In some cases, these checks may erroneously report that you are missing littleutils even though you have littleutils installed.', $this->plugin_text_domain ); ?>
                        </p>	
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="<?php echo $this->plugin_name; ?>_preserve_exif_datas" />
							<?php _e( 'Preserve EXIF datas ?', $this->plugin_text_domain ); ?>
                        </label>
                    </th>
                    <td>								
                        <div class="slider-checkbox">
                            <input type="checkbox" id="<?php echo $this->plugin_name; ?>_preserve_exif_datas" name="<?php echo $this->plugin_name; ?>_preserve_exif_datas" value="true"<?php if(get_option($this->plugin_name .'_preserve_exif_datas') == TRUE) : ?> checked="true" <?php endif;?> />
                            <span class="label"><?php _e( 'Preserve exif datas', $this->plugin_text_domain ); ?></span>
  						</div>
                        <p id="<?php echo $this->plugin_name; ?>_preserve_exif_datas-description" class="description">
							<?php _e( 'Preserving exif datas will increase file size. Only available for jpg files', $this->plugin_text_domain ); ?>
                        </p>	
                    </td>
                </tr>
            </table>
            
            <p class="submit">
					<input type="hidden" name="action" value="optimizer_form_response">
					<?php wp_nonce_field( $this->plugin_name.'submit-form' ); ?>
					<input class="button button-primary" type="submit" id="<?php echo $this->plugin_name; ?>-submit" name="<?php echo $this->plugin_name; ?>-submit" value="<?php esc_attr_e('Save Settings', $this->plugin_text_domain); ?>"/>
            </p>
        </form>
    	<div class="clear"></div>
    </div>
</div>
<?php //phpinfo(); ?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
