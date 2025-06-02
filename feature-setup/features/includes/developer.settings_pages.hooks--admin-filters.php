<?php defined('ABSPATH') or exit; ?>

<div class="wrap">
    <?php advset_page_header() ?>
    <br />
    <?php echo advset_page_experimental(); ?>
    <br />
    <br />

    <div style="padding: 1rem; background: #fff; border: #f00 solid 2px; border-radius: .5rem; ">
        <h3 style="margin: 0; ">WARNING</h3>
        <p style="margin: 0; "><?php _e('Be careful, removing a filter can destabilize your system. For security reasons, no filter removal has efects over this page.') ?></p>
    </div>
    <br />

    <?php
    global $wp_filter;

    $hook=$wp_filter;
    ksort($hook);

    $remove_filters = (array) get_option( 'advset_remove_filters' );

    echo '<table id="advset_filters" style="font-size:90%">
        <tr><td>&nbsp;</td><td><strong>'.__('priority').'</strong></td></tr>';

    foreach($hook as $tag => $priority){
        echo "<tr><th align='left'>[<a target='_blank' href='https://developer.wordpress.org/reference/hooks/$tag/'>$tag</a>]</th></tr>";
        echo '<tr><td>';
        foreach($priority->callbacks as $priority => $function){
            foreach($function as $function => $properties) {

                $checked = isset($remove_filters[$tag][$function]) ? '': "checked='checked'";

                echo "<tr><td> <label><input type='checkbox' name='$tag' value='$function' $checked />
                    $function</label>
                    <sub><a target='_blank' href='https://developer.wordpress.org/reference/hooks/$function/'>help</a></sub></td>
                    <td align='right'>$priority</td></tr>";
            }
        }
        echo '<tr><td>&nbsp;</td></tr>';
    }
    echo '</table>';
    ?>

    <script>
    jQuery('#advset_filters input').click(function(){
        const checkbox = this;
        let originalState = checkbox.checked;
        
        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>',
            {
                'action': 'advset_filters',
                'tag': checkbox.name,
                'function': checkbox.value,
                'enable': checkbox.checked,
                'nonce': '<?php echo wp_create_nonce('advset_filters_nonce'); ?>',
            },
            function(response){
                if (!response.success) {
                    alert(response.data);
                    checkbox.checked = !originalState;
                    return;
                }

                originalState = checkbox.checked;
            }
        ).fail(function() {
            alert('Connection error');
            checkbox.checked = !originalState;
        });
    });
    </script>

</div>
