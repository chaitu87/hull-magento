<?php

class Hull_Connection_Model_User extends Varien_object {

  private $customer;

  public function find($userId)
  {
    $client = Mage::helper('hull_connection')->getClient();
    // Get Actual user from Hull's Server from its ID
    $res = $client->get($userId);
    if (isset($res)) {
      $this->hydrate($res);
    }
    return $this;
  }

  public function hydrate($data)
  {
    $this->setAttributes($data);
    $this->setId($data->id);
    $this->setName($data->name);
    $this->setEmail($data->email);
    $identities = array();
    foreach($data->identities as $ident) {
      $identities[str_replace('_account', '', $ident->type)] = $ident;
    }
    $this->setIdentities($identities);
    $this->setMainIdentity($identities[$data->main_identity]);

    $firstName = $this->_getIdentityAttr("first_name");
    $lastName = $this->_getIdentityAttr("last_name");
    if (empty($lastName) && empty($firstName)) {
      $name = explode(" ", $data->name, 2);
      $firstName = $name[0];
      $lastName = $name[1];
    }
    $this->setFirstName($firstName);
    $this->setLastName($lastName);
    return $this;
  }

  public function getEmail()
  {
    return $this->_getIdentityAttr("email");
  }

  public function getGender($fallback = null)
  {
    $gender = $this->_getIdentityAttr("gender", $fallback);
    if (!is_null($gender)) {
      return ucfirst($gender);
    }
  }

  public function getBirthdate($fallback = null)
  {
    return $this->_getIdentityAttr("birthdate", $fallback);
  }

  public function getOrCreateCustomer() {
    if (!is_null($this->customer)) {
      return $this->customer;
    }

    $customerLookupMethods = array(
      "_getCustomerByUid",
      "_getCustomerByExternalId",
      "_getCustomerByEmail",
      "_createCustomer"
    );

    foreach($customerLookupMethods as $method) {
      $ret = $this->$method();
      if (!is_null($ret)) {
        $this->customer = $ret;
        break;
      }
    }

    return $this->customer;
  }

  private function _getCustomerByEmail() {
    if (!is_null($this->getEmail())) {
      $ret = Mage::getModel('customer/customer')
        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
        ->loadByEmail($this->getEmail());
      if ($ret->getId()) {
        if (is_null($ret->getConfirmation())) {
          $this->_associateToExistingCustomer($ret);
          return $ret;
        } else {
          return false;
        }
      }
    }
  }

  private function _getCustomerByExternalId() {
    if ($this->_isExternalUser()) {
      return Mage::getModel('customer/customer')->load($this->getMainIdentity()->uid);
    }
  }

  private function _isExternalUser() {
    $mainIdentity = $this->getMainIdentity();
    if ($mainIdentity->provider == 'external' && $mainIdentity->uid) {
      return true;
    } else {
      return false;
    }
  }

  private function _getCustomerByUid() {
    if (!is_null($this->getId())) {
      $customerModel = Mage::getModel('customer/customer');
      $collection = $customerModel->getCollection()
        ->addAttributeToFilter('hull_uid', $this->getId())
        ->setPageSize(1);
      if($customerModel->getSharingConfig()->isWebsiteScope()) {
        $collection->addAttributeToFilter('website_id', Mage::app()->getWebsite()->getId());
      }
      if ((bool)$collection->count()) {
        return $collection->getFirstItem();
      }
    }
  }

  private function _associateToExistingCustomer($customer) {
    if (!is_null($customer) && is_null($customer->getHullUid()) && !$this->_isExternalUser() && !is_null($this->getId())) {
      $customer->setHullUid($this->getId());
      $customer->save();
      return $customer;
    }
  }

  private function _createCustomer() {
    $customer = Mage::getModel('customer/customer');
    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());

    if(!$customer->getId()) {
      $hullUserEmail = $this->getEmail();
      //This is to avoid redirect loops;
      $currentRouteName = Mage::app()->getRequest()->getRouteName();

      if (empty($hullUserEmail)) {
        if ($currentRouteName != 'hull') {
          $targetUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'hull/user/complete';
          Mage::app()->getResponse()->setRedirect($targetUrl);
        }
        return null;
      } else {
        $customer->setId(null)
          ->setHullUid($this->getId())
          ->setGender($this->getGender())
          ->setEmail($this->getEmail())
          ->setFirstname($this->getFirstName())
          ->setLastname($this->getLastName())
          ->setPassword($customer->generatePassword(8));
        $customer->save();
        $customer->setConfirmation(null);
        $customer->save();
        $customer->sendNewAccountEmail();
        return $customer;
      }
    }
  }

  private function _getIdentityAttr($attr, $fallback = null)
  {
    $mainIdent = $this->getMainIdentity();
    if ($mainIdent && !empty($mainIdent->$attr)) {
      return $mainIdent->$attr;
    } else {
      $identities = $this->getIdentities();
      $ret = false;
      foreach($identities as $n => $i) {
        if (!$ret && !empty($i->$attr)) {
          $ret = $i->$attr;
        }
      }
      if ($ret != false) {
        return $ret;
      } else {
        return $fallback;
      }
    }
  }

}
