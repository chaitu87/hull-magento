<?php
class Hull_Connection_Block_Complete extends Mage_Core_Block_Template
{

  private $_userData;

  protected function isEnabled()
  {
    return Mage::getSingleton('hull_connection/config')->isEnabled();
  }

  protected function _toHtml()
  {
    if (!$this->isEnabled()) {
      return '';
    }
    return parent::_toHtml();
  }

  public function getFormData()
  {
    if (!$this->_userData) {
      $data = new Varien_Object();
      $formData = Mage::getSingleton('hull_connection/form')->getCustomerFormData();
      $data->addData($formData);
      $data->setCustomerData(1);
      $this->_userData = $data;
    }
    return $this->_userData;
  }

  private function _getCustomerSession()
  {
    return Mage::getSingleton('customer/session');
  }

  public function getPostActionUrl()
  {
    return $this->helper('hull_connection')->getCompletePostUrl();
  }
}
