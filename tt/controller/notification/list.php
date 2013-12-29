<?php

/*
 *
 *       _/_/_/                      _/        _/_/_/_/_/
 *    _/          _/_/      _/_/    _/  _/          _/      _/_/      _/_/
 *   _/  _/_/  _/_/_/_/  _/_/_/_/  _/_/          _/      _/    _/  _/    _/
 *  _/    _/  _/        _/        _/  _/      _/        _/    _/  _/    _/
 *   _/_/_/    _/_/_/    _/_/_/  _/    _/  _/_/_/_/_/    _/_/      _/_/
 *
 *
 *  Copyright 2013-2014, Geek Zoo Studio
 *  http://www.ecmobile.cn/license.html
 *
 *  HQ China:
 *    2319 Est.Tower Van Palace
 *    No.2 Guandongdian South Street
 *    Beijing , China
 *
 *  U.S. Office:
 *    One Park Place, Elmira College, NY, 14901, USA
 *
 *  QQ Group:   329673575
 *  BBS:        bbs.ecmobile.cn
 *  Fax:        +86-10-6561-5510
 *  Mail:       info@geek-zoo.com
 */

define('INIT_NO_USERS', true);

require(EC_PATH . '/includes/init.php');
include_once(EC_PATH . '/includes/lib_order.php');
GZ_Api::authSession();

$record_count = $db->getOne("SELECT COUNT(*) FROM " . $ecs->table('notification'));
$page_parm = GZ_Api::$pagination;
$page = $page_parm['page'];

$pager = get_pager('user.php', array('act' => 'list'), $record_count, $page, $page_parm['count']);
$data = notification_list($pager['size'], $pager['start']);
GZ_Api::outPut($data);

function notification_list($num, $start)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('notification') . ' ORDER BY `time` DESC';
    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);
    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[] = $row;
    }
    return $arr;
}