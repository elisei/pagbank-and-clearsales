<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Plugin\InventorySource;

use Magento\InventoryAdminUi\Ui\DataProvider\SourceDataProvider;
use O2TI\PagBankSourceInventoryAuth\Helper\Data;

class PagBankAttribute
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Construct.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    public function afterGetData(
        SourceDataProvider $subject,
        array              $result
    ): array
    {
        foreach ($result as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            if (isset($result[$key]['general']['extension_attributes']['oauth'])
                && $oAuth = $result[$key]['general']['extension_attributes']['oauth']) {
                $result[$key]['extension_attributes']['oauth'] = $oAuth;
            }

            if (isset($result[$key]['general']['extension_attributes']['refresh_oauth'])
            && $refreshOauth = $result[$key]['general']['extension_attributes']['refresh_oauth']) {
                $result[$key]['extension_attributes']['refresh_oauth'] = $refreshOauth;
            }

            if (isset($result[$key]['general']['extension_attributes']['public_key'])
            && $publicKey = $result[$key]['general']['extension_attributes']['public_key']) {
                $result[$key]['extension_attributes']['public_key'] = $publicKey;
            }

            if (isset($result[$key]['general']['extension_attributes'])) {
                $result[$key]['extension_attributes']['url_auth'] = $this->helperData->getUrlToConnect();
            }

            if (isset($result[$key]['general']['extension_attributes'])) {
                $result[$key]['extension_attributes']['url_deauthorized'] = $this->helperData->getUrlToConnect();
            }

        }
        return $result;
    }

}