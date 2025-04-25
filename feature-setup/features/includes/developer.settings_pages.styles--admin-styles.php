<?php defined('ABSPATH') or exit; ?>

<div class="wrap">
	<?php advset_page_header() ?>
	<?php if ($notice = get_option('advset_notice')) { ?>
		<div class="notice notice-<?php echo $notice['class'] ?> is-dismissible">
			<p><b><?php echo $notice['size'] ?> <?php _e( $notice['text'] ); ?></b><?php echo $notice['files'] ?></p>
		</div>
	<?php delete_option('advset_notice') ?>
	<?php } ?>

	<form action="options.php" method="post">

		<input type="hidden" name="advset_group" value="styles" />

		<?php settings_fields( 'advanced-settings' ); ?>

		<h2 class="title">Tracking styles &nbsp; <?php echo advset_page_experimental(); ?></h2>
		<p>
			<?php _e('Check the "Track enqueued styles" option and browse the website pages and refresh this page to show the captured styles.'); ?>
		</p>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e('Options'); ?></th>
				<td>
					<fieldset>
						
						<p>
							<label for="track_enqueued_styles">
								<input name="track_enqueued_styles" type="checkbox" id="track_enqueued_styles" value="1" <?php advset_check_if('track_enqueued_styles') ?> />
								<?php _e('Track enqueued styles') ?>
							</label>
						</p>

						<p>
							<label for="track_merge_removed_styles">
								<input name="track_merge_removed_styles" type="checkbox" id="track_merge_removed_styles" value="1" <?php advset_check_if('track_merge_removed_styles') ?> />
								<?php _e('Merge and include removed styles') ?>
							</label>
						</p>
						
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Tracked Styles <br /> <i style="color:#999">Check to remove styles</i>'); ?></th>
				<td>
					<fieldset>
						
						<p>
							<?php $tracked = get_option('advset_tracked_styles');
							if ($tracked) {
								echo '<fieldset>';
								foreach ($tracked as $script) {
									// print_r($script);
									if (!$script->ver) {
										$script->ver = '0';
									}

									$check_name = 'remove_enqueued_style_'.$script->handle;
									$cheked = advset_check_if($check_name, false);

									$src = (strpos($script->src, '/')===0? get_site_url() : '') . $script->src;

									// $css = file_get_contents($src);
									// $urlTest = strpos($css, 'url(')>-1;

									echo "<label style='width:100%; display:inline-block;' for='$check_name'> <input id='$check_name' name='$check_name' type='checkbox' style='float:left; margin-top:0' value='$script->handle' $cheked /> ";
									echo "<div style='overflow:auto'><b>$script->handle</b> ($script->ver)";
									if ($src) {
										echo "<br /><small>$src</small>";
									}
									if ($script->deps) {
										echo '<br /> <small style="color:#888">dependency: '.implode(', ', $script->deps).'</small>';
									}
									// if ($urlTest) {
									// 	$urls = preg_match_all('/url\([^\)]+\)/', $css, $matches);
									// 	$url_replacements = [];
									// 	foreach ($matches[0] as $match) {
									// 		if (!preg_match('/url\([^a-z]*(http|data)/i', $match)) {
									// 			$newUrl = preg_replace("/(url\(['\"]*)/", "$0".dirname($src).'/', $match);
									// 			$url_replacements[] = (object) ['old' => $match, 'new' => $newUrl];
									// 		}
									// 		else {
									// 			$newUrl = $match;
									// 		}
									// 	}
									// 	if (count($url_replacements))
									// 	{
									// 		echo '<br /> <small style="color:red">Image URL replaces:</small>';
									// 		foreach ($url_replacements as $url_replacement)
									// 		{
									// 			echo '<br /> <small> <small> &nbsp; &nbsp; &bull; '.esc_html($url_replacement->old).' &rsaquo; '.esc_html($url_replacement->new).'</small></small>';
									// 		}
									// 	}
									// }
									echo '</div></label>';
								}
								echo '</fieldset>';
							}
							else {
								echo '<i>No tracked styles yet. Try browsing your website.</i>';
							} ?>
						</p>
						
					</fieldset>
				</td>
			</tr>

		</table>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save changes') ?>"></p>
	</form>
</div>
