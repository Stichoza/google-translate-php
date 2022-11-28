<?php

namespace Stichoza\GoogleTranslate\Tokens;

/**
 * A nice interface for providing tokens.
 */
class SampleTokenGenerator implements TokenProviderInterface
{
    /**
     * Generate a fake token just as an example.
     *
     * @param string $source Source language
     * @param string $target Target language
     * @param string $text Text to translate
     * @return string Token
     */
    public function generateToken(string $source, string $target, string $text): string
    {
        return microtime(true);
    }
}
