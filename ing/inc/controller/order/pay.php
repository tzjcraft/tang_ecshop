<?php

define('INIT_NO_USERS', true);

require(EC_PATH . '/includes/init.php');

GZ_Api::authSession();

include_once(EC_PATH . '/includes/lib_transaction.php');
include_once(EC_PATH . '/includes/lib_payment.php');
include_once(EC_PATH . '/includes/lib_order.php');
include_once(EC_PATH . '/includes/lib_clips.php');

$order_id = _POST('order_id', 0);

if (!$order_id) {
	GZ_Api::outPut(101);
}

$user_id = $_SESSION['user_id'];

/* 订单详情 */
$order = get_order_detail($order_id, $user_id);

if ($order === false)
{
	GZ_Api::outPut(8);
}

$base = sprintf('<base href="%s/" />', dirname($GLOBALS['ecs']->url()));
$html = '<!DOCTYPE html><html><head><title></title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0">'.$base.'</head><body>%s</body></html>';

GZ_Api::outPut(array('data' => sprintf($html, $order['pay_online'])));

?>
