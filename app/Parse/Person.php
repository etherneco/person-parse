<?php

/**
 *
 * Create Person Parser 
 * @author Daniel Wojtak <etherneco@gmail.com>
 * @use ParsePerson::parse(name)
 * 
 * 
 * 
 */

namespace App\Parse;

use App\Parse\Dictionary;
use App\Parse\Helper;

class Person
{

    private static $errorList = [];
    private $notNicknames = [];

    
    
    /**
     * Parse Static entry point.
     *
     * @param string $name the full name you wish to parse
     * @return array returns associative array of name parts
     */
    public static function parse($name)
    {
        $parser = new self();
        return $parser->parseName($name);
    }


    /**
     * This is the primary method which calls all other methods
     *
     * @param string $name the full name you wish to parse
     * @return array returns associative array of name parts
     */
    public function parseName($fullName)
    {

        // Setup
        $fullName = trim($fullName);
        $salutation = '';
        $fname = '';
        $initials = '';
        $lname = '';
        $lnameBase = '';
        $lnameCompound = '';
        $suffix = '';
        $name = [];

        // Find all the professional suffixes possible
        $professional = $this->getProSuffix($fullName);

        // The position of the first professional suffix denotes the end of the name and the start of suffixes
        $firstSuffix = mb_strlen($fullName);
        foreach ($professional as $psx) {
            $start = mb_strpos($fullName, $psx);
            if ($start === false) {
                continue;
            }
            if ($start < $firstSuffix) {
                $firstSuffix = $start;
            }
        }

        // everything to the right of the first professional suffix is part of the suffix
        $suffix = mb_substr($fullName, $firstSuffix);
        $fullName = mb_substr($fullName, 0, $firstSuffix);

        // Deal with nickname, push to array
        $nick = $this->getName($fullName);
        if ($nick) {
            $name['nickname'] = mb_substr($nick, 1, mb_strlen($nick) - 2);
            $fullName = str_replace($nick, '', $fullName);
            $fullName = str_replace('  ', ' ', $fullName);
        }

        // Grab a list of words from the remainder of the full name
        $unfiltered = $this->breakWords($fullName);

        // Is first word a title or multiple titles consecutively?
        if (count($unfiltered)) {
            while (count($unfiltered) > 0 && $s = $this->isSalutation($unfiltered[0])) {
                $salutation .= $s . ' ';
                array_shift($unfiltered);
            }
            $salutation = trim($salutation);
            while (count($unfiltered) > 0 && $s = $this->isLineSuffix($unfiltered[count($unfiltered) - 1], $fullName)) {
                if ($suffix !== '') {
                    $suffix = $s . ', ' . $suffix;
                } else {
                    $suffix .= $s;
                }
                array_pop($unfiltered);
            }
            $suffix = trim($suffix);
        } else {
            $salutation = '';
            $suffix = '';
        }

        $nameArr = [];
        foreach ($unfiltered as $namePart) {
            $namePart = trim($namePart);
            $namePart = rtrim($namePart, ',');
            if (mb_strlen($namePart) === 1) {
                if (!Helper::alpha($namePart)) {
                    $namePart = '';
                }
            }
            if (mb_strlen(trim($namePart))) {
                $nameArr[] = $namePart;
            }
        }
        $unfiltered = $nameArr;

        // set the ending range after prefix/suffix trim
        $end = count($unfiltered);

        for ($i = 0; $i < $end - 1; $i++) {
            $string = $unfiltered[$i];
            // move on to parsing the last name if we find an indicator of a compound last name (Von, Van, etc)
            // we use $i != 0 to allow for rare cases where an indicator is actually the first name (like "Von Fabella")
            if ($this->isCompound($string) && $i !== 0) {
                break;
            }
            if ($this->isInitial($string)) {
                // is the initial the first word?
                if ($i === 0) {
                    if ($this->isInitial($unfiltered[$i + 1])) {
                        $fname .= ' ' . mb_strtoupper($string);
                    } else {
                        $initials .= ' ' . mb_strtoupper($string);
                    }
                }
                // otherwise, just go ahead and save the initial
                else {
                    $initials .= ' ' . mb_strtoupper($string);
                }
            } else {
                $fname .= ' ' . $this->fixWords($string);
            }
        }

        if (count($unfiltered)) {
            // check that we have more than 1 word in our string
            if ($end - 0 > 1) {
                // concat the last name and split last name in base and compound
                for ($i; $i < $end; $i++) {
                    if ($this->isCompound($unfiltered[$i])) {
                        $lnameCompound .= ' ' . $unfiltered[$i];
                    } else {
                        $lnameBase .= ' ' . $this->fixWords($unfiltered[$i]);
                    }
                    $lname .= ' ' . $this->fixWords($unfiltered[$i]);
                }
            } else {
                // otherwise, single word strings are assumed to be first names
                $fname = $this->fixWords($unfiltered[$i]);
            }
        } else {
            $fname = '';
        }

        // return the various parts in an array
        $name['salutation'] = $salutation;
        $name['fname'] = trim($fname);
        $name['initials'] = trim($initials);
        $name['lname'] = trim($lname);
        $name['lname_base'] = trim($lnameBase);
        $name['lname_compound'] = trim($lnameCompound);
        $name['suffix'] = $suffix;
        return $name;
    }

    
    
    /**
     * Get list error while proccessing
     * 
     *    * @return all error list
     */
    public static function getErrorList()
    {
        return self::$errorList;
    }

    
    /**
     * Breaks name into individual words
     *
     * @param string $name the full name you wish to parse
     * @return array full list of words broken down by spaces
     */
    public function breakWords($name)
    {
        $tempArr = explode(' ', $name);
        $finalArr = [];
        foreach ($tempArr as $string) {
            if ($string !== '' && $string !== ',') {
                $finalArr[] = $string;
            }
        }
        return $finalArr;
    }

    /**
     * Checks for the existence of, and returns professional suffix
     *
     * @param string $name the name you wish to test
     * @return mixed returns the suffix if exists, false otherwise
     */
    public function getProSuffix($name)
    {

        $foundArr = [];
        $dictionary = Dictionary::getDictionary();
        foreach ($dictionary['suffixes']['prof'] as $suffix) {
            if (preg_match('/[,\s]+' . preg_quote($suffix) . '\b/i', $name, $matches)) {
                $found = trim($matches[0]);
                $found = rtrim($found, ',');
                $found = ltrim($found, ',');
                $foundArr[] = trim($found);
            }
        }
        return $foundArr;
    }

    /**
     * Function to check name for existence of nickname based on these stipulations
     *  - String wrapped in parentheses (string)
     *  - String wrapped in double quotes "string"
     *  x String wrapped in single quotes 'string'
     *
     *  I removed the check for strings in single quotes 'string' due to possible
     *  conflicts with names that may include apostrophes. Arabic transliterations, for example
     *
     * @param string $name the name you wish to test against
     * @return mixed returns nickname if exists, false otherwise
     */
    protected function getName($name)
    {
        if (preg_match("/[\(|\"].*?[\)|\"]/", $name, $matches)) {
            if (!in_array(mb_strtolower($matches[0]), $this->notNicknames, true)) {
                return $matches[0];
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Checks word against array of common lineage suffixes
     *
     * @param string $string the single word you wish to test
     * @param string $name full name for context in determining edge-cases
     * @return mixed boolean if false, string if true (returns suffix)
     */
    protected function isLineSuffix($string, $name)
    {

        $string = str_replace('.', '', mb_strtolower($string));
        $string = rtrim($string, ',');

        $dictionary = Dictionary::getDictionary();
        $line = array_search($string, array_map('mb_strtolower', $dictionary['suffixes']['line']));

        if ($line !== false) {
            $matchedCase = $dictionary['suffixes']['line'][$line];

            $temp = $dictionary['suffixes']['line'];
            unset($temp[$line]);

            if ($string === 'senior' || $string === 'junior') {

                if ($this->mbStrWordCount($name) < 3) {
                    return false;
                }

                foreach ($temp as $suffix) {
                    if (preg_match("/\b" . $suffix . "\b/i", $name)) {
                        return false;
                    }
                }
            }
            return $matchedCase;
        }
        return false;
    }

    /**
     * Checks word against list of common honorific prefixes
     *
     * @param string $string the single word you wish to test
     * @return boolean
     */
    protected function isSalutation($string)
    {
        $string = str_replace('.', '', mb_strtolower($string));
        $dictionary = Dictionary::getDictionary();
        foreach ($dictionary['prefix'] as $replace => $originals) {
            if (in_array($string, $originals)) {
                return $replace;
            }
        }
        return false;
    }

    /**
     * Checks our dictionary of compound indicators to see if last name is compound
     *
     * @param string $string the single word you wish to test
     * @return boolean
     */
    protected function isCompound($string)
    {
        $dictionary = Dictionary::getDictionary();
        return in_array(mb_strtolower($string), $dictionary['compound'], true);
    }

    /**
     * Test string to see if it's a single letter/initial (period optional)
     *
     * @param string $string the single word you wish to test
     * @return boolean
     */
    protected function isInitial($string)
    {
        return ((mb_strlen($string) === 1) || (mb_strlen($string) === 2 && mb_substr($string, 1, 1) === '.'));
    }


    // ucfirst words split by dashes or periods
    // ucfirst all upper/lower strings, but leave camelcase words alone

    public function fixWords($string)
    {
        $dictionary = Dictionary::getDictionary();

        // Fix case for words split 
        if (mb_strpos($string, '.') !== false) {
            $string = Helper::safeFirst('.', $string);
        }
        if (mb_strpos($string, '-') !== false) {
            $string = Helper::safeFirst('-', $string);
        }
        if (mb_strlen($string) === 1) {
            $string = mb_strtoupper($string);
        }

        // Special case for 2-letter words
        if (mb_strlen($string) === 2) {
            $firstChar = mb_substr($string, 0, 1);
            $secondChar = mb_substr($string, 1, 1);
            $firstLower = mb_strtolower($firstChar);
            $secondLower = mb_strtolower($secondChar);
            $firstIsVowel = in_array($firstLower, $dictionary['vowels'], true);
            $secondIsVowel = in_array($secondLower, $dictionary['vowels'], true);

            // Both letters vowels (uppercase both)
            if ($firstIsVowel && $secondIsVowel) {
                $string = mb_strtoupper($string);
            }
            // Both letters consonants (uppercase both)
            if (!$firstIsVowel && !$secondIsVowel) {
                $string = mb_strtoupper($string);
            }
            // First letter is vowel, second letter consonant (uppercase first)
            if ($firstIsVowel && !$secondIsVowel) {
                $string = Helper::first(mb_strtolower($string));
            }
            // First letter consonant, second letter vowel or "y" (uppercase first)
            if (!$firstIsVowel && ($secondIsVowel || $secondLower === 'y')) {
                $string = Helper::first(mb_strtolower($string));
            }
        }

        if ((mb_strlen($string) >= 3) && (Helper::upper($string) || Helper::lower($string))) {
            $string = Helper::first(mb_strtolower($string));
        }

        return $string;
    }

    private function mbStrWordCount($text)
    {
        return Helper::strWordCount($text);
    }

}
