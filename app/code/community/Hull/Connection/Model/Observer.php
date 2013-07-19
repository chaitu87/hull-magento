<?php
class Hull_Connection_Model_Observer
{
  public function logout()
  {
    $appId = Mage::getSingleton('hull_connection/config')->getAppId();
    Mage::app()->getCookie()->delete('hull_' . $appId);
    Mage::log('Disconnecting from app ' . $appId);
  }
}
