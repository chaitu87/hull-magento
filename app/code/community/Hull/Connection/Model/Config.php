<?php
class Hull_Connection_Model_Config
{
  const XML_PATH_ENABLED = 'hull_options/hull/enabled';
  const XML_PATH_APP_ID = 'hull_options/hull/app_id';
  const XML_PATH_ORG_URL = 'hull_options/hull/org_url';
  const XML_PATH_APP_SECRET = 'hull_options/hull/app_secret';
  const XML_PATH_HULL_VERSION = 'hull_options/hull/hull_version';

  const DEFAULT_HULL_VERSION = '0.6.9';

  public function isEnabled($storeId=null)
  {
    if( Mage::getStoreConfigFlag(self::XML_PATH_ENABLED, $storeId) &&
      $this->getAppId($storeId) &&
      $this->getOrgUrl($storeId))
    {
      return true;
    }
    return false;
  }

  public function getAppSecret($storeId=null)
  {
    return trim(Mage::getStoreConfig(self::XML_PATH_APP_SECRET, $storeId));
  }

  public function getAppId($storeId=null)
  {
    return trim(Mage::getStoreConfig(self::XML_PATH_APP_ID, $storeId));
  }

  public function getOrgUrl($storeId=null)
  {
    return trim(Mage::getStoreConfig(self::XML_PATH_ORG_URL, $storeId));
  }

  public function getHullVersion($storeId=null)
  {
    $version = trim(Mage::getStoreConfig(self::XML_PATH_HULL_VERSION, $storeId));
    if (!strlen($version)) {
      $version = self::DEFAULT_HULL_VERSION;
    }
    return $version;
  }
}

