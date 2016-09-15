<?php

namespace Stichoza\GoogleTranslate\Tokens;

/**
 * A nice interface for providing tokens.
 */
interface TokenProviderInterface
{
    /**
     * Generate and return a token.
     *
     * @param string $source Source language
     * @param string $target Target language
     * @param string $text   Text to translate
     *
     * @return mixed A token
     */
    public function generateToken($source, $target, $text);
}
