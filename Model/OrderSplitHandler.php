<?php
/*
 * *
 *  * Copyright (C) Shaymaa Saied, All Rights Reserved
 *  * Last Modified 23/09/2022, 23:37
 *
 */

namespace MageArab\OrderSplit\Model;

use MageArab\OrderSplit\Helper\Data;
use MageArab\OrderSplit\Api\OrderSplitHandlerInterface;
use MageArab\OrderSplit\Api\QuoteHandlerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\Product;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\QuoteManagement;
use Magento\Catalog\Model\ProductFactory;

class OrderSplitHandler implements OrderSplitHandlerInterface
{
    /**
     * @var Data
     */
    private $_helperData;

    /**
     * @var CartRepositoryInterface
    */
    private $_cartRepository;

    /**
     * @var QuoteHandlerInterface
    */
    private $_quoteHandler;

    /**
     * @var QuoteManagement
    */
    private $_quoteManagement;

    /**
     * @var ProductFactory
     */
    private $_productFactory;

    public function __construct(
        Data                                $data,
        CartRepositoryInterface             $cartRepository,
        QuoteManagement                     $quoteManagement,
        QuoteHandlerInterface               $quoteHandler,
        ProductFactory                      $productFactory
    ){
        $this->_helperData          =   $data;
        $this->_cartRepository      =   $cartRepository;
        $this->_quoteHandler        =   $quoteHandler;
        $this->_quoteManagement     =   $quoteManagement;
        $this->_productFactory      =   $productFactory;
    }

    public function splitOrder(Order $order){
        // TODO: Implement splitOrder() method.
        $quoteId = $order->getQuoteId();
        /** @var Quote $currentQuote */
        $currentQuote = $this->_cartRepository->get($quoteId);
        if(count($currentQuote->getAllVisibleItems())<=1){
            return false;
        }
        $attributeItemsList = $this->generateSplitAttributeList($currentQuote);

        if(!$attributeItemsList || empty($attributeItemsList)){
            return false;
        }
        foreach ($attributeItemsList as $itemsList){
            /** @var Quote $newQuote*/
           $newQuote= $this->_quoteHandler->generateNewQuote($currentQuote,$itemsList,count($attributeItemsList));
           if($newOrder=$this->_quoteManagement->submit($newQuote)){
               $newOrder->setData('parent_id',$order->getId());
               $newOrder->setData('parent_increment',$order->getIncrementId());
               $newOrder->save();
               continue;
           }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function generateSplitAttributeList(Quote $quote)
    {
        $attributeList=[];
        // TODO: Implement generateAttributeGroups() method.
        if(!$this->_helperData->getSplitOrderAttribute()){
            return false;
        }

        $splitAttributeCode = $this->_helperData->getSplitOrderAttribute();

        /** @var Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var Product $product */
            $product = $this->_productFactory->create()->load($item->getProduct()->getId()) ;
            $attributeValue = $product->getAttributeText($splitAttributeCode);
            if($attributeValue){
                $attributeList[$attributeValue][] = $item;
            }
        }
        return $attributeList;
    }

}
