<?php


namespace Storm\Model;

use Storm\Model\Support\Collection;
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
 * @property string PriceCatalogFormatted
 * @property string PriceCatalogFormattedCurrency
 * @property Currency Currency
 */
class Product extends StormModel
{
    /**
     *
     */
    const PRODUCT = 1;
    /**
     *
     */
    const FREIGHT = 3;
    /**
     * @var string $Name
     */
    protected $updateUrl = "GetProduct?id=%d&statusSeed=&storeSeed=&pricelistSeed=&customerId=&companyId=&cultureCode=&currencyId=";
    /**
     * @var array
     */
    protected $appends = [
        'ImageUrl',
        'PriceFormatted',
        'PriceFormattedCurrency',
        'PriceCatalogFormatted',
        'PriceCatalogFormattedCurrency',
        'Total',
        'TotalFormatted',
        'TotalFormattedCurrency',
        'VatFormatted',
        'VatFormattedCurrency',
        'Currency',
        'Purchasable'
    ];

    /**
     *
     */
    public function update()
    {
        $url = sprintf($this->updateUrl, $this->Id);
        $this->fill(json_decode(StormClient::self()->products()->get($url)));
    }

    /**
     * @param int $quantity
     * @return BasketItem
     */
    public function basketItem($quantity = 1)
    {
        if ($quantity == null) {
            $quantity = 1;
        }
        $item = new BasketItem($this->attributes);
        $item->Quantity = $quantity;
        $item->ProductId = $this->Id;
        $item->LineNo = 1;
        return $item;
    }

    /**
     * @return ProductAccessories
     */
    public function accessories()
    {
        return StormClient::self()->products()->ListProductAccessories5($this->Id);
    }

    /**
     * @return bool
     */
    public function hasAccessories()
    {
        return StormClient::self()->products()->ListProductAccessories5($this->Id)->Accessories->ItemCount > 0;
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
     * @return bool
     */
    public function inStock()
    {
        $inStock = false;
        if ($this->OnHandSupplier->Value > 0) {
            $inStock = true;
        }
        if ($this->OnHandStore->Value > 0) {
            $inStock = true;
        }
        if ($this->OnHand->Value > 0) {
            $inStock = true;
        }
        return $inStock;
    }

    /**
     * @param int $width
     * @param int $height
     * @return array
     */
    public function images($width = 0, $height = 0)
    {
        $images = [$this->imageUrl($width, $height)];
        foreach ($this->Files as $productFile) {
            /**
             * @var $productFile ProductFile
             */
            if ($productFile->Type == 1) {
                $images[] = $this->getImage($productFile->Key, $width, $height);
            }
        }
        return $images;
    }

    /**
     * @param $key
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getImage($key, $width = 0, $height = 0)
    {
        $base = Str::trailingSlashIt(StormClient::self()->imageBaseUrl());
        if ($width > 0 && $height > 0) {
            return "{$base}{$key}.jpg?maxwidth=$width&maxheight=$height&scale=upscalecanvas";
        } else {
            return "{$base}{$key}.jpg";
        }
    }

    /**
     * @return string
     */
    public function cacheKey()
    {
        return $this->key() . "-" . $this->Id;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl();
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
    public function getPriceCatalogFormatted()
    {
        return Format::formatPrice($this->PriceCatalog);
    }

    /**
     * @return mixed|string
     */
    public function getPriceCatalogFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->PriceCatalog);
    }

    public function getTotal()
    {
        return $this->Price * $this->VatRate;
    }

    /**
     * @return mixed
     */
    public function getTotalFormatted()
    {
        return Format::formatPrice($this->Price * $this->VatRate);
    }

    /**
     * @return mixed|string
     */
    public function getTotalFormattedCurrency()
    {
        return Format::formatPriceCurrency($this->Price * $this->VatRate);
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
     * @return mixed
     */
    public function getPurchasable()
    {
        return StormClient::self()->middlewareContainer()->resolve('product_purchasable', true, $this);
    }

    /**
     * @return array|Collection|IFilterItem[]
     */
    public function getFocusParametricsList($id)
    {
        $parametrics = new Collection([]);
        if ($id > 0) {
            $parametrics = StormClient::self()->products()->ListFocusParametrics($id);
        }
        return $parametrics;
    }

    public function getGroupedParametrics()
    {
        $grouped = [];
        foreach ($this->Parametrics as $parametric) {
            $grouped[$parametric->GroupName][] = $parametric;
        }
        return $grouped;
    }

    /**
     * @return array
     */
    public function getFeaturesList()
    {

        $id = $this->Categories->first()->Id;
        $focusParametrics = $this->getFocusParametricsList($id)->lists('Id');
        $list = [];
        foreach ($this->Parametrics as $parametric) {
            /**
             * @var ProductParametric $parametric
             */
            if (!in_array($parametric->Id, $focusParametrics)) {
                continue;
            }
            $list[] = $parametric;
        }
        return $list;
    }
}