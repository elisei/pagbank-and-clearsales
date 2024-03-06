<?php
/**
 * O2TI PagBank and ClearSales.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */
namespace O2TI\PagBankAndClearSales\Model\Config\Source\Order\Status;

/**
 * Order Statuses source model
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class PaymentReview extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
}
