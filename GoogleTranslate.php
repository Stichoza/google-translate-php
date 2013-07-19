<?php

/**
 * Google Translate PHP class
 *
 * @author      Levan Velijanashvili <me@stichoza.com>
 * @link        http://stichoza.com/
 * @version     1.3.0
 * @access      public
 */
class GoogleTranslate {
    
    /**
     * Last translation
     * @var string
     * @access private
     */
    public $lastResult = "";
    
    /**
     * Language translating from
     * @var string
     * @access private
     */
    private $langFrom;
    
    /**
     * Language translating to
     * @var string
     * @access private
     */
    private $langTo;
    
    /**
     * Google Translate URL format
     * @var string
     * @access private
     */
    private static $urlFormat = "http://translate.google.com/translate_a/t?client=t&text=%s&hl=en&sl=%s&tl=%s&ie=UTF-8&oe=UTF-8&multires=1&otf=1&pc=1&trs=1&ssel=3&tsel=6&sc=1";

    /**
     * Class constructor
     * 
     * @param string $from Language translating from (Optional)
     * @param string $to Language translating to (Optional)
     * @access public
     */
    public function __construct($from = "en", $to = "ka") {
        $this->setLangFrom($from)->setLangTo($to);
    }

    /**
     * Set language we are transleting from
     * 
     * @param string $from Language code
     * @return GoogleTranslate
     * @access public
     */
    public function setLangFrom($lang) {
        $this->langFrom = $lang;
        return $this;
    }
    
    /**
     * Set language we are transleting to
     * 
     * @param string $to Language code
     * @return GoogleTranslate
     * @access public
     */
    public function setLangTo($lang) {
        $this->langTo = $lang;
        return $this;
    }
    
    
    /**
     * Simplified curl method
     * @param string $url URL
     * @param array $params Parameter array
     * @param boolean $cookieSet
     * @return string
     * @access public
     */
    public static final function makeCurl($url, array $params = array(), $cookieSet = false) {
        if (!$cookieSet) {
            $cookie = tempnam("/tmp", "CURLCOOKIE");
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
        }
        
        $queryString = http_build_query($params);

        $curl = curl_init($url . "?" . $queryString);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        
        return $output;
    }

    /**
     * Translate text
     * 
     * @param string $string Text to translate
     * @return string Translated text
     * @access public
     */
    public function translate($string) {
        $url = sprintf(self::$urlFormat, rawurlencode($string), $this->langFrom, $this->langTo);
        $result = preg_replace('!,+!', ',', self::makeCurl($url)); // remove repeated commas (causing JSON syntax error)
        $resultArray = json_decode($result, true);
        return $this->lastResult = $resultArray[0][0][0];
    }

    /**
     * Static method for translating text
     * 
     * @param string $string Text to translate
     * @param string $from Language code
     * @param string $to Language code
     * @return string Translated text
     * @access public
     */
    public static function staticTranslate($string, $from, $to) {
        $url = sprintf(self::$urlFormat, rawurlencode($string), $from, $to);
        $result = preg_replace('!,+!', ',', self::makeCurl($url)); // remove repeated commas (causing JSON syntax error)
        $resultArray = json_decode($result, true);
        return $resultArray[0][0][0];
    }

}

?>
