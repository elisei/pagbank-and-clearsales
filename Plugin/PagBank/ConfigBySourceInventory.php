<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Plugin\PagBank;

use PagBank\PaymentMagento\Gateway\Config\Config;

class ConfigBySourceInventory
{
    /**
     * Change value.
     *
     * @param Config $subject
     * @param \Closure $proceed
     */
    public function aroundGetMerchantGatewayOauth(
        Config $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        return $result;
    }

    /**
     * Change value.
     *
     * @param Config $subject
     * @param \Closure $proceed
     */
    public function aroundGetMerchantGatewayRefreshOauth(
        Config $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        return $result;
    }

    /**
     * Change value.
     *
     * @param Config $subject
     * @param \Closure $proceed
     */
    public function aroundGetMerchantGatewayPublicKey(
        Config $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        return $result;
    }
}
