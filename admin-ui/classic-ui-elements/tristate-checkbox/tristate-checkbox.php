<?php

if (!defined('ABSPATH')) exit;

/**
 * Generic tri-state checkbox: TRUE | FALSE | not set.
 * Renders markup and enqueues assets; POST/option handling is left to the caller.
 */
class Advset_Tristate_Checkbox {

    /**
     * Map POST/raw value to tri-state: true | false | null (not set).
     *
     * @param mixed $post_value Value from $_POST or similar (e.g. 'true', 'false', 'null')
     * @return bool|null
     */
    public static function post_to_tristate($post_value) {
        return $post_value === 'true' ? true : ($post_value === 'false' ? false : null);
    }

    /**
     * Register and enqueue tri-state CSS and JS. Call on the admin page that uses the control.
     */
    public static function enqueue_assets() {
        $includes_dir = __DIR__;

        wp_register_style(
            'advset-tristate',
            plugins_url('tristate-checkbox.css', __FILE__),
            [],
            filemtime($includes_dir . '/tristate-checkbox.css')
        );
        wp_enqueue_style('advset-tristate');
    }

    /**
     * Output markup for one tri-state checkbox.
     *
     * @param string    $name    Input name (e.g. 'public').
     * @param string    $label   Visible label.
     * @param bool      $default Default when not set: true = show check, false = show cross.
     * @param bool|null $value   Current value: true, false, or null (not set).
     */
    public static function render($name, $options = []) {
        $options = array_merge([
            'label' => $name,
            'default' => false,
            'value' => null,
            'trueOnly' => false,
            'aboutURL' => null,
        ], $options);
        $label = $options['label'];
        $default = $options['default'];
        $value = $options['value'];
        $trueOnly = $options['trueOnly'];
        $aboutURL = $options['aboutURL'];
        $id = 'advset-tristate-' . sanitize_title($name);
        ?>
        <div class="advset-tristate-container">
            <fieldset class="advset-tristate-fieldset" data-advset-default="<?php echo esc_attr($default ? 'true' : 'false'); ?>" id="<?php echo esc_attr($id); ?>">
                <legend class="advset-tristate-legend"><span><?php echo esc_html($label); ?></span></legend>
                <input class="advset-tristate-input" type="radio" data-type="tristate" name="<?php echo esc_attr($name); ?>" value="null" id="<?php echo esc_attr($id); ?>-unset" <?php checked(is_bool($value), false); ?> />
                <label class="advset-tristate-label advset-tristate-label-unset" for="<?php echo esc_attr($id); ?>-unset"><?php echo esc_html(__('Not set', 'advanced-settings')); ?></label>
                <input class="advset-tristate-input" type="radio" data-type="tristate" name="<?php echo esc_attr($name); ?>" value="true" id="<?php echo esc_attr($id); ?>-true" <?php checked($value, true); ?> />
                <label class="advset-tristate-label advset-tristate-label-true" for="<?php echo esc_attr($id); ?>-true"><?php echo esc_html(__('True', 'advanced-settings')); ?></label>
                <?php if (!$trueOnly): ?>
                <input class="advset-tristate-input" type="radio" data-type="tristate" name="<?php echo esc_attr($name); ?>" value="false" id="<?php echo esc_attr($id); ?>-false" <?php checked($value, false); ?> />
                <label class="advset-tristate-label advset-tristate-label-false" for="<?php echo esc_attr($id); ?>-false"><?php echo esc_html(__('False', 'advanced-settings')); ?></label>
                <?php endif; ?>
                <div class="advset-tristate-icon"></div>
                <?php if ($aboutURL): ?>
                <a class="advset-tristate-about-link" href="<?php echo esc_url($aboutURL); ?>" target="_blank" aria-label="<?php echo esc_attr(__('About', 'advanced-settings')); ?>" title="<?php echo esc_attr(__('About', 'advanced-settings')); ?>"><?php echo esc_html(__('About', 'advanced-settings')); ?></a>
                <?php endif; ?>
            </fieldset>
        </div>
        <?php
    }
}

