<?php
/*
Plugin Name: Fetch JFT
Plugin URI: https://wordpress.org/plugins/fetch-jft/
Description: This is a plugin that fetches the Just For Today from NAWS and puts it on your site Simply add [jft] shortcode to your page. Fetch JFT Widget can be added to your sidebar or footer as well.
Version: 1.7.0
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

require_once('admin/jft-dashboard.php');
require_once('includes/jdatetimeplus.class.php');

// create admin menu settings page
add_action('admin_menu', 'jft_options_menu');
function jft_options_menu()
{
    add_options_page('Fetch JFT Plugin Settings', 'Fetch JFT', 'manage_options', 'jft-plugin', 'fetch_jft_plugin_page');
}

// add settings link to plugins page
function plugin_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=jft-plugin">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'plugin_add_settings_link');

function jft_func($atts = [])
{
    $args = shortcode_atts(
        array(
            'language'  =>  '',
            'layout'    =>  ''
        ),
        $atts
    );

    $headers = array(
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0 +fetchjft'
        ),
        'timeout' => 60
    );

    //Set language and layout - shortcode parameter overrides admin settings
    $jft_language = (!empty($args['language']) ? sanitize_text_field(strtolower($args['language'])) : get_option('jft_language'));
    $jft_layout = (!empty($args['layout']) ? sanitize_text_field(strtolower($args['layout'])) : get_option('jft_layout'));

    switch ($jft_language) {
        case 'english':
            $jft_language_url = 'https://jftna.org/jft/';
            $jft_language_dom_element = 'table';
            $jft_language_footer = '<div align="right" id="jft-subscribe" class="jft-rendered-element"><a href="https://www.jftna.org/jft-subscription.htm" target="_blank">Subscribe</a></div>';
            break;
        case 'spanish':
            $jft_language_url = 'https://forozonalatino.org/wp-content/uploads/meditaciones/';
            $jft_language_footer = '';
            break;
        case 'french':
            $jft_language_url = 'https://jpa.narcotiquesanonymes.org/';
            $jft_language_dom_element = '*[@class=\'contenu-principal\']';
            $jft_language_footer = ' <br><p id="jft_copyright" class="jft-rendered-element"><a href="https://www.na.org/" target="_blank">Copyright (c) 2007-'.date("Y").', NA World Services, Inc. All Rights Reserved</a></p> ';
            break;
        case 'farsi':
            $pdate = new jDateTimePlus(true, true, 'Asia/Tehran');
            $jft_language_url = 'https://nairan.org/jft/page/' . $pdate->date("m-d", false, false) . '.html';
            $jft_language_dom_element = '*[@id=\'table1\']';
            $jft_language_footer = ' <br><p id="jft_copyright" class="jft-rendered-element"><a href="http://nairan1.org/" target="_blank">انجمن معتادان گمنام ایران <br>شماره ثبت : 21065</a></p> ';
            break;
        case 'portuguese':
            $jft_language_url = 'https://www.na.org.br/meditacao';
            $jft_language_dom_element = '*[@class=\'page-content\']';
            $jft_language_footer = '';
            break;
        case 'german':
            $jft_language_url = 'http://www.narcotics-anonymous.de/nfh/nfh_include.php';
            $jft_language_footer = '<div align="right" id="jft-subscribe" class="jft-rendered-element"><a href="https://www.narcotics-anonymous.de/nur-fuer-heute-anmeldung.html" target="_blank">Anmeldung</a></div>';
            break;
        case 'italian':
            $jft_language_url = 'https://na-italia.org/get-jft';
            $jft_language_dom_element = '*[@class=\'region region-content\']';
            $jft_language_footer = ' <div class=\'footer\'>Narcotici Anonimi Italia: <a href="https://na-italia.org/" target="_blank">https://na-italia.org</a></div> ';
            break;
        case 'russian':
            $jft_language_url = 'http://na-russia.org/eg';
            $jft_language_dom_element = '*[@class=\'module mod-box  deepest\']';
            $jft_language_footer = ' <div class=\'footer\'>Copyright ' . date("Y") . ' - Анонимные Наркоманы. Русскоязычный Зональный Форум.</div> ';
            break;
        case 'japanese':
            $jft_language_url = 'http://najapan.org/just_for_today/';
            $jft_language_dom_element = '*[@id=\'container\']';
            $jft_language_footer = '';
            break;
//        case 'arabic':
//            $jft_language_url = 'https://nakuwait.odoo.com/page/jft';
//            $jft_language_dom_element = '*[@class=\'mb32 col-md-12\']';
//            $jft_language_footer = ' <div class=\'footer\'>Copyright ' . date("Y") . ' - Narcotics Anonymous - Kuwait.</div> ';
//            break;
        case 'swedish':
            $jft_language_url = 'https://www.nasverige.org/dagens-text-img/';
            $jft_language_footer = ' <div class=\'footer\'>Copyright ' . date("Y") . ' - Anonyma Narkomaner NA Sverige.</div> ';
            break;
        case 'danish':
            $jft_language_url = 'https://nadanmark.dk/jft_images/';
            $jft_language_footer = '';
            break;
        default:
            $jft_language_url = 'https://jftna.org/jft/';
            $jft_language_dom_element = 'table';
            $jft_language_footer = '<div align="right" id="jft-subscribe" class="jft-rendered-element"><a href="https://www.jftna.org/jft-subscription.htm" target="_blank">Subscribe</a></div>';
    }
 
    $subscribe_link = '<div align="right" id="jft-subscribe" class="jft-rendered-element"><a href="https://www.jftna.org/jft-subscription.htm" target="_blank">Subscribe</a></div>';
    $jft_class = 'jft-rendered-element';

    // Get the contents of JFT
    if ($jft_layout == 'block' && $jft_language == 'english') {
        libxml_use_internal_errors(true);
        $url = utf8_encode(wp_remote_retrieve_body(wp_remote_get($jft_language_url, $headers)));
        $d = new DOMDocument();
        $d->validateOnParse = true;
        $d->loadHTML($url);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
   
        $jft_ids = array('jft-date','jft-title','jft-page','jft-quote','jft-quote-source','jft-content','jft-thought','jft-copyright');
        $jft_class = 'jft-rendered-element';
        $i = 0;
        $k = 1;
        $content = '<div id="jft-container" class="'.$jft_class.'">';

        foreach ($d->getElementsByTagName('tr') as $element) {
            if ($i != 5) {
                $formated_element = trim($element->nodeValue);
                $content .= '<div id="'.$jft_ids[$i].'" class="'.$jft_class.'">'.$formated_element.'</div>';
            } else {
                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML(utf8_encode(wp_remote_retrieve_body(wp_remote_get($jft_language_url, $headers))));
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                $values = array();
                $xpath = new DOMXPath($dom);
                foreach ($xpath->query('//tr') as $row) {
                    $row_values = array();
                    foreach ($xpath->query('td', $row) as $cell) {
                        $innerHTML= '';
                        $children = $cell->childNodes;
                        foreach ($children as $child) {
                            $innerHTML .= $child->ownerDocument->saveXML($child);
                        }
                        $row_values[] = $innerHTML;
                    }
                    $values[] = $row_values;
                }
                $break_array = preg_split('/<br[^>]*>/i', (join('', $values[5])));
                $content .= '<div id="'.$jft_ids[$i].'" class="'.$jft_class.'">';
                foreach ($break_array as $p) {
                    if (!empty($p)) {
                        $formated_element = '<p id="'.$jft_ids[$i].'-'.$k.'" class="'.$jft_class.'">'.trim($p).'</p>';
                        $content .= preg_replace("/<p[^>]*>([\s]|&nbsp;)*<\/p>/", '', $formated_element);
                        $k++;
                    }
                }
                $content .= '</div>';
            }
            $i++;
        }
        $content .= $subscribe_link;
            $content .= '</div>';
    } elseif ($jft_language == 'german') {
        date_default_timezone_set('Europe/Berlin');
        $content = '<div id="jft-container" class="'.$jft_class.'">';
        $content .= '<div id="jft-container" class="jft-rendered-element">';
        $content .= '<img src="http://www.narcotics-anonymous.de/nfh/files/'.date("md").'.gif" class="jft-image">';
        $content .= $jft_language_footer;
        $content .= '</div>';
    } elseif ($jft_language == 'swedish') {
        date_default_timezone_set('Europe/Stockholm');
        $content = '<div id="jft-container" class="'.$jft_class.'">';
        $content .= '<div id="jft-container" class="jft-rendered-element">';
        $content .= '<img src="https://www.nasverige.org/dagens-text-img/'.date("md").'.jpg" class="jft-image">';
        $content .= $jft_language_footer;
        $content .= '</div>';
    } elseif ($jft_language == 'danish') {
        date_default_timezone_set('Europe/Copenhagen');
        $content = '<div id="jft-container" class="'.$jft_class.'">';
        $content .= '<div id="jft-container" class="jft-rendered-element">';
        $content .= '<img src="http://nadanmark.dk/jft_images/'.date("md").'.jpg" class="jft-image">';
        $content .= $jft_language_footer;
        $content .= '</div>';
    } elseif ($jft_language == 'italian') {
        date_default_timezone_set('Europe/Rome');
        $italian_jft = json_decode(wp_remote_retrieve_body(wp_remote_get($jft_language_url, $headers)), true);
        $ret = '';
        foreach ($italian_jft as $content) {
            $ret .= $content['title'];
            $ret .= $content['content'];
            $ret .= $content['excerpt'];
        }
        $content = '<div id="jft-container" class="jft-rendered-element">';
        $content .= mb_convert_encoding($ret, 'HTML-ENTITIES', 'UTF-8');
        $content .= $jft_language_footer;
        $content .= '</div>';
    } elseif ($jft_language == 'spanish') {
        date_default_timezone_set('America/Mexico_City');
        $spanish_jft = $jft_language_url . date("m") . "/" . date("d") . ".html";
        $content = <<<CON
    <style type="text/css">
        @import url("https://forozonalatino.org/wp-content/uploads/meditaciones/css/sxh.css");
    </style>
CON;
        $sjft = new DOMDocument;
        libxml_use_internal_errors(true);
        $sjft->loadHTML(mb_convert_encoding(wp_remote_retrieve_body(wp_remote_get($spanish_jft, $headers)), 'HTML-ENTITIES', "UTF-8"));
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $sbody = $sjft->saveHTML($sjft->getElementsByTagName('body')->item(0));
        $content .= str_replace(array( '<body>', '</body>' ), '', $sbody);
    } else {
        $jft_get = wp_remote_get($jft_language_url, $headers);
        $jft_content_header = wp_remote_retrieve_header($jft_get, 'content-type');
        $jft_body = wp_remote_retrieve_body($jft_get);

        if (preg_match('/\s*charset=(.*)?/im', $jft_content_header, $matches)) {
            if (isset($matches[1])) {
                $char_encoding = strtoupper(trim($matches[1]));
            } else {
                $char_encoding = "UTF-8";
            }
        } else {
            $char_encoding = "UTF-8";
        }

        $content = '';
        $d1 = new DOMDocument;
        $jft = new DOMDocument;
        libxml_use_internal_errors(true);
        $d1->loadHTML(mb_convert_encoding($jft_body, 'HTML-ENTITIES', $char_encoding));
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new DOMXpath($d1);
        $body = $xpath->query("//$jft_language_dom_element");
        foreach ($body as $child) {
            $jft->appendChild($jft->importNode($child, true));
        }
        $content .= $jft->saveHTML();
        $content .= $jft_language_footer;
    }
        $content .= "<style type='text/css'>" . get_option('custom_css_jft') . "</style>";
    return $content;
}

// create [jft] shortcode
add_shortcode('jft', 'jft_func');

/** START Fetch JFT Widget **/
// register JFT_Widget
add_action('widgets_init', function () {
    register_widget('JFT_Widget');
});
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class JFT_Widget extends WP_Widget
{
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
    /**
     * Sets up a new Fetch JFT widget instance.
     *
     */
    public function __construct()
    {
        $widget_ops = array(
            'classname' => 'JFT_widget',
            'description' => 'Displays the Just For Today',
        );
        parent::__construct('JFT_widget', 'Fetch JFT', $widget_ops);
    }

    /**
    * Outputs the content for the current Fetch JFT widget instance.
    *
    *
    * @jft_func gets and parses the jft
    *
    * @param array $args     Display arguments including 'before_title', 'after_title',
    *                        'before_widget', and 'after_widget'.
    * @param array $instance Settings for the current Area Meetings Dropdown widget instance.
    */

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (! empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        echo jft_func($atts);
        echo $args['after_widget'];
    }

    /**
     * Outputs the settings form for the Fetch JFT widget.
     * @param $instance
     */
    public function form($instance)
    {
        $title = ! empty($instance['title']) ? $instance['title'] : esc_html__('Title', 'text_domain');
        ?>
        <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
        <?php esc_attr_e('Title:', 'text_domain'); ?>
        </label>

        <input
            class="widefat"
            id="<?php echo esc_attr($this->get_field_id('title')); ?>"
            name="<?php echo esc_attr($this->get_field_name('title')); ?>"
            type="text"
            value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    /**
    * Handles updating settings for the current Fetch JFT widget instance.
    *
    * @param array $new_instance New settings for this instance as input by the user via
    *                            WP_Widget::form().
    * @param array $old_instance Old settings for this instance.
    * @return array Updated settings to save.
    */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = ( ! empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}
/** END Fetch JFT Widget **/
?>
