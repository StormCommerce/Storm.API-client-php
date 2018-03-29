<?php


namespace Storm\Model;


use Storm\Model\Support\StormModel;
use Storm\Util\Format;

/**
 * Class SummaryItem
 * @package Storm\Model
 * @property string AmountFormatted
 * @property string AmountFormattedCurrency
 * @property string VatFormatted
 * @property string VatFormattedCurrency
 * @property string TotalFormatted
 * @property string TotalFormattedCurrency
 */
class SummaryItem extends StormModel
{
    protected $appends = [
        'AmountFormatted',
        'AmountFormattedCurrency',
        'VatFormatted',
        'VatFormattedCurrency',
        'TotalFormatted',
        'TotalFormattedCurrency'
    ];

    public function getAmountFormatted()
    {
        return Format::formatPrice($this->Amount);
    }

    public function getVatFormatted()
    {
        return Format::formatPrice($this->Vat);
    }

    public function getVatFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Vat);
    }

    public function getTotalFormatted()
    {
        return Format::formatPrice($this->Vat + $this->Amount);
    }

    public function getTotalFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Vat + $this->Amount);
    }

    public function getAmountFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Amount);
    }
}