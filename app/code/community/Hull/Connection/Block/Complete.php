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

  private function _getCustomerSession()
  {
    return Mage::getSingleton('customer/session');
  }

  public function getPostActionUrl()
  {
    return $this->helper('hull_connection')->getCompletePostUrl();
  }
}
