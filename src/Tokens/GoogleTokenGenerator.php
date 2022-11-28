<?php

namespace Stichoza\GoogleTranslate\Tokens;

/**
 * Google Token Generator.
 *
 * Thanks to @helen5106, @tehmaestro and few other cool guys
 * at https://github.com/Stichoza/google-translate-php/issues/32
 */
class GoogleTokenGenerator implements TokenProviderInterface
{
    protected array $tkk = ['406398', 2087938574];

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
        $a = $text;

        $b = $this->tkk[0];

        for ($d = [], $e = 0, $f = 0; $f < $this->length($a); $f++) {
            $g = $this->charCodeAt($a, $f);
            if (128 > $g) {
                $d[$e++] = $g;
            } else {
                if (2048 > $g) {
                    $d[$e++] = $g >> 6 | 192;
                } else {
                    if ($g & 64512 === 55296 && $f + 1 < $this->length($a) && ($this->charCodeAt($a, $f + 1) & 64512) === 56320) {
                        $g = 65536 + (($g & 1023) << 10) + ($this->charCodeAt($a, ++$f) & 1023);
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
        $a = $b;
        foreach ($d as $value) {
            $a += $value;
            $a = $this->RL($a, '+-a^+6');
        }
        $a = $this->RL($a, '+-3^+b+-f');
        $a ^= $this->tkk[1] ? $this->tkk[1] + 0 : 0;
        if (0 > $a) {
            $a = ($a & 2147483647) + 2147483648;
        }
        $a = fmod($a, 1000000);

        return $a . '.' . ($a ^ $b);
    }

    /**
     * Process token data by applying multiple operations.
     * (Params are safe, no need for multibyte functions)
     *
     * @param int $a
     * @param string $b
     *
     * @return int
     */
    private function RL($a, $b)
    {
        for ($c = 0; $c < strlen($b) - 2; $c += 3) {
            $d = $b[$c + 2];
            $d = $d >= 'a' ? ord($d[0]) - 87 : (int) $d;
            $d = $b[$c + 1] === '+' ? $this->unsignedRightShift($a, $d) : $a << $d;
            $a = $b[$c] === '+' ? ($a + $d & 4294967295) : $a ^ $d;
        }

        return $a;
    }

    /**
     * Unsigned right shift implementation
     * https://msdn.microsoft.com/en-us/library/342xfs5s(v=vs.94).aspx
     * http://stackoverflow.com/a/43359819/2953830
     *
     * @param $a
     * @param $b
     *
     * @return number
     */
    private function unsignedRightShift($a, $b)
    {
        if ($b >= 32 || $b < -32) {
            $m = (int)($b / 32);
            $b -= ($m * 32);
        }

        if ($b < 0) {
            $b = 32 + $b;
        }

        if ($b === 0) {
            return (($a >> 1) & 0x7fffffff) * 2 + (($a >> $b) & 1);
        }

        if ($a < 0) {
            $a >>= 1;
            $a &= 2147483647;
            $a |= 0x40000000;
            $a >>= ($b - 1);
        } else {
            $a >>= $b;
        }

        return $a;
    }

    /**
     * Get JS charCodeAt equivalent result with UTF-16 encoding
     *
     * @param string $string
     * @param int    $index
     *
     * @return int
     */
    private function charCodeAt(string $string, int $index): int
    {
        $utf16 = mb_convert_encoding($string, 'UTF-16LE', 'UTF-8');

        return ord($utf16[$index * 2]) + (ord($utf16[$index * 2 + 1]) << 8);
    }

    /**
     * Get JS equivalent string length with UTF-16 encoding
     *
     * @param string $string
     *
     * @return int
     */
    private function length(string $string): int
    {
        $utf16 = mb_convert_encoding($string, 'UTF-16LE', 'UTF-8');

        return strlen($utf16) / 2;
    }
}
