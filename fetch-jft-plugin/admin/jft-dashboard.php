<?php
 
/* Admin dashboard of the JFT plugin */




function jft_plugin_settings() {
	//register our settings
	register_setting('jft-plugin-settings-group', 'jft_layout');
    register_setting('jft-plugin-settings-group', 'jft_language');
}

add_action( 'admin_init', 'jft_plugin_settings' );

function fetch_jft_plugin_page() { ?>
    <div class="wrap">
        <h1>Fetch JFT Plugin Settings</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'jft-plugin-settings-group' );
            do_settings_sections( 'jft-plugin-settings-group' );
            ?>
        	<table class="form-table">
                <tr valign="top">
                    <th scope="row">Language</th>
                    <td>
                        <select id="jft_language" name="jft_language">
                            <option value="english" <?php if(esc_attr(get_option('jft_language'))=='english') echo 'selected="selected"'; ?>>English</option>
                            <option value="spanish" <?php if(esc_attr(get_option('jft_language'))=='spanish') echo 'selected="selected"'; ?>>Spanish</option>
                            <option value="french" <?php if(esc_attr(get_option('jft_language'))=='french') echo 'selected="selected"'; ?>>French</option>
                        </select>
                        <p class="description">Choose the language for the JFT Display.<br> <strong>Languages other then English only works with raw HTML layout.</strong></p>
                    </td>
                </tr>
		        <tr valign="top">
		        	<th scope="row">Layout</th>
		        	<td>
		        		<select id="jft_layout" name="jft_layout">
							<option value="table" <?php if(esc_attr(get_option('jft_layout'))=='table') echo 'selected="selected"'; ?>>Raw HTML</option>
							<option value="block" <?php if(esc_attr(get_option('jft_layout'))=='block') echo 'selected="selected"'; ?>>Block (For English)</option>
						</select>
                        <p class="description"><strong>Only for English.</strong> Change between raw HTML Table and CSS block elements.</p>
		        	</td>
		        </tr>
		    </table>
            <?php  submit_button(); ?>
        </form>
   </div>
 <?php }




// End JFT Settings Page Function
?>
