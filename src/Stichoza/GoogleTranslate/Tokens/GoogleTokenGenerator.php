<?php

namespace Stichoza\GoogleTranslate\Tokens;

use DateTime;

/**
 * Google Token Generator
 *
 * Thanks to @helen5106 and @tehmaestro and few other cool guys
 * at https://github.com/Stichoza/google-translate-php/issues/32
 */
class GoogleTokenGenerator implements TokenProviderInterface
{
    /**
     * Generate and return a token
     *
     * @param string $source Source language
     * @param string $target Target language
     * @param string $text Text to translate
     * @return mixed A token
     */
    public function generateToken($source, $target, $text)
    {
        return $this->TL($text);
    }

    private function mb_str_split($str, $length = 1) 
    {
        if ($length < 1) return false;
        $result = array();
        for ($i = 0; $i < mb_strlen($str); $i += $length) {
            $result[] = mb_substr($str, $i, $length);
        }
        return $result;
    }
    /**
     * Generate a valid Google Translate request token
     *
     * @param string $a text to translate
     * @return string
     */
    private function TL($a)
    {
        $b = $this->generateB();
        $d = array_map('ord', $this->mb_str_split($a));
        $a = $b;
        for ($e = 0; $e < count($d); $e++) {
            $a = $a + $d[$e];
            $a = RL($a, '+-a^+6');
        }
        $a = RL($a, "+-3^+b+-f");
        $a  =  $a >= 0 ? $a : ($a & 0x7FFFFFFF) + 0x80000000;
        $a = fmod($a, pow(10, 6));
        return $a . "." . ($a ^ $b);   
    }

    /**
     * Generate "b" parameter
     * The number of hours elapsed, since 1st of January 1970
     *
     * @return double
     */
    private function generateB()
    {
        $start = new DateTime('1970-01-01');
        $now = new DateTime('now');

        $diff = $now->diff($start);

        return $diff->h + ($diff->days * 24);
    }

    /**
     * Process token data by applying multiple operations
     *
     * @param $a
     * @param $b
     * @return int
     */
    private function RL($a, $b)
    {
        for ($c = 0; $c < strlen($b) - 2; $c += 3) {
            $d = $b{$c + 2};
            $d = $d >= 'a' ? $this->charCodeAt($d, 0) - 87 : intval($d);
            $d = $b{$c + 1} == '+' ? $this->shr32($a, $d) : $a << $d;
            $a = $b{$c} == '+' ? ($a + $d & 4294967295) : $a ^ $d;
        }
        return $a;
    }

    /**
     * Crypto function
     *
     * @param $x
     * @param $bits
     * @return number
     */
    private function shr32($x, $bits)
    {
        if ($bits <= 0) {
            return $x;
        }
        if ($bits >= 32) {
            return 0;
        }
        $bin = decbin($x);
        $l = strlen($bin);
        if ($l > 32) {
            $bin = substr($bin, $l - 32, 32);
        } elseif ($l < 32) {
            $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);
        }
        return bindec(str_pad(substr($bin, 0, 32 - $bits), 32, '0', STR_PAD_LEFT));
    }

    /**
     * Get the Unicode of the character at the specified index in a string
     *
     * @param string $str
     * @param int $index
     * @return null|number
     */
    private function charCodeAt($str, $index)
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');
        if (mb_check_encoding($char, 'UTF-8')) {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            $result = hexdec(bin2hex($ret));
            return $result;
        }
        return null;
    }
}
