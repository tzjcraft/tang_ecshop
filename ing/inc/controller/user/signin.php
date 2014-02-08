<?php

require(EC_PATH . '/includes/init.php');
include_once(EC_PATH . '/includes/lib_order.php');

$name = _POST('name');
$password = _POST('password');
 
if (!$user->login($name, $password)) {
	GZ_Api::outPut(6);
}

$user_info = GZ_user_info($_SESSION['user_id']);

$out = array(
	'session' => array(
		'sid' => SESS_ID.$GLOBALS['sess']->gen_session_key(SESS_ID),
		'uid' => $_SESSION['user_id']
	),

	'user' => $user_info
);

update_user_info();
recalculate_price();

GZ_Api::outPut($out);
