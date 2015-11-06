<?php
namespace Stichoza\GoogleTranslate;

use Exception;
use ErrorException;
use BadMethodCallException;
use InvalidArgumentException;
use UnexpectedValueException;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;

/**
 * Free Google Translate API PHP Package
 *
 * @author      Levan Velijanashvili <me@stichoza.com>
 * @link        http://stichoza.com/
 * @license     MIT
 */
class TranslateClient
{
    /**
     * @var TranslateClient Because nobody cares about singletons
     */
    private static $staticInstance;

    /**
     * @var \GuzzleHttp\Client HTTP Client
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
     * @var string|boolean Last detected source language
     */
    private static $lastDetectedSource;

    /**
     * @var string Google Translate URL base
     */
    private $urlBase = 'http://translate.google.com/translate_a/t';

    /**
     * @var array URL Parameters
     */
    private $urlParams = [
        'client'   => 't',
        'hl'       => 'en',
        'sl'       => null, // Source language
        'tl'       => null, // Target language
        'text'     => null, // String to translate
        'ie'       => 'UTF-8', // Input encoding
        'oe'       => 'UTF-8', // Output encoding
        'multires' => 1,
        'otf'      => 0,
        'pc'       => 1,
        'trs'      => 1,
        'ssel'     => 0,
        'tsel'     => 0,
        'sc'       => 1,
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
     * For more information about HTTP client configuration options, visit
     * "Creating a client" section of GuzzleHttp docs.
     * 5.x - http://guzzle.readthedocs.org/en/5.3/clients.html#creating-a-client
     *
     * @param string $source Source language (Optional)
     * @param string $target Target language (Optional)
     * @param array $options Associative array of http client configuration options (Optional)
     */
    public function __construct($source = null, $target = 'en', $options = [])
    {
        $this->httpClient = new GuzzleHttpClient($options); // Create HTTP client
        $this->setSource($source)->setTarget($target); // Set languages
        $this::$lastDetectedSource = false;
    }

    /**
     * Override translate method for static call
     *
     * @throws BadMethodCallException If calling nonexistent method
     * @throws InvalidArgumentException If parameters are passed incorrectly
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public static function __callStatic($name, $args)
    {
        switch ($name) {
            case 'getResponse':
                return self::getResponse($args[0]);
            case 'translate':
                if (count($args) < 3) {
                    throw new InvalidArgumentException("Expecting 3 parameters");
                }
                try {
                    $result = self::staticTranslate($args[0], $args[1], $args[2]);
                } catch (Exception $e) {
                    throw $e;
                }
                return $result;
            case 'getLastDetectedSource':
                return self::staticGetLastDetectedSource();
            default:
                throw new BadMethodCallException("Method [{$name}] does not exist");
        }
    }

    /**
     * Override translate method for instance call
     *
     * @throws BadMethodCallException If calling nonexistent method
     * @throws InvalidArgumentException If parameters are passed incorrectly
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public function __call($name, $args)
    {
        switch ($name) {
            case 'getResponse':
                return $this->getResponse($args[0]);
            case 'translate':
                if (count($args) < 1) {
                    throw new InvalidArgumentException("Expecting 1 parameter");
                }
                try {
                    $result = $this->instanceTranslate($args[0]);
                } catch (Exception $e) {
                    throw $e;
                }
                return $result;
            case 'getLastDetectedSource':
                return $this::staticGetLastDetectedSource();
            default:
                throw new BadMethodCallException("Method [{$name}] does not exist");
        }
    }

    /**
     * Check if static instance exists and instantiate if not
     *
     * @return void
     */
    private static function checkStaticInstance()
    {
        if (!isset(self::$staticInstance)) {
            self::$staticInstance = new self();
        }
    }

    /**
     * Set source language we are transleting from
     *
     * @param string $source Language code
     * @return TranslateClient
     */
    public function setSource($source = null)
    {
        $this->sourceLanguage = is_null($source) ? 'auto' : $source;
        return $this;
    }

    /**
     * Set translation language we are transleting to
     *
     * @param string $target Language code
     * @return TranslateClient
     */
    public function setTarget($target)
    {
        $this->targetLanguage = $target;
        return $this;
    }

    /**
     * Get response array
     *
     * @param string|array $data String or array of strings to translate
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     * @return array Response
     */
    private function getResponse($data)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new InvalidArgumentException("Invalid argument provided");
        }

        $queryArray = array_merge($this->urlParams, [
            'text' => $data,
            'sl'   => $this->sourceLanguage,
            'tl'   => $this->targetLanguage,
        ]);

        $queryUrl = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($queryArray));

        try {
            $response = $this->httpClient->post($this->urlBase, ['body' => $queryUrl]);
        } catch (GuzzleRequestException $e) {
            throw new ErrorException($e->getMessage());
        }

        $body = $response->getBody(); // Get response body

        // Modify body to avoid json errors
        $bodyJson = preg_replace(array_keys($this->resultRegexes), array_values($this->resultRegexes), $body);

        // Decode JSON data
        if (($bodyArray = json_decode($bodyJson, true)) === null) {
            throw new UnexpectedValueException('Data cannot be decoded or it\'s deeper than the recursion limit');
        }
        return $bodyArray;
    }

    /**
     * Translate text
     *
     * This can be called from instance method translate() using __call() magic method.
     * Use $instance->translate($string) instead.
     *
     * @param string|array $data Text or array of texts to translate
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     * @return string|boolean Translated text
     */
    private function instanceTranslate($data)
    {
        // Whether or not is the data an array
        $isArray = is_array($data);

        // Rethrow exceptions
        try {
            $responseArray = $this->getResponse($data);
        } catch (Exception $e) {
            throw $e;
        }

        // Check if translation exists
        if (!isset($responseArray[0]) || empty($responseArray[0])) {
            return false;
        }

        // Detect languages
        $detectedLanguages = [];
        $responseArrayForLanguages = ($isArray) ? $responseArray[0] : [$responseArray];
        foreach ($responseArrayForLanguages as $itemArray) {
            foreach ($itemArray as $item) {
                if (is_string($item)) {
                    $detectedLanguages[] = $item;
                }
            }
        }

        // Another case of detected language
        if (isset($responseArray[count($responseArray) - 2][0][0])) {
            $detectedLanguages[] = $responseArray[count($responseArray) - 2][0][0];
        }

        // Set initial detected language to null
        $this::$lastDetectedSource = false;

        // Iterate and set last detected language
        foreach ($detectedLanguages as $lang) {
            if ($this->isValidLocale($lang)) {
                $this::$lastDetectedSource = $lang;
                break;
            }
        }

        // Reduce array to generate translated sentenece
        if ($isArray) {
            $carry = [];
            foreach ($responseArray[0] as $item) {
                $carry[] = $item[0][0][0];
            }
            return $carry;
        } else {
            return array_reduce($responseArray[0], function($carry, $item) {
                $carry .= $item[0];
                return $carry;
            });
        }
    }

    /**
     * Translate text statically
     *
     * This can be called from static method translate() using __callStatic() magic method.
     * Use TranslateClient::translate($source, $target, $string) instead.
     *
     * @param string $source Source language
     * @param string $target Target language
     * @param string $string Text to translate
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     * @return string|boolean Translated text
     */
    private static function staticTranslate($source, $target, $string)
    {
        self::checkStaticInstance();
        try {
            $result = self::$staticInstance
                ->setSource($source)
                ->setTarget($target)
                ->translate($string);
        } catch (Exception $e) {
            throw $e;
        }
        return $result;
    }

    /**
     * Get last detected language
     * @return string|boolean Last detected language or boolean FALSE
     */
    private static function staticGetLastDetectedSource()
    {
        return self::$lastDetectedSource;
    }

    /**
     * Check if given locale is valid
     * @param string $lang Langauge code to verify
     * @return boolean
     */
    private function isValidLocale($lang)
    {
        return !!preg_match('/^([a-z]{2})(-[A-Z]{2})?$/', $lang);
    }
}
