<?php
class Hull_Connection_Block_Template extends Mage_Core_Block_Template
{

  public function getConnectUrl()
  {
    return $this->getUrl('hull_connection/customer_account/connect', array('_secure'=>true));
  }

  public function getLogoutUrl()
  {
    return $this->helper('customer')->getLogoutUrl();
  }

  public function getAppId()
  {
    return Mage::getSingleton('hull_connection/config')->getAppId();
  }

  public function getOrgUrl()
  {
    return Mage::getSingleton('hull_connection/config')->getOrgUrl();
  }

  public function isEnabled()
  {
    return Mage::getSingleton('hull_connection/config')->isEnabled();
  }

  public function getUserForHash()
  {
    $hullSession    = Mage::getSingleton('hull_connection/session');
    $currentUserId  = $hullSession->getCurrentUserId();
    if ($this->helper('customer')->isLoggedIn()) {
      $customer = $this->helper('customer')->getCustomer();
      if (!$customer->hasHullUid()) {
        return array(
          'id'    => $customer->getId(),
          'email' => $customer->getEmail(),
          'name'  => ($customer->getFirstname() . ' ' . $customer->getLastname())
        );
      }
    }
  }

  public function getInitConfig()
  {
    $hullClient     = Mage::helper('hull_connection')->getClient();
    $customer = $this->helper('customer')->getCustomer();
    $conf = array(
      "appId" => $this->getAppId(),
      "orgUrl" => $this->getOrgUrl(),
      "jsUrl" => "https://d3f5pyioow99x0.cloudfront.net"
    );

    if (Mage::getSingleton('hull_connection/session')->isLoggingOut()) {
      $conf["userHash"] = $hullClient->userHash(array());
    } else {
      $userForHash = $this->getUserForHash();
      if ($userForHash) {
        $conf["userHash"] = $hullClient->userHash($userForHash);
      }
    }

    return $conf;
  }

  protected function _toHtml()
  {
    if (!$this->isEnabled()) {
      return '';
    }
    return parent::_toHtml();
  }
}
