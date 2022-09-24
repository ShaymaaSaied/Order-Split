<?php
/*
 * *
 *  * Copyright (C) Shaymaa Saied, All Rights Reserved
 *  * Last Modified 22/09/2022, 23:17
 *
 */

namespace MageArab\OrderSplit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
/**
 * Class Data
 * Helper Class
 */
class Data extends AbstractHelper
{
    const SECTION   ='order_split_general';
    const GROUP     ='general';

    /** @var  StoreManagerInterface */
    private $_storeManager;

    /** @var  ScopeConfigInterface */
    private $_scopeConfig;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context  $context
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context                           $context,
        ScopeConfigInterface                                            $scopeConfig,
        StoreManagerInterface                                           $storeManager

    ) {
        $this->_storeManager        =   $storeManager;
        $this->_scopeConfig         =   $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Check if module enabled
     *
     * @return boolean
     */
    public function isEnabled(){
        return $this->_scopeConfig->getValue(
            self::SECTION.'/'.self::GROUP.'/'.'enabled',
            ScopeInterface::SCOPE_WEBSITE
        );

    }

    /**
     * Get split order attribute code
     *
     * @return string
     */
    public function getSplitOrderAttribute(){
        return $this->_scopeConfig->getValue(
            self::SECTION.'/'.self::GROUP.'/'.'split_attribute_code',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
