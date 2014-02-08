<?php

require(EC_PATH . '/includes/init.php');

$sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 AND id != 6 ORDER BY dis_order, id';
$extend_info_list = $db->getAll($sql);

$out = array();
foreach ($extend_info_list as $val) {
    $out[] = array(
        'id' => $val['id'],
        'name' => $val['reg_field_name'],
        'need' => $val['is_need']
    );
}

GZ_Api::outPut($out);