<?php

define('INIT_NO_USERS', true);

require(EC_PATH . '/includes/init.php');

GZ_Api::authSession();

include_once(EC_PATH . '/includes/lib_transaction.php');

$address_id = _POST('address_id', 0);

if (empty($address_id)) {
	GZ_Api::outPut(101);
}

drop_consignee($address_id);

GZ_Api::outPut(array());
