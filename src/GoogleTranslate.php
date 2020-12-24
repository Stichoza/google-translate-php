<?php

namespace Stichoza\GoogleTranslate;

use BadMethodCallException;
use ErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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
     * @var string[] Languages
     */
    public const LANGUAGES = [
        'af',
        'ar',
        'bn',
        'bs',
        'ca',
        'cs',
        'cy',
        'da',
        'de',
        'el',
        'en',
        'eo',
        'es',
        'et',
        'fi',
        'fr',
        'gu',
        'hi',
        'hr',
        'hu',
        'hy',
        'id',
        'is',
        'it',
        'ja',
        'jw',
        'km',
        'kn',
        'ko',
        'la',
        'lv',
        'mk',
        'ml',
        'mr',
        'my',
        'ne',
        'nl',
        'no',
        'pl',
        'pt',
        'ro',
        'ru',
        'si',
        'sk',
        'sq',
        'sr',
        'su',
        'sv',
        'sw',
        'ta',
        'te',
        'th',
        'tl',
        'tr',
        'vi',
        'uk',
        'ur',
        'zh',
        'zh-cn',
        'zh-tw',
    ];
    
    /**
     * @var array URL Parameters
     */
    protected const QUERY = [
        'client'   => 'webapp',
        'dt'       => [
            't',   // Translate
            'bd',  // Full translate with synonym ($bodyArray[1])
            'at',  // Other translate ($bodyArray[5] - in google translate page this shows when click on translated word)
            'ex',  // Example part ($bodyArray[13])
            'ld',  // I don't know ($bodyArray[8])
            'md',  // Definition part with example ($bodyArray[12])
            'qca', // I don't know ($bodyArray[8])
            'rw',  // Read also part ($bodyArray[14])
            'rm',  // I don't know
            'ss'   // Full synonym ($bodyArray[11])
        ],
        'ie'       => 'UTF-8', // Input encoding
        'oe'       => 'UTF-8', // Output encoding
        'multires' => 1,
        'otf'      => 0,
        'pc'       => 1,
        'trs'      => 1,
        'ssel'     => 0,
        'tsel'     => 0,
        'kc'       => 1,
    ];
    
    /**
     * @var array Regex key-value patterns to replace on response data
     */
    protected const RESULT_REGEXES = [
        '/,+/'  => ',',
        '/\[,/' => '[',
    ];
    
    /**
     * @var \GuzzleHttp\Client HTTP Client
     */
    protected $client;

    /**
     * @var string Source language - from where the string should be translated
     */
    protected $source = 'auto';

    /**
     * @var string Target language - to which language string should be translated
     */
    protected $target = 'en';

    /**
     * @var string|null Last detected source language
     */
    protected $lastDetectedSource;

    /**
     * @var string Google Translate URL base
     */
    protected $url = 'https://translate.google.com/translate_a/single';

    /**
     * @var array HTTP client options
     */
    protected $options = [];

    /**
     * @var TokenProviderInterface Token provider
     */
    protected $tokenProvider;

    /**
     * Class constructor.
     *
     * For more information about HTTP client configuration options, see "Request Options" in
     * GuzzleHttp docs: http://docs.guzzlephp.org/en/stable/request-options.html
     *
     * @param string $target Target language
     * @param string $source Source language
     * @param array $options Associative array of http client configuration options
     * @param TokenProviderInterface|null $tokenProvider
     */
    public function __construct(
        string $target = 'en', 
        string $source = 'auto', 
        array $options = [], 
        ?TokenProviderInterface $tokenProvider = null
    ) {
        $this->client = new Client();
        $this->setTokenProvider($tokenProvider ?? new GoogleTokenGenerator())
            ->setOptions($options) // Options are already set in client constructor tho.
            ->setSource($source)
            ->setTarget($target);
    }

    /**
     * Set target language for translation.
     *
     * @param string $target Language code
     * @return $this
     */
    public function setTarget(string $target) : self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Set source language for translation.
     *
     * @param string $source Language code
     * @return $this
     */
    public function setSource(string $source = 'auto') : self
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Set Google Translate URL base
     *
     * @param string $url Google Translate URL base
     * @return $this
     */
    public function setUrl(string $url) : self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set GuzzleHttp client options.
     *
     * @param array $options guzzleHttp client options.
     * @return $this
     */
    public function setOptions(array $options = []) : self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Set token provider.
     *
     * @param TokenProviderInterface $tokenProvider
     * @return $this
     */
    public function setTokenProvider(TokenProviderInterface $tokenProvider) : self
    {
        $this->tokenProvider = $tokenProvider;
        return $this;
    }

    /**
     * Get last detected source language.
     *
     * @return string|null Last detected source language
     */
    public function getLastDetectedSource(): ?string
    {
        return $this->lastDetectedSource;
    }

    /**
     * Override translate method for static call.
     *
     * @param string $string
     * @param string $target
     * @param string $source
     * @param array $options
     * @param TokenProviderInterface|null $tokenProvider
     * @return null|string
     * @throws ErrorException If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public static function trans(
        string $string,
        string $target = 'en',
        string $source = 'auto',
        array $options = [],
        TokenProviderInterface $tokenProvider = null
    ): ?string {
        return (new static($target, $source, $options, $tokenProvider))->translate($string);
    }

    /**
     * Translate text.
     *
     * @param string $string String to translate
     * @return string|null
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public function translate(string $string): ?string
    {
        // if source lang and target lang are the same just return the string without any request to Google
        if ($this->source === $this->target) {
            return $string;
        }
        
        $response = $this->getResponse($string);

        /*
         * if response in text and the content has zero the empty returns true, lets check
         * if response is string and not empty and create array for further logic
         */
        if (is_string($response) && $response) {
            $response = [$response];
        }

        // Check if translation exists
        if (! $response || empty($response[0])) {
            return null;
        }

        // Detect languages
        $detectedLanguages = [];

        // the response contains only single translation, don't create loop that will end with
        // invalid foreach and warning
        if (is_array($response)) {
            foreach ($response as $item) {
                if (is_string($item)) {
                    $detectedLanguages[] = $item;
                }
            }
        }

        // Another case of detected language
        $index = count($response) - 2;
        if (isset($response[$index][0][0])) {
            $detectedLanguages[] = $response[$index][0][0];
        }

        // Set initial detected language to null
        $this->lastDetectedSource = null;
        // Iterate and set last detected language
        foreach ($detectedLanguages as $language) {
            if ($this->isValidLocale($language)) {
                $this->lastDetectedSource = $language;
                break;
            }
        }

        // the response can be sometimes an translated string.
        if (is_string($response)) {
            return $response;
        }
        if (is_array($response[0])) {
            return implode('', array_column($response[0], 0));
        }
        return (string) $response[0];
    }

    /**
     * Get response body.
     *
     * @param string $string String to translate
     * @return array|string Response body
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public function getResponse(string $string)
    {
        $query = static::QUERY + [
            'sl' => $this->source,
            'hl' => $this->source,
            'tl' => $this->target,
            'tk' => $this->tokenProvider->generateToken($this->source, $this->target, $string),
            'q'  => $string
        ];
        $query = preg_replace('/%5B\d+%5D=/', '=', http_build_query($query));
        
        try {
            $response = $this->client->get($this->url, ['query' => $query] + $this->options);
        } catch (RequestException $e) {
            throw new ErrorException($e->getMessage());
        }
        
        // Get response body
        $body = $response->getBody()->getContents();
        // Modify body to avoid JSON errors
        $body = preg_replace(array_keys(static::RESULT_REGEXES), static::RESULT_REGEXES, $jbody);
        // Decode JSON data
        $decodedBody = json_decode($body, true);
        if ($decodedBody === null && json_last_error()) {
            throw new UnexpectedValueException(json_last_error_msg());
        }
        
        return $decodedBody;
    }

    /**
     * Check if given locale is valid.
     *
     * @param string $language Langauge code to verify
     * @return bool
     */
    protected function isValidLocale(string $language): bool
    {
        return in_array($language, static::LANGUAGES, true);
    }
}
