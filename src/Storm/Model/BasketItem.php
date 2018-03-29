<?php


namespace Storm\Model;


use Storm\Model\Support\StormModel;
use Storm\StormClient;
use Storm\Util\Format;
use Storm\Util\Str;

/**
 * Class Product
 * @package Storm\Model
 * @property string ImageUrl
 * @property string PriceFormatted
 * @property string PriceFormattedCurrency
 * @property string TotalFormatted
 * @property string TotalFormattedCurrency
 * @property string PriceWithVatFormatted
 * @property string PriceWithVatFormattedCurrency
 * @property string PriceOriginalFormatted
 * @property string PriceOriginalFormattedCurrency
 * @property string PriceOriginalWithVatFormatted
 * @property string PriceOriginalWithVatFormattedCurrency
 * @property string TotalWithVatFormatted
 * @property string TotalWithVatFormattedCurrency
 * @property string VatFormatted
 * @property string VatFormattedCurrency
 * @property Currency Currency
 */
class BasketItem extends StormModel
{
    /**
     * @var array
     */
    protected $appends = [
        'ImageUrl',
        'TotalFormatted',
        'TotalFormattedCurrency',
        'PriceFormatted',
        'PriceFormattedCurrency',
        'PriceWithVatFormatted',
        'PriceWithVatFormattedCurrency',
        'PriceOriginalFormatted',
        'PriceOriginalFormattedCurrency',
        'PriceOriginalWithVatFormatted',
        'PriceOriginalWithVatFormattedCurrency',
        'PriceRecommendedFormatted',
        'PriceRecommendedFormattedCurrency',
        'PriceRecommendedWithVatFormatted',
        'PriceRecommendedWithVatFormattedCurrency',
        'TotalWithVatFormatted',
        'TotalWithVatFormattedCurrency',
        'VatFormatted',
        'VatFormattedCurrency',
        'Currency'
    ];

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl();
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
     * @return mixed
     */
    public function getPriceFormatted()
    {
        return Format::formatPrice($this->Price);
    }

    /**
     * @return mixed|string
     */
    public function getPriceFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Price);
    }

    /**
     * @return mixed
     */
    public function getPriceWithVatFormatted()
    {
        return Format::formatPrice($this->Price * $this->VatRate);
    }

    /**
     * @return mixed|string
     */
    public function getPriceWithVatFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Price * $this->VatRate);
    }

    /**
     * @return mixed
     */
    public function getPriceOriginalFormatted()
    {
        return Format::formatPrice($this->PriceOriginal);
    }

    /**
     * @return mixed|string
     */
    public function getPriceOriginalFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->PriceOriginal);
    }

    /**
     * @return mixed
     */
    public function getPriceOriginalWithVatFormatted()
    {
        return Format::formatPrice($this->PriceOriginal * 1.25);
    }

    /**
     * @return mixed|string
     */
    public function getPriceOriginalWithVatFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->PriceOriginal * 1.25);
    }

    public function getPriceRecommendedFormatted()
    {
        return Format::formatPrice($this->PriceRecommended);
    }

    public function getPriceRecommendedFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->PriceRecommended);
    }

    public function getPriceRecommendedWithVatFormatted()
    {
        return Format::formatPrice($this->PriceRecommended * $this->VatRate);
    }

    public function getPriceRecommendedWithVatFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->PriceRecommended * $this->VatRate);
    }

    /**
     * @return mixed
     */
    public function getTotalFormatted()
    {
        return Format::formatPrice($this->Price * $this->Quantity);
    }

    /**
     * @return mixed|string
     */
    public function getTotalFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Price * $this->Quantity);
    }

    /**
     * @return mixed
     */
    public function getTotalWithVatFormatted()
    {
        return Format::formatPrice($this->Price * $this->VatRate * $this->Quantity);
    }

    /**
     * @return mixed|string
     */
    public function getTotalWithVatFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Price * $this->VatRate * $this->Quantity);
    }

    /**
     * @return mixed
     */
    public function getVatFormatted()
    {
        $vat = ($this->Price * $this->VatRate) - $this->Price;
        return Format::formatPrice($vat);
    }

    /**
     * @return mixed|string
     */
    public function getVatFormattedCurrency()
    {
        $vat = ($this->Price * $this->VatRate) - $this->Price;
        return Format::formatPriceCurrency($vat);
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return StormClient::self()->context()->currency();
    }

    /**
     * @return ProductAccessories
     */
    public function accessories()
    {
        return StormClient::self()->products()->ListProductAccessories5($this->ProductId);
    }
}  