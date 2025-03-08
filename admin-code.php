<?php defined('ABSPATH') or exit; ?>

<div class="wrap">
	<?php advset_page_header() ?>
	<form action="options.php" method="post">

		<input type="hidden" name="advset_group" value="code" />

		<?php settings_fields( 'advanced-settings' ); ?>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e('Header'); ?></th>
				<td>
					<fieldset>
						<label for="facebook_og_metas">
							<input name="facebook_og_metas" type="checkbox" id="facebook_og_metas" value="1" <?php advset_check_if('facebook_og_metas') ?>>
							<?php _e('Fix incorrect Facebook thumbnails including OG metas') ?> 
						</label>

						<br />
						<label for="remove_menu">
							<input name="remove_menu" type="checkbox" id="remove_menu" value="1" <?php advset_check_if('remove_menu') ?>>
							<?php _e('Hide top admin menu') ?> 
						</label>

						<br />
						<label for="favicon">
							<input name="favicon" type="checkbox" id="favicon" value="1" <?php advset_check_if('favicon') ?> />
							<?php _e('Automatically add a FavIcon') ?> <i style="color:#999">(<?php _e('whenever there is a favicon.ico, favicon.png or favicon.svg file in the theme folder') ?>)</i></label>
						</label>

						<br />
						<label for="description">
							<input name="description" type="checkbox" id="description" value="1" <?php advset_check_if('description') ?> />
							<?php _e('Add a description meta tag using the blog description') ?> (SEO)
						</label>

						<br />
						<label for="single_metas">
							<input name="single_metas" type="checkbox" id="single_metas" value="1" <?php advset_check_if('single_metas') ?> />
							<?php _e('Add description and keywords meta tags in each posts') ?> (SEO)
						</label>

						<br />
						<label for="remove_generator">
							<input name="remove_generator" type="checkbox" id="remove_generator" value="1" <?php advset_check_if('remove_generator') ?> />
							<?php _e('Remove header WordPress generator meta tag') ?>
						</label>

						<br />
						<br />
						<label for="remove_wlw">
							<input name="remove_wlw" type="checkbox" id="remove_wlw" value="1" <?php advset_check_if('remove_wlw') ?> />
							<s><?php _e('Remove header WLW Manifest meta tag (Windows Live Writer link)'); echo '</s>' . advset_page_deprecated(); ?>
						</label>

						<br />
						<br />
						<label for="remove_rsd">
							<input name="remove_rsd" type="checkbox" id="remove_rsd" value="1" <?php advset_check_if('remove_rsd') ?> />
							<?php _e('Remove header RSD (Weblog Client Link) meta tag') ?>
						</label>

						<br />
						<label for="remove_shortlink">
							<input name="remove_shortlink" type="checkbox" id="remove_shortlink" value="1" <?php advset_check_if('remove_shortlink') ?> />
							<?php _e('Remove header shortlink meta tag') ?>
						</label>

						<br />
						<label for="config_wp_title">
							<input name="config_wp_title" type="checkbox" id="config_wp_title" value="1" <?php advset_check_if('config_wp_title') ?> />
							<?php _e('Configure site title to use just the wp_title() function') ?> <i style="color:#999">(<?php _e('better for hardcode programming') ?>)</i>
						</label>
						<!--p class="description"><?php _e('better for hardcode programming') ?></p-->
					
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Content'); ?></th>
				<td>
					<fieldset>

						<label for="excerpt_limit">
							<?php _e('Limit the excerpt length to') ?>
							<input name="excerpt_limit" type="text" size="2" maxlength="10" id="excerpt_limit" value="<?php echo (int) advset_option( 'excerpt_limit' ) ?>" />
							<?php _e('words') ?>
						</label>

						<br />
						<label for="excerpt_more_text">
							<?php _e('Add a read more link after excerpt with the text: ') ?>
							<input name="excerpt_more_text" type="text" size="10" id="excerpt_more_text" value="<?php echo advset_option( 'excerpt_more_text', '' ) ?>" />
						</label>

						<br />
						<label for="remove_wptexturize">
							<input name="remove_wptexturize" type="checkbox" id="remove_wptexturize" value="1" <?php advset_check_if('remove_wptexturize') ?> />
							<?php _e('Remove wptexturize filter') ?> <i style="color:#999">(<?php _e('transformations of quotes to smart quotes, apostrophes, dashes, ellipses, the trademark symbol, and the multiplication symbol') ?>)</i>
						</label>

					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Comments'); ?></th>
				<td>
					<fieldset>
						<label for="remove_pingbacks_trackbacks_count">
							<input name="remove_pingbacks_trackbacks_count" type="checkbox" id="remove_pingbacks_trackbacks_count" value="1" <?php advset_check_if('remove_pingbacks_trackbacks_count') ?> />
							<?php _e('Remove Trackbacks and Pingbacks from Comment Count') ?>
						</label>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Author Bio'); ?></th>
				<td>
					<fieldset>
						<label for="author_bio">
							<input name="author_bio" type="checkbox" id="author_bio" value="1" <?php advset_check_if('author_bio') ?> />
							<?php _e('Insert author bio in each post') ?>
						</label>

						<br />

						<label for="author_bio_html">
							<input name="author_bio_html" type="checkbox" id="author_bio_html" value="1" <?php advset_check_if('author_bio_html') ?> />
							<?php _e('Allow more complex HTML in user profile') ?>
						</label>

					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Optimize'); ?></th>
				<td>
					<fieldset>
						<label for="compress">
							<input name="compress" type="checkbox" id="compress" value="1" <?php advset_check_if('compress') ?> />
							<?php _e('Compress all code') ?>
						</label>

						<br />
						<label for="remove_comments">
							<input name="remove_comments" type="checkbox" id="remove_comments" value="1" <?php advset_check_if('remove_comments') ?> />
							<?php _e('Remove HTML comments') ?> <i style="color:#999">(<?php _e('it doesn\'t remove conditional IE comments like') ?>: &lt;!--[if IE]&gt;)</i>
						</label>

					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('Google Analytics ID'); ?> <br />
					<i style="color:#999"><?php _e('inserts a javascript code in the footer') ?></i>
				</th>
				<td>
					<fieldset>
						<label for="analytics">
							<input name="analytics" type="text" size="12" id="analytics" value="<?php echo advset_option('analytics') ?>" />
						</label>

					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('FeedBurner'); ?></th>
				<td>
					<fieldset>
						<label for="feedburner">
							<input name="feedburner" type="text" size="12" id="feedburner" value="<?php echo advset_option('feedburner') ?>" />
						</label>
					</fieldset>
				</td>
			</tr>

		</table>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save changes') ?>"></p>
	</form>
</div>
