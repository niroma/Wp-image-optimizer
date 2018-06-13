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
	$total = $this->get_files_sum(true);
	?>
	<div class="wpio-container">
        
		<div class="flex-grid">
            <div class="col col-1">
                <div class="panel">
                    <h3 class="title"><?php _e( 'Optimized Images', $this->plugin_text_domain ); ?></h3>
                    <div class="clear"></div>
                    <?php if ($total > 0) { ?>
                    	<div id="alien" class="alien loading"></div>
                        <div class="clear"></div>
                        <p id="wpio_opti_row" class="wpio_text"></p>
                        <div class="clear"></div>
                        <div id="bulkOptimizeButtons" class="flex-grid">
                        	<div class="col">
                                <button id="bulkOptimizeAllFiles" type="button" class="button-secondary">
                                    <?php _e( 'Optimize ALL files now', $this->plugin_text_domain ); ?>
                                </button>
                            </div>
                        	<div id="bulkOptimizeFilesCol" class="col" style="display:none">
                                <button id="bulkOptimizeFiles" type="button" class="button-primary">
                                    <?php _e( 'Optimize UNOPTIMIZED files now', $this->plugin_text_domain  ); ?>
                                </button>
                            </div>
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
            <div class="col">
            	<div class="panel">
                	<h3 class="title"><?php _e( 'Love WP Image Optimizer ?', $this->plugin_text_domain ); ?></h3>
                    <div class="clear"></div>
                    <a href="https://wordpress.org/plugins/wp-image-optimizer" target="_blank"><?php _e( 'Give it 5 stars on WordPress.org !', $this->plugin_text_domain ); ?></a>
                </div>
            	<div class="clear"></div>
            	<div class="flex-grid">
                    <div class="col">
                        <div class="panel">
                            <h3 class="title"><?php _e( 'Stats', $this->plugin_text_domain ); ?></h3>
                            <div class="clear"></div>
                            <div class="flex-grid">
                                <div class="col stat">
                                    <div id="imagesFound" class="loading"><span class="bounceball"></span></div>
                                    <?php _e( 'images found', $this->plugin_text_domain ); ?>
                                </div>
                                <div class="col stat">
                                    <div id="imagesOpti" class="loading"><span class="bounceball"></span></div>
                                    <?php _e( 'optimized images', $this->plugin_text_domain ); ?>
                                </div>
                            </div>
                            <br />
                            <div class="flex-grid">
                                <div class="col stat">
                                    <div id="savedSpace" class="loading"><span class="bounceball"></span></div>
                                    <?php _e( 'disk space saved', $this->plugin_text_domain ); ?>
                                </div>
                                <div class="col stat">
                                    <div id="avgReduction" class="loading"><span class="bounceball"></span></div>
                                    <?php _e( 'average file size reduction', $this->plugin_text_domain ); ?>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                
                    <div class="col">
                        <div class="panel">
                                <h3 class="title"><?php _e( 'Compatibility Check', $this->plugin_text_domain ); ?></h3>
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
                                        <li><?php echo $this->opt_jpeg_recompress  ? '<span class="dashicons dashicons-yes"></span> JPEG_RECOMPRESS '. __( 'is installed', $this->plugin_text_domain ) : '<span class="dashicons dashicons-no"></span> JPEG_RECOMPRESS '. __( 'is missing', $this->plugin_text_domain ); ?></li>
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
    </div>
    <div class="clear"></div>
    <div class="flex-grid">
    	<div class="col">
            <div class="panel">
                <h3 class="title"><?php _e( 'Optimizer Settings', $this->plugin_text_domain ); ?></h3>
                <div class="clear"></div>
                    <form name="<?php echo $this->plugin_name; ?>_settings" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo $this->plugin_name; ?>_skip_check">
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
                                <label for="<?php echo $this->plugin_name; ?>_preserve_exif_datas">
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
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo $this->plugin_name; ?>_enable_lossy">
                                    <?php _e( 'Enable Lossy Compression ?', $this->plugin_text_domain ); ?>
                                </label>
                            </th>
                            <td>								
                                <div class="slider-checkbox">
                                    <input type="checkbox" id="<?php echo $this->plugin_name; ?>_enable_lossy" name="<?php echo $this->plugin_name; ?>_enable_lossy" value="true"<?php if(get_option($this->plugin_name .'_enable_lossy') == TRUE) : ?> checked="true" <?php endif;?> />
                                    <span class="label"><?php _e( 'Lossy Compression', $this->plugin_text_domain ); ?></span>
                                </div>
                                <p id="<?php echo $this->plugin_name; ?>_preserve_exif_datas-description" class="description">
                                    <?php _e( 'Lossy compression alter image quality but saves more space. If Lossy optimization fails, WP Image Optimiszer switches back to Lossless compression', $this->plugin_text_domain ); ?>
                                </p>	
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo $this->plugin_name; ?>_enable_lossy">
                                    <?php _e( 'Enable Cron ?', $this->plugin_text_domain ); ?>
                                </label>
                            </th>
                            <td>								
                                <div class="slider-checkbox">
                                    <input type="checkbox" id="<?php echo $this->plugin_name; ?>_enable_cron" name="<?php echo $this->plugin_name; ?>_enable_cron" value="true"<?php if(get_option($this->plugin_name .'_enable_cron') == TRUE) : ?> checked="true" <?php endif;?> />
                                    <span class="label"><?php _e( 'Enable cron task', $this->plugin_text_domain ); ?></span>
                                </div>
                                <p id="<?php echo $this->plugin_name; ?>_enable_cron-description" class="description">
                                    <?php _e( 'A cron job will be set to resize unoptimized images', $this->plugin_text_domain ); ?>
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
	</div>
</div>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
