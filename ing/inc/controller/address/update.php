<?php

define('INIT_NO_USERS', true);

require(EC_PATH . '/includes/init.php');

GZ_Api::authSession();

include_once(EC_PATH . '/includes/lib_transaction.php');
include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');

$address = _POST('address', array());
$address['address_id'] = _POST('address_id', 0);

if (empty($address['address_id'])) {
	GZ_Api::outPut(101);
}

unset($address['id']);
$address['user_id'] = $_SESSION['user_id'];
$a = update_address($address);

GZ_Api::outPut(array());
