<?php

namespace Jft;

class Dashboard
{
    const SETTING_GROUP = 'jft-plugin-settings-group';

    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_init', [$this, 'registerSettings']);
            add_action('admin_menu', [$this, 'createMenu']);
            add_action('admin_enqueue_scripts', [ $this, 'enqueueBackendFiles' ], 500);
        }
    }

    public function enqueueBackendFiles(string $hook): void
    {
        if ('settings_page_jft-plugin' !== $hook) {
            return;
        }
        $base_url = plugin_dir_url(dirname(__FILE__));
        wp_enqueue_script('fetch-jft-admin', $base_url . 'js/jft.js', [ 'jquery' ], filemtime(plugin_dir_path(dirname(__FILE__)) . 'js/jft.js'), false);
    }

    public function registerSettings(): void
    {
        register_setting(self::SETTING_GROUP, 'jft_layout');
        register_setting(
            self::SETTING_GROUP,
            'jft_language',
            [
                'type'              => 'string',
                'default'           => 'english',
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
        register_setting(
            self::SETTING_GROUP,
            'jft_timezone',
            [
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );
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

    private static function renderSelectOption(string $name, string $selected_value, array $options): string
    {
        // Render a dropdown select input for settings
        $select_html = "<select id='$name' name='$name'>";
        foreach ($options as $value => $label) {
            $selected    = selected($selected_value, $value, false);
            $select_html .= "<option value='$value' $selected>$label</option>";
        }
        $select_html .= '</select>';

        return $select_html;
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
                                <?php

                                $selectedLanguage = esc_attr(get_option('jft_language'));
                                $timezone         = esc_attr(get_option('jft_timezone'));
                                $allowed_html = [
                                    'select' => [
                                        'id'   => [],
                                        'name' => [],
                                    ],
                                    'option' => [
                                        'value'   => [],
                                        'selected'   => [],
                                    ],
                                ];
                                ?>
                    <tr valign="top" id="language-container">
                        <th scope="row">Language</th>
                        <td>
                            <?php
                            echo wp_kses(
                                static::renderSelectOption(
                                    'jft_language',
                                    $selectedLanguage,
                                    [
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
                                    ]
                                ),
                                $allowed_html
                            );
                            ?>
                        </td>
                        <p class="description">Choose the language for the JFT Display.<br> insert [jft] shortcode on your page or post. <strong>Languages other then English only works with raw HTML layout.</strong></p>
                    </tr>
                    </tr>
                    <tr valign="top" id="layout-container">
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
                    <tr valign="top" id="timezone-container">
                        <th scope="row">Timezone (English Only)</th>
                        <td>
                            <?php
                            $timezone_options = [
                                '' => 'Server Default',
                                // North America
                                'America/New_York' => 'America/New_York',
                                'America/Chicago' => 'America/Chicago',
                                'America/Denver' => 'America/Denver',
                                'America/Los_Angeles' => 'America/Los_Angeles',
                                'America/Anchorage' => 'America/Anchorage',
                                'America/Honolulu' => 'America/Honolulu',
                                'America/Phoenix' => 'America/Phoenix',

                                // South America
                                'America/Sao_Paulo' => 'America/Sao_Paulo',
                                'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
                                'America/Santiago' => 'America/Santiago',

                                // Europe
                                'Europe/London' => 'Europe/London',
                                'Europe/Paris' => 'Europe/Paris',
                                'Europe/Berlin' => 'Europe/Berlin',
                                'Europe/Moscow' => 'Europe/Moscow',

                                // Africa
                                'Africa/Cairo' => 'Africa/Cairo',
                                'Africa/Johannesburg' => 'Africa/Johannesburg',
                                'Africa/Lagos' => 'Africa/Lagos',

                                // Asia
                                'Asia/Dubai' => 'Asia/Dubai',
                                'Asia/Kolkata' => 'Asia/Kolkata',
                                'Asia/Bangkok' => 'Asia/Bangkok',
                                'Asia/Singapore' => 'Asia/Singapore',
                                'Asia/Tokyo' => 'Asia/Tokyo',
                                'Asia/Shanghai' => 'Asia/Shanghai',
                                'Asia/Seoul' => 'Asia/Seoul',

                                // Australia/Pacific
                                'Australia/Sydney' => 'Australia/Sydney',
                                'Australia/Perth' => 'Australia/Perth',
                                'Pacific/Auckland' => 'Pacific/Auckland',
                                'Pacific/Fiji' => 'Pacific/Fiji'
                            ];
                            echo wp_kses(
                                static::renderSelectOption(
                                    'jft_timezone',
                                    $timezone,
                                    $timezone_options
                                ),
                                $allowed_html
                            );
                            ?>
                            <p class="description">Only applies when English language is selected. Leave blank to use server default.</p>
                        </td>
                    </tr>
                </table>
                <?php  submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
