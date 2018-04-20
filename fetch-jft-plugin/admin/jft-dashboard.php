<?php
 
/* Admin dashboard of the JFT plugin */


add_action('admin_menu', 'jft_options_menu');

function jft_options_menu() {
  add_options_page('Fetch JFT Plugin Settings', 'Fetch JFT', 'manage_options', 'jft-plugin', 'fetch_jft_plugin_page');
}

function jft_plugin_settings() {
	//register our settings
	register_setting( 'jft-plugin-settings-group', 'jft_layout' );
}

add_action( 'admin_init', 'jft_plugin_settings' );

function fetch_jft_plugin_page() {
 ?>
   <div class="wrap">
     <form action="options.php" method="post">
       <?php
       settings_fields( 'jft-plugin-settings-group' );
       do_settings_sections( 'jft-plugin-settings-group' );
       ?>
  <h3>Fetch JFT Layout</h3>
  <p>Change between a plain table and css block elements.</p>
			<table class="form-table">
		        <tr valign="top">
		        	<th scope="row">Layout</th>
		        	<td>
		        		<select id="jft_layout" name="jft_layout">
							<option value="table" <?php if(esc_attr(get_option('jft_layout'))=='table') echo 'selected="selected"'; ?>>Table</option>
							<option value="block" <?php if(esc_attr(get_option('jft_layout'))=='block') echo 'selected="selected"'; ?>>Block</option>
						</select>
		        	</td>
		        </tr>
		    </table>
  <?php  submit_button(); ?>
  </form>
   </div>
 <?php
}

// add settings link to plugins page
function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=jft-plugin">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );

// End JFT Settings Page Function
?>
