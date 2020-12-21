<?php

namespace Stichoza\GoogleTranslate\Tokens;

/**
 * Google token generator.
 *
 * @link https://github.com/Stichoza/google-translate-php/issues/32 Thanks to @helen5106 and @tehmaestro and few other cool guys
 * {@inheritDoc}
 */
class GoogleTokenGenerator implements TokenProviderInterface
{
    /**
     * @var array Token keys
     */
    protected const TKK = ['406398', 2087938574];
    
    /**
     * @var string Character encoding
     */
    protected $encoding;
    
    /**
     * @var string[] Generated tokens
     */
    protected $tokens = [];
    
    /**
     * Creates new instance.
     * 
     * @param string $encoding Character encoding
     * @return void
     */
    public function __construct(string $encoding = 'UTF-8')
    {
        $this->encoding = $encoding;
    }
    
    /**
     * Generate and return a token.
     *
     * @param string $source Source language
     * @param string $target Target language
     * @param string $text Text to translate
     * @return string Token
     */
    public function generateToken(string $source, string $target, string $text): string
    {
        $hash = md5($text);
        if (isset($this->tokens[$hash])) {
            return $this->tokens[$hash];
        }
        
        $b = static::TKK[0];
        for ($d = [], $e = 0, $f = 0; $f < $this->length($text); $f++) {
            $g = $this->charCodeAt($text, $f);
            if (128 > $g) {
                $d[$e++] = $g;
            } else {
                if (2048 > $g) {
                    $d[$e++] = $g >> 6 | 192;
                } else {
                    if (55296 === ($g & 64512) && $f + 1 < $this->length($text) && 56320 === ($this->charCodeAt($text, $f + 1) & 64512)) {
                        $g = 65536 + (($g & 1023) << 10) + ($this->charCodeAt($text, ++$f) & 1023);
                        $d[$e++] = $g >> 18 | 240;
                        $d[$e++] = $g >> 12 & 63 | 128;
                    } else {
                        $d[$e++] = $g >> 12 | 224;
                    }
                    $d[$e++] = $g >> 6 & 63 | 128;
                }
                $d[$e++] = $g & 63 | 128;
            }
        }
        $text = $b;
        for ($e = 0; $e < count($d); $e++) {
            $text = $this->rl($text + $d[$e], '+-a^+6');
        }
        $text = $this->rl($text, '+-3^+b+-f');
        $text ^= static::TKK[1];
        if (0 > $text) {
            $text = ($text & 2147483647) + 2147483648;
        }
        $text = fmod($text, pow(10, 6));
        
        $this->tokens[$hash] = $text . '.' . ($text ^ $b);
        
        return $this->tokens[$hash];
    }

    /**
     * Process token data by applying multiple operations.
     * (Parameters are safe, no need for multibyte functions)
     *
     * @param int $a
     * @param string $b
     * @return int
     */
    private function rl(int $a, string $b): int
    {
        for ($c = 0; $c < strlen($b) - 2; $c += 3) {
            $d = $b[$c + 2];
            $d = 'a' <= $d ? ord($d[0]) - 87 : (int) $d;
            $d = '+' === $b[$c + 1] ? $this->unsignedRightShift($a, $d) : $a << $d;
            $a = '+' === $b[$c] ? ($a + $d & 4294967295) : $a ^ $d;
        }
        return $a;
    }

    /**
     * JS unsigned right shift(`>>>`) implementation.
     * 
     * @link https://msdn.microsoft.com/en-us/library/342xfs5s(v=vs.94).aspx
     * @link http://stackoverflow.com/a/43359819/2953830
     * @param int $a
     * @param int $b
     * @return int
     */
    private function unsignedRightShift($a, $b): int
    {
        if ($b >= 32 || $b < -32) {
            $b -= intval($b / 32) * 32;
        }
        if ($b < 0) {
            $b += 32;
        }
        
        if ($b === 0) {
            return (($a >> 1) & 0x7fffffff) * 2 + (($a >> $b) & 1);
        }

        if ($a < 0) {
            $a = $a >> 1;
            $a &= 2147483647;
            $a |= 0x40000000;
            $a = ($a >> ($b - 1));
        } else { 
            $a = $a >> $b;
        }

        return $a;
    }

    /**
     * Get JS `charCodeAt()` equivalent result.
     *
     * @param string $str
     * @param int    $index
     * @return int
     */
    private function charCodeAt(string $str, int $index): int
    {
        return mb_ord(mb_substr($str, $index, 1, $this->encoding), $this->encoding);
    }

    /**
     * Get JS equivalent string `length`.
     *
     * @param string $str
     * @return int
     */
    private function length(string $str): int
    {
        return mb_strlen($str, $this->encoding);
    }
}
