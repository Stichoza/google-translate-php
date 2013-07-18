<?php

class GoogleTranslate {

    public $lastResult;
    private $langFrom;
    private $langTo;
    private $urlFormat = "http://translate.google.com/translate_a/t?client=t&text=%s&hl=en&sl=%s&tl=%s&ie=UTF-8&oe=UTF-8&multires=1&otf=1&pc=1&trs=1&ssel=3&tsel=6&sc=1";

    public function __construct($from, $to) {
        $this->setLangFrom($from)->setLangTo($to);
    }

    public function setLangFrom($lang) {
        $this->langFrom = $lang;
        return $this;
    }

    public function setLangTo($lang) {
        $this->langTo = $lang;
        return $this;
    }

    public static final function makeCurl($url, array $params = array(), $cookieSet = false) {
        if (!$cookieSet) {
            $cookie = tempnam("/tmp", "CURLCOOKIE"); //create a cookie file
            // visit the homepage to set the cookie properly
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


    public function translate($string) {
        $url = sprintf($this->urlFormat, rawurlencode($string), $this->langFrom, $this->langTo);
        $result = preg_replace('!,+!', ',', self::makeCurl($url));
        $resultArray = json_decode($result, true);
        //print_r($resultArray);die();
        return $this->lastResult = $resultArray[0][0][0];
    }

}

?>
