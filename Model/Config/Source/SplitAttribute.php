<?php
/*
 * *
 *  * Copyright (C) Shaymaa Saied, All Rights Reserved
 *  * Last Modified 22/09/2022, 23:17
 *
 */

namespace MageArab\OrderSplit\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Class SplitAttribute
 */

class SplitAttribute implements \Magento\Framework\Option\ArrayInterface
{
    /** @var  CollectionFactory */
    protected $_collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ){
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
        $options = [];
        foreach ($this->_getOptions() as $optionValue => $optionLabel) {
            $options[] = ['value' => $optionValue, 'label' => $optionLabel];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(){
        return $this->_getOptions();
    }


    /**
     * Get options
     *
     * @return array
     */
    protected function _getOptions(){
        $collection = $this->_collectionFactory->create();
        $collection->addIsFilterableFilter();
        $collection->addOrder('attribute_code', 'asc');

        $options = ['' => __('-- Select Attribute --')];
        foreach ($collection->getItems() as $attribute) {
            /** @var Attribute $attribute */
            $options[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        return $options;
    }
}
