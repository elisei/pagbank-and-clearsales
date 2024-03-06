<?php
/**
 * O2TI PagBank and ClearSales.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankAndClearSales\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use O2TI\PagBankAndClearSales\Logger\Logger;

class FindOrders
{
    /**
     * Payment Method Credit Card.
     */
    public const PAYMENT_METHOD_CC = 'pagbank_paymentmagento_cc';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ScopeConfigInterface  $scopeConfig
     * @param CollectionFactory     $collectionFactory
     * @param Logger                $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory,
        Logger $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * Find For Accept.
     */
    public function findForAccept()
    {
        $enabled = $this->scopeConfig->getValue('pagbank_and_clearsales/general/enabled');
        $status = $this->scopeConfig->getValue('pagbank_and_clearsales/general/accepted_order_status');

        if ($enabled) {
            $orders = $this->getFilterdOrders($status);

            foreach ($orders as $order) {
                $this->logDetails('Accept', $order);
                $payment = $order->getPayment();
                try {
                    $payment->accept(true);
                    $order->save();
                } catch (\Throwable $th) {
                    continue;
                }
            }
        }
    }

    /**
     * Find For Accept.
     */
    public function findForDeny()
    {
        $enabled = $this->scopeConfig->getValue('pagbank_and_clearsales/general/enabled');
        $status = $this->scopeConfig->getValue('pagbank_and_clearsales/general/denied_order_status');

        if ($enabled) {
            $orders = $this->getFilterdOrders($status);

            foreach ($orders as $order) {
                $this->logDetails('Deny', $order);
                $payment = $order->getPayment();
                try {
                    $payment->deny(true);
                    $order->save();
                } catch (\Throwable $th) {
                    continue;
                }
            }
        }
    }

    /**
     * Get Filtered Orders.
     *
     * @param string $status
     *
     * @return CollectionFactory|null
     */
    public function getFilterdOrders($status)
    {
        $orders = $this->collectionFactory->create()
                    ->addFieldToFilter('state', [
                        'eq' => [
                            Order::STATE_PAYMENT_REVIEW,
                        ],
                    ])
                    ->addFieldToFilter('status', [
                        'eq' => [
                            $status,
                        ],
                    ]);

        $orders->getSelect()
            ->join(
                ['sop' => 'sales_order_payment'],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )
            ->where('sop.method = ?', self::PAYMENT_METHOD_CC);

        return $orders;
    }

    /**
     * Log Details
     *
     * @param string $type
     * @param Order $order
     */
    public function logDetails($type, $order)
    {

        $orderId = $order->getIncrementId();
        $orderState = $order->getState();
        $orderStatus = $order->getStatus();
        $this->logger->info(
            __(
                'Increment Id: %1 for %2 Order - Current State: %3 - Current Status: %4',
                $orderId,
                $type,
                $orderState,
                $orderStatus
            )
        );
    }
}
