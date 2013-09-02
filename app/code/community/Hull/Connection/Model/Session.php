<?php

class Hull_Connection_Model_Session extends Varien_Object
{
  private $_userId;
  private $_signature;
  private $_payload;
  private $_currentUser;

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
    return !!$this->getCurrentUserId();
  }

  public function getCurrentUser()
  {
    if (!is_null($this->currentUser)) {
      return $this->currentUser;
    }
    $_currentUserId = $this->getCurrentUserId();
    if ($_currentUserId) {
      $this->currentUser = Mage::getModel('hull_connection/user')->find($_currentUserId);
    } else {
      $this->currentUser = false;
    }
    return $this->currentUser;
  }

  public function getCurrentUserId()
  {
    if(!$this->hasData()) { return false; }

    $expectedSignature = hash_hmac('sha1', $this->_payload, Mage::getSingleton('hull_connection/config')->getAppSecret(), false);
    if ($expectedSignature == $this->_signature) {
      return $this->_userId;
    }
  }

  public function isLoggingOut() {
    if ($this->getCookie() == 'logout') {
      return true;
    } else {
      return false;
    }
  }

  public function getCookie()
  {
    return Mage::app()->getRequest()->getCookie('hull_'.Mage::getSingleton('hull_connection/config')->getAppId());
  }


  public function getClient()
  {
    return Mage::helper('hull_connection')->getClient();
  }
}
