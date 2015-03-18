<?php namespace Stichoza\GoogleTranslate;

use Stichoza\GoogleTranslate\Exception\RequestException;
use Stichoza\GoogleTranslate\Exception\TranslationException;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;

/**
 * Free Google Translate PHP Package
 *
 * @author      Levan Velijanashvili <me@stichoza.com>
 * @link        http://stichoza.com/
 * @license     MIT
 */
class TranslateClient {

    /**
     * @var \Guzzle\Http\Client HTTP Client
     */
    private $httpClient;
    
    /**
     * @var string Source language - from where the string should be translated
     */
    private $sourceLanguage;
    
    /**
     * @var string Target language - to which language string should be translated
     */
    private $targetLanguage;
    
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
        'tl'       => null, // Target language
        'ie'       => 'UTF-8', // Input encoding
        'oe'       => 'UTF-8', // Output encoding
        'multires' => '1',
        'otf'      => '0',
        'pc'       => '1',
        'trs'      => '1',
        'ssel'     => '0',
        'tsel'     => '0',
        'sc'       => '1'
    ];

    /**
     * @var array Regex key-value patterns to replace on response data
     */
    private $resultRegexes = [
        '/,+/'  => ',',
        '/\[,/' => '[',
    ];

    /**
     * Class constructor
     * 
     * @param string $source Source language (Optional)
     * @param string $target Target language (Optional)
     */
    public function __construct($source = 'auto', $target = 'en') {
        $this->httpClient = new GuzzleHttpClient(); // Create HTTP client
        $this->setSource($source)->setTarget($target); // Set languages
    }

    public static function __callStatic($name, $arguments) {
        return 'lol';
    }

    /**
     * Set source language we are transleting from
     * 
     * @param string $source Language code
     * @return TranslateClient
     */
    public function setSource($source = null) {
        $this->sourceLanguage = is_null($source) ? 'auto' : $source;
        return $this;
    }
    
    /**
     * Set translation language we are transleting to
     * 
     * @param string $target Language code
     * @return TranslateClient
     */
    public function setTarget($target) {
        $this->targetLanguage = $target;
        return $this;
    }

    /**
     * Translate text
     * 
     * @param string $string Text to translate
     * @throws TranslationException if the provided argument is not of type 'string'
     * @throws RequestException if the HTTP request fails
     * @return string/boolean Translated text
     */
    public function translate($string) {

        if (!is_string($string)) {
            throw new TranslationException("Invalid string provided");
        }

        $queryArray = array_merge($this->urlParams, [
            'text' => $string,
            'sl'   => $this->sourceLanguage,
            'tl'   => $this->targetLanguage
        ]);

        try {
            $response = $this->httpClient->get($this->urlBase, ['query' => $queryArray]);
        } catch (GuzzleRequestException $e) {
            throw new RequestException("Error processing request");
        }

        $body = $response->getBody(); // Get response body

        // Modify body to avoid json errors
        $bodyJson = preg_replace(array_keys($this->resultRegexes), array_values($this->resultRegexes), $body);
        
        // Decode JSON data
        if (($bodyArray = json_decode($bodyJson, true)) === null) {
            throw new TranslationException('Data cannot be decoded or it\'s deeper than the recursion limit');
        }

        // Check if translated data exists
        if (empty($bodyArray[0])) return false;

        // Reduce array to generate translated sentenece
        return array_reduce($bodyArray[0], function($carry, $item) {
            $carry .= $item[0];
            return $carry;
        });

    }

}
