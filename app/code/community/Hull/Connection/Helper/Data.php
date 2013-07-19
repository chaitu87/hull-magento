<?php
class Hull_Connection_Helper_Data extends Mage_Core_Helper_Abstract
{
  public function getCompletePostUrl()
  {
    return $this->_getUrl('hull_connection/customer_account/finish');
  }

  public function getClient()
  {
    return new Hull_Client(array('hull' => array(
      'host'      =>  Mage::getSingleton('hull_connection/config')->getOrgUrl(),
      'appId'     =>  Mage::getSingleton('hull_connection/config')->getAppId(),
      'appSecret' =>  Mage::getSingleton('hull_connection/config')->getAppSecret()
    )));
  }
}
