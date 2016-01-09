<?php

namespace Stichoza\GoogleTranslate\Tokens;

/**
 * A nice interface for providing tokens.
 */
class SampleTokenGenerator implements TokenProviderInterface
{
    /**
     * Generate a fake token just as an example.
     */
    public function generateToken($source, $target, $text)
    {
        return sprintf('%d.%d', rand(10000, 99999), rand(10000, 99999));
    }
}
