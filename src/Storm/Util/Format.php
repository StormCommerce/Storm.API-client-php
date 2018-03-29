<?php


namespace Storm\Util;


use Storm\StormClient;

class Format
{
    public static function getNumberFormat($lang)
    {
        $format = [];
        $lang = strtolower($lang);
        switch ($lang) {
            case 'gb':
                $format['decimal_mark'] = '.';
                $format['separator'] = ',';
                $format['decimals'] = 2;
                $format['lang'] = $lang;
                break;
            default:
            case 'se':
                $format['decimal_mark'] = ',';
                $format['separator'] = ' ';
                $format['decimals'] = 0;
                $format['lang'] = $lang;
                break;
        }
        return $format;
    }

    public static function formatNumber($number, $format)
    {
        if ($format['decimals'] == 0) {
            $number = intval(round($number));
        }
        if(is_string($number) && mb_strlen($number) == 0) {
            $number = 0;
        }
        if ($format['lang'] == 'gb') {
            setlocale(LC_MONETARY, 'en_GB');
            $fmt = '%i';
            $number = money_format($fmt, $number);
        } else {
            $number = str_replace('.', $format['decimal_mark'], strval($number));
            $number = preg_replace('/\\B(?=(\\d{3})+(?!\\d))/', $format['separator'], $number);
        }

        return $number;
    }

    public static function formatPrice($price)
    {
        $format = Format::getNumberFormat(StormClient::self()->application()->GetApplication()->Countries->Default->Code);
        return Format::formatNumber($price, $format);
    }

    public static function formatPriceCurrency($price)
    {
        $price = Format::formatPrice($price);
        $currency = StormClient::self()->context()->currency();
        if ($currency->Prefix != null) {
            $price = $currency->Prefix . $price;
        }
        if ($currency->Suffix != null) {
            $price = $price . $currency->Suffix;
        }
        return $price;
    }
}