<?php

if (!defined('ABSPATH')) exit;


$advset_posttype_nonce = wp_create_nonce('advset_posttype_nonce');

$advset_posttypes_data = get_option('advset_post_types', []);


?>
<script>

document.addEventListener('DOMContentLoaded', function() {
    const formContainerElement = document.getElementById('advset_posttype_form_container');
    const formTitleAddElement = document.getElementById('advset_posttype_form_title_add');
    const formTitleEditElement = document.getElementById('advset_posttype_form_title_edit');
    const listContainerElement = document.getElementById('advset_posttype_list_container');
    const formElement = formContainerElement?.querySelector('#advset_posttype_form');
    const labelInputElement = formContainerElement?.querySelector('[name="label"]');
    const typeInputElement = formContainerElement?.querySelector('[name="type"]');
    const typeStoredInputElement = formContainerElement?.querySelector('[name="type_stored"]');
    const typeAvailableIndicatorElement = formContainerElement?.querySelector('#advset_type_available_indicator');
    const submitButtonElement = formContainerElement?.querySelector('[type="submit"]');

    const posttypes_data = <?php echo json_encode($advset_posttypes_data); ?>;

    const tristateElements = Array.from(formElement?.querySelectorAll('.advset-tristate-fieldset')).reduce((acc, fieldsetElement) => {
        const inputNullElement = fieldsetElement.querySelector('input[value="null"]');
        const inputTrueElement = fieldsetElement.querySelector('input[value="true"]');
        const inputFalseElement = fieldsetElement.querySelector('input[value="false"]');
        const name = inputNullElement.name;
        if (!inputNullElement && !inputTrueElement && !inputFalseElement) {
            return acc;
        }
        acc[name] = {
            name,
            fieldsetElement,
            getValue: () => inputTrueElement?.checked ? true : (inputFalseElement?.checked ? false : null),
            getResultingValue: () => inputTrueElement?.checked ? true : (inputFalseElement?.checked ? false : fieldsetElement.dataset.advsetDefault === 'true' ? true : false),
            setDefaultValue: (value) => {
                fieldsetElement.dataset.advsetDefault = value ? 'true' : 'false';
            },
        };
        return acc;
    }, {});

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


    document.addEventListener('change', function(event) {
        updateFormFieldStates();
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

    function updateFormFieldStates() {
        tristateElements['exclude_from_search'].setDefaultValue(!tristateElements['public'].getResultingValue());
        tristateElements['publicly_queryable'].setDefaultValue(tristateElements['public'].getResultingValue());
        tristateElements['show_ui'].setDefaultValue(tristateElements['public'].getResultingValue());
        tristateElements['show_in_menu'].setDefaultValue(tristateElements['show_ui'].getResultingValue());
        tristateElements['show_in_nav_menus'].setDefaultValue(tristateElements['public'].getResultingValue());
        tristateElements['show_in_admin_bar'].setDefaultValue(tristateElements['show_in_menu'].getResultingValue());

        const supportsElements = Object.values(tristateElements).filter(element => element.name.startsWith('supports['));
        const supportsChecked = supportsElements.filter(element => element.getValue() !== null).length > 0;
        tristateElements['supports[title]']?.setDefaultValue(!supportsChecked);
        tristateElements['supports[editor]']?.setDefaultValue(!supportsChecked);
    }

    function showForm(type) {

        const isNew = !type;
        const data = type ? posttypes_data[type] : {};
        data.label = data?.labels?.name ?? type;
        data.type = type ?? '';
        data.type_stored = type ?? '';

        formElement.querySelectorAll('[name]').forEach(element => {
            if (element.name.slice(0, 1) === '_') {
                return;
            }

            if (element.type === 'radio' && element.dataset.type === 'tristate') {
                const keys = element.name.split(/\[(\w+)\]/).filter(Boolean);
                const val = keys.length === 1 ? data[element.name] : (data[keys[0]]?.includes(keys[1]) ? true : null);
                element.checked = element.value === (val === true ? 'true' : (val === false ? 'false' : 'null'));
                return;
            }

            if (element.type === 'checkbox') {
                if (element.name.slice(-2) === '[]') {
                    element.checked = data[element.name.slice(0, -2)]?.includes(element.value) ?? element.defaultChecked;
                } else {
                    element.checked = data[element.name] ?? element.defaultChecked;
                }
                return;
            }
            
            if (['text', 'hidden', 'textarea'].includes(element.type)) {
                element.value = data[element.name] ?? element.defaultValue;
                return;
            }
        });

        submitButtonElement.value = type ? submitButtonElement.dataset.saveChanges : submitButtonElement.dataset.create;

        setTypeValidity(null);

        updateFormFieldStates();

        formTitleAddElement.style.display = isNew ? '' : 'none';
        formTitleEditElement.style.display = isNew ? 'none' : '';

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

        <h3 id="advset_posttype_form_title_add" style="display:none"><?php _e('Add') ?></h3>
        <h3 id="advset_posttype_form_title_edit" style="display:none"><?php _e('Edit') ?></h3>

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
                        <?php
                        foreach ([
                            'title' => true,
                            'editor' => true,
                            'comments' => false,
                            'revisions' => false,
                            'trackbacks' => false,
                            'author' => false,
                            'excerpt' => false,
                            'page-attributes' => false,
                            'thumbnail' => false,
                            'custom-fields' => false,
                            'post-formats' => false,
                        ] as $support => $default) {
                            Advset_Tristate_Checkbox::render('supports[' . $support . ']', [
                                'label' => $support,
                                'default' => $default,
                                'trueOnly' => true,
                                'aboutURL' => 'https://developer.wordpress.org/reference/functions/register_post_type/#supports',
                            ]);
                        }
                        ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Settings'); ?></th>
                    <td>
                        <?php
                        foreach ([
                            'public' => false,
                            'hierarchical' => false,
                            'exclude_from_search' => true, // Dynamic defaults are set via JS - see updateFormFieldStates()
                            'publicly_queryable' => false, // Dynamic defaults are set via JS - see updateFormFieldStates()
                            'show_ui' => false, // Dynamic defaults are set via JS - see updateFormFieldStates()
                            'show_in_menu' => false, // Dynamic defaults are set via JS - see updateFormFieldStates()
                            'show_in_nav_menus' => false, // Dynamic defaults are set via JS - see updateFormFieldStates()
                            'show_in_admin_bar' => false,
                            'show_in_rest' => false,
                            'late_route_registration' => false,
                            'map_meta_cap' => false,
                            'has_archive' => false,
                            'query_var' => true,
                            'can_export' => true,
                        ] as $setting => $default) {
                            if (in_array($setting, ['late_route_registration'])) {
                                $aboutURL = null;
                            } else {
                                $aboutURL = 'https://developer.wordpress.org/reference/functions/register_post_type/#' . $setting;
                            }
                            Advset_Tristate_Checkbox::render($setting, [
                                'default' => $default,
                                'aboutURL' => $aboutURL,
                            ]);
                        }
                        ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Taxonomies'); ?></th>
                    <td>
                        <?php
                        foreach ([
                            'category' => false,
                            'post_tag' => false,
                        ] as $taxonomy => $default) {
                            Advset_Tristate_Checkbox::render('taxonomies[' . $taxonomy . ']', [
                                'label' => $taxonomy,
                                'default' => $default,
                                'trueOnly' => true,
                                'aboutURL' => 'https://developer.wordpress.org/reference/functions/register_post_type/#taxonomies-2',
                            ]);
                        }
                        ?>
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
