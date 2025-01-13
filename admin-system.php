<?php defined('ABSPATH') or exit; ?>

<div class="wrap">

	<?php advset_powered(); ?>

	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php _e('Advanced Settings &rsaquo; System'); ?></h2>

	<form action="options.php" method="post">

		<input type="hidden" name="advset_group" value="system" />

		<?php settings_fields( 'advanced-settings' ); ?>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e('Dashboard'); ?></th>
				<td>
					<fieldset>

						<p>
							<label for="hide_update_message">
								<input name="hide_update_message" type="checkbox" id="hide_update_message" value="1" <?php advset_check_if('hide_update_message') ?> />
								<?php _e('Hide the WordPress update message in the Dashboard') ?>
							</label>
						</p>

						<p>
							<label for="dashboard_logo">
								<input name="dashboard_logo" type="text" size="50" placeholder="<?php _e('https://www.example.com/your-custom-logo.png') ?>" id="dashboard_logo" value="<?php echo advset_option('dashboard_logo') ?>" />
								<i style="color:#999">(<?php _e('paste your custom dashboard logo here') ?>)</i>
							</label>
						</p>

					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('Optimize'); ?> <br />
					<i style="color:#999"><?php _e('removing some SQL queries can do the database work faster') ?></i>
				</th>
				<td>
					<fieldset>
						<label for="remove_default_wp_widgets">
							<input name="remove_default_wp_widgets" type="checkbox" id="remove_default_wp_widgets" value="1" <?php advset_check_if('remove_default_wp_widgets') ?> />
							<?php _e('Unregister default WordPress widgets') ?>
						</label>

						<br />

						<label for="remove_widget_system">
							<input name="remove_widget_system" type="checkbox" id="remove_widget_system" value="1" <?php advset_check_if('remove_widget_system') ?> />
							<?php _e('Disable widget system') ?>
						</label>

						<br />

						<label for="remove_comments_system">
							<input name="remove_comments_system" type="checkbox" id="remove_comments_system" value="1" <?php advset_check_if('remove_comments_system') ?> /> <?php _e('Disable comment system') ?>
						</label>

						<br />

						<label for="disable_auto_save">
							<input name="disable_auto_save" type="checkbox" id="disable_auto_save" value="1" <?php advset_check_if('disable_auto_save') ?> />
							<?php _e('Disable Posts Auto Saving') ?>
						</label>

						<br />

						<label for="disable_author_pages">
							<input name="disable_author_pages" type="checkbox" id="disable_author_pages" value="1" <?php advset_check_if('disable_author_pages') ?> />
							<?php _e('Disable Author Pages') ?>
						</label>

					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Images'); ?></th>
				<td>
					<fieldset>

						<label for="add_thumbs">
							<?php
							if( current_theme_supports('post-thumbnails') && !defined('ADVSET_THUMBS') ) {
								echo '<i style="color:#999">['.__('Current theme already has post thumbnail support').']</i>';
							} else {
									?>
								<input name="add_thumbs" type="checkbox" id="add_thumbs" value="1" <?php advset_check_if( 'add_thumbs' ) ?> />
								<?php _e('Add thumbnail support') ?>
							<?php } ?>
						</label>

						<p>
						<label for="auto_thumbs">
							<input name="auto_thumbs" type="checkbox" id="auto_thumbs" value="1" <?php advset_check_if( 'auto_thumbs' ) ?> />
							<?php _e('Automatically generate the Post Thumbnail') ?> <i style="color:#999">(<?php _e('from the first image in post') ?>)</i>
						</label>

						<p>
						<label for="jpeg_quality">
							<?php _e('Set JPEG quality to') ?> <input name="jpeg_quality" type="text" size="2" maxlength="3" id="jpeg_quality" value="<?php echo (int) advset_option( 'jpeg_quality', 0) ?>" /> <i style="color:#999">(<?php _e('when send and resize images') ?>)</i>
						</label>

						<p>

							<strong><?php _e('Resize image at upload to max size') ?>:</strong>

							<ul>
								<li>
									<label for="max_image_size_w">
									&nbsp; &nbsp; &bull; <?php _e('width') ?> (px) <input name="max_image_size_w" type="text" size="3" maxlength="5" id="max_image_size_w" value="<?php echo (int) advset_option( 'max_image_size_w', 0) ?>" />
										<i style="color:#999">(<?php _e('if zero resize to max height or dont resize if both is zero') ?>)</i></label>
									<label for="max_image_size_h">
								</li>
								<li>
									&nbsp; &nbsp; &bull; <?php _e('height') ?> (px) <input name="max_image_size_h" type="text" size="3" maxlength="5" id="max_image_size_h" value="<?php echo (int) advset_option( 'max_image_size_h', 0) ?>" />
										<i style="color:#999">(<?php _e('if zero resize to max width or dont resize if both is zero') ?>)</i></label>
								</li>
							</ul>
						</p>

					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('System'); ?></th>
				<td>
					<fieldset>

					<?php /*if( !defined('EMPTY_TRASH_DAYS') ) { ?>
					<label for="empty_trash">
						<?php _e('Posts stay in the trash for ') ?>
						<input name="empty_trash" type="text" size="2" id="empty_trash" value="<?php echo advset_option('empty_trash') ?>" />
						<?php _e('days') ?> <i style="color:#999">(<?php _e('To disable trash set the number of days to zero') ?>)</i>
						</label>

					<br />
					<? } else echo EMPTY_TRASH_DAYS;*/ ?>

					<label for="core_upgrade_skip_new_bundled">
						<input name="core_upgrade_skip_new_bundled" type="checkbox" id="core_upgrade_skip_new_bundled" value="1" <?php advset_check_if('core_upgrade_skip_new_bundled') ?> />
						<?php _e('Prevent installation of new default WordPress themes during core updates') ?>
					</label>

					<br />

					<label for="show_query_num">
						<input name="show_query_num" type="checkbox" id="show_query_num" value="1" <?php advset_check_if('show_query_num') ?> />
						<?php _e('Display total number of executed SQL queries and page loading time <i style="color:#999">(only admin users can see this)') ?></i>
					</label>

					<!--br />
					<label for="post_type_pag">
						<input name="post_type_pag" type="checkbox" id="post_type_pag" value="1" <?php // advset_check_if('post_type_pag') ?> />
						<?php // _e('Fix post type pagination') ?>
					</label-->

					</fieldset>
				</td>
			</tr>

		</table>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save changes') ?>"></p>
	</form>
</div>
