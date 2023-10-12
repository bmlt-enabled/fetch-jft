<?php

namespace Jft;

require_once(__DIR__ . '/jDateTimePlus.php');

class Reading
{
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0';
    const DEFAULT_CHAR_ENCODING = "UTF-8";
    const CSS_CLASS = 'jft-rendered-element';

    const DEFAULT_CONFIG = [
        'url' => 'https://jftna.org/jft/',
        'dom_element' => 'table',
        'footer' => '<div align="right" id="jft-subscribe" class="jft-rendered-element"><a href="https://www.jftna.org/jft-subscription.htm" target="_blank">Subscribe</a></div>',
        'language' => 'english'
    ];

    public function renderReading($atts = []): string
    {
        $args = shortcode_atts(['layout' => '', 'language' => ''], $atts);
        $layout = $this->sanitizeLayout($args);
        $language = $this->sanitizeLanguage($args);
        $languageConfig = $this->getLanguageConfig($language);
        $response = $this->getJft($languageConfig);
        if ($layout == "block" && $language != "english") {
            return '<b>Fetch JFT Error</b> - Block layout can only be used with english. Language set: ' . $language;
        }
        return $this->getContent($layout, $response, $languageConfig);
    }

    protected function getLanguageConfig($language): array
    {
        $pdate = new \jDateTimePlus(true, true, 'Asia/Tehran');
        $languageConfig = [
            'english' => self::DEFAULT_CONFIG,
            'spanish' => [
                'url' => 'https://forozonalatino.org/wp-content/uploads/meditaciones/' . $this->getTimezoneDate('spanish', 'm/d') . ".html",
                'footer' => '',
                'dom_element' => '',
                'language' => 'spanish'
            ],
            'french' => [
                'url' => 'https://jpa.narcotiquesanonymes.org/',
                'footer' => '<br><p id="jft_copyright" class="jft-rendered-element"><a href="https://www.na.org/" target="_blank">Copyright (c) 2007-' . date("Y") . ', NA World Services, Inc. All Rights Reserved</a></p>',
                'dom_element' => '*[@class=\'contenu-principal\']',
                'language' => 'french'
            ],
            'portuguese' => [
                'url' => 'https://www.na.org.br/meditacao',
                'footer' => '',
                'dom_element' => 'div[contains(@class, \'elementor-element-a5fb4b5\')]',
                'language' => 'portuguese'
            ],
            'german' => [
                'url' => 'https://www.narcotics-anonymous.de/nfh/files/' . $this->getTimezoneDate('german') . '.gif',
                'footer' => '<div align="right" id="jft-subscribe" class="jft-rendered-element"><a href="https://www.narcotics-anonymous.de/nur-fuer-heute-anmeldung.html" target="_blank">Anmeldung</a></div>',
                'dom_element' => '',
                'language' => 'german'
            ],
            'italian' => [
                'url' => 'https://na-italia.org/get-jft',
                'footer' => '<div class=\'footer\'>Narcotici Anonimi Italia: <a href="https://na-italia.org/" target="_blank">https://na-italia.org</a></div>',
                'dom_element' => '*[@class=\'region region-content\']',
                'language' => 'italian'
            ],
            'russian' => [
                'url' => 'http://na-russia.org/eg',
                'footer' => '<div class=\'footer\'>Copyright ' . date("Y") . ' - Анонимные Наркоманы. Русскоязычный Зональный Форум.</div>',
                'dom_element' => '*[@class=\'module mod-box  deepest\']',
                'language' => 'russian'
            ],
            'japanese' => [
                'url' => 'http://najapan.org/just_for_today/',
                'footer' => '',
                'dom_element' => '*[@id=\'container\']',
                'language' => 'japanese'
            ],
            'swedish' => [
                'url' => 'https://www.nasverige.org/dagens-text-img/' . $this->getTimezoneDate('swedish') . '.jpg',
                'footer' => '<div class=\'footer\'>Copyright ' . date("Y") . ' - Anonyma Narkomaner NA Sverige.</div>',
                'dom_element' => '',
                'language' => 'swedish'
            ],
            'danish' => [
                'url' => 'https://nadanmark.dk/jft_images/' . $this->getTimezoneDate('danish') . '.jpg',
                'footer' => '',
                'dom_element' => '',
                'language' => 'danish'
            ],
            'farsi' => [
                'url' => 'https://nairan1.org/jft/page/' . $pdate->date("m-d", false, false) . '.html',
                'footer' => '<br><p id="jft_copyright" class="jft-rendered-element"><a href="http://nairan1.org/" target="_blank">انجمن معتادان گمنام ایران <br>شماره ثبت : 21065</a></p>',
                'dom_element' => '*[@id=\'table1\']',
                'language' => 'farsi'
            ]
        ];

        return $languageConfig[$language] ?? self::DEFAULT_CONFIG;
    }

    protected function sanitizeLayout(array $args): string
    {
        return !empty($args['layout']) ? sanitize_text_field(strtolower($args['layout'])) : get_option('jft_layout');
    }

    protected function sanitizeLanguage(array $args): string
    {
        return !empty($args['language']) ? sanitize_text_field(strtolower($args['language'])) : get_option('jft_language');
    }

    protected function getJft(array $languageConfig): string
    {
        $charset = self::DEFAULT_CHAR_ENCODING;
        $get = wp_remote_get($languageConfig['url'], ['headers' => ['User-Agent' => self::USER_AGENT], 'timeout' => 60]);
        $contentTypeHeader = wp_remote_retrieve_header($get, 'content-type');
        if ($contentTypeHeader) {
            if (preg_match('/charset=([\w-]+)/i', $contentTypeHeader, $matches)) {
                $charset = $matches[1];
            }
        }
        $data = wp_remote_retrieve_body($get);
        // if content is not image, get html entities
        if ($contentTypeHeader && !strpos($contentTypeHeader, 'image')) {
            $data = mb_convert_encoding($data, 'HTML-ENTITIES', $charset);
        }
        return $data;
    }

    protected function getContent(string $layout, string $data, array $languageConfig): string
    {
        return $layout === 'block' ? $this->generateBlockContent($data, $languageConfig) : $this->generateContent($data, $languageConfig);
    }

    protected function generateBlockContent(string $data, array $languageConfig): string
    {
        $domDoc = $this->createDomDocument($data);
        $jft_ids = array('jft-date', 'jft-title', 'jft-page', 'jft-quote', 'jft-quote-source', 'jft-content', 'jft-thought', 'jft-copyright');
        $i = 0;
        $k = 1;
        $content = '<div id="jft-container" class="' . self::CSS_CLASS . '">';

        foreach ($domDoc->getElementsByTagName('tr') as $element) {
            if ($i != 5) {
                $formated_element = trim($element->nodeValue);
                $content .= '<div id="' . $jft_ids[$i] . '" class="' . self::CSS_CLASS . '">' . $formated_element . '</div>';
            } else {
                $values = [];
                $xpath = new \DOMXPath($domDoc);
                foreach ($xpath->query('//tr') as $row) {
                    $row_values = array();
                    foreach ($xpath->query('td', $row) as $cell) {
                        $innerHTML = '';
                        $children = $cell->childNodes;
                        foreach ($children as $child) {
                            $innerHTML .= $child->ownerDocument->saveXML($child);
                        }
                        $row_values[] = $innerHTML;
                    }
                    $values[] = $row_values;
                }
                $break_array = preg_split('/<br[^>]*>/i', (join('', $values[5])));
                $content .= '<div id="' . $jft_ids[$i] . '" class="' . self::CSS_CLASS . '">';
                foreach ($break_array as $p) {
                    if (!empty($p)) {
                        $formated_element = '<p id="' . $jft_ids[$i] . '-' . $k . '" class="' . self::CSS_CLASS . '">' . trim($p) . '</p>';
                        $content .= preg_replace("/<p[^>]*>([\s]|&nbsp;)*<\/p>/", '', $formated_element);
                        $k++;
                    }
                }
                $content .= '</div>';
            }
            $i++;
        }
        $content .= $languageConfig['footer'];
        $content .= '</div>';
        $content .= "<style>" . get_option('custom_css_jft') . "</style>";
        return $content;
    }

    protected function createDomDocument(string $data): \DOMDocument
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        return $dom;
    }

    protected function getTimezoneDate(string $language, $format = 'md'): string
    {
        $timezoneMap = [
            'german' => 'Europe/Berlin',
            'swedish' => 'Europe/Stockholm',
            'danish' => 'Europe/Copenhagen',
            'italian' => 'Europe/Rome',
            'spanish' => 'America/Mexico_City',
        ];

        $timezone = array_key_exists($language, $timezoneMap) ? new \DateTimeZone($timezoneMap[$language]) : new \DateTimeZone('America/New_York');
        $date = new \DateTime('now', $timezone);
        return $date->format($format);
    }

    protected function generateContent(string $data, array $languageConfig): string
    {
        $content = '<div id="jft-container" class="' . self::CSS_CLASS . '">';
        $language = $languageConfig['language'];
        $footer = $languageConfig['footer'];

        switch ($language) {
            case 'german':
            case 'swedish':
            case 'danish':
                $content .= $this->generateImageContent($languageConfig);
                break;
            case 'italian':
                $content .= $this->generateItalianContent($data);
                break;
            case 'spanish':
                $content .= $this->generateSpanishContent($data);
                break;
            default:
                $content .= $this->generateDefaultContent($data, $languageConfig);
                break;
        }

        $content .= $footer;
        $content .= '</div>';
        $content .= "<style>" . get_option('custom_css_jft') . "</style>";
        return $content;
    }

    protected function generateImageContent(array $languageConfig): string
    {
        return '<img src="' . $languageConfig['url'] . '" class="jft-image">';
    }

    protected function generateItalianContent(string $data): string
    {
        $content = '';
        $italianJft = json_decode($data, true);
        foreach ($italianJft as $jftData) {
            $content .= $jftData['title'];
            $content .= $jftData['content'];
            $content .= $jftData['excerpt'];
        }
        return $content;
    }

    protected function generateSpanishContent(string $data): string
    {

        $domDoc = $this->createDomDocument($data);
        $body = $domDoc->saveHTML($domDoc->getElementsByTagName('body')->item(0));
        $body .= <<<CON
    <style>
        @import url("https://forozonalatino.org/wp-content/uploads/meditaciones/css/sxh.css");
    </style>
CON;
        return str_replace(['<body>', '</body>'], '', $body);
    }

    protected function generateDefaultContent(string $data, array $languageConfig): string
    {
        $domDoc = $this->createDomDocument($data);
        $xpath = new \DOMXpath($domDoc);
        $body = $xpath->query("//" . $languageConfig['dom_element']);
        $reading = new \DOMDocument();
        foreach ($body as $child) {
            $reading->appendChild($reading->importNode($child, true));
        }
        return $reading->saveHTML();
    }
}
