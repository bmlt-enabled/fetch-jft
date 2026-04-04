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
            esc_html__('Fetch JFT Plugin Settings', 'fetch-jft'), // Page Title
            esc_html__('Fetch JFT', 'fetch-jft'),                 // Menu Title
            'manage_options',             // Capability
            'jft-plugin',                // Menu Slug
            [$this, 'drawSettings']  // Callback function to display the page content
        );
        add_filter('plugin_action_links_' . $baseFile, [$this, 'settingsLink']);
    }

    public function settingsLink($links)
    {
        $settings_url = admin_url('options-general.php?page=jft-plugin');
        $links[] = '<a href="' . esc_url($settings_url) . '">' . esc_html__('Settings', 'fetch-jft') . '</a>';
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
            <h1><?php esc_html_e('Fetch JFT Plugin Settings', 'fetch-jft'); ?></h1>
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
                        <th scope="row"><?php esc_html_e('Language', 'fetch-jft'); ?></th>
                        <td>
                            <?php
                            echo wp_kses(
                                static::renderSelectOption(
                                    'jft_language',
                                    $selectedLanguage,
                                    [
                                        'danish'     => __('Danish', 'fetch-jft'),
                                        'english'    => __('English', 'fetch-jft'),
                                        'farsi'      => __('Farsi', 'fetch-jft'),
                                        'french'     => __('French', 'fetch-jft'),
                                        'german'     => __('German', 'fetch-jft'),
                                        'italian'    => __('Italian', 'fetch-jft'),
                                        'japanese'   => __('Japanese', 'fetch-jft'),
                                        'portuguese' => __('Portuguese', 'fetch-jft'),
                                        'russian'    => __('Russian', 'fetch-jft'),
                                        'spanish'    => __('Spanish', 'fetch-jft'),
                                        'swedish'    => __('Swedish', 'fetch-jft'),
                                    ]
                                ),
                                $allowed_html
                            );
                            ?>
                        </td>
                        <p class="description"><?php esc_html_e('Choose the language for the JFT Display.', 'fetch-jft'); ?><br> <?php esc_html_e('insert [jft] shortcode on your page or post.', 'fetch-jft'); ?> <strong><?php esc_html_e('Languages other than English only work with raw HTML layout.', 'fetch-jft'); ?></strong></p>
                    </tr>
                    </tr>
                    <tr valign="top" id="layout-container">
                        <th scope="row"><?php esc_html_e('Layout', 'fetch-jft'); ?></th>
                        <td>
                            <select id="jft_layout" name="jft_layout">
                                <option value="table" <?php if (esc_attr(get_option('jft_layout')) == 'table') {
                                    echo 'selected="selected"';
                                                      } ?>><?php esc_html_e('Table (Raw HTML)', 'fetch-jft'); ?></option>
                                <option value="block" <?php if (esc_attr(get_option('jft_layout')) == 'block') {
                                    echo 'selected="selected"';
                                                      } ?>><?php esc_html_e('Block (For English)', 'fetch-jft'); ?></option>
                            </select>
                            <p class="description"><strong><?php esc_html_e('Only for English.', 'fetch-jft'); ?></strong> <?php esc_html_e('Change between raw HTML Table and CSS block elements.', 'fetch-jft'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" id="timezone-container">
                        <th scope="row"><?php esc_html_e('Timezone (English Only)', 'fetch-jft'); ?></th>
                        <td>
                            <?php
                            $timezone_options = [
                                '' => __('Server Default', 'fetch-jft'),
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
                            <p class="description"><?php esc_html_e('Only applies when English language is selected. Leave blank to use server default.', 'fetch-jft'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php  submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
