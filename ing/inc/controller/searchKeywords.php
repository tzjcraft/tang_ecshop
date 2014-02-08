<?php

require(EC_PATH . '/includes/init.php');

$sql = 'SELECT tag_words, COUNT(tag_id) AS tag_count' .
        ' FROM ' . $GLOBALS['ecs']->table('tag') .
        " GROUP BY tag_words ORDER BY tag_count DESC LIMIT 20";
$tags = $GLOBALS['db']->getAll($sql);

$data = array();
foreach ($tags as $val) {
    $data[] = $val['tag_words'];
}

GZ_Api::outPut($data);