<?php

namespace App\Parse;

class Helper
{

    // helper public function for fix_case
    public static function safeFirst($separator, $word)
    {
        // uppercase words split by the separator (ex. dashes or periods)
        $parts = explode($separator, $word);
        $words = [];
        foreach ($parts as $part) {
            $words[] = self::isCamel($part) ? $part : self::first(mb_strtolower($part));
        }
        return implode($separator, $words);
    }

    // helper public function for multibytes ctype_alpha
    public static function alpha($text)
    {
        return (bool) preg_match('/^\p{L}*$/u', $text);
    }
    
    // helper public function for multibytes ctype_lower
    public static function lower($text)
    {
        return (bool) preg_match('/^\p{Ll}*$/u', $text);
    }

    // helper public function for multibytes ctype_upper
    public static function upper($text)
    {
        return (bool) preg_match('/^\p{Lu}*$/u', $text);
    }

    // helper public function for multibytes str_word_count
    public static function strWordCount($text)
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return 0;
        }
        return preg_match_all('/\s+/u', $trimmed) + 1;
    }

    // helper public function for multibytes ucfirst
    public static function first($string)
    {
        $strlen = mb_strlen($string);
        $firstChar = mb_substr($string, 0, 1);
        $then = mb_substr($string, 1, $strlen - 1);
        return mb_strtoupper($firstChar) . $then;
    }

     /**
     * Checks for camelCase words such as McDonald and MacElroy
     *
     * @param string $string the single word you wish to test
     * @return boolean
     */
    protected static function isCamel($string)
    {
        if (preg_match('/\p{L}(\p{Lu}*\p{Ll}\p{Ll}*\p{Lu}|\p{Ll}*\p{Lu}\p{Lu}*\p{Ll})\p{L}*/u', $string)) {
            return true;
        }
        return false;
    }

    
    
}
