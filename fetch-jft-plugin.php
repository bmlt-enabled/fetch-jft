<?php

/*
Plugin Name: Fetch JFT
Plugin URI: https://wordpress.org/plugins/fetch-jft/
Author: bmlt-enabled
Description: This is a plugin that fetches the Just For Today from NAWS and puts it on your site Simply add [jft] shortcode to your page. Fetch JFT Widget can be added to your sidebar or footer as well.
Version: 1.8.4
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */

namespace Jft;

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
        } else {
            add_action('wp_enqueue_scripts', [$this, 'assets']);
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

    public function assets()
    {
        wp_enqueue_style("jftcss", plugin_dir_url(__FILE__) . "css/jft.css", false, filemtime(plugin_dir_path(__FILE__) . "css/jft.css"), false);
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
;
