<?php

namespace Hideyo\Services\Cart;

use Hideyo\Services\Cart\Helpers\Helpers;

class Cart
{
    /**
     * the item storage
     *
     * @var
     */
    protected $session;
    /**
     * the event dispatcher
     *
     * @var
     */
    protected $events;
    /**
     * the cart session key
     *
     * @var
     */
    protected $instanceName;

    /**    
     *
     * @var
     */
    protected $sessionKeyCartItems;

    /**
     * the session key use to persist cart conditions
     *
     * @var
     */
    protected $sessionKeyCartConditions;

    /**
     * the session key use to persist voucher
     *
     * @var
     */
    protected $sessionKeyCartVoucher;

    /**
     * Configuration to pass to ItemCollection
     *
     * @var
     */
    protected $config;

    /**
     * our object constructor
     *
     * @param $session
     * @param $events
     * @param $instanceName
     */
    public function __construct($session, $events, $instanceName, $session_key, $config)
    {
        $this->events = $events;
        $this->session = $session;
        $this->instanceName = $instanceName;
        $this->sessionKeyCartItems = $session_key . '_cart_items';
        $this->sessionKeyCartConditions = $session_key . '_cart_conditions';
        $this->sessionKeyCartVoucher = $session_key . '_voucher';
        $this->fireEvent('created');
        $this->config = $config;
    }


    /**
     * get instance name of the cart
     *
     * @return string
     */
    public function getInstanceName()
    {
        return $this->instanceName;
    }


    /**
     * get an item on a cart by item ID
     *
     * @param $itemId
     * @return mixed
     */
    public function get($itemId)
    {
        return $this->getContent()->get($itemId);
    }

    /**
     * check if an item exists by item ID
     *
     * @param $itemId
     * @return bool
     */
    public function has($itemId)
    {
        return $this->getContent()->has($itemId);
    }

    /**
     * add item to the cart, it can be an array or multi dimensional array
     *
     * @param string|array $id
     * @param string $name
     * @param float $price
     * @param int $quantity
     * @param array $attributes
     * @param CartCondition|array $conditions
     * @return $this
     * @throws InvalidItemException
     */
    public function add($id, $attributes = array(), $quantity = null, $conditions = array(), $orderId = 0)
    {
        // validate data
        $item = array(
            'id' => $id,
            'orderId' => $orderId,
            'attributes' => $attributes,
            'quantity' => $quantity,
            'conditions' => $conditions
        );

        $cart = $this->getContent();
        // if the item is already in the cart we will just update it
        if ($cart->has($id)) {
            $this->update($id, $item);
        } else {

            $this->addRow($id, $item);
        }
        return $this;
    }

    /**
     * update a cart
     *
     * @param $id
     * @param $data
     *
     * the $data will be an associative array, you don't need to pass all the data, only the key value
     * of the item you want to update on it
     * @return bool
     */
    public function update($id, $data)
    {
        if($this->fireEvent('updating', $data) === false) {
            return false;
        }
        $cart = $this->getContent();
        $item = $cart->pull($id);
        foreach ($data as $key => $value) {
            // if the key is currently "quantity" we will need to check if an arithmetic
            // symbol is present so we can decide if the update of quantity is being added
            // or being reduced.
            if ($key == 'quantity') {
                // we will check if quantity value provided is array,
                // if it is, we will need to check if a key "relative" is set
                // and we will evaluate its value if true or false,
                // this tells us how to treat the quantity value if it should be updated
                // relatively to its current quantity value or just totally replace the value
                if (is_array($value)) {
                    if (isset($value['relative'])) {
                        if ((bool)$value['relative']) {
                            $item = $this->updateQuantityRelative($item, $key, $value['value']);
                        } else {
                            $item = $this->updateQuantityNotRelative($item, $key, $value['value']);
                        }
                    }
                } else {
                    $item = $this->updateQuantityRelative($item, $key, $value);
                }
            } elseif ($key == 'attributes') {
                $item[$key] = new ItemAttributeCollection($value);
            } else {
                $item[$key] = $value;
            }
        }
        $cart->put($id, $item);
        $this->save($cart);
        $this->fireEvent('updated', $item);
        return true;
    }

    /**
     * update a cart item quantity relative to its current quantity
     *
     * @param $item
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function updateQuantityRelative($item, $key, $value)
    {
        if (preg_match('/\-/', $value) == 1) {
            $value = (int)str_replace('-', '', $value);
            // we will not allowed to reduced quantity to 0, so if the given value
            // would result to item quantity of 0, we will not do it.
            if (($item[$key] - $value) > 0) {
                $item[$key] -= $value;
            }
        } elseif (preg_match('/\+/', $value) == 1) {
            $item[$key] += (int)str_replace('+', '', $value);
        } else {
            $item[$key] += (int)$value;
        }
        return $item;
    }

    /**
     * update cart item quantity not relative to its current quantity value
     *
     * @param $item
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function updateQuantityNotRelative($item, $key, $value)
    {
        $item[$key] = (int)$value;
        return $item;
    }

    /**
     * get the cart
     *
     * @return CartCollection
     */
    public function getContent()
    {
        return (new CartCollection($this->session->get($this->sessionKeyCartItems)));
    }

    /**
     * add row to cart collection
     *
     * @param $id
     * @param $item
     * @return bool
     */
    protected function addRow($id, $item)
    {
        if($this->fireEvent('adding', $item) === false) {
            return false;
        }
        $cart = $this->getContent();
        $cart->put($id, new ItemCollection($item, $this->config));
        $this->save($cart);
        $this->fireEvent('added', $item);
        return true;
    }

    /**
     * add a condition on the cart
     *
     * @param CartCondition|array $condition
     * @return $this
     * @throws InvalidConditionException
     */
    public function condition($condition)
    {
        if (is_array($condition)) {
            foreach ($condition as $c) {
                $this->condition($c);
            }
            return $this;
        }
        if (!$condition instanceof CartCondition) throw new InvalidConditionException('Argument 1 must be an instance of \'Darryldecode\Cart\CartCondition\'');
        $conditions = $this->getConditions();
        // Check if order has been applied
        if ($condition->getOrder() == 0) {
            $last = $conditions->last();
            $condition->setOrder(!is_null($last) ? $last->getOrder() + 1 : 1);
        }
        $conditions->put($condition->getName(), $condition);
        $conditions = $conditions->sortBy(function ($condition, $key) {
            return $condition->getOrder();
        });
        $this->saveConditions($conditions);
        return $this;
    }

    /**
     * get conditions applied on the cart
     *
     * @return CartConditionCollection
     */
    public function getConditions()
    {
        return new CartConditionCollection($this->session->get($this->sessionKeyCartConditions));
    }

    /**
     * get condition applied on the cart by its name
     *
     * @param $conditionName
     * @return CartCondition
     */
    public function getCondition($conditionName)
    {
        return $this->getConditions()->get($conditionName);
    }

    /**
     * Get all the condition filtered by Type
     * Please Note that this will only return condition added on cart bases, not those conditions added
     * specifically on an per item bases
     *
     * @param $type
     * @return CartConditionCollection
     */
    public function getConditionsByType($type)
    {
        return $this->getConditions()->filter(function (CartCondition $condition) use ($type) {
            return $condition->getType() == $type;
        });
    }

    /**
     * Remove all the condition with the $type specified
     * Please Note that this will only remove condition added on cart bases, not those conditions added
     * specifically on an per item bases
     *
     * @param $type
     * @return $this
     */
    public function removeConditionsByType($type)
    {
        $this->getConditionsByType($type)->each(function ($condition) {
            $this->removeCartCondition($condition->getName());
        });
    }

    /**
     * removes a condition on a cart by condition name,
     * this can only remove conditions that are added on cart bases not conditions that are added on an item/product.
     * If you wish to remove a condition that has been added for a specific item/product, you may
     * use the removeItemCondition(itemId, conditionName) method instead.
     *
     * @param $conditionName
     * @return void
     */
    public function removeCartCondition($conditionName)
    {
        $conditions = $this->getConditions();
        $conditions->pull($conditionName);
        $this->saveConditions($conditions);
    }

    /**
     * remove a condition that has been applied on an item that is already on the cart
     *
     * @param $itemId
     * @param $conditionName
     * @return bool
     */
    public function removeItemCondition($itemId, $conditionName)
    {
        if (!$item = $this->getContent()->get($itemId)) {
            return false;
        }
        if ($this->itemHasConditions($item)) {
            // NOTE:
            // we do it this way, we get first conditions and store
            // it in a temp variable $originalConditions, then we will modify the array there
            // and after modification we will store it again on $item['conditions']
            // This is because of ArrayAccess implementation
            // see link for more info: http://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect
            $tempConditionsHolder = $item['conditions'];
            // if the item's conditions is in array format
            // we will iterate through all of it and check if the name matches
            // to the given name the user wants to remove, if so, remove it
            if (is_array($tempConditionsHolder)) {
                foreach ($tempConditionsHolder as $k => $condition) {
                    if ($condition->getName() == $conditionName) {
                        unset($tempConditionsHolder[$k]);
                    }
                }
                $item['conditions'] = $tempConditionsHolder;
            }
            // if the item condition is not an array, we will check if it is
            // an instance of a Condition, if so, we will check if the name matches
            // on the given condition name the user wants to remove, if so,
            // lets just make $item['conditions'] an empty array as there's just 1 condition on it anyway
            else {
                $conditionInstance = "Darryldecode\\Cart\\CartCondition";
                if ($item['conditions'] instanceof $conditionInstance) {
                    if ($tempConditionsHolder->getName() == $conditionName) {
                        $item['conditions'] = array();
                    }
                }
            }
        }
        $this->update($itemId, array(
            'conditions' => $item['conditions']
        ));
        return true;
    }

    /**
     * remove all conditions that has been applied on an item that is already on the cart
     *
     * @param $itemId
     * @return bool
     */
    public function clearItemConditions($itemId)
    {
        if (!$item = $this->getContent()->get($itemId)) {
            return false;
        }
        $this->update($itemId, array(
            'conditions' => array()
        ));
        return true;
    }

    /**
     * clears all conditions on a cart,
     * this does not remove conditions that has been added specifically to an item/product.
     * If you wish to remove a specific condition to a product, you may use the method: removeItemCondition($itemId, $conditionName)
     *
     * @return void
     */
    public function clearCartConditions()
    {
        $this->session->put(
            $this->sessionKeyCartConditions,
            array()
        );
    }

    /**
     * get cart sub total without conditions
     * @param bool $formatted
     * @return float
     */
    public function getSubTotalWithoutConditions($formatted = true)
    {
        $cart = $this->getContent();
        $sum = $cart->sum(function ($item) {
            return $item->getOriginalPriceWithTaxSum();
        });

        return Helpers::formatValue(floatval($sum), $formatted, $this->config);
    }    

    /**
     * get cart sub total with tax
     * @param bool $formatted
     * @return float
     */
    public function getSubTotalWithTax($formatted = true)
    {
        $cart = $this->getContent();
        $sum = $cart->sum(function ($item) {
            return $item->getOriginalPriceWithTaxSum(false);
        });


        return Helpers::formatValue(floatval($sum), $formatted, $this->config);
    }

    /**
     * get cart sub total with out tax
     * @param bool $formatted
     * @return float
     */
    public function getSubTotalWithoutTax($formatted = true)
    {
        $cart = $this->getContent();
        $sum = $cart->sum(function ($item) {
            return $item->getOriginalPriceWithoutTaxSum(false);
        });

        return Helpers::formatValue(floatval($sum), $formatted, $this->config);
    }

    /**
     * the new total with tax in which conditions are already applied
     *
     * @return float
     */
    public function getTotalWithTax($formatted = true)
    {

        $subTotal = $this->getSubTotalWithTax(false);
        $newTotal = 0.00;
        $process = 0;
        $conditions = $this
            ->getConditions()
            ->filter(function ($cond) {
                return $cond->getTarget() === 'subtotal';
            });
        // if no conditions were added, just return the sub total
        if (!$conditions->count()) {
            return Helpers::formatValue(floatval($subTotal), $formatted, $this->config);
        }
        $conditions
            ->each(function ($cond) use ($subTotal, &$newTotal, &$process) {
                $toBeCalculated = ($process > 0) ? $newTotal : $subTotal;
                $newTotal = $cond->applyCondition($toBeCalculated);
                $process++;
            });


        return Helpers::formatValue(floatval($newTotal), $formatted, $this->config);
    }


    /**
     * the new total without tax in which conditions are already applied
     *
     * @return float
     */
    public function getTotalWithoutTax($formatted = true)
    {
        $subTotal = $this->getSubTotalWithoutTax(false);
        $newTotal = 0.00;
        $process = 0;
        $conditions = $this
            ->getConditions()
            ->filter(function ($cond) {
                return $cond->getTarget() === 'subtotal';
            });
        // if no conditions were added, just return the sub total
        if (!$conditions->count()) {
            return $subTotal;
        }
        $conditions
            ->each(function ($cond) use ($subTotal, &$newTotal, &$process) {
                $toBeCalculated = ($process > 0) ? $newTotal : $subTotal;
                $newTotal = $cond->applyConditionWithoutTax($toBeCalculated);
                $process++;
            });

        return Helpers::formatValue(floatval($newTotal), $formatted, $this->config);
    }

    /**
     * removes an item on cart by item ID
     *
     * @param $id
     * @return bool
     */
    public function remove($id)
    {
        $cart = $this->getContent();
        if($this->fireEvent('removing', $id) === false) {
            return false;
        }
        $cart->forget($id);
        $this->save($cart);
        $this->fireEvent('removed', $id);
        return true;
    }

    /**
     * save the cart
     *
     * @param $cart CartCollection
     */
    protected function save($cart)
    {
        $this->session->put($this->sessionKeyCartItems, $cart);
    }

    /**
     * save the cart conditions
     *
     * @param $conditions
     */
    protected function saveConditions($conditions)
    {
        $this->session->put($this->sessionKeyCartConditions, $conditions);
    }

    /**
     * save the cart voucher
     *
     * @param $voucher
     */
    public function saveVoucher($voucher)
    {
        $this->session->put($this->sessionKeyCartVoucher, $voucher);
    }

    /**
     * get the cart voucher
     *

     */
    public function getVoucher()
    {
        $voucher = $this->session->get($this->sessionKeyCartVoucher);
        if($voucher){

        $totalWithTax = self::getTotalWithTax();
        $totalWithoutTax = self::getTotalWithoutTax();
        $voucher['used_value_with_tax']  = $voucher['value'];
        $voucher['used_value_without_tax']  = $voucher['value'];
        if($totalWithTax <= $voucher['value']) {
            $voucher['used_value_with_tax']  = $voucher['value'] - ($voucher['value'] - $totalWithTax);
        }

        if($totalWithTax <= $voucher['value']) {
            $voucher['used_value_without_tax']  = $voucher['value'] - ($voucher['value'] - $totalWithoutTax);
        }

        $this->session->put($this->sessionKeyCartVoucher, $voucher);

        }

        return $this->session->get($this->sessionKeyCartVoucher);
    }

    public function getToPayWithTax($formatted = true) 
    {
        $voucher = self::getVoucher();
        $toPay = self::getTotalWithTax(false) - $voucher['used_value_with_tax'];

        return Helpers::formatValue(floatval($toPay), $formatted, $this->config);      
    }

    public function getToPayWithoutTax($formatted = true) 
    {
        $voucher = self::getVoucher();
        $toPay = self::getTotalWithoutTax(false) - $voucher['used_value_without_tax'];

        return Helpers::formatValue(floatval($toPay), $formatted, $this->config); 
    }

    /**
     * clear the cart voucher
     *
     */
    public function clearVoucher()
    {
        $this->session->put($this->sessionKeyCartVoucher, array());
    }

    /**
     * clear cart
     * @return bool
     */
    public function clear()
    {
        if($this->fireEvent('clearing') === false) {
            return false;
        }
        $this->session->put(
            $this->sessionKeyCartItems,
            array()
        );
        $this->fireEvent('cleared');
        return true;
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    protected function fireEvent($name, $value = [])
    {
        return $this->events->fire($this->getInstanceName() . '.' . $name, array_values([$value, $this]));
    }  
}