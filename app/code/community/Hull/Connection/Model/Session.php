<?php
class Hull_Connection_Model_Session extends Varien_Object
{
  private $_userId;
  private $_signature;
  private $_payload;

  public function __construct()
  {
    if($this->getCookie()) {
      $data = json_decode(base64_decode($this->getCookie()));

      $payload = $data->{'Hull-User-Sig'};
      $this->_userId = $data->{'Hull-User-Id'};
      list($time, $signature) = explode(".", $payload);
      $this->setData($time . '-' . $this->_userId);
      $this->_signature = $signature;
      $this->_payload = $time . '-' . $this->_userId;
    }
  }

  public function isConnected()
  {
    return $this->validate();
  }

  public function validate()
  {
    if(!$this->hasData()) {
      return false;
    }

    $expectedSignature = hash_hmac('sha1', $this->_payload, Mage::getSingleton('hull_connection/config')->getAppSecret(), false);
    return ($expectedSignature==$this->_signature);
  }

  public function getCookie()
  {
    return Mage::app()->getRequest()->getCookie('hull_'.Mage::getSingleton('hull_connection/config')->getAppId(), false);
  }

  public function getUserId()
  {
    return $this->_userId;
  }

  public function getClient()
  {
    return Mage::helper('hull_connection')->getClient();
  }
}
