<?php
/*
Plugin Name: Fetch JFT
Plugin URI: https://wordpress.org/plugins/fetch-jft/
Description: This is a plugin that fetches the Just For Today from NAWS and puts it on your site Simply add [jft] shortcode to your page. Fetch JFT Widget can be added to your sidebar or footer as well.
Version: 1.4.4
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

require_once('admin/jft-dashboard.php');

// create admin menu settings page
add_action('admin_menu', 'jft_options_menu');
function jft_options_menu() {
 add_options_page('Fetch JFT Plugin Settings', 'Fetch JFT', 'manage_options', 'jft-plugin', 'fetch_jft_plugin_page');
}

// add settings link to plugins page
function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=jft-plugin">' . __( 'Settings' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );

function jft_func($atts = []) {
    extract(shortcode_atts(array(
        'language'	=>	'',
        'layout'	=>	''
    ), $atts));

    //Set language and layout - shortcode parameter overrides admin settings
    $jft_language = (!empty($language) ? sanitize_text_field(strtolower($language)) : get_option('jft_language'));
    $jft_layout = (!empty($layout) ? sanitize_text_field(strtolower($layout)) : get_option('jft_layout'));

    switch ($jft_language) {
        case 'english' :
            $jft_language_url = 'https://jftna.org/jft/';
            $jft_language_dom_element = 'table';
   $jft_language_footer = '<div align="right" id="jft-subscribe" class="jft-rendered-element"><a href="https://www.jftna.org/jft-subscription.htm" target="_blank">Subscribe</a></div>';
            break;
        case 'spanish':
            $jft_language_url = 'https://forozonalatino.org/sxh';
            $jft_language_dom_element = '*[@id=\'sx-wrapper\']';
   $jft_language_footer = '<p class="copyright-sxh">Servicio del Foro Zonal Latinoamericano, Copyright 2017 NA World Services, Inc. Todos los Derechos Reservados.</p>';
            break;
        case 'french':
            $jft_language_url = 'https://jpa.narcotiquesanonymes.org/';
            $jft_language_dom_element = '*[@class=\'contenu-principal\']';
   $jft_language_footer = ' <br><p id="jft_copyright" class="'.$jft_class.'"><a href="https://www.na.org/" target="_blank">Copyright (c) 2007-'.date("Y").', NA World Services, Inc. All Rights Reserved</a></p> ';
            break;
        case 'portuguese':
            $jft_language_url = 'http://www.na.org.br/meditacao';
            $jft_language_dom_element = '*[@class=\'content-home\']';
   $jft_language_footer = ' <div class=\'footer\'>Todos os direitos reservados à: http://www.na.org.br</div> ';
            break;
        default:
            $jft_language_url = 'https://jftna.org/jft/';
            $jft_language_dom_element = 'table';
   $jft_language_footer = '<div align="right" id="jft-subscribe" class="jft-rendered-element"><a href="https://www.jftna.org/jft-subscription.htm" target="_blank">Subscribe</a></div>';
    }
 
    $subscribe_link = '<div align="right" id="jft-subscribe" class="jft-rendered-element"><a href="https://www.jftna.org/jft-subscription.htm" target="_blank">Subscribe</a></div>';
 
    // Get the contents of JFT
  if($jft_layout == 'block' && $jft_language == 'english') {
   libxml_use_internal_errors(true);
    $url = wp_remote_fopen($jft_language_url);
   libxml_clear_errors();
   libxml_use_internal_errors(false);
        $d = new DOMDocument();
        $d->validateOnParse = true;
         $d->loadHTML($url);
   
        $jft_ids = array('jft-date','jft-title','jft-page','jft-quote','jft-quote-source','jft-content','jft-thought','jft-copyright');
        $jft_class = 'jft-rendered-element';
        $i = 0;
         $k = 1;
         $content = '';
            $content = '<div id="jft-container" class="'.$jft_class.'">';

            foreach($d->getElementsByTagName('tr') as $element) {
                if($i != 5) {
                    $formated_element = trim($element->nodeValue);
                    $content .= '<div id="'.$jft_ids[$i].'" class="'.$jft_class.'">'.$formated_element.'</div>';
                } else {
                    $dom = new DOMDocument();
                    $dom->loadHTML(wp_remote_fopen($jft_language_url));
                    $values = array();
                    $xpath = new DOMXPath($dom);
                    foreach($xpath->query('//tr') as $row) {
                        $row_values = array();
                        foreach($xpath->query('td', $row) as $cell) {
                            $innerHTML= '';
                            $children = $cell->childNodes;
                            foreach ($children as $child) {
                                $innerHTML .= $child->ownerDocument->saveXML( $child );
                            }
                            $row_values[] = $innerHTML;
                        }
                        $values[] = $row_values;
                    }
                    $break_array = preg_split('/<br[^>]*>/i', (join('', $values[5])));
                    $content .= '<div id="'.$jft_ids[$i].'" class="'.$jft_class.'">';
                    foreach($break_array as $p) {
                        if(!empty($p)){
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
        } else {
            $d1 = new DOMDocument;
            $jft = new DOMDocument;
            libxml_use_internal_errors(true);
            $d1->loadHTML(wp_remote_fopen($jft_language_url));
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
add_shortcode( 'jft', 'jft_func' );

/** START Fetch JFT Widget **/
// register JFT_Widget
add_action( 'widgets_init', function(){
    register_widget( 'JFT_Widget' );
});

class JFT_Widget extends WP_Widget {
    /**
     * Sets up a new Fetch JFT widget instance.
     *
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'JFT_widget',
            'description' => 'Displays the Just For Today',
        );
    parent::__construct( 'JFT_widget', 'Fetch JFT', $widget_ops );
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

    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        echo jft_func( $atts );
        echo $args['after_widget'];
    }
    /**
    * Outputs the settings form for the Fetch JFT widget.
    *
    */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Title', 'text_domain' );
        ?>
        <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
        <?php esc_attr_e( 'Title:', 'text_domain' ); ?>
        </label>

        <input
            class="widefat"
            id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
            name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
            type="text"
            value="<?php echo esc_attr( $title ); ?>">
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
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
}
/** END Fetch JFT Widget **/
?>
