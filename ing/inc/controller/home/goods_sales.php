<?php

require(EC_PATH . '/includes/init.php');

include_once(EC_PATH . '/includes/lib_goods.php');

$type = _POST('type');
$recommend_goods = get_recommend_goods($type);
GZ_Api::outPut($recommend_goods);