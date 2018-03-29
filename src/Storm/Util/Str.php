<?php


namespace Storm\Util;


class Str
{
    public static function trailingSlashIt($string)
    {
        return rtrim($string, '/\\') . "/";
    }

    public static function contains($search, $haystack, $caseSensitive = true)
    {
        if (!$caseSensitive) {
            $search = strtolower($search);
            $haystack = strtolower($haystack);
        }
        return mb_strpos($haystack, $search) !== false;
    }

    public static function searchReplace($search, $replace, $subject, $count = 1)
    {
        if (!is_array($search) && is_array($replace)) {
            return false;
        }
        if (is_array($subject)) {
            // call mb_replace for each single string in $subject
            foreach ($subject as &$string) {
                $string = self::searchReplace($search, $replace, $string, $c);
                $count += $c;
            }
        } elseif (is_array($search)) {
            if (!is_array($replace)) {
                foreach ($search as &$string) {
                    $subject = self::searchReplace($string, $replace, $subject, $c);
                    $count += $c;
                }
            } else {
                $n = max(count($search), count($replace));
                while ($n--) {
                    $subject = self::searchReplace(current($search), current($replace), $subject, $c);
                    $count += $c;
                    next($search);
                    next($replace);
                }
            }
        } else {
            $parts = mb_split(preg_quote($search), $subject);
            $count = count($parts) - 1;
            $subject = implode($replace, $parts);
        }
        return $subject;
    }


    public static function startsWithUpper($str)
    {
        $chr = mb_substr($str, 0, 1, "UTF-8");
        return mb_strtolower($chr, "UTF-8") != $chr;
    }

    public static function snakeCase($string)
    {
        return strtolower(
            preg_replace(
                ["/([A-Z]+)/", "/_([A-Z]+)([A-Z][a-z])/"],
                ["_$1", "_$1_$2"],
                lcfirst($string)
            )
        );
    }

    public static function endsWith($with, $string)
    {
        // search forward starting from end minus needle length characters
        return $with === "" || (($temp = strlen($string) - strlen($with)) >= 0 && strpos($string, $with, $temp) !== false);
    }

    public static function startsWith($with, $string)
    {
        return mb_substr($string, 0, strlen($with)) === $with;
    }
}