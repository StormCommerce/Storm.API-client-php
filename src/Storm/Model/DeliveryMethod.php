<?php


namespace Storm\Model;


use Storm\Model\Support\StormModel;
use Storm\StormClient;
use Storm\Util\Format;
use Storm\Util\Str;

class DeliveryMethod extends StormModel
{
    protected $appends = [
        'TotalFormatted',
        'TotalFormattedCurrency',
        'PriceFormatted',
        'PriceFormattedCurrency',
        //'ImageUrl'
    ];

    public function getImageUrl()
    {
        return $this->getImage($this->ImageKey);
    }

    /**
     * @param $key
     * @return string
     */
    public function getImage($key)
    {
        $base = Str::trailingSlashIt(StormClient::self()->imageBaseUrl());
        return "{$base}{$key}.jpg";
    }

    /**
     * @param int $width
     * @param int $height
     * @return string
     */
    public function imageUrl($width = 0, $height = 0)
    {
        if (mb_strlen($this->ImageKey) == 0) {
            return "";
        }
        $image = $this->getImage($this->ImageKey);
        if ($width > 0 && $height > 0) {
            $image = "$image?maxwidth=$width&maxheight=$height&scale=upscalecanvas";
        }
        return $image;
    }

    public function getTotalFormatted()
    {
        return Format::formatPrice($this->Price * $this->VatRate);
    }

    public function getTotalFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Price * $this->VatRate);
    }

    public function getPriceFormatted()
    {
        return Format::formatPrice($this->Price);
    }

    public function getPriceFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Price);
    }

}