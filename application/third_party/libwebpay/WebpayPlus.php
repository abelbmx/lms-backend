<?php

defined('BASEPATH') OR exit('No direct script access allowed');
//namespace Transbank\Webpay;


//use Transbank\Utils\HttpClient;


include( APPPATH.'/third_party/Utils/HttpClient.php');

require_once( APPPATH.'/third_party/Exceptions/WebpayException.php');


include( APPPATH.'/third_party/libwebpay/Transaction.php');
require_once( APPPATH.'/third_party/libwebpay/TransactionCommitResponse.php');
require_once( APPPATH.'/third_party/libwebpay/TransactionCreateResponse.php');
require_once( APPPATH.'/third_party/libwebpay/TransactionStatusResponse.php');
require_once( APPPATH.'/third_party/libwebpay/TransactionRefundResponse.php');


require_once( APPPATH.'/third_party/libwebpay/Exceptions/TransactionCaptureException.php');
require_once( APPPATH.'/third_party/libwebpay/Exceptions/TransactionCommitException.php');
require_once( APPPATH.'/third_party/libwebpay/Exceptions/TransactionCreateException.php');
require_once( APPPATH.'/third_party/libwebpay/Exceptions/TransactionRefundException.php');
require_once( APPPATH.'/third_party/libwebpay/Exceptions/TransactionStatusException.php');

require_once( APPPATH.'/third_party/Options.php');
require_once( APPPATH.'/third_party/libwebpay/WebpayPlus.php');


/**
 * Class WebpayPlus
 *
 * @package Transbank\Webpay
 *
 */
class WebpayPlus
{

    /**
     * @var array $INTEGRATION_TYPES contains key-value pairs of
     * integration_type => url_of_that_integration
     */
    public static $INTEGRATION_TYPES = [
        "LIVE" => "https://webpay3g.transbank.cl/",
        "TEST" => "https://webpay3gint.transbank.cl/",
        "MOCK" => ""
    ];
    /**
     * @var $httpClient HttpClient|null
     */
    public static $httpClient = null;
    private static $apiKey = Options::DEFAULT_API_KEY;
    private static $commerceCode = Options::DEFAULT_COMMERCE_CODE;
    private static $integrationType = Options::DEFAULT_INTEGRATION_TYPE;

    /**
     * @return string
     */
    public static function getApiKey()
    {
        return self::$apiKey;
    }

    /**
     * @param string $apiKey
     */
    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public static function getCommerceCode()
    {
        return self::$commerceCode;
    }

    /**
     * @param string $commerceCode
     */
    public static function setCommerceCode($commerceCode)
    {
        self::$commerceCode = $commerceCode;
    }

    /**
     * @return string
     */
    public static function getIntegrationType()
    {
        return self::$integrationType;
    }

    /**
     * @param string $integrationType
     */
    public static function setIntegrationType($integrationType)
    {
        self::$integrationType = $integrationType;
    }

    /**
     * @return HttpClient
     */
    public static function getHttpClient()
    {
        if (!isset(self::$httpClient) || self::$httpClient == null) {
            self::$httpClient = new HttpClient();
        }
        return self::$httpClient;
    }

    public static function getIntegrationTypeUrl($integrationType = null)
    {
        if ($integrationType == null) {
            return self::$INTEGRATION_TYPES[self::$integrationType];
        }

        return self::$INTEGRATION_TYPES[$integrationType];
    }

    public static function configureForTesting()
    {
        self::setApiKey(Options::DEFAULT_API_KEY);
        self::setCommerceCode(Options::DEFAULT_COMMERCE_CODE);
        self::setIntegrationType(self::$INTEGRATION_TYPES["TEST"]);
    }

    public static function configureMallForTesting()
    {
        self::setApiKey(Options::DEFAULT_API_KEY);
        self::setCommerceCode(Options::DEFAULT_MALL_COMMERCE_CODE);
        self::setIntegrationType(self::$INTEGRATION_TYPES["TEST"]);
    }

    public static function configureDeferredForTesting()
    {
        self::setApiKey(Options::DEFAULT_API_KEY);
        self::setCommerceCode(Options::DEFAULT_DEFERRED_COMMERCE_CODE);
        self::setIntegrationType(self::$INTEGRATION_TYPES["TEST"]);
    }

}
