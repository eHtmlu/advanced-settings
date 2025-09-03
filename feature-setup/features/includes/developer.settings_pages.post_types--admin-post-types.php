<?php

if (!defined('ABSPATH')) exit;


$advset_posttype_nonce = wp_create_nonce('advset_posttype_nonce');

$advset_posttypes_data = get_option('advset_post_types', []);

$advset_posttypes_data_default = [
    'supports' => [
        'title',
        'editor',
    ],
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'has_archive' => false,
    'hierarchical' => false,
    'taxonomies' => [
        'category',
        'post_tag',
    ],
];




?>
<script>

document.addEventListener('DOMContentLoaded', function() {
    const formContainerElement = document.getElementById('advset_posttype_form_container');
    const listContainerElement = document.getElementById('advset_posttype_list_container');
    const formElement = formContainerElement?.querySelector('#advset_posttype_form');
    const labelInputElement = formContainerElement?.querySelector('[name="label"]');
    const typeInputElement = formContainerElement?.querySelector('[name="type"]');
    const typeStoredInputElement = formContainerElement?.querySelector('[name="type_stored"]');
    const typeAvailableIndicatorElement = formContainerElement?.querySelector('#advset_type_available_indicator');
    const submitButtonElement = formContainerElement?.querySelector('[type="submit"]');

    const posttypes_data = <?php echo json_encode($advset_posttypes_data); ?>;
    const posttypes_data_default = <?php echo json_encode($advset_posttypes_data_default); ?>;

    document.addEventListener('click', function(event) {
        if (event.target.closest('.advset-posttype-edit-link')) {
            event.preventDefault();
            showForm(event.target.getAttribute('data-type'));
        }
        if (event.target.closest('.advset-posttype-cancel-link')) {
            event.preventDefault();
            showList();
        }
        if (event.target.closest('.advset-posttype-delete-link')) {
            event.preventDefault();
            if (confirm('<?php _e('Are you sure you want to delete this post type?') ?>')) {
                advset_submit_delete_posttype(event.target.getAttribute('data-type'));
            }
        }
    });

    labelInputElement.addEventListener('blur', function() {
        if (typeInputElement.value === '') {
            checkType(true);
        }
    });

    typeInputElement.addEventListener('input', function() {
        checkType();
    });

    function checkType(generate_from_label = false) {
        setTypeValidity(null);
        if (!generate_from_label && typeInputElement.value === '') {
            return;
        }
        fetch('<?php echo rest_url('advset_posttypes/v1/check-type'); ?>', {
            method: 'POST',
            body: JSON.stringify({
                label_input: labelInputElement.value,
                type_input: typeInputElement.value,
                type_stored: typeStoredInputElement.value,
                generate_from_label,
            }),
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (generate_from_label && data.type_available) {
                typeInputElement.value = data.type_available;
                setTypeValidity(true);
            }
            else if (data.is_taken) {
                setTypeValidity(false);
            }
            else if (data.is_valid) {
                setTypeValidity(true);
            }
        });
    }

    function setTypeValidity(validity) {
        let color, icon, text;
        switch (validity) {
            case true:
                color = 'green';
                icon = '✅';
                text = 'Type is available';
                break;
            case false:
                color = 'red';
                icon = '❌';
                text = 'Type already exists';
                break;
            default:
                color = 'gray';
                icon = '';
                text = '';
                break;
        }
        typeInputElement.setCustomValidity(validity === false ? text : '');
        typeAvailableIndicatorElement.textContent = icon + ' ' + text;
        typeAvailableIndicatorElement.style.color = color;
    }

    function showForm(type) {

        const data = type ? posttypes_data[type] : posttypes_data_default;
        data.label = data?.labels?.name ?? type;
        data.type = type ?? '';
        data.type_stored = type ?? '';

        formElement.querySelectorAll('[name]').forEach(element => {
            if (element.name.slice(0, 1) === '_') {
                return;
            }
            
            if (element.type === 'checkbox') {
                if (element.name.slice(-2) === '[]') {
                    element.checked = data[element.name.slice(0, -2)]?.includes(element.value) ?? element.defaultChecked;
                } else {
                    element.checked = data[element.name] ?? element.defaultChecked;
                }
            } else if (['text', 'hidden', 'textarea'].includes(element.type)) {
                element.value = data[element.name] ?? element.defaultValue;
            }
        });

        submitButtonElement.value = type ? submitButtonElement.dataset.saveChanges : submitButtonElement.dataset.create;

        setTypeValidity(null);

        listContainerElement.style.display = 'none';
        formContainerElement.style.display = '';
        labelInputElement.focus();
    }

    function showList() {
        formContainerElement.style.display = 'none';
        listContainerElement.style.display = '';
    };

    // Submit delete via hidden POST form
    function advset_submit_delete_posttype(slug) {
        const form = document.getElementById('advset_delete_posttype_form');
        const input = document.getElementById('advset_delete_posttype_slug');
        if (!slug || !form || !input) return false;
        input.value = slug;
        form.submit();
        return false;
    };
});
</script>

<div class="wrap">
    <?php advset_page_header() ?>
    <br />
    <!-- <?php echo advset_page_experimental(); ?>
    <br />
    <br /> -->


    <!-- <div style="padding: 1rem; background: #fff; border: #f00 solid 2px; border-radius: .5rem; ">
        <h3 style="margin: 0; ">WARNING</h3>
        <p style="margin: 0; "><?php _e('Be careful, changing a post type can significantly impact the functionality of the website.') ?></p>
    </div>
    <br /> -->
    <a class="advset-posttype-edit-link add-new-h2" href="#" data-type=""><?php _e('Add') ?></a>
    <br />
    <br />

    <div id="advset_posttype_form_container" style="display:none">

        <h3><?php _e('Add') ?></h3>

        <form id="advset_posttype_form" action="" method="post">
            <?php #settings_fields( 'advanced-settings-post-types' ); ?>

            <input type="hidden" name="_advset_posttype_nonce" value="<?php echo $advset_posttype_nonce; ?>" />

            <input type="hidden" name="_advset_posttype_action_save" value="1" />

            <input type="hidden" name="type_stored" value="" />

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Label'); ?></th>
                    <td>
                        <input name="label" type="text" value="" />

                        <!--p><a href="#">+ show more labels</a></p-->

                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Type Name'); ?></th>
                    <td>
                        <input name="type" type="text" value="" pattern="^[a-z0-9_\-]{1,20}$" required /> <span id="advset_type_available_indicator"></span><br />
                        <i style="color:#999">Post type names must be between 1 and 20 characters in length. Lowercase alphanumeric characters, dashes, and underscores are allowed. </i>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Description'); ?></th>
                    <td>
                        <!-- <textarea name="description" rows="3" style="width: 100%;"></textarea> -->
                            <input name="description" type="text" value="" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Supports'); ?></th>
                    <td>
                        <input name="supports[]" id="posttype-support-title" value="title" type="checkbox">
                        <label for="posttype-support-title">title</label><br />
                        <input name="supports[]" id="posttype-support-editor" value="editor" type="checkbox">
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
                        <label><input name="public" value="1" type="checkbox">
                            public</label><br />
                        <label><input name="publicly_queryable" value="1" type="checkbox">
                            publicly_queryable</label><br />
                        <label><input name="show_ui" value="1" type="checkbox">
                            show_ui</label><br />
                        <label><input name="show_in_menu" value="1" type="checkbox">
                            show_in_menu</label><br />
                        <label><input name="query_var" value="1" type="checkbox">
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
                        <label><input name="taxonomies[]" value="category" type="checkbox">
                            category</label><br />
                        <label><input name="taxonomies[]" value="post_tag" type="checkbox">
                            post_tag</label>
                    </td>
                </tr>

            </table>
            <p class="submit">
                <input type="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes') ?>" data-save-changes="<?php _e('Save Changes') ?>" data-create="<?php _e('Create') ?>" />
                <input type="button" class="button advset-posttype-cancel-link" value="<?php _e('Cancel') ?>" />
            </p>
        </form>
    </div>

    <div id="advset_posttype_list_container">
        <?php if (!empty($advset_posttypes_data)) { ?>
        <form id="advset_posttype_list" action="options.php" method="post">

            <table class="widefat fixed striped" cellspacing="0">
                <thead>
                    <tr>
                        <th scope="col" id="title" class="manage-column column-title" width="40%"><?php _e('Label') ?></th>
                        <th scope="col" id="type_name" class="manage-column column-title" width="30%"><?php _e('Type') ?></th>
                        <th scope="col" id="type_desc" class="manage-column column-title" width="30%"><?php _e('Description') ?></th>
                    </tr>
                </thead>
                <?php foreach($advset_posttypes_data as $typename => $post_type) { ?>
                <tr class=" iedit">
                    <td>
                        <strong><?php echo $post_type['labels']['name'] ?></strong>
                        <div class="row-actions">
                        <span class="edit">
                            <a class="advset-posttype-edit-link" href="#" data-type="<?php echo esc_attr($typename) ?>"><?php _e('Edit') ?></a>
                            |
                        </span>
                        <span class="trash">
                            <a class="advset-posttype-delete-link" href="#" data-type="<?php echo esc_attr($typename) ?>"><?php _e('Delete') ?></a>
                        </span>
                        </div>
                    </td>
                    <td><?php echo $typename ?></td>
                    <td><?php echo str_replace("\n", '<br />', strip_tags($post_type['description'] ?? '')) ?></td>
                </tr>
                <?php } ?>

                <tfoot>
                    <tr>
                        <th scope="col" id="title" class="manage-column column-title" width="40%"><?php _e('Label') ?></th>
                        <th scope="col" id="type_name" class="manage-column column-title" width="30%"><?php _e('Type') ?></th>
                        <th scope="col" id="type_desc" class="manage-column column-title" width="30%"><?php _e('Description') ?></th>
                    </tr>
                </tfoot>

            </table>
        </form>
        <?php } else { ?>
        <div>
            <p><?php _e('No custom post types found.') ?></p>
        </div>
        <?php } ?>

        <form id="advset_delete_posttype_form" action="<?php echo esc_url( admin_url('options-general.php?page=advanced-settings-post-types') ); ?>" method="post" style="display: none;">
            <input type="hidden" name="_advset_posttype_nonce" value="<?php echo esc_attr($advset_posttype_nonce); ?>" />
            <input type="hidden" name="_advset_posttype_action_delete" id="advset_delete_posttype_slug" value="" />
        </form>
    </div>
</div>
