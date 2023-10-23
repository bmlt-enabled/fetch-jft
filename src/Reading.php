<?php

namespace Jft;

use FetchMeditation\JFTLanguage;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

require_once(__DIR__ . '/jDateTimePlus.php');

class Reading
{
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0';
    const CSS_CLASS = 'jft-rendered-element';

    public function renderReading($atts = []): string
    {
        $layout = sanitize_text_field(strtolower($atts['layout'] ?? get_option('jft_layout')));
        $language = sanitize_text_field(strtolower($atts['language'] ?? get_option('jft_language')));
        $languageConfig = $this->getLanguageConfig($language);
        return $this->generateContent($languageConfig, $layout);
    }

    protected function getLanguageConfig($language): array
    {
        $pdate = new \jDateTimePlus(true, true, 'Asia/Tehran');
        $languageConfig = [
            'english' => ['language' => 'english'],
            'french' => ['language' => 'french'],
            'italian' => ['language' => 'italian'],
            'japanese' => ['language' => 'japanese'],
            'portuguese' => ['language' => 'portuguese'],
            'russian' => ['language' => 'russian'],
            'spanish' => ['language' => 'spanish'],
            'swedish' => ['language' => 'swedish'],
            'german' => ['language' => 'german'],
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

        return $languageConfig[$language] ?? [];
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

    protected function generateContent(array $languageConfig, $layout): string
    {
        switch ($languageConfig['language']) {
            case 'danish':
                $content = $this->generateImageContent($languageConfig);
                break;
            case 'farsi':
                $content = $this->generateFarsiContent($languageConfig);
                break;
            default:
                $content = $this->generateDefaultContent($languageConfig, $layout);
                break;
        }

        $content .= "<style>" . get_option('custom_css_jft') . "</style>";
        return $content;
    }

    protected function generateImageContent(array $languageConfig): string
    {
        $content = '<img src="' . $languageConfig['url'] . '" class="jft-image">';
        $content .= $languageConfig['footer'];
        return $content;
    }

    protected function generateFarsiContent(array $languageConfig): string
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $get = wp_remote_get($languageConfig['url'], ['headers' => ['User-Agent' => self::USER_AGENT], 'timeout' => 60]);
        $data = wp_remote_retrieve_body($get);
        $dom->loadHTML('<?xml encoding="ISO-8859-1">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXpath($dom);
        $elements = $xpath->query("//" . $languageConfig['dom_element']);
        $reading = new \DOMDocument();
        foreach ($elements as $element) {
            $reading->appendChild($reading->importNode($element, true));
        }
        $content = '<div id="jft-container" class="' . self::CSS_CLASS . '">';
        $content .=  $reading->saveHTML();
        $content .= $languageConfig['footer'];
        $content .= '</div>';
        return $content;
    }

    protected function generateDefaultContent(array $languageConfig, string $layout): string
    {
        $selectedLanguage = match ($languageConfig['language']) {
            'english' => JFTLanguage::English,
            'french' => JFTLanguage::French,
            'german' => JFTLanguage::German,
            'italian' => JFTLanguage::Italian,
            'japanese' => JFTLanguage::Japanese,
            'portuguese' => JFTLanguage::Portuguese,
            'russian' => JFTLanguage::Russian,
            'spanish' => JFTLanguage::Spanish,
            'swedish' => JFTLanguage::Swedish,
            default => JFTLanguage::English
        };

        $settings = new JFTSettings($selectedLanguage);
        $instance = JFT::getInstance($settings);
        $entry = $instance->fetch();
        return static::buildLayout($entry, $layout === "block");
    }

    private static function buildLayout(object $entry, bool $inBlock): string
    {
        $cssIdentifier = $inBlock ? 'jft' : 'jft-table';

        $paragraphContent = '';
        $count = 1;

        foreach ($entry->content as $c) {
            if ($inBlock) {
                $paragraphContent .= "\n    <p id=\"$cssIdentifier-content-$count\" class=\"$cssIdentifier-rendered-element\">$c</p>";
            } else {
                $paragraphContent .= "$c<br><br>";
            }
            $count++;
        }
        $paragraphContent .= "\n";

        $content = "\n<div id=\"$cssIdentifier-container\" class=\"jft-rendered-element\">\n";
        if (!$inBlock) {
            $content .= '<table align="center">' . "\n";
        }

        $data = [
            'date' => $entry->date,
            'title' => $entry->title,
            'page' => $entry->page,
            'quote' => $entry->quote,
            'source' => $entry->source,
            'paragraphs' => $paragraphContent,
            'thought' => $entry->thought,
            'copyright' => $entry->copyright,
        ];

        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if ($key === 'quote' && !$inBlock) {
                $element = '<i>' . $value . '</i>';
            } elseif ($key === 'title' && !$inBlock) {
                $element = '<h1>' . $value . '</h1>';
            } elseif ($key === 'date' && !$inBlock) {
                $element = '<h2>' . $value . '</h2>';
            } else {
                $element = $value;
            }
            if ($key === 'source') {
                $key = 'quote-source';
            }

            if ($inBlock) {
                $content .= "  <div id=\"$cssIdentifier-$key\" class=\"$cssIdentifier-rendered-element\">$element</div>\n";
            } else {
                $alignment = in_array($key, ['title', 'page', 'quote-source']) ? 'center' : 'left';
                $lineBreak = in_array($key, ['quote-source', 'quote', 'thought', 'page']) ? '<br><br>' : '';
                $content .= "<tr><td align=\"$alignment\">$element$lineBreak</td></tr>\n";
            }
        }

        $content .= $inBlock ? "</div>\n" : "</table>\n</div>\n";
        return $content;
    }
}
