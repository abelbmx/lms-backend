<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'/third_party/libwebpay/WebpayPlus.php';

class Transbank {

  const DEFAULT_API_KEY = '708db374474d6365e8b3250dae80cc20';

  const DEFAULT_COMMERCE_CODE = '597036480904';
  const DEFAULT_INTEGRATION_TYPE = "LIVE";
  const DEFAULT_INTEGRATION_TYPE_URL = "https://webpay3g.transbank.cl/";
  /*
  const DEFAULT_WEBPAY_PLUS_MALL_COMMERCE_CODE = '597055555535';
  const DEFAULT_WEBPAY_PLUS_MALL_CHILD_COMMERCE_CODES = array('597055555536','597055555537');
  const DEFAULT_DEFERRED_COMMERCE_CODE = '597055555540';

  const DEFAULT_ONECLICK_MALL_COMMERCE_CODE = '597055555541';
  const DEFAULT_ONECLICK_MALL_CHILD_COMMERCE_CODE_1 = '597055555542';
  const DEFAULT_ONECLICK_MALL_CHILD_COMMERCE_CODE_2 = '597055555543';

  const DEFAULT_PATPASS_BY_WEBPAY_COMMERCE_CODE = '597055555550';
*/
  /**
   * @var string $apiKey Your api key, given by Transbank.Sent as a header when
   * making requests to Transbank on a field called "Tbk-Api-Key-Secret"
   */
  public $apiKey = null;
  /**
   * @var string $commerceCode Your commerce code, given by Transbank. Sent as
   * a header when making requests to Transbank on a field called "Tbk-Api-Key-Id"
   */
  public $commerceCode = null;
  /**
   * @var string $integrationType Sets the environment that the SDK is going
   * to point to (eg. TEST, LIVE, etc).
   */
  public $integrationType = 'TEST';

  public function __construct()
  {

      $apiKey = "708db374474d6365e8b3250dae80cc20";
      $commerceCode = "597036480904";
      $integrationType = 'LIVE';
      $this->setApiKey($apiKey);
      $this->setCommerceCode($commerceCode);
      $this->setIntegrationType($integrationType);
  }

  /**
   * @return Options Return an instance of Options with default values
   * configured
   */
  public static function defaultConfig()
  {
      return new Options(self::DEFAULT_API_KEY,
          self::DEFAULT_COMMERCE_CODE);
  }

  /**
   * @return string
   */
  public function getIntegrationType()
  {
      return $this->integrationType;
  }

  /**
   * @param string $integrationType
   *
   * @return Options
   */
  public function setIntegrationType($integrationType)
  {
      $this->integrationType = $integrationType;
      return $this;
  }

  /**
   * @return mixed
   */
  public function getApiKey()
  {
      return $this->apiKey;
  }

  /**
   * @param string $apiKey
   *
   * @return Options
   */
  public function setApiKey($apiKey)
  {
      $this->apiKey = $apiKey;
      return $this;
  }

  /**
   * @return mixed
   */
  public function getCommerceCode()
  {
      return $this->commerceCode;
  }

  /**
   * @param mixed $commerceCode
   *
   * @return Options
   */
  public function setCommerceCode($commerceCode)
  {
      $this->commerceCode = $commerceCode;
      return $this;
  }

  /**
   * @return string Returns the base URL used for making requests, depending on which
   * integration types
   */
  public function integrationTypeUrl()
  {
      return WebpayPlus::$INTEGRATION_TYPES[$this->integrationType];
  }
}
