<?php defined('ABSPATH') or exit; ?>

<div class="wrap">
	<?php advset_page_header() ?>
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
				<th scope="row"><?php _e('Optimize'); ?></th>
				<td>
					<fieldset>
						<?php if (advset_option('show_deprecated_features') || advset_option('remove_default_wp_widgets')) { ?>
						<label for="remove_default_wp_widgets">
							<input name="remove_default_wp_widgets" type="checkbox" id="remove_default_wp_widgets" value="1" <?php advset_check_if('remove_default_wp_widgets') ?> />
							<s><?php _e('Unregister default WordPress widgets') ?></s> <?php echo advset_page_deprecated(); ?>
						</label>
						<br />
						<br />
						<?php } ?>

						<?php if (advset_option('show_deprecated_features') || advset_option('remove_widget_system')) { ?>
						<label for="remove_widget_system">
							<input name="remove_widget_system" type="checkbox" id="remove_widget_system" value="1" <?php advset_check_if('remove_widget_system') ?> />
							<s><?php _e('Disable widget system') ?></s> <?php echo advset_page_deprecated(); ?>
						</label>
						<br />
						<br />
						<?php } ?>

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
							<?php $already_has_thumbs = current_theme_supports('post-thumbnails') && !defined('ADVSET_THUMBS'); ?>
							<input name="add_thumbs" type="checkbox" id="add_thumbs" value="1" <?php advset_check_if( 'add_thumbs' ) ?> <?php echo $already_has_thumbs ? 'disabled' : '' ?> />
							<?php if ( $already_has_thumbs ) echo '<span style="color: #999;">'; ?>
								<?php _e('Add thumbnail support') ?>
							<?php if ( $already_has_thumbs ) echo '</span>'; ?>
							<?php if( $already_has_thumbs ) { ?>
								<i>[<?php _e('Already supported by current theme') ?>]</i>
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

			<tr valign="top">
				<th scope="row"><?php _e('Update Behavior'); ?></th>
				<td>
					<fieldset>

					<?php
					$digit1 = [
						'2' => 'Twenty',
						'3' => 'Thirty',
						'4' => 'Forty',
						'5' => 'Fifty',
						'6' => 'Sixty',
						'7' => 'Seventy',
						'8' => 'Eighty',
						'9' => 'Ninety',
					];
					$digit2 = [
						'0' => '',
						'1' => '-One',
						'2' => '-Two',
						'3' => '-Three',
						'4' => '-Four',
						'5' => '-Five',
						'6' => '-Six',
						'7' => '-Seven',
						'8' => '-Eight',
						'9' => '-Nine',
					];
					$current_year = date('y');
					$current_theme_slug = 'twenty' . strtolower($digit1[$current_year[0]] . substr($digit2[$current_year[1]], 1));
					$current_theme_name = 'Twenty ' . $digit1[$current_year[0]] . $digit2[$current_year[1]];
					$current_theme_url = 'https://wordpress.org/themes/' . $current_theme_slug . '/';
					?>
					<label for="core_upgrade_skip_new_bundled">
						<input name="core_upgrade_skip_new_bundled" type="checkbox" id="core_upgrade_skip_new_bundled" value="1" <?php advset_check_if('core_upgrade_skip_new_bundled') ?> />
						<?php _e('Prevent installation of new default WordPress themes during core updates') ?> <i style="color:#999">(<?php echo sprintf(__('By default, themes like %s are added automatically every year'), '<a href="' . $current_theme_url . '" target="_blank">' . $current_theme_name . '</a>') ?>)</i>
					</label>

					<br />

					<label for="prevent_auto_core_update_send_email">
						<input name="prevent_auto_core_update_send_email" type="checkbox" id="prevent_auto_core_update_send_email" value="1" <?php advset_check_if('prevent_auto_core_update_send_email') ?> />
						<?php _e('Prevent sending email notifications for core updates') ?> <i style="color:#999">(<?php echo sprintf(__('change recipient in %s'), '<a href="' . admin_url('options-general.php') . '">' . __('General Settings') . '</a>') ?>)</i>
					</label>

					<br />

					<label for="prevent_auto_plugin_update_send_email">
						<input name="prevent_auto_plugin_update_send_email" type="checkbox" id="prevent_auto_plugin_update_send_email" value="1" <?php advset_check_if('prevent_auto_plugin_update_send_email') ?> />
						<?php _e('Prevent sending email notifications for plugin updates') ?> <i style="color:#999">(<?php echo sprintf(__('change recipient in %s'), '<a href="' . admin_url('options-general.php') . '">' . __('General Settings') . '</a>') ?>)</i>
					</label>

					<br />

					<label for="prevent_auto_theme_update_send_email">
						<input name="prevent_auto_theme_update_send_email" type="checkbox" id="prevent_auto_theme_update_send_email" value="1" <?php advset_check_if('prevent_auto_theme_update_send_email') ?> />
						<?php _e('Prevent sending email notifications for theme updates') ?> <i style="color:#999">(<?php echo sprintf(__('change recipient in %s'), '<a href="' . admin_url('options-general.php') . '">' . __('General Settings') . '</a>') ?>)</i>
					</label>

					</fieldset>
				</td>
			</tr>

		</table>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save changes') ?>"></p>
	</form>
</div>
