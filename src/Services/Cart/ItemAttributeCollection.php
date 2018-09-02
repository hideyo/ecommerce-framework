<?php

namespace Hideyo\Ecommerce\Framework\Services\Cart;

use Illuminate\Support\Collection;

class ItemAttributeCollection extends Collection {

    public function __get($name)
    {
        if( $this->has($name) ) return $this->get($name);
        return null;
    }
}