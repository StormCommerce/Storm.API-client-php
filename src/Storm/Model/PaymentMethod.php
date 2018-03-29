<?php


namespace Storm\Model;


use Storm\Model\Support\StormModel;
use Storm\StormClient;
use Storm\Util\Str;

class PaymentMethod extends StormModel
{
    protected $appends = [
        'ImageUrl'
    ];

    public function getImageUrl() {
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
}