<?php
/*
 * *
 *  * Copyright (C) Shaymaa Saied, All Rights Reserved
 *  * Last Modified 23/09/2022, 23:37
 *
 */

namespace MageArab\OrderSplit\Api;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

interface OrderSplitHandlerInterface
{

    /** Add Sample Product to cart
     * @param Order $order
     * @return bool
     */
    public function splitOrder(Order $order);

    /** Add Sample Product to cart
     * @param Quote $quote
     * @return bool|array
     */
    public function generateSplitAttributeList(Quote $quote);

}
