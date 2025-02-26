<?php defined('ABSPATH') or exit;

	global $_wp_post_type_features;

	$post_types = get_post_types( '', 'objects' );

	unset( $post_types['attachment'], $post_types['revision'], $post_types['nav_menu_item'] );

	$adv_post_types = (array) get_option( 'adv_post_types', array() );

	?>
	<script>
	<?php echo 'posttype_data='.json_encode($post_types).';'; ?>
	<?php echo 'supports='.json_encode($_wp_post_type_features).';'; ?>
	show_form=function(type) {

		$=jQuery;

		if( type ) {

			data = posttype_data[type];

			data.type=type;
			populate( '#posttype_form', data );

			var checks = $('[name=supports\\[\\]]', '#posttype_form');
			for( i=0; i<checks.length;i++ )
				if( supports[type][$(checks[i]).attr('value')] )
					$(checks[i]).attr("checked", "checked");
				else
					$(checks[i]).attr("checked", null);

			var checks = $('[name=taxonomies\\[\\]]', '#posttype_form');
			for( i=0; i<checks.length;i++ )
				if( data['taxonomies'].join(',').indexOf($(checks[i]).attr('value'))>-1 )
					$(checks[i]).attr("checked", "checked");
				else
					$(checks[i]).attr("checked", null);

		}

		$('#post_type_list').hide();
		$('#post_type_form').fadeIn();
		//$('#post_type_form').reset(); // dont works
		$('#namefield').focus();
		return false;
	};
	populate=function (frm, data) {
		$=jQuery;
		$.each(data, function(key, value){
			var $ctrl = $('[name='+key+']', frm);
			switch($ctrl.attr("type")) {
				case "text" :
				case "hidden":
				case "textarea":
				$ctrl.val(value);
				break;
				case "radio" : case "checkbox":
				$ctrl.each(function(){
					if( $(this).attr('value') == value
						|| $(this).attr('name')=='query_var' && $(this).attr('value')!='' )
						$(this).attr("checked",value);
					else
						$(this).attr("checked",null);
				});
				break;
			}
		});
	};
	show_list=function() {
		$('#post_type_form').hide();
		$('#post_type_list').fadeIn();
	};
	str2slug=function(str) {
		var new_str = '';
		str=str.toLowerCase();
		str=str.replace(/[aáàãâä]/g,'a');
		str=str.replace(/[éèêë]/g,'e');
		str=str.replace(/[íìîï]/g,'i');
		str=str.replace(/[óòõôö]/g,'o');
		str=str.replace(/[úùûü]/g,'u');
		str=str.replace('ç','c');
		return str;
	};
	function in_array(needle, haystack) {
		var length = haystack.length;
		for(var i = 0; i < length; i++) {
			if(haystack[i] == needle) return true;
		}
		return false;
	}
	</script>

	<div class="wrap">

		<div id="icon-options-general" class="icon32"><br></div>
		<h2><?php _e('Advanced Settings &rsaquo; Post Types'); ?> <sub style="color:red">beta</sub>
			<a href="#" onclick="return show_form();" class="add-new-h2">Add New Post Type</a>
			</h2>

		<div id="post_type_form" style="display:none">

			<h3>Add New Post Type</h3>

			<form id="posttype_form" action="" method="post">
				<?php #settings_fields( 'advanced-settings-post-types' ); ?>

				<input type="hidden" name="advset_action_posttype" value="1" />

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('Label'); ?></th>
						<td>
							<input id="namefield" name="label" type="text" value="" onblur="
							input = jQuery('#typefield');
							if( input.val()=='' )
								input.val(str2slug(this.value));
							" />

							<!--p><a href="#">+ show more labels</a></p-->

						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e('Type Name'); ?></th>
						<td>
							<input id="typefield" name="type" type="text" value="" />
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e('Supports'); ?></th>
						<td>
							<input name="supports[]" id="posttype-support-title" value="title" type="checkbox" checked="checked">
							<label for="posttype-support-title">title</label><br />
							<input name="supports[]" id="posttype-support-editor" value="editor" type="checkbox" checked="checked">
							<label for="posttype-support-editor">editor</label><br />
							<input name="supports[]" id="posttype-support-author" value="author" type="checkbox">
							<label for="posttype-support-author">author</label><br />
							<input name="supports[]" id="posttype-support-thumbnail" value="thumbnail" type="checkbox">
							<label for="posttype-support-thumbnail">thumbnail</label><br />
							<input name="supports[]" id="posttype-support-excerpt" value="excerpt" type="checkbox">
							<label for="posttype-support-excerpt">excerpt</label><br />
							<input name="supports[]" id="posttype-support-trackbacks" value="trackbacks" type="checkbox">
							<label for="posttype-support-trackbacks">trackbacks</label><br />
							<input name="supports[]" id="posttype-support-custom-fields" value="custom-fields" type="checkbox">
							<label for="posttype-support-custom-fields">custom fields</label><br />
							<input name="supports[]" id="posttype-support-comments" value="comments" type="checkbox">
							<label for="posttype-support-comments">comments</label><br />
							<input name="supports[]" id="posttype-support-revisions" value="revisions" type="checkbox">
							<label for="posttype-support-revisions">revisions</label> <br />
							<input name="supports[]" id="posttype-support-page-attributes" value="page-attributes" type="checkbox">
							<label for="posttype-support-page-attributes">page attributes</label>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e('Settings'); ?></th>
						<td>
							<label><input name="public" value="1" type="checkbox" checked="checked">
								public</label><br />
							<label><input name="publicly_queryable" value="1" type="checkbox" checked="checked">
								publicly_queryable</label><br />
							<label><input name="show_ui" value="1" type="checkbox" checked="checked">
								show_ui</label><br />
							<label><input name="show_in_menu" value="1" type="checkbox" checked="checked">
								show_in_menu</label><br />
							<label><input name="query_var" value="1" type="checkbox" checked="checked">
								query_var</label><br />
							<!--label><input name="rewrite" value="1" type="checkbox">
								rewrite</label><br /-->
							<!--label><input name="capability_type" value="1" type="checkbox">
								capability_type</label><br /-->
							<label><input name="has_archive" value="1" type="checkbox">
								has_archive</label><br />
							<label><input name="hierarchical" value="1" type="checkbox">
								hierarchical</label> <br />
							<!--label><input name="menu_position" value="1" type="checkbox">
								menu_position</label-->
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e('Taxonomies'); ?></th>
						<td>
							<label><input name="taxonomies[]" value="category" type="checkbox" checked="checked">
								category</label><br />
							<label><input name="taxonomies[]" value="post_tag" type="checkbox" checked="checked">
								post_tag</label>
						</td>
					</tr>

				</table>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes') ?>" />
					<input type="button" onclick="show_list();" class="button" value="<?php _e('Cancel') ?>" />
				</p>
			</form>
		</div>

		<form id="post_type_list" action="options.php" method="post">

			<table class="widefat fixed" cellspacing="0">
			  <thead>
				<tr>
				  <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><label class="screen-reader-text" for="cb-select-all-1">Selecionar Tudo</label><input id="cb-select-all-1" type="checkbox"></th>
				  <th scope="col" id="title" class="manage-column column-title" width="40%">Label <small>(Menu name)</small></th>
				  <th scope="col" id="type_name" class="manage-column column-title" width="30%">Type</th>
				  <th scope="col" id="type_desc" class="manage-column column-title" width="30%">Description</th>
				</tr>
			  </thead>
				<?php foreach($post_types as $typename=>$post_type) { ?>
				<tr class=" iedit">
					<th scope="row" class="check-column">

						<input id="cb-select-1" type="checkbox" name="post[]" value="1">
						<div class="locked-indicator"></div>
									</th>
				  <td>
					<strong><?php echo $post_type->label ?></strong>
					<div class="row-actions">
						<?php if( !in_array( $post_type->name, array('post', 'page') ) ) { ?>
					  <span class="edit">
						<a href="#" onclick="show_form('<?php echo $post_type->name ?>');">Edit</a>
					  </span>
							| <a href="options-general.php?page=post-types&delete_posttype=<?php echo $post_type->name ?>" title="default categories">delete</a>
						<?php } else echo '&nbsp;'; ?>

					</div>
				  </td>
				  <td><?php echo $post_type->name ?></td>
				  <td></td>
				</tr>
				<?php } ?>

			  <tfoot>
				<tr>
				  <th scope="col" class="manage-column column-cb check-column" style=""><label class="screen-reader-text" for="cb-select-all-2">Selecionar Tudo</label><input id="cb-select-all-2" type="checkbox"></th>
				  <th scope="col" id="title" class="manage-column column-title" width="40%">Label <small>(Menu name)</small></th>
				  <th scope="col" id="type_name" class="manage-column column-title" width="30%">Type</th>
				  <th scope="col" id="type_desc" class="manage-column column-title" width="30%">Description</th>
				</tr>
			  </tfoot>

			</table>

			<!--p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save changes') ?>"></p-->
		</form>
	</div>
