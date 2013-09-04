<?php
class Hull_Connection_Model_Observer
{

  public function currentCustomer($params)
  {

    $customerSession = $this->_getCustomerSession();
    $hullSession     = $this->_getHullSession();

    // If not connected on Hull, we just pass
    if (!$hullSession->isConnected()) {
      return;
    }

    // If connected on Hull and not connected as a Magento customer
    if (!$customerSession->isLoggedIn()) {
      $currentUser  = $hullSession->getCurrentUser();
      $customer     = $currentUser->getOrCreateCustomer();
      if ($customer) {
        // Auto login
        $customerSession->setCustomerAsLoggedIn($customer);
      }
    } else {
      $customer = $customerSession->getCustomer();
      $hullUid = $customer->getHullUid();
      if (empty($hullUid)) {
        $customer->setHullUid($hullSession->getCurrentUserId());
        $customer->save();
      }
    }
  }

  public function customerJustLoggedIn($params)
  {
    $this->_getHullSession()->setLatestEvent('customer_login');
  }

  public function customerJustLoggedOut($params)
  {
    $cookieName = 'hull_' . Mage::getSingleton('hull_connection/config')->getAppId();
    setcookie($cookieName, 'logout', time() + 86400, '/');
  }

  protected function _setLatestEvent($eventName)
  {
    if (is_null($this->_getLatestEvent())) {
      $this->_getHullSession()->setLatestEvent($eventName);
    }
  }

  protected function _getLatestEvent($eventName)
  {
    return $this->_getHullSession()->getLatestEvent($eventName);
  }

  protected function _getHullSession()
  {
    return Mage::getSingleton('hull_connection/session');
  }

  protected function _getCustomerSession()
  {
    return Mage::getSingleton('customer/session');
  }



}
