<?php

namespace Stichoza\GoogleTranslate;

use ErrorException;
use Exception;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use InvalidArgumentException;
use ReflectionClass;
use Stichoza\GoogleTranslate\Tokens\GoogleTokenGenerator;
use Stichoza\GoogleTranslate\Tokens\TokenProviderInterface;
use UnexpectedValueException;

/**
 * Free Google Translate API PHP Package.
 *
 * @author      Levan Velijanashvili <me@stichoza.com>
 *
 * @link        http://stichoza.com/
 *
 * @license     MIT
 *
 */
class Translator
{
    /**
     * @var \GuzzleHttp\Client HTTP Client
     */
    protected $httpClient;

    /**
     * @var string Source language - from where the string should be translated
     */
    protected $sourceLanguage;

    /**
     * @var string Target language - to which language string should be translated
     */
    protected $targetLanguage;

    /**
     * @var string|bool Last detected source language
     */
    protected $lastDetectedSource = false;

    /**
     * @var string Google Translate URL base
     */
    private $urlBase = 'http://translate.google.com/translate_a/single';

    /**
     * @var array Dynamic guzzleHTTP client options
     */
    protected $httpOptions = [];

    /**
     * @var array URL Parameters
     */
    protected $urlParams = [
        'client'   => 't',
        'hl'       => 'en',
        'dt'       => 't',
        'sl'       => null, // Source language
        'tl'       => null, // Target language
        'q'        => null, // String to translate
        'ie'       => 'UTF-8', // Input encoding
        'oe'       => 'UTF-8', // Output encoding
        'multires' => 1,
        'otf'      => 0,
        'pc'       => 1,
        'trs'      => 1,
        'ssel'     => 0,
        'tsel'     => 0,
        'kc'       => 1,
        'tk'       => null,
    ];

    /**
     * @var array Regex key-value patterns to replace on response data
     */
    protected $resultRegexes = [
        '/,+/'  => ',',
        '/\[,/' => '[',
    ];

    /**
     * @var TokenProviderInterface
     */
    protected $tokenProvider;

    /**
     * Class constructor.
     *
     * For more information about HTTP client configuration options, visit
     * "Creating a client" section of GuzzleHttp docs.
     * 5.x - http://guzzle.readthedocs.org/en/5.3/clients.html#creating-a-client
     *
     * @param string $source  Source language (Optional)
     * @param string $target  Target language (Optional)
     * @param array  $options Associative array of http client configuration options (Optional)
     *
     * @throws Exception If token provider does not implement TokenProviderInterface
     */
    public function __construct($source = null, $target = 'en', $options = [], TokenProviderInterface $tokenProvider = null)
    {
        $this->httpClient = new GuzzleHttpClient($options); // Create HTTP client
        $this->setSource($source)->setTarget($target); // Set languages

        if (!$tokenProvider) {
            $tokenProvider = new GoogleTokenGenerator();
        }

        $this->tokenProvider = $tokenProvider;
    }

    /**
     * Set source language we are transleting from.
     *
     * @param string $source Language code
     *
     * @return Translator
     */
    public function setSource($source = null)
    {
        $this->sourceLanguage = is_null($source) ? 'auto' : $source;

        return $this;
    }

    /**
     * Set translation language we are translating to.
     *
     * @param string $target Language code
     *
     * @return Translator
     */
    public function setTarget($target)
    {
        $this->targetLanguage = $target;

        return $this;
    }

    /**
     * Set guzzleHttp client options.
     *
     * @param array $options guzzleHttp client options.
     *
     * @return Translator
     */
    public function setHttpOption(array $options)
    {
        $this->httpOptions = $options;

        return $this;
    }

    /**
     * Get response array.
     *
     * @param string|array $data String or array of strings to translate
     *
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     *
     * @return array Response
     */
    public function getResponse($data)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new InvalidArgumentException('Invalid argument provided');
        }

        $tokenData = is_array($data) ? implode('', $data) : $data;

        $queryArray = array_merge($this->urlParams, [
            'sl' => $this->sourceLanguage,
            'tl' => $this->targetLanguage,
            'tk' => $this->tokenProvider->generateToken($this->sourceLanguage, $this->targetLanguage, $tokenData),
        ]);

        $queryUrl = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($queryArray));

        $queryBodyArray = [
            'q' => $data,
        ];

        $queryBodyEncoded = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($queryBodyArray));

        try {
            $response = $this->httpClient->post($this->urlBase, [
                    'query' => $queryUrl,
                    'body'  => $queryBodyEncoded,
                ] + $this->httpOptions);
        } catch (GuzzleRequestException $e) {
            throw new ErrorException($e->getMessage());
        }

        $body = $response->getBody()->getContents(); // Get response body

        // Modify body to avoid json errors
        $bodyJson = preg_replace(array_keys($this->resultRegexes), array_values($this->resultRegexes), $body);

        // Decode JSON data
        if (($bodyArray = json_decode($bodyJson, true)) === null) {
            throw new UnexpectedValueException('Data cannot be decoded or it\'s deeper than the recursion limit');
        }

        return $bodyArray;
    }

    public function translate($data)
    {
        if (is_string($data)) {
            return $this->translateStuff($data);
        }

        if (is_array($data)) {
            $translatedStuff = $this->translateStuff(implode(' || ', $data));

            return explode(' || ', $translatedStuff);
        }

        throw new InvalidArgumentException("Expecting string or array as an argument on translate method.");
    }

    /**
     * Translate text.
     *
     * This can be called from instance method translate() using __call() magic method.
     * Use $instance->translate($string) instead.
     *
     * @param string|array $data Text or array of texts to translate
     *
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     *
     * @return string|bool Translated text
     */
    public function translateStuff($data)
    {
        // Whether or not is the data an array
        $isArray = is_array($data);

        // Rethrow exceptions
        try {
            if ($isArray) {
                $responseArray = $this->getResponse(implode(' || ', $data));
            } else {
                $responseArray = $this->getResponse($data);
            }
        } catch (Exception $e) {
            throw $e;
        }

        // if response in text and the content has zero the empty returns true, lets check
        // if response is string and not empty and create array for further logic
        if (is_string($responseArray) && $responseArray != '') {
            $responseArray = [$responseArray];
        }

        // Check if translation exists
        if (!isset($responseArray[0]) || empty($responseArray[0])) {
            return false;
        }

        // Detect languages
        $detectedLanguages = [];

        // the response contains only single translation, dont create loop that will end with
        // invalide foreach and warning
        if ($isArray || !is_string($responseArray)) {
            $responseArrayForLanguages = ($isArray) ? $responseArray[0] : [$responseArray];
            foreach ($responseArrayForLanguages as $itemArray) {
                foreach ($itemArray as $item) {
                    if (is_string($item)) {
                        $detectedLanguages[] = $item;
                    }
                }
            }
        }

        // Another case of detected language
        if (isset($responseArray[count($responseArray) - 2][0][0])) {
            $detectedLanguages[] = $responseArray[count($responseArray) - 2][0][0];
        }

        // Set initial detected language to null
        $this->lastDetectedSource = false;

        // Iterate and set last detected language
        foreach ($detectedLanguages as $lang) {
            if ($this->isValidLocale($lang)) {
                $this->lastDetectedSource = $lang;
                break;
            }
        }

        // Reduce array to generate translated sentence
        if ($isArray) {
            $carry = [];
            foreach ($responseArray[0] as $item) {
                $carry[] = $item[0][0][0];
            }

            return $carry;
        } // the response can be sometimes an translated string.
        elseif (is_string($responseArray)) {
            return $responseArray;
        } else {
            if (is_array($responseArray[0])) {
                return array_reduce($responseArray[0], function ($carry, $item) {
                    $carry .= $item[0];

                    return $carry;
                });
            } else {
                return $responseArray[0];
            }
        }
    }

    /**
     * Get last detected language.
     *
     * @return string|bool Last detected language or boolean FALSE
     */
    public function getLastDetectedSource()
    {
        return $this->lastDetectedSource;
    }

    /**
     * Check if given locale is valid.
     *
     * @param string $lang Language code to verify
     *
     * @return bool
     */
    public function isValidLocale($lang)
    {
        return (bool)preg_match('/^([a-z]{2})(-[A-Z]{2})?$/', $lang);
    }
}
