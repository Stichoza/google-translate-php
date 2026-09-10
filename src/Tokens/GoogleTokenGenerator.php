<?php

declare(strict_types=1);

namespace Stichoza\GoogleTranslate\Tokens;

/**
 * Google Token Generator.
 *
 * Thanks to @helen5106, @tehmaestro and a few other cool guys
 * at https://github.com/Stichoza/google-translate-php/issues/32
 */
class GoogleTokenGenerator implements TokenProviderInterface
{
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
        $tkk = ['406398', 2087938574];

        // Work on real UTF-16 code units (not Unicode code points) so that
        // characters outside the Basic Multilingual Plane (e.g. most emoji,
        // U+10000 and above) are seen as surrogate pairs, exactly like
        // JavaScript's charCodeAt()/length. Using code points here made the
        // surrogate-pair branch below unreachable and produced a wrong
        // token (and a 403 from Google) whenever the text contained an emoji.
        $units = $this->utf16CodeUnits($text);
        $length = count($units);

        for ($d = [], $e = 0, $f = 0; $f < $length; $f++) {
            $g = $units[$f];
            if ($g < 128) {
                $d[$e++] = $g;
            } else {
                if ($g < 2048) {
                    $d[$e++] = $g >> 6 | 192;
                } else {
                    if (($g & 64512) === 55296 && $f + 1 < $length && ($units[$f + 1] & 64512) === 56320) {
                        $g = 65536 + (($g & 1023) << 10) + ($units[++$f] & 1023);
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

        $a = $tkk[0];
        foreach ($d as $value) {
            $a += $value;
            $a = $this->rl($a, '+-a^+6');
        }
        $a = $this->rl($a, '+-3^+b+-f');
        $a ^= $tkk[1];
        if ($a < 0) {
            $a = ($a & 2147483647) + 2147483648;
        }
        $a = fmod($a, 1000000);

        return $a . '.' . ($a ^ $tkk[0]);
    }

    /**
     * Process token data by applying multiple operations.
     * (Parameters are safe, no need for multibyte functions)
     *
     * @param int $a
     * @param string $b
     *
     * @return int
     */
    private function rl(int $a, string $b): int
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
     * @param int $a
     * @param int $b
     *
     * @return int
     */
    private function unsignedRightShift(int $a, int $b): int
    {
        if ($b >= 32 || $b < -32) {
            $m = (int) ($b / 32);
            $b -= ($m * 32);
        }

        if ($b < 0) {
            $b += 32;
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
     * Split a UTF-8 string into true UTF-16 code units, surrogate pairs
     * included, matching JavaScript's charCodeAt()/length semantics for
     * characters above U+FFFF (astral plane, e.g. most emoji).
     *
     * @param string $string
     * @return int[]
     */
    private function utf16CodeUnits(string $string): array
    {
        $utf16be = mb_convert_encoding($string, 'UTF-16BE', 'UTF-8');

        $units = [];

        for ($i = 0, $len = strlen($utf16be); $i < $len; $i += 2) {
            $units[] = (ord($utf16be[$i]) << 8) | ord($utf16be[$i + 1]);
        }

        return $units;
    }
}
