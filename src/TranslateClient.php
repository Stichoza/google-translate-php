<?php

namespace Stichoza\GoogleTranslate;

use BadMethodCallException;
use ErrorException;
use Exception;
use InvalidArgumentException;
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
 * @method string getLastDetectedSource() Can be called statically too.
 * @method string translate(string $text) Can be called statically with signature
 *                                        string translate(string $source, string $target, string $text)
 */
class TranslateClient
{
    /**
     * @var Translator
     */
    private static $translatorInstance;

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
    public function __construct($source = null, $target = 'en', $options = [], TokenProviderInterface $tokener = null)
    {
        self::getInstance($tokener)
            ->setSource($source)
            ->setTarget($target)
            ->setHttpOption($options);
    }

    /**
     * Returns the singleton instance of Translator.
     *
     * @param TokenProviderInterface|null $tokener
     *
     * @return Translator
     */
    protected static function getInstance(TokenProviderInterface $tokener = null)
    {
        if (!self::$translatorInstance) {
            self::$translatorInstance = new Translator(null, 'en', [], $tokener);
        }

        return self::$translatorInstance;
    }

    /**
     * Override translate method for static call.
     *
     * @throws BadMethodCallException   If calling nonexistent method
     * @throws InvalidArgumentException If parameters are passed incorrectly
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public static function __callStatic($name, $args)
    {
        switch ($name) {
            case 'translate':
                if (count($args) < 3) {
                    throw new InvalidArgumentException('Expecting 3 parameters');
                }

                try {
                    return self::getInstance()
                        ->setSource($args[0])
                        ->setTarget($args[1])
                        ->translate($args[2]);

                } catch (Exception $e) {
                    throw $e;
                }

            case 'getLastDetectedSource':
                return self::getInstance()->getLastDetectedSource();
            default:
                throw new BadMethodCallException("Method [{$name}] does not exist");
        }
    }

    /**
     * Override translate method for instance call.
     *
     * @throws BadMethodCallException   If calling nonexistent method
     * @throws InvalidArgumentException If parameters are passed incorrectly
     * @throws InvalidArgumentException If the provided argument is not of type 'string'
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public function __call($methodName, $args)
    {
        if (!method_exists(self::getInstance(), $methodName)) {
            throw new BadMethodCallException("Method [{$methodName}] does not exist");
        }

        $reflectionMethod = new \ReflectionMethod(self::getInstance(), $methodName);
        $minimumArgs = $reflectionMethod->getNumberOfRequiredParameters();
        if (count($args) < $minimumArgs) {
            throw new InvalidArgumentException("Expecting $minimumArgs parameter".($minimumArgs == 1 ? '' : 's'));
        }

        return call_user_func_array([self::getInstance(), $methodName], $args);
    }

    /**
     * Check if given locale is valid.
     *
     * @param string $lang Language code to verify
     *
     * @return bool
     */
    public static function isValidLocale($lang)
    {
        return self::getInstance()->isValidLocale($lang);
    }
}
