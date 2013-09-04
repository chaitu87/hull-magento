<?
class Hull_Connection_UserController extends Mage_Core_Controller_Front_Action
{
  public function completeAction() {
    $isPost = $this->getRequest()->isPost();
    if ($isPost) {
      $referer = Mage::helper('core')->urlDecode($this->getRequest()->getCookie('hull-referer'));
      if (!$this->_isUrlInternal($referer)) {
        $referer = 'hull/user/complete';
      }
      $this->_redirect($referer);
      Mage::app()->getCookie()->delete('hull-referer');
    } else {
      $loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
      $hullLoggedIn = Mage::getSingleton('hull_connection/session')->isConnected();
      if (!($hullLoggedIn && !$loggedIn)) {
        $this->_redirect('/');
        return;
      }
      $this->loadLayout();
      $this->_initLayoutMessages('customer/session');
      $this->renderLayout();
    }
  }

  private function _getCustomerSession()
  {
    return Mage::getSingleton('customer/session');
  }
}
