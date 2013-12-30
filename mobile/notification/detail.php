<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/../includes/init.php');

$id = isset($_GET['notification_id']) && intval($_GET['notification_id']) ? intval($_GET['notification_id']) : null;

$sql = 'SELECT * FROM ' . $ecs->table('notification') . ' WHERE `notification_id` = ' . $id;
$res = $db->getRow($sql, 1);
$smarty->assign('notification', $res);
$smarty->assign('footer', get_footer());
$smarty->display('notification/detail.html');
