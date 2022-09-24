<?php
/*
 * *
 *  * Copyright (C) Shaymaa Saied, All Rights Reserved
 *  * Last Modified 23/09/2022, 23:58
 *
 */

namespace MageArab\OrderSplit\Model;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Api\Data\AddressInterface;
use MageArab\OrderSplit\Api\QuoteHandlerInterface;

class QuoteHandler implements QuoteHandlerInterface
{
    /**
     * @var GuestCartManagementInterface
     */
    private $_guestCart;

    /**
     * @var CartManagementInterface
     */
    private $_cartManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $_cartRepository;

    /**
     * @var CartInterface
     */
    protected $_quote;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var Configurable
     */
    protected $_configurableType;

    public function __construct(
        GuestCartManagementInterface            $guestCart,
        CartManagementInterface                 $cartManagement,
        CartRepositoryInterface                 $cartRepository,
        ProductRepositoryInterface              $productRepository,
        Configurable                            $configurable

    ) {
        $this->_guestCart               =       $guestCart;
        $this->_cartManagement          =       $cartManagement;
        $this->_cartRepository          =       $cartRepository;
        $this->_productRepository       =       $productRepository;
        $this->_configurableType        =       $configurable;
    }


    /**
     * @inheritDoc
    */
    public function generateNewQuote(Quote $currentQuote, array $items, $quotesCount){
        // TODO: Implement generateNewQuote() method.
        $isCustomerQuote = true;
        /** @var CustomerInterface $customer */
        $customer = $currentQuote->getCustomer();
        if (!$customer->getId() && $currentQuote->getCustomerIsGuest()){
            $isCustomerQuote = false;
        }
        if($isCustomerQuote){
            $this->createCustomerCart($customer->getId());
            $this->_quote->setStoreId($currentQuote->getStoreId())
                ->setCustomer($currentQuote->getCustomer())
                ->setCustomerIsGuest($currentQuote->getCustomerIsGuest());
        }else{
            $this->createEmptyGuestCart();
            $this->_quote->setCustomerId(null)
                ->setCustomerEmail($currentQuote->getBillingAddress()->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
        }
        $this->saveQuote();
        /** @var Item $item */
        foreach ($items as $item){
            $item->setItemId(null)
                ->setQuoteId(null);
            $this->_quote->addItem($item);
        }

        $this->_quote->getBillingAddress()->setData($this->generateAddress($currentQuote->getBillingAddress()->getData()));
        $this->_quote->getShippingAddress()->setData($this->generateAddress($currentQuote->getShippingAddress->getData()));
        $this->collectTotals($quotesCount, $currentQuote,$items);
        $this->setPaymentMethod($currentQuote);
        $this->saveQuote();
        return $this->_quote;
    }

    /** Create Guest Cart
     *
    */
    private function createEmptyGuestCart() {
        $this->_quote =   $this->_guestCart->createEmptyCart();
        return $this->_quote;
    }

    /** Create Customer Cart
     * @param int $customerId
    */
    private function createCustomerCart($customerId){
        $this->_quote = $this->_cartManagement->createEmptyCartForCustomer($customerId);
        return $this->_quote;
    }

    /** Add Product to cart
     * @param Product $product
     */
    private function addProductToCart($product,$qty){
        switch ($product->getTypeId()){
            case 'configurable':
                $this->addConfigurableProductToCart($product);
                break;
            case 'bundle':
                break;
            case 'grouped':
                break;
            case 'downloadable ':
                break;
            case 'simple':
            default:
                $this->addSampleProductToCart($product,$qty);
                break;
        }
    }

    /** Add Sample Product to cart
     * @param Product $product
     * @param int $qty
     */
    private function addSampleProductToCart($product,$qty){
        if($this->_quote->addProduct($product, $qty))
        $this->_quote->addProduct($product, $qty);
    }

    /** Add Configurable Product to cart
     * @param Product $product
     */
    private function addConfigurableProductToCart($product){
        $childId = $product->getId();
        $childProduct = $this->_productRepository->getById($childId);

        /** @var Configurable configurableType */
        $parentId = $this->_configurableType->getParentIdsByChild($childId);
        if (($parentId = reset($parentId)) !== false) {
            $parentProduct = $this->_productRepository->getById($parentId);
            $productAttributeOptions = $this->_configurableType->getConfigurableAttributesAsArray($parentProduct);

            $options = [];
            foreach ($productAttributeOptions as $option) {
                $options[$option['attribute_id']] =  $childProduct->getData($option['attribute_code']);
            }
            $buyRequest = new \Magento\Framework\DataObject(['super_attribute' => $options]);

            if($this->_quote->addProduct($parentProduct, $buyRequest)){
                return true;
            }

        }
        return false;
    }


    /**
     * Save quote
     *
     * @return $this
     */
    private function saveQuote()
    {
        $this->_cartRepository->save($this->_quote);

        return $this;
    }

    /**
    * Generate address
     * @param AddressInterface $address
     * @return AddressInterface
     */
    private function generateAddress($address)
    {
        unset($address['id']);
        unset($address['quote_id']);
        return $address;
    }

    /**
     * Collect Totals
     * @param int $quotesCount
     * @param Quote $currentQuote
     * @param Item[] $quoteItems
     * @return $this
    */
    private function collectTotals($quotesCount, $currentQuote, array $quoteItems){

        $quoteTaxes=0.0;
        $quoteDiscount=0.0;
        $quoteBaseDiscount=0.0;
        $quoteBaseTaxes=0.0;
        $quoteSubtotal=0.0;
        $quoteBaseSubtotal=0.0;
        $quoteGrandTotal=0.0;
        $quoteBaseGrandTotal=0.0;
        /** @var Item $item */
        foreach ($quoteItems as $item){
            $quoteDiscount+=$item->getDiscountAmount();
            $quoteBaseDiscount+=$item->getBaseDiscountAmount();
            $quoteTaxes+=$item->getTaxAmount();
            $quoteBaseTaxes+=$item->getBaseTaxAmount();
            $quoteSubtotal+=$item->getRowTotal();
            $quoteBaseSubtotal+=$item->getBaseRowTotal();
        }

        $quoteShipping= $this->calculateShipping($quotesCount);
        $quoteGrandTotal=   ($quoteTaxes+$quoteShipping+$quoteSubtotal)-$quoteDiscount;
        $quoteBaseGrandTotal= ($quoteBaseTaxes+$quoteBaseSubtotal+$quoteShipping)-$quoteBaseDiscount;

        /** @var \Magento\Quote\Model\Quote\Address $address $*/
        foreach ($this->_quote->getAllAddresses() as $address){
            $address->setTaxAmount($quoteTaxes)
                    ->setBaseTaxAmount($quoteBaseTaxes)
                    ->setShippingAmount($quoteShipping)
                    ->setShippingDescription($currentQuote->getShippingAddress()->getShippingDescription())
                    ->setShippingMethod($currentQuote->getShippingAddress()->getShippingMethod())
                    ->setDiscountAmount($quoteDiscount)
                    ->setBaseDiscountAmount($quoteBaseDiscount)
                    ->setDiscountDescription($currentQuote->getShippingAddress()->getDiscountDescription())
                    ->setSubtotal($quoteSubtotal)
                    ->setBaseSubtotal($quoteBaseSubtotal)
                    ->setGrandTotal($quoteGrandTotal)
                    ->setBaseGrandTotal($quoteBaseGrandTotal);

        }
        return $this;
    }

    /**
     * Calculate Shipping
     * @param int $quotesCount
     * @return float
    */
    private function calculateShipping($quotesCount){
        $shipping =0.0;
        if ($this->_quote->hasVirtualItems() === true) {
            return $shipping;
        }

        $shippingTotals = $this->_quote->getShippingAddress()->getShippingAmount();
        if ($shippingTotals > 0) {
            // Divide shipping to each order.
            $shipping = (float) ($shippingTotals / $quotesCount);
            $this->_quote->getShippingAddress()->setShippingAmount($shipping);
        }
        return $shipping;
    }

    /**
     * Set Payment Method
     * @param Quote $currentQuote
     */
    private function setPaymentMethod(Quote $currentQuote){
       $paymentMethod= $currentQuote->getPayment()->getMethod();

       $this->_quote->getPayment()->setMethod($paymentMethod);
       $this->_quote->getPayment ()->importData (array ('method' => $paymentMethod));
    }
}
