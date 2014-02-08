<?php

define('INIT_NO_USERS', true);

require(EC_PATH . '/includes/init.php');
include_once(EC_PATH . '/includes/lib_order.php');
//GZ_Api::authSession();

$page_parm = GZ_Api::$pagination;
if(!is_int($page_parm['page']) || !is_int($page_parm['count']) || $page_parm['page']<0 || $page_parm['count']<0){
	$data = 101;
}else{
	$record_count = $db->getOne("SELECT COUNT(1) FROM " . $ecs->table('notification'));
	$page = $page_parm['page'];
	$page_start = ($page-1)*$page_parm['count']>0?($page-1)*$page_parm['count']:0;
	$data = notification_list($page_parm['count'], $page_start);
	if(empty($data)){
		$data = 8;
	}else{
		$pager = array('total'=>$record_count,'count'=>$page_parm['count'],'more'=>$page);
	}
}
GZ_Api::outPut($data,$pager);

function notification_list($num, $start)
{
    $sql = 'SELECT `notification_id`,`title`,`time` FROM ' . $GLOBALS['ecs']->table('notification') . ' ORDER BY `time` DESC';
    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);
    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
    	//$row['content'] = strip_tags($row['content']);
    	$row['time'] = local_date("Y年m月d日 H:i",$row['time']);
      	$arr[] = $row;
    }
    return $arr;
}