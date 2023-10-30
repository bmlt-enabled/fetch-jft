<?php

/*
Plugin Name: Fetch JFT
Plugin URI: https://wordpress.org/plugins/fetch-jft/
Author: bmlt-enabled
Description: This is a plugin that fetches the Just For Today from NAWS and puts it on your site Simply add [jft] shortcode to your page. Fetch JFT Widget can be added to your sidebar or footer as well.
Version: 1.9.0
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */

namespace Jft;

require_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

spl_autoload_register(function (string $class) {
    if (strpos($class, 'Jft\\') === 0) {
        $class = str_replace('Jft\\', '', $class);
        require __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    }
});

class FetchJFTPlugin
{
    private static $instance = null;

    public function __construct()
    {
        add_action('init', [$this, 'pluginSetup']);
    }

    public function pluginSetup()
    {
        if (is_admin()) {
            add_action('admin_menu', [$this, 'optionsMenu']);
            add_action("admin_enqueue_scripts", [$this, "enqueueBackendFiles"], 500);
        } else {
            add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendFiles']);
            add_shortcode('jft', [$this, 'reading']);
            add_action('widgets_init', function () {
                register_widget(Widget::class);
            });
        }
    }

    public function optionsMenu()
    {
        $dashboard = new Dashboard();
        $dashboard->createMenu(plugin_basename(__FILE__));
    }

    public function reading($atts)
    {
        $reading = new Reading();
        return $reading->renderReading($atts);
    }

    public function enqueueBackendFiles(string $hook): void
    {
        if ($hook !== 'settings_page_jft-plugin') {
            return;
        }
        $baseUrl = plugin_dir_url(__FILE__);
        wp_enqueue_script('jft-plugin-admin', $baseUrl . 'js/jft-plugin.js', ['jquery'], filemtime(plugin_dir_path(__FILE__) . 'js/jft-plugin.js'), false);
    }

    public function enqueueFrontendFiles(): void
    {
        wp_enqueue_style('jft-plugin', plugin_dir_url(__FILE__) . 'css/jft-plugin.css', false, '1.0.0', 'all');
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

FetchJFTPlugin::getInstance();
