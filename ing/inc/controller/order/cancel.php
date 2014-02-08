<?php

define('INIT_NO_USERS', true);

require(EC_PATH . '/includes/init.php');

GZ_Api::authSession();

include_once(EC_PATH . '/includes/lib_transaction.php');
include_once(EC_PATH . '/includes/lib_order.php');

$user_id = $_SESSION['user_id'];
$order_id = _POST('order_id', 0);

if (cancel_order($order_id, $user_id)) {
	GZ_Api::outPut(array());
} else {
	GZ_Api::outPut(8);
}





