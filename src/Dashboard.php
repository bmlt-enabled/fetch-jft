<?php

namespace Jft;

class Dashboard
{
    const SETTING_GROUP = 'jft-plugin-settings-group';

    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_menu', [$this, 'createMenu']);
    }

    public function registerSettings(): void
    {
        register_setting(self::SETTING_GROUP, 'jft_layout');
        register_setting(self::SETTING_GROUP, 'jft_language');
    }

    public function createMenu(string $baseFile): void
    {
        add_options_page(
            esc_html__('Fetch JFT Plugin Settings'), // Page Title
            esc_html__('Fetch JFT'),                 // Menu Title
            'manage_options',             // Capability
            'jft-plugin',                // Menu Slug
            [$this, 'drawSettings']  // Callback function to display the page content
        );
        add_filter('plugin_action_links_' . $baseFile, [$this, 'settingsLink']);
    }

    public function settingsLink($links)
    {
        $settings_url = admin_url('options-general.php?page=jft-plugin');
        $links[] = "<a href='{$settings_url}'>Settings</a>";
        return $links;
    }

    public function drawSettings(): void
    {
        ?>
        <div class="wrap">
            <h1>Fetch JFT Plugin Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::SETTING_GROUP);
                do_settings_sections(self::SETTING_GROUP);
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Language</th>
                        <td>
                            <select id="jft_language" name="jft_language">
                                <?php
                                $languages = [
                                    'danish' => 'Danish',
                                    'english' => 'English',
                                    'farsi' => 'Farsi',
                                    'french' => 'French',
                                    'german' => 'German',
                                    'italian' => 'Italian',
                                    'japanese' => 'Japanese',
                                    'portuguese' => 'Portuguese',
                                    'russian' => 'Russian',
                                    'spanish' => 'Spanish',
                                    'swedish' => 'Swedish',
                                ];

                                $selectedLanguage = esc_attr(get_option('jft_language'));

                                foreach ($languages as $value => $label) {
                                    $selected = $selectedLanguage === $value ? 'selected="selected"' : '';
                                    echo '<option value="' . esc_attr($value) . '" ' . esc_attr($selected) . '>' . esc_html($label) . '</option>';
                                }
                                ?>
                            </select>
                            <p class="description">Choose the language for the JFT Display.<br> insert [jft] shortcode on your page or post. <strong>Languages other then English only works with raw HTML layout.</strong></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Layout</th>
                        <td>
                            <select id="jft_layout" name="jft_layout">
                                <option value="table" <?php if (esc_attr(get_option('jft_layout')) == 'table') {
                                    echo 'selected="selected"';
                                                      } ?>>Table (Raw HTML)</option>
                                <option value="block" <?php if (esc_attr(get_option('jft_layout')) == 'block') {
                                    echo 'selected="selected"';
                                                      } ?>>Block (For English)</option>
                            </select>
                            <p class="description"><strong>Only for English.</strong> Change between raw HTML Table and CSS block elements.</p>
                        </td>
                    </tr>
                </table>
                <?php  submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
