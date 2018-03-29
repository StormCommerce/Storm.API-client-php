<?php


namespace Storm\Model\Support;


use Storm\Model\Basket;
use Storm\Model\BasketItem;
use Storm\Model\EmptyBasket;
use Storm\Model\Failure;
use Storm\Model\Product;
use Storm\Model\ProductItem;
use Storm\Model\StormItem;
use Storm\StormClient;
use Storm\Util\StormContext;
use StormWordpress\Template\Loader;

class Cart
{
    protected $basket;

    /**
     * @param $productItem Product|ProductItem|int
     */
    public function add($productItem, $quantity = null)
    {
        if (!is_object($productItem)) {
            $productItem = StormClient::self()->products()->GetProduct($productItem);
        }
        if ($productItem instanceof BasketItem) {
            $item = $productItem;
        } else {
            $product = $productItem->basketItem($quantity);
            $item = $this->getCartItemProductId($productItem);
        }
        if ($item == false) {
            $response = $this->shopping()->InsertBasketItem($this->basketId(), $product, $this->context()->get('AccoutId', 1));
        } else {
            if ($quantity == 0) {
                $response = $this->shopping()->DeleteBasketItem($this->basketId(), $item->LineNo);
            } else {
                if(($item->Quantity + $quantity) <= $item->OnHandSupplier->Value) {
                    $item->Quantity += $quantity;
                }
                $response = $this->shopping()->UpdateBasketItem($this->basketId(), $item);
            }
        }
        if ($response instanceof Failure) {
            echo $response->body;
            die;
        }
        return $response;
    }

    public function set($productItem, $quantity)
    {
        $item = $this->getCartItem($productItem);
        if ($item == null) {
            $item = $this->getCartItemProductId($productItem);
        }
        if($quantity <= $item->OnHandSupplier->Value) {
            $item->Quantity = $quantity;
        }
        $response = $this->shopping()->UpdateBasketItem($this->basketId(), $item);
        return $response;
    }

    /**
     * @param $productItem Product|ProductItem|int
     */
    public function remove($lineNo)
    {
        return $this->shopping()->DeleteBasketItem($this->basketId(), $lineNo);
    }

    public function getBasket()
    {
        return $this->hasBasket() ? $this->retrieveBasket() : $this->createBasket();
    }

    public function basketCreated()
    {
        return !empty($this->context()->get('BasketId'));
    }

    public function emptyCart()
    {
        return new EmptyBasket();
    }

    private function createBasket()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (StormClient::self()->context()->isLoggedIn()) {
            $user = StormClient::self()->context()->user();

            $attributes = [
                'CustomerId' => $user->Id
            ];

            if (!$user->Companies->isEmpty()) {
                $attributes['CompanyId'] = $user->Companies->first()->Id;
            }
            $basket = new StormItem($attributes,'Basket');
            $basket = $this->shopping()->CreateBasket($basket, $ip, $this->context()->get('AccountId', 1));
        } else {
            $basket = $this->shopping()->CreateBasket(null, $ip, $this->context()->get('AccountId', 1));

        }
        $this->context()->set('BasketId', $basket->Id);
        return $this->basket = $basket;
    }

    private function retrieveBasket()
    {
        $basketId = $this->context()->get('BasketId');
        if($basketId == null) {
            $this->createBasket();
        } else {
            $this->basket = $this->shopping()->GetBasket($basketId);
        }

        return $this->basket;
    }

    public function clearBasket()
    {
        if ($this->hasBasket()) {
            $this->shopping()->ClearBasket($this->context()->get('BasketId'));
        }
    }

    public function hasBasket()
    {
        return $this->context()->has('BasketId');
    }

    public function context()
    {
        return StormClient::self()->context();
    }

    /**
     * @param $item
     * @return BasketItem
     */
    public function getCartItemProductId($item)
    {
        $id = $item instanceof StormModel ? $item->Id : $item;
        $basket = $this->getBasket();
        if ($item !== null) {
            foreach ($basket->Items as $bItem) {
                /**
                 * @var $bItem BasketItem
                 */
                if ($id == $bItem->ProductId) {
                    return $bItem;
                }

            }
        }

        return false;
    }

    public function getCartItem($id)
    {
        $basket = $this->getBasket();

        foreach ($basket->Items as $bItem) {
            /**
             * @var $bItem BasketItem
             */
            if ($id == $bItem->Id) {
                return $bItem;
            }

        }

    }

    public function verifyOnHand()
    {
        return StormClient::self()->shopping()->ListExternalProductOnHandByBasket([
            'basketId' => $this->context()->get('BasketId'),
            'warehouse' => new StormItem([], 'Warehouse')
        ]);
    }

    /**
     * @return mixed|\Storm\Proxy\ShoppingProxy
     */
    public function shopping()
    {
        return StormClient::self()->shopping();
    }

    public function basketId()
    {
        return $this->getBasket()->Id;
    }
}