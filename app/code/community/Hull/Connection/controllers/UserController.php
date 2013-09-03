<?
class Hull_Connection_UserController extends Mage_Core_Controller_Front_Action
{
  public function completeAction() {
    $isPost = $this->getRequest()->isPost();
    $referer = $this->getRequest()->getCookie('hull-referer');
    if ($isPost && !empty($referer)) {
      $referer = Mage::helper('core')->urlDecode($referer);
      if ($this->_isUrlInternal($referer)) {
        $this->_redirect($referer);
        Mage::app()->getCookie()->delete('hull-referer');
      } else {
        $this->noRouteAction();
      }
    } else {
      $loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
      $hullLoggedIn = Mage::getSingleton('hull_connection/session')->isConnected();
      if (!($hullLoggedIn && !$loggedIn)) {
        return $this->noRouteAction();
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
