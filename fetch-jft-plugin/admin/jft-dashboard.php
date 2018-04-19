<?php
/* Admin dashboard of the JFT plugin */


function jft_plugin_admin_add_page() {
	add_menu_page('JFT Settings', 'JFT', 'manage_options', 'jft_settings', 'jft_settings_page', 'dashicons-book', 6);
}
function jft_plugin_settings() {
	//register our settings
	register_setting( 'jft-plugin-settings-group', 'jft_layout' );
	//register_setting( 'jft-plugin-settings-group', 'jft_language' );
}
add_action( 'admin_init', 'jft_plugin_settings' );
function jft_settings_page() { ?>
<div class="wrap">
	<h1>JFT Settings</h1>
	<div>
		<form class="jft-form" method="post" action="options.php">
			<?php 
			settings_fields( 'jft-plugin-settings-group' ); 
			do_settings_sections( 'jft-plugin-settings-group' );
			?>
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
		        <!-- <tr valign="top">
		        	<th scope="row">Display Language</th>
		        	<td>
		        		<select id="jft_language" name="jft_language">
							<option value="english" <?php if(esc_attr(get_option('jft_language'))=='english') echo 'selected="selected"'; ?>>English</option>
							<option value="spanish" <?php if(esc_attr(get_option('jft_language'))=='spanish') echo 'selected="selected"'; ?>>Spanish</option>
							<option value="french" <?php if(esc_attr(get_option('jft_language'))=='french') echo 'selected="selected"'; ?>>French</option>
						</select>
		        	</td>
		        </tr> -->
		    </table>			
			<?php submit_button(); ?>
		</form>
	</div>
</div>
<?php } // End JFT Settings Page Function