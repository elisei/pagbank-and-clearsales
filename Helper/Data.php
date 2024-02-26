<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Math\Random;
use PagBank\PaymentMagento\Gateway\Config\Config;
use O2TI\PagBankSourceInventoryAuth\Logger\Logger;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @var string
     */
    protected $codeVerifier;

    /**
     * @param ScopeConfigInterface  $scopeConfig
     * @param Logger                $logger
     * @param Http                  $request
     * @param UrlInterface          $backendUrl
     * @param Config                $config
     * @param Random                $mathRandom
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Http $request,
        UrlInterface $backendUrl,
        Config $config,
        Random $mathRandom
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->request = $request;
        $this->backendUrl = $backendUrl;
        $this->config = $config;
        $this->mathRandom = $mathRandom;
    }

    /**
     * Get Config Data.
     *
     * @param string $field
     * @return string|null
     */
    public function getConfigData($field)
    {
        $pathPattern = 'o2ti_pagbank_source_inventory_auth/general/%s';

        return $this->scopeConfig->getValue(
            sprintf($pathPattern, $field)
        );
    }

    /**
     * Get Api Configs.
     *
     * @return array
     */
    public function getApiConfigs()
    {
        return [
            'maxredirects'  => 0,
            'timeout'       => 45000,
        ];
    }

    /**
     * Get Pub Header.
     *
     * @param int|null $storeId
     *
     * @return array
     */
    public function getPubHeader($storeId = null)
    {
        $environment = $this->config->getAddtionalValue('environment', $storeId);
        $pub = $this->getConfigData('cipher_text');

        if ($environment === 'sandbox') {
            $pub = $this->getConfigData('cipher_text_sandbox');
        }

        return [
            'Content-Type'      => 'application/json',
            'Authorization'     => 'Pub '.$pub,
            'x-api-version'     => '4.0',
        ];
    }

    /**
     * Get Url For Auth
     */
    public function getUrlForAuth()
    {
        $key = $this->request->getParam('key');
        $sourceCode = $this->request->getParam('source_code');
        return $sourceCode . ' key ' . $key;
    }

    /**
     * Url Authorize.
     *
     * @return string
     */
    public function getUrlAuthorize()
    {
        $storeUri = $this->backendUrl->getUrl(
            'o2ti_pagbank/sourceconfig/oauth',
            [
                'source_code'   => $this->request->getParam('source_code'),
                'code_verifier' => $this->codeVerifier,
            ]
        );

        return $storeUri;
    }

    /**
     * Url to connect.
     *
     * @return string
     */
    public function getUrlToConnect()
    {
        $urlConnect = Config::ENDPOINT_CONNECT_PRODUCTION;
        $appId = $this->getConfigData('app_id');
        $scope = $this->getConfigData('app_scope');
        $state = Config::OAUTH_STATE;
        $responseType = Config::OAUTH_CODE;

        $codeChallenge = $this->getCodeChallenge();
        $redirectUri = $this->getUrlAuthorize();
        $codeChallengeMethod = Config::OAUTH_CODE_CHALLENGER_METHOD;

        if ($this->config->getEnvironmentMode() === Config::ENVIRONMENT_SANDBOX) {
            $appId = $this->getConfigData('app_id_sandbox');
            $scope = $this->getConfigData('app_scope_sandbox');
            $urlConnect = Config::ENDPOINT_CONNECT_SANDBOX;
        }

        $params = [
            'response_type'         => $responseType,
            'client_id'             => $appId,
            'scope'                 => $scope,
            'state'                 => $state,
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => $codeChallengeMethod,
            'redirect_uri'          => $redirectUri,
        ];

        $link = $urlConnect.'?'.http_build_query($params, '&');

        return urldecode($link);
    }

    /**
     * Get Code Challenger.
     *
     * @return string
     */
    public function getCodeChallenge()
    {
        $params = $this->request->getParams();

        $this->codeVerifier = sha1($this->mathRandom->getRandomString(100));

        if (isset($params['key'])) {
            $this->codeVerifier = $params['key'];
        }

        $codeChallenge = $this->getBase64UrlEncode(
            pack('H*', hash('sha256', $this->codeVerifier))
        );

        return $codeChallenge;
    }

    /**
     * Get Base64 Url Encode.
     *
     * @param string $code
     *
     * @return string
     */
    public function getBase64UrlEncode($code)
    {
        return rtrim(strtr(base64_encode($code), '+/', '-_'), '=');
    }
}
