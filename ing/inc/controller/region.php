<?php

require(EC_PATH . '/includes/init.php');

$parent_id = _POST('parent_id', 0);


$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['ecs']->table('region') .
        " WHERE parent_id = '$parent_id'";

$result =  $GLOBALS['db']->GetAll($sql);

$out = array();

foreach ($result as $val) {
    $out[] = array(
        'id' => $val['region_id'],
        'name' => $val['region_name'],
		'parent_id' => $val['parent_id']
    );
}
$out = array(
    'more' => intval(!empty($out)),
    'regions' => $out
);
GZ_Api::outPut($out);