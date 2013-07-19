<?php
$installer = $this;
$installer->startSetup();
$installer->addAttribute('customer', 'hull_uid', array(
  'type'      => 'varchar',
  'label'     => 'Hull User id',
  'visible'   => false,
  'required'  => false
));
$installer->endSetup();

