<?php
class Hull_Connection_Model_Config
{
  const XML_PATH_ENABLED = 'hull_options/hull/enabled';
  const XML_PATH_APP_ID = 'hull_options/hull/app_id';
  const XML_PATH_ORG_URL = 'hull_options/hull/org_url';
  const XML_PATH_APP_SECRET = 'hull_options/hull/app_secret';

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

}

