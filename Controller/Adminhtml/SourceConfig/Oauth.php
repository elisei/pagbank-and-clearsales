<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Controller\Adminhtml\SourceConfig;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use O2TI\PagBankSourceInventoryAuth\Model\Api\Credential;

/**
 * Class oAuth - Create Authorization.
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Oauth extends \Magento\Backend\App\Action
{
    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Credential
     */
    protected $credential;

    /**
     * @param Context               $context
     * @param TypeListInterface     $cacheTypeList
     * @param Pool                  $cacheFrontendPool
     * @param JsonFactory           $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param Json                  $json
     * @param Credential            $credential
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        Json $json,
        Credential $credential
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->credential = $credential;
        parent::__construct($context);
    }

    /**
     * ACL - Check is Allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('O2TI_PagBankSourceInventoryAuth::oauth');
    }

    /**
     * Excecute.
     *
     * @return json
     */
    public function execute()
    {
        $configDefault = false;

        $params = $this->getRequest()->getParams();
        $storeId = 0;

        if (!$storeId) {
            $configDefault = true;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $oAuth = null;

        if (isset($params['code'])) {
            $oAuthResponse = $this->credential->getAuthorize(
                $storeId,
                $params['code'],
                $params['code_verifier'],
                $params['source_code']
            );
            
            if ($oAuthResponse) {
                $oAuthResponse = $this->json->unserialize($oAuthResponse);
                if (isset($oAuthResponse['access_token'])) {
                    $oAuth = $oAuthResponse['access_token'];
                    $configs = [
                        'oauth'  => $oAuth,
                        'refresh_oauth' => $oAuthResponse['refresh_token'],
                    ];

                    $this->credential->setNewConfigs($configs, $params['source_code']);
                }
                if ($oAuth) {
                    $publicKey = $this->credential->getPublicKey($oAuth, $storeId);
                    $publicKey = $this->json->unserialize($publicKey);

                    $this->credential->setNewConfigs(
                        ['public_key' => $publicKey['public_key']],
                        $params['source_code']
                    );

                    $this->cacheTypeList->cleanType('config');
                    $this->messageManager->addSuccess(__('You are connected to PagBank. =)'));
                    $resultRedirect->setUrl($this->getUrlConfig());

                    return $resultRedirect;
                }
            }
        }

        $this->messageManager->addError(__('Unable to get the code, try again. =('));
        $resultRedirect->setUrl($this->getUrlConfig());

        return $resultRedirect;
    }

    /**
     * Get Url.
     *
     * @return string
     */
    private function getUrlConfig()
    {
        return $this->getUrl(
            'inventory/source/edit/',
            [
                'source_code' => $this->getRequest()->getParam('source_code'),
            ]
        );
    }
}
