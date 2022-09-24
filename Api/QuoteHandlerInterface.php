<?php
/*
 * *
 *  * Copyright (C) Shaymaa Saied, All Rights Reserved
 *  * Last Modified 23/09/2022, 23:26
 *
 */

namespace MageArab\OrderSplit\Api;


use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

interface QuoteHandlerInterface
{
    /**
     * Generate New Quote
     * @param Quote $currentQuote
     * @param Item[] $items
     * @param int $quotesCount
     * @retrun Quote
     */

    public function generateNewQuote(Quote $currentQuote, array $items, $quotesCount);
}
