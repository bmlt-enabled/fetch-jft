<?php
 
/* Admin dashboard of the JFT plugin */

function jft_plugin_settings() {
    //register our settings
    register_setting('jft-plugin-settings-group', 'jft_layout');
    register_setting('jft-plugin-settings-group', 'jft_language');
    register_setting('jft-plugin-settings-group', 'custom_css_jft');
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
                            <option value="french" <?php if(esc_attr(get_option('jft_language'))=='french') echo 'selected="selected"'; ?>>French</option>
                            <option value="german" <?php if(esc_attr(get_option('jft_language'))=='german') echo 'selected="selected"'; ?>>German</option>
                            <option value="italian" <?php if(esc_attr(get_option('jft_language'))=='italian') echo 'selected="selected"'; ?>>Italian</option>
                            <option value="portuguese" <?php if(esc_attr(get_option('jft_language'))=='portuguese') echo 'selected="selected"'; ?>>Portuguese</option>
                            <option value="spanish" <?php if(esc_attr(get_option('jft_language'))=='spanish') echo 'selected="selected"'; ?>>Spanish</option>
                            <option value="swedish" <?php if(esc_attr(get_option('jft_language'))=='swedish') echo 'selected="selected"'; ?>>Swedish</option>
                        </select>
                        <p class="description">Choose the language for the JFT Display.<br> <strong>Languages other then English only works with raw HTML layout.</strong></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Layout</th>
                    <td>
                        <select id="jft_layout" name="jft_layout">
                            <option value="table" <?php if(esc_attr(get_option('jft_layout'))=='table') echo 'selected="selected"'; ?>>Table (Raw HTML)</option>
                            <option value="block" <?php if(esc_attr(get_option('jft_layout'))=='block') echo 'selected="selected"'; ?>>Block (For English)</option>
                        </select>
                        <p class="description"><strong>Only for English.</strong> Change between raw HTML Table and CSS block elements.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Custom CSS</th>
                    <td>
                        <textarea id="custom_css_jft" name="custom_css_jft" cols="100" rows="10"><?php echo get_option('custom_css_jft'); ?></textarea>
                    </td>
                </tr>
            </table>
            <?php  submit_button(); ?>
        </form>
   </div>
 <?php }

// End JFT Settings Page Function
?>
