<?php defined('ABSPATH') or exit; ?>

<div class="wrap">
	<?php advset_page_header() ?>

  <div>&nbsp;</div>

  <div id="message" class="error"><?php _e('Be careful, removing a filter can destabilize your system. For security reasons, no filter removal has efects over this page.') ?></div>

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
    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>',
        {
          'action':'advset_filters',
          'tag':this.name,
          'function':this.value,
          'enable':this.checked
         },
         function(response){
         //alert('The server responded: ' + response);
         }
    );
  });
  </script>

</div>
