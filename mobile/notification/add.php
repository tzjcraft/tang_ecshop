<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/../includes/init.php');
if (isset($_POST['add_notification']))
{
    $title = $_POST['title'];
    $addTime = time();
    $sql = 'INSERT INTO ' . $ecs->table('notification') . ' (`title`, `time`) VALUES' . ' ("' . $title . '", "' . $addTime . '") ';
    $db->query($sql);
    $id = $db->insert_id();
    header("Location: ./detail.php?notification_id=" . $id);
}
$smarty->display('notification/add.html');
