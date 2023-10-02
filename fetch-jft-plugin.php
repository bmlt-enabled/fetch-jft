<?php
/*
Plugin Name: Fetch JFT
Plugin URI: https://wordpress.org/plugins/fetch-jft/
Description: This is a plugin that fetches the Just For Today from NAWS and puts it on your site Simply add [jft] shortcode to your page. Fetch JFT Widget can be added to your sidebar or footer as well.
Version: 1.8.0
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

spl_autoload_register(function (string $class) {
    if (strpos($class, 'Jft\\') === 0) {
        $class = str_replace('Jft\\', '', $class);
        require __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    }
});

use Jft\Dashboard;
use Jft\Reading;
use Jft\Widget;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class FetchJFTPlugin
{
    // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
    public function __construct()
    {
        add_action('admin_menu', [$this, 'optionsMenu']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_shortcode('jft', [$this, 'reading']);
        add_action('widgets_init', function () {
            register_widget(Widget::class);
        });
    }

    public function optionsMenu()
    {
        $dashboard = new Dashboard();
        $dashboard->createMenu();
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
}

$fetchJFTPlugin = new FetchJFTPlugin();
