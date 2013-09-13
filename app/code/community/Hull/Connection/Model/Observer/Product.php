<?
class Hull_Connection_Model_Observer_Product
{
  public function updateEntity($observer)
  {
    $event = $observer->getEvent();
    $product = $event->getProduct();
    $productUrl = $product->getProductUrl();
    $client = $this->getClient();
    try {
      $res = $client->put('entity', array('uid' => $productUrl));
    } catch (Exception $e) {
      Mage::log($e->getMessage());
    }
  }

  public function getClient()
  {
    return Mage::helper('hull_connection')->getClient();
  }
}

