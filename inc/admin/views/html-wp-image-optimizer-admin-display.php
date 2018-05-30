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
	<h2>WP Image Optimizer</h2>
	<?php 
        $total = $this->get_files_sum();
		if ($total > 0) {
			$optimized = $this->get_optimized_files_sum();
			$nonoptimized = $total - $optimized;
			$optimizedPercent = round($optimized / $total * 100,2);
		}
    ?>
    <div id="col-container">
        <div id="col-left">
            <div class="col-wrap">
                <div class="card">
                    <h3 class="title">Optimized Images</h3>
                    <div class="clear"></div>
                    <?php if ($total > 0) { ?>
                        <div class="c100 p<?php echo (int) $optimizedPercent; ?> big">
                            <span><?php echo $optimizedPercent; ?>%</span>
                            <div class="slice">
                                <div class="bar"></div>
                                <div class="fill"></div>
                            </div>
                        </div>
                        <div class="clear"></div>
                            <p><?php echo 'We found '. $total .' images in your media library.'; ?></p>
                            <p>
                            <?php  
                            if ($optimizedPercent != 100) echo 'You need to run the bulk optimizer as '. $nonoptimized .' files need an optimization.';
                            else echo "<b>Congratulations ! All images are optimised :)</b>"; 
                            ?>
                            </p>
                        <div class="clear"></div>
                        <div id="bulkOptimizeButtons">
                            <button id="bulkOptimizeAllFiles" type="button" class="button-secondary action">Optimize ALL files now</button>
                            <?php if ($optimizedPercent != 100) echo'<button id="bulkOptimizeFiles" type="button" class="button-secondary action">Optimize ALL unoptimized files now</button>'; ?>
                        </div>
                        <div id="bulkOptimize" style="display:none">
                            <div id="bulkOptimizeOutputProgress" class="meter">
                                <div class="percentHolder"><div id="bulkOptimizeOutputProgressPercent" class="showpercent"></div></div> 
                                <span style="width:0%"></span>
                            </div>
                            <p id="bulkOptimizeOutputNotice"></p>
                            <p><b> PLEASE NOTE : DO NOT CLOSE THIS WINDOW OR OPERATION WILL BE ABORTED</b></p>
                        </div>
                    
                    <?php } else echo "<p>No files detected yet !</p>"; ?>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div id="col-right">
			<div class="col-wrap">
				<div class="card">
                        <h3 class="title">Compatibility Check</h3>
                        <div class="clear"></div>
                        <?php if (!$this->opt_valid_os) {
							echo '<p><span class="dashicons dashicons-no"></span> We are sorry, your OS is not compatible with '. $this->plugin_name .'</p>';
						} else { ?>
                        	<ul>
								<li><span class="dashicons dashicons-yes"></span> Compatible OS detected : <?php echo PHP_OS; ?></li>
                                 <?php if ($this->opt_skip_check) { ?>
                                    <li><span class="dashicons dashicons-warning"></span> Littleutils check is disabled</li>
                                 <?php } else { ?>
                                    <li><?php echo $this->opt_gif  ? '<span class="dashicons dashicons-yes"></span> OPT-GIF is installed' : '<span class="dashicons dashicons-no"></span> OPT-GIF is missing'; ?></li>
                                    <li><?php echo $this->opt_png  ? '<span class="dashicons dashicons-yes"></span> OPT-PNG is installed' : '<span class="dashicons dashicons-no"></span> OPT-PNG is missing'; ?></li>
                                    <li><?php echo $this->opt_jpg  ? '<span class="dashicons dashicons-yes"></span> OPT-JPG is installed' : '<span class="dashicons dashicons-no"></span> OPT-JPG is missing'; ?></li>
                                <?php }?>
								<li><?php echo $this->opt_exec_enable  ? '<span class="dashicons dashicons-yes"></span> Exec is enabled' : '<span class="dashicons dashicons-no"></span> Exec is disabled'; ?></li>
								<li><?php echo function_exists('getimagesize')  ? '<span class="dashicons dashicons-yes"></span> getimagesize found' : '<span class="dashicons dashicons-no"></span> getimagesize php function is mssing'; ?></li>
								<li><?php echo function_exists('mime_content_type')  ? '<span class="dashicons dashicons-yes"></span> mime_content_type found' : '<span class="dashicons dashicons-no"></span> mime_content_type php function is mssing'; ?></li>
                            </ul>
						<?php } ?>
                        <div class="clear"></div>
				</div>
			</div>
        </div>
    </div>
    <div class="clear"></div>
    <div class="welcome-panel">
    	<h3 class="title">Optimizer Settings</h3>
        <div class="clear"></div>
        <p>WP Image Optimizer performs several checks to make sure your system is capable of optimizing images.</p>
        <p>In some cases, these checks may erroneously report that you are missing littleutils even though you have littleutils installed.</p>
		<form name="<?php echo $this->plugin_name; ?>_settings" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
            <p>Do you want to skip the littleutils check?</p>
            <input type="checkbox" id="<?php echo $this->plugin_name; ?>_skip_check" name="<?php echo $this->plugin_name; ?>_skip_check" value="true" <?php if(get_option($this->plugin_name .'_skip_check') == TRUE) : ?>checked="true"<?php endif; ?> /> <label for="<?php echo $this->plugin_name; ?>_skip_check" />Skip littleutils check</label><br />
            <p class="submit">
            
					<input type="hidden" name="action" value="optimizer_form_response">
					<?php wp_nonce_field( $this->plugin_name.'submit-form' ); ?>
					<input class="button button-primary" type="submit" id="<?php echo $this->plugin_name; ?>-submit" name="<?php echo $this->plugin_name; ?>-submit" value="<?php esc_attr_e('Save Settings', $this->plugin_name); ?>"/>
            </p>
        </form>
    	<div class="clear"></div>
    </div>
</div>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
