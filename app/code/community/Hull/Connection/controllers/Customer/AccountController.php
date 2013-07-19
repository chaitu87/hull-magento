<?php
class Hull_Connection_Customer_AccountController extends Mage_Core_Controller_Front_Action
{
  public function preDispatch()
  {
    parent::preDispatch();

    if (!Mage::getSingleton('hull_connection/config')->isEnabled()) {
      $this->norouteAction();
      return $this;
    }
    return $this;
  }

  public function postDispatch()
  {
    parent::postDispatch();
    Mage::app()->getCookie()->delete('hull-referer');
    return $this;
  }

  public function connectAction()
  {
    if(!$this->_getSession()->validate()) {
      $this->_getCustomerSession()->addError($this->__('Hull connection failed.'));
      $this->_redirect('customer/account');
      return;
    }

    //login or connect

    $customer = Mage::getModel('customer/customer');

    $collection = $customer->getCollection()
      ->addAttributeToFilter('hull_uid', $this->_getSession()->getUserId())
      ->setPageSize(1);

    if($customer->getSharingConfig()->isWebsiteScope()) {
      $collection->addAttributeToFilter('website_id', Mage::app()->getWebsite()->getId());
    }

    if($this->_getCustomerSession()->isLoggedIn()) {
      $collection->addFieldToFilter('entity_id', array('neq' => $this->_getCustomerSession()->getCustomerId()));
    }

    $uidExist = (bool)$collection->count();

    if($this->_getCustomerSession()->isLoggedIn() && $uidExist) {
      $existingCustomer = $collection->getFirstItem();
      $existingCustomer->setHullUid('');
      $existingCustomer->getResource()->saveAttribute($existingCustomer, 'hull_uid');
    }

    if($this->_getCustomerSession()->isLoggedIn()) {
      $currentCustomer = $this->_getCustomerSession()->getCustomer();
      $currentCustomer->setHullUid($this->_getSession()->getUserId());
      $currentCustomer->getResource()->saveAttribute($currentCustomer, 'hull_uid');

      $this->_getCustomerSession()->addSuccess(
        $this->__('Your Hull account has been successfully connected. Now you can fast login using Hull anytime.')
      );
      $this->_redirect('customer/account');
      return;
    }

    if($uidExist) {
      $uidCustomer = $collection->getFirstItem();
      if($uidCustomer->getConfirmation()) {
        $uidCustomer->setConfirmation(null);
        Mage::getResourceModel('customer/customer')->saveAttribute($uidCustomer, 'confirmation');
      }
      $this->_getCustomerSession()->setCustomerAsLoggedIn($uidCustomer);
      //since FB redirects IE differently, it's wrong to use referer like before
      //@TODO Check this for Hull
      $this->_loginPostRedirect();
      return;
    }


    //let's go with an e-mail

    try {
      if ($this->_getCustomerSession()->getCustomerFormData()) {
        $standardInfo = (object)$this->_getCustomerSession()->getCustomerFormData(true);
      } else {
        $standardInfo = $this->_getSession()->getClient()->get($this->_getSession()->getUSerId());
      }
    } catch(Mage_Core_Exception $e) {
      $this->_getCustomerSession()->addError(
        $this->__('Hull connection failed.') .
        ' ' .
        $this->__('Service temporarily unavailable.')
      );
      $this->_redirect('customer/account/login');
      return;
    }

    if (isset($standardInfo->first_name) && isset($standardInfo->last_name)) {
      $firstName = $standardInfo->first_name;
      $lastName = $standardInfo->last_name;
    } elseif(isset($standardInfo->name)) {
      $firstName = $standardInfo->name;
      $lastName = '';
    } else {
      $firstName = '';
      $lastName = '';
    }
    $standardInfo->firstname = $firstName;
    $standardInfo->lastname = $lastName;

    if (!isset($standardInfo->email)) {
      $this->_getCustomerSession()->setCustomerFormData((array)$standardInfo);
      $this->_redirect('hull_connection/customer_account/complete');
      return;
    }

    $customer
      ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
      ->loadByEmail($standardInfo->email);

    if($customer->getId()) {
      $customer->setHullUid($this->_getSession()->getUserId());
      Mage::getResourceModel('customer/customer')->saveAttribute($customer, 'hull_uid');

      if($customer->getConfirmation()) {
        $customer->setConfirmation(null);
        Mage::getResourceModel('customer/customer')->saveAttribute($customer, 'confirmation');
      }

      $this->_getCustomerSession()->setCustomerAsLoggedIn($customer);
      $this->_getCustomerSession()->addSuccess(
        $this->__('Your Hull account has been successfully connected. Now you can fast login using Hull anytime.')
      );
      $this->_redirect('customer/account');
      return;
    }

    //registration needed

    $randomPassword = $customer->generatePassword(8);

    $customer	->setId(null)
      ->setSkipConfirmationIfEmail($standardInfo->email)
      ->setFirstname($firstName)
      ->setLastname($lastName)
      ->setEmail($standardInfo->email)
      ->setPassword($randomPassword)
      ->setConfirmation($randomPassword)
      ->setHullUid($this->_getSession()->getUserId());

    //FB: Show my sex in my profile
    if(isset($standardInfo->gender) && $gender=Mage::getResourceSingleton('customer/customer')->getAttribute('gender')) {
      $genderOptions = $gender->getSource()->getAllOptions();
      foreach($genderOptions as $option) {
        if($option->label==ucfirst($standardInfo->gender)) {
          $customer->setGender($option->value);
          break;
        }
      }
    }

    //FB: Show my full birthday in my profile
    if(isset($standardInfo->birthday) && count(explode('/',$standardInfo->birthday))==3) {

      $dob = $standardInfo->birthday;

      if(method_exists($this,'_filterDates')) {
        $filtered = $this->_filterDates(array('dob'=>$dob), array('dob'));
        $dob = current($filtered);
      }

      $customer->setDob($dob);
    }

    //$customer->setIsSubscribed(1);

    //registration will fail if tax required, also if dob, gender aren't allowed in profile
    $errors = array();
    $validationCustomer = $customer->validate();
    if (is_array($validationCustomer)) {
      $errors = array_merge($validationCustomer, $errors);
    }
    $validationResult = count($errors) == 0;

    if (true === $validationResult) {
      $customer->save();

      $this->_getCustomerSession()->addSuccess(
        $this->__('Thank you for registering with %s', Mage::app()->getStore()->getFrontendName()) .
        '. ' .
        $this->__('You will receive welcome email with registration info in a moment.')
      );

      $customer->sendNewAccountEmail();

      $this->_getCustomerSession()->setCustomerAsLoggedIn($customer);
      $this->_redirect('customer/account');
      return;

      //else set form data and redirect to registration
    } else {
      $this->_getCustomerSession()->setCustomerFormData($customer->getData());
      $this->_getCustomerSession()->addError($this->__('Hull profile can\'t provide all required info, please register and then connect with Hull for fast login.'));
      if (is_array($errors)) {
        foreach ($errors as $errorMessage) {
          $this->_getCustomerSession()->addError($errorMessage);
        }
      }

      $this->_redirect('customer/account/create');

    }

  }

  protected function _loginPostRedirect()
  {
    $session = $this->_getCustomerSession();
    $redirectUrl = Mage::getUrl('customer/account');

    if ($session->getBeforeAuthUrl() &&
      !in_array($session->getBeforeAuthUrl(), array(Mage::helper('customer')->getLogoutUrl(), Mage::getBaseUrl()))) {
        $redirectUrl = $session->getBeforeAuthUrl(true);
      } elseif(($referer = $this->getRequest()->getCookie('hull-referer'))) {
        $referer = Mage::helper('core')->urlDecode($referer);

        //@todo: check why is this added in Magento 1.7
        //$referer = Mage::getModel('core/url')->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));

        if($this->_isUrlInternal($referer)) {
          $redirectUrl = $referer;
        }
      }

    $this->_redirectUrl($redirectUrl);
  }

  public function completeAction()
  {

    if ($this->_getCustomerSession()->isLoggedIn()) {
      $this->_redirect('*/*');
      return;
    }

    $data = $this->_getCustomerSession()->getCustomerFormData();
    Mage::getSingleton('hull_connection/form')->setCustomerFormData($data);

    $this->loadLayout();
    $this->_initLayoutMessages('customer/session');
    $this->renderLayout();
  }

  /**
   * Create customer account action
   */
  public function finishAction()
  {
    $session = $this->_getCustomerSession();
    if ($session->isLoggedIn()) {
      $this->_redirect('*/*/');
      return;
    }
    $customerFormData = (array)$this->_getCustomerSession()->getCustomerFormData();
    $previousData = $this->getRequest()->getPost();

    $this->_getCustomerSession()->setCustomerFormData(array_merge($customerFormData, $previousData));
    $this->_redirect('hull_connection/customer_account/connect');
    return;

    // $session->setEscapeMessages(true); // prevent XSS injection in user input
    // if ($this->getRequest()->isPost()) {
    //   $errors = array();

    //   Mage::getSingleton('hull_connection/form')->setCustomerFormData($this->getRequest()->getPost());
    //   if (!$customer = Mage::registry('current_customer')) {
    //     $customer = Mage::getModel('customer/customer')->setId(null);
    //   }

    //   /* @var $customerForm Mage_Customer_Model_Form */
    //   $customerForm = Mage::getModel('customer/form');
    //   $customerForm->setFormCode('customer_account_create')
    //     ->setEntity($customer);

    //   $customerData = $customerForm->extractData($this->getRequest());

    //   if ($this->getRequest()->getParam('is_subscribed', false)) {
    //     $customer->setIsSubscribed(1);
    //   }

    //   /**
    //    * Initialize customer group id
    //    */
    //   $customer->getGroupId();


    //   try {
    //     $customerErrors = $customerForm->validateData($customerData);
    //     if ($customerErrors !== true) {
    //       $errors = array_merge($customerErrors, $errors);
    //     } else {
    //       $customerForm->compactData($customerData);
    //       $customer->setPassword($this->getRequest()->getPost('password'));
    //       $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
    //       $customerErrors = $customer->validate();
    //       if (is_array($customerErrors)) {
    //         $errors = array_merge($customerErrors, $errors);
    //       }
    //     }

    //     $validationResult = count($errors) == 0;

    //     if (true === $validationResult) {
    //       $customer->save();

    //       Mage::dispatchEvent('customer_register_success',
    //         array('account_controller' => $this, 'customer' => $customer)
    //       );

    //       if ($customer->isConfirmationRequired()) {
    //         $customer->sendNewAccountEmail(
    //           'confirmation',
    //           $session->getBeforeAuthUrl(),
    //           Mage::app()->getStore()->getId()
    //         );
    //         $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
    //         $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
    //         return;
    //       } else {
    //         $session->setCustomerAsLoggedIn($customer);
    //         $url = $this->_welcomeCustomer($customer);
    //         $this->_redirectSuccess($url);
    //         return;
    //       }
    //     } else {
    //       $session->setCustomerFormData($this->getRequest()->getPost());
    //       if (is_array($errors)) {
    //         foreach ($errors as $errorMessage) {
    //           $session->addError($errorMessage);
    //         }
    //       } else {
    //         $session->addError($this->__('Invalid customer data'));
    //       }
    //     }
    //   } catch (Mage_Core_Exception $e) {
    //     $session->setCustomerFormData($this->getRequest()->getPost());
    //     if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
    //       $url = Mage::getUrl('customer/account/forgotpassword');
    //       $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
    //       $session->setEscapeMessages(false);
    //     } else {
    //       $message = $e->getMessage();
    //     }
    //     $session->addError($message);
    //   } catch (Exception $e) {
    //     $session->setCustomerFormData($this->getRequest()->getPost())
    //       ->addException($e, $this->__('Cannot save the customer.'));
    //   }
    // }

    // $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
  }
  private function _getCustomerSession()
  {
    return Mage::getSingleton('customer/session');
  }

  private function _getSession()
  {
    return Mage::getSingleton('hull_connection/session');
  }

}

