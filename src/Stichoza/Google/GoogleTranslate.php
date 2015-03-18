<?php namespace Stichoza\Google;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;

/**
 * Free Google Translate PHP Package
 *
 * @author      Levan Velijanashvili <me@stichoza.com>
 * @link        http://stichoza.com/
 * @license     MIT
 */
class GoogleTranslate {

    /**
     * @var \Guzzle\Http\Client HTTP Client
     */
    private $httpClient;
    
    /**
     * @var string Source language - from where the string should be translated
     */
    private $sourceLanguage;
    
    /**
     * @var string Translation language - to which language string should be translated
     */
    private $translationLanguage;
    
    /**
     * @var string Google Translate URL base
     */
    private $urlBase = 'http://translate.google.com/translate_a/t';

    /**
     * @var array URL Parameters
     */
    private $urlParams = [
        'client'   => 't',
        'text'     => null, // String
        'hl'       => 'en',
        'sl'       => null, // Source language
        'tl'       => null, // Translation language
        'ie'       => 'UTF-8',
        'oe'       => 'UTF-8',
        'multires' => '1',
        'otf'      => '1',
        'pc'       => '1',
        'trs'      => '1',
        'ssel'     => '3',
        'tsel'     => '6',
        'sc'       => '1'
    ];

    /**
     * Class constructor
     * 
     * @param string $from Language translating from (Optional)
     * @param string $to Language translating to (Optional)
     */
    public function __construct($from = 'auto', $to = 'en') {
        
        /*
         * Create HTTP client
         * Currently using Guzzle and it's awesome!
         */        
        $this->httpClient = new GuzzleHttpClient();

        /*
         * Set languages
         */
        $this->setSource($from)->setTranslation($to);

    }

    /**
     * Set source language we are transleting from
     * 
     * @param string $lang Language code
     * @return GoogleTranslate
     */
    public function setSource($lang = null) {
        $this->sourceLanguage = is_null($lang) ? 'auto' : $lang;
        return $this;
    }
    
    /**
     * Set translation language we are transleting to
     * 
     * @param string $lang Language code
     * @return GoogleTranslate
     * @access public
     */
    public function setTranslation($lang) {
        $this->translationLanguage = $lang;
        return $this;
    }

    /**
     * Translate text
     * 
     * @param string $string Text to translate
     * @return string/boolean Translated text
     * @access public
     */
    public function translate($string) {

        $queryArray = array_merge($this->urlParams, [
            'text' => $string,
            'sl'   => $this->sourceLanguage,
            'tl'   => $this->translationLanguage
        ]);

        $response = $this->httpClient->get($this->urlBase, ['query' => $queryArray]);

        return $response->getBody();

    }

    /**
     * Static method for translating text
     * 
     * @param string $string Text to translate
     * @param string $from Language code
     * @param string $to Language code
     * @return string/boolean Translated text
     * @access public
     */
    public static function staticTranslate($string, $from, $to) {

        

        /*
         * remove repeated commas (causing JSON syntax error)
         */
        $result = preg_replace('!,+!', ',', self::makeCurl($url));
        $result = str_replace ("[,", "[", $result);
        $resultArray = json_decode($result, true);

        $finalResult = '';
        if (!empty($resultArray[0])) {
            foreach ($resultArray[0] as $results) {
                $finalResult .= $results[0];
            }
            return $finalResult;
        }
        return false;
    }

}

?>
