<?
class Hull_Connection_UserController extends Mage_Core_Controller_Front_Action
{
  public function completeAction() {
    $isPost = $this->getRequest()->isPost();
    $isHullLoggedIn = true;
    $isCustomerLoggedIn = false;
    $email = $this->getRequest()->getParam('email', false);
    if ($email && $isPost && $isHullLoggedIn && !$isCustomerLoggedIn) {
      Mage::getSingleton('customer/session')->set('providedEmail', $email);
      var_dump(Mage::getSingleton('customer/session')->get('providedEmail'));die;
    }
    $this->loadLayout();
    $this->_initLayoutMessages('customer/session');
    $this->renderLayout();
  }

  private function _getCustomerSession()
  {
    return Mage::getSingleton('customer/session');
  }
}
