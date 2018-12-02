<?php

namespace Stichoza\GoogleTranslate;

use BadMethodCallException;
use ErrorException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use Stichoza\GoogleTranslate\Tokens\GoogleTokenGenerator;
use Stichoza\GoogleTranslate\Tokens\TokenProviderInterface;
use UnexpectedValueException;

/**
 * Free Google Translate API PHP Package.
 *
 * @author      Levan Velijanashvili <me@stichoza.com>
 * @link        http://stichoza.com/
 * @license     MIT
 */
class GoogleTranslate
{
    /**
     * @var \GuzzleHttp\Client HTTP Client
     */
    private $client;

    /**
     * @var string|null Source language - from where the string should be translated
     */
    private $source;

    /**
     * @var string Target language - to which language string should be translated
     */
    private $target;

    /**
     * @var string|null Last detected source language
     */
    private $lastDetectedSource;

    /**
     * @var string Google Translate URL base
     */
    private $url = 'https://translate.google.com/translate_a/single';

    /**
     * @var array Dynamic GuzzleHttp client options
     */
    private $options = [];

    /**
     * @var array URL Parameters
     */
    private $urlParams = [
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
    private $resultRegexes = [
        '/,+/'  => ',',
        '/\[,/' => '[',
    ];

    /**
     * @var TokenProviderInterface Token provider
     */
    private $tokenProvider;

    /**
     * Class constructor.
     *
     * For more information about HTTP client configuration options, see "Request Options" in
     * GuzzleHttp docs: http://docs.guzzlephp.org/en/stable/request-options.html
     *
     * @param string $source Source language (Optional)
     * @param string $target Target language (Optional)
     * @param array $options Associative array of http client configuration options (Optional)
     * @param TokenProviderInterface|null $tokenProvider
     */
    public function __construct($source = null, $target = 'en', $options = [], TokenProviderInterface $tokenProvider = null)
    {
        $this->client = new Client($options); // Create HTTP client
        $this->setSource($source)->setTarget($target); // Set languages
        $this->setTokenProvider($tokenProvider ?? new GoogleTokenGenerator());
    }

    /**
     * Override translate method for static call.
     *
     * @throws BadMethodCallException   If calling nonexistent method
     * @throws InvalidArgumentException If parameters are passed incorrectly
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     * @throws Exception
     */
    public static function __callStatic($name, $args)
    {
        if ($name == 'translate') {
            return (new self)
                ->setOptions($args[3] ?? [])
                ->setSource($args[2] ?? null)
                ->setTarget($args[1] ?? 'en')
                ->translate($args[0] ?? null);
        } else {
            throw new BadMethodCallException("Method [{$name}] does not exist");
        }
    }

    /**
     * Set source language for translation.
     *
     * @param string|null $source Language code
     * @return GoogleTranslate
     */
    public function setSource($source = null) : self
    {
        $this->source = $source ?? 'auto';
        return $this;
    }

    /**
     * Set target language for translation.
     *
     * @param string $target Language code
     * @return GoogleTranslate
     */
    public function setTarget($target) : self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Set Google Translate URL base
     *
     * @param string $url Google Translate URL base
     * @return GoogleTranslate
     */
    public function setUrl($url) : self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set GuzzleHttp client options.
     *
     * @param array $options guzzleHttp client options.
     * @return GoogleTranslate
     */
    public function setOptions(array $options) : self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Set token provider.
     *
     * @param TokenProviderInterface $tokenProvider
     * @return GoogleTranslate
     */
    public function setTokenProvider(TokenProviderInterface $tokenProvider) : self
    {
        $this->tokenProvider = $tokenProvider;
        return $this;
    }

    /**
     * Translate text.
     *
     * This can be called from instance method translate() using __call() magic method.
     * Use $instance->translate($string) instead.
     *
     * @param string $string String to translate
     * @return string|null
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public function translate(string $string) : string
    {
        $responseArray = $this->getResponse($string);

        /*
         * if response in text and the content has zero the empty returns true, lets check
         * if response is string and not empty and create array for further logic
         */
        if (is_string($responseArray) && $responseArray != '') {
            $responseArray = [$responseArray];
        }

        // Check if translation exists
        if (!isset($responseArray[0]) || empty($responseArray[0])) {
            return null;
        }

        // Detect languages
        $detectedLanguages = [];

        // the response contains only single translation, don't create loop that will end with
        // invalid foreach and warning
        if (!is_string($responseArray)) {
            foreach ($responseArray as $item) {
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
        $this->lastDetectedSource = null;

        // Iterate and set last detected language
        foreach ($detectedLanguages as $lang) {
            if ($this->isValidLocale($lang)) {
                $this->lastDetectedSource = $lang;
                break;
            }
        }

        // the response can be sometimes an translated string.
        if (is_string($responseArray)) {
            return $responseArray;
        } else {
            if (is_array($responseArray[0])) {
                return (string) array_reduce($responseArray[0], function ($carry, $item) {
                    $carry .= $item[0];
                    return $carry;
                });
            } else {
                return (string) $responseArray[0];
            }
        }
    }

    /**
     * Check if given locale is valid.
     *
     * @param string $lang Langauge code to verify
     * @return bool
     */
    private function isValidLocale(string $lang) : bool
    {
        return (bool) preg_match('/^([a-z]{2})(-[A-Z]{2})?$/', $lang);
    }

    /**
     * Get response array.
     *
     * @param string $string String to translate
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     * @return array Response
     */
    private function getResponse(string $string)
    {
        $queryArray = array_merge($this->urlParams, [
            'sl'   => $this->source,
            'tl'   => $this->target,
            'tk'   => $this->tokenProvider->generateToken($this->source, $this->target, $string),
        ]);

        $queryUrl = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($queryArray));

        $queryBodyArray = [
            'q' => $string,
        ];

        $queryBodyEncoded = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($queryBodyArray));

        try {
            $response = $this->client->post($this->url, [
                    'query' => $queryUrl,
                    'body'  => $queryBodyEncoded,
                ] + $this->options);
        } catch (RequestException $e) {
            throw new ErrorException($e->getMessage());
        }

        $body = $response->getBody(); // Get response body

        // Modify body to avoid json errors
        $bodyJson = preg_replace(array_keys($this->resultRegexes), array_values($this->resultRegexes), $body);

        // Decode JSON data
        if (($bodyArray = json_decode($bodyJson, true)) === null) {
            throw new UnexpectedValueException('Data cannot be decoded or it is deeper than the recursion limit');
        }

        return $bodyArray;
    }
}
