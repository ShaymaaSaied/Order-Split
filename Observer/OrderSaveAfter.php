<?php
/*
 * *
 *  * Copyright (C) Shaymaa Saied, All Rights Reserved
 *  * Last Modified 24/09/2022, 00:09
 *
 */

namespace MageArab\OrderSplit\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use MageArab\OrderSplit\Helper\Data;
use MageArab\OrderSplit\Api\OrderSplitHandlerInterface;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var $data
     */
    private $_helperDate;

    /**
     * @var OrderSplitHandlerInterface
    */
    private $_orderSplitHandlerInterface;

    public function __construct(
        OrderSplitHandlerInterface  $orderSplitHandler,
        Data                        $helper

    ){
        $this->_orderSplitHandlerInterface      =   $orderSplitHandler;
        $this->_helperDate                      =   $helper;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if($this->_helperDate->isEnabled()){
            $this->_orderSplitHandlerInterface->splitOrder($order);
        }
    }
}
