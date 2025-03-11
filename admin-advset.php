<?php defined('ABSPATH') or exit; ?>

<div class="wrap">
	<?php advset_page_header() ?>
	<form action="options.php" method="post">

		<input type="hidden" name="advset_group" value="advset" />

		<?php settings_fields( 'advanced-settings' ); ?>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e('Advanced Settings Plugin Configuration'); ?></th>
				<td>
					<fieldset>
						<label for="show_deprecated_features">
							<input name="show_deprecated_features" type="checkbox" id="show_deprecated_features" value="1" <?php advset_check_if('show_deprecated_features') ?>>
							<?php _e('Show all deprecated features') ?> 
                            <i style="color:#999">(<?php _e('By default, only deprecated features that are in use are shown.') ?>)</i>
						</label>

						<br />
						<label for="advset_tracksettings_choice">
							<?php _e('Agree to let us know which features of the plugin you use?') ?>
                            <select name="advset_tracksettings_choice" id="advset_tracksettings_choice">
                                <option></option>
                                <option value="1"<?php echo advset_option('advset_tracksettings_choice') === '1' ? ' selected' : '' ?>>yes</option>
                                <option value="0"<?php echo advset_option('advset_tracksettings_choice') === '0' ? ' selected' : '' ?>>no</option>
                            </select>
						</label>
					</fieldset>
				</td>
			</tr>

		</table>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save changes') ?>"></p>
	</form>
</div>
