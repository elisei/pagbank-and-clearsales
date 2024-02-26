<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Model\Api;

use Laminas\Http\ClientFactory;
use Laminas\Http\Request;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PagBank\PaymentMagento\Gateway\Config\Config as ConfigBase;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use O2TI\PagBankSourceInventoryAuth\Helper\Data;

/**
 * Class Credential - Get access credential on PagBank.
 */
class Credential
{
    /**
     * @var Config
     */
    protected $resourceConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ConfigBase
     */
    protected $configBase;

    /**
     * @var ClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Constructor.
     *
     * @param Config                    $resourceConfig
     * @param EncryptorInterface        $encryptor
     * @param StoreManagerInterface     $storeManager
     * @param ConfigBase                $configBase
     * @param ClientFactory             $httpClientFactory
     * @param Json                      $json
     * @param SourceRepositoryInterface $sourceRepository
     * @param Data                      $helperData
     */
    public function __construct(
        Config $resourceConfig,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager,
        ConfigBase $configBase,
        ClientFactory $httpClientFactory,
        Json $json,
        SourceRepositoryInterface $sourceRepository,
        Data $helperData
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
        $this->configBase = $configBase;
        $this->httpClientFactory = $httpClientFactory;
        $this->json = $json;
        $this->sourceRepository = $sourceRepository;
        $this->helperData = $helperData;
    }

    /**
     * Set New Configs.
     *
     * @param array $configs
     * @param bool  $sourceCode
     *
     * @return void
     */
    public function setNewConfigs(
        $configs,
        string $sourceCode
    ) {
        $inventorySource = $this->sourceRepository->get($sourceCode);
        foreach ($configs as $config => $value) {
            if ($config === 'oauth') {
                $inventorySource->setOauth($value);
            }

            if ($config === 'refresh_oauth') {
                $inventorySource->setRefreshOauth($value);
            }

            if ($config === 'public_key') {
                $inventorySource->setPublicKey($value);
            }
        }
        $inventorySource->save();

    }

    /**
     * Get Authorize.
     *
     * @param int    $storeId
     * @param string $code
     * @param string $codeVerifier
     * @param string $sourceCode
     *
     * @return json
     */
    public function getAuthorize($storeId, $code, $codeVerifier, $sourceCode)
    {
        $url = $this->configBase->getApiUrl($storeId);
        $headers = $this->helperData->getPubHeader($storeId);
        $apiConfigs = $this->configBase->getApiConfigs();
        $uri = $url.'oauth2/token';

        $store = $this->storeManager->getStore('admin');
        $storeCode = '/'.$store->getCode().'/';
        $redirectUrl = (string) $store->getUrl('o2ti_pagbank/sourceconfig/oauth', [
            'source_code'    => $sourceCode,
            'code_verifier' => $codeVerifier,
        ]);

        $search = '/'.preg_quote($storeCode, '/').'/';
        $redirectUrl = preg_replace($search, '/', $redirectUrl, 0);

        $data = [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUrl,
            'code_verifier' => $codeVerifier,
        ];

        /** @var LaminasClient $client */
        $client = $this->httpClientFactory->create();
        $client->setUri($uri);
        $client->setHeaders($headers);
        $client->setMethod(Request::METHOD_POST);
        $client->setOptions($apiConfigs);
        $client->setRawBody($this->json->serialize($data));

        $send = $client->send();

        return $send->getBody();
    }

    /**
     * Get Public Key.
     *
     * @param string $oAuth
     * @param int    $storeId
     *
     * @return string
     */
    public function getPublicKey($oAuth, $storeId)
    {
        $url = $this->configBase->getApiUrl($storeId);
        $uri = $url.'public-keys/';
        $apiConfigs = $this->configBase->getApiConfigs();

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer '.$oAuth,
        ];

        $data = ['type' => 'card'];

        /** @var LaminasClient $client */
        $client = $this->httpClientFactory->create();
        $client->setUri($uri);
        $client->setHeaders($headers);
        $client->setMethod(Request::METHOD_POST);
        $client->setOptions($apiConfigs);
        $client->setRawBody($this->json->serialize($data));

        $send = $client->send();

        return $send->getBody();
    }
}
