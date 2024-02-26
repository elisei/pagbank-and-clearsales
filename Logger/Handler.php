<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * Logging level for custom logger.
     *
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * Custom File name.
     *
     * @var string
     */
    protected $fileName = '/var/log/o2ti_pags_source_inventory_auth.log';
}
