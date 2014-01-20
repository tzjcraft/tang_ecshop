<?php

/**
 * ECSHOP 销售明细列表程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: sale_list.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . "includes/fckeditor/fckeditor.php");
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
$smarty->assign('lang', $_LANG);

if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'list'))
{
    /* 检查权限 */
//    admin_priv('sale_order_stats');
    $smarty->assign('ur_here', $_LANG['list_all']);
    $smarty->assign('full_page', 1);

    $list = get_notification();

    $smarty->assign('notification_list', $list['item']);
    $smarty->assign('filter', $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count', $list['page_count']);

    $sort_flag = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    $smarty->assign('action_link', array('text' => $_LANG['add_notification'], 'href' => 'notification.php?act=edit'));
    assign_query_info();

    $smarty->display('notification.htm');
}
if ($_REQUEST['act'] == 'query')
{
    $list = get_notification();

    $smarty->assign('notification_list', $list['item']);
    $smarty->assign('filter', $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count', $list['page_count']);

    $sort_flag = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('notification.htm'), '',
            array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
if ($_REQUEST['act'] == 'remove')
{
    $id = intval($_GET['id']);

    $db->query("DELETE FROM " . $ecs->table('notification') . " WHERE notification_id ='$id'");

    $url = 'notification.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}
if ($_REQUEST['act'] == 'edit')
{
    $notification_id = isset($_GET['id']) && intval($_GET['id'] > 0) ? intval($_GET['id']) : null;
    if (!$notification_id)
    {
        $notification = array('notification_id' => null,
            'title' => '',
            'content' => ''
        );
    }
    else
    {
        $sql = "SELECT  * " .
                " FROM " . $ecs->table('notification') . " WHERE notification_id='{$notification_id}'";

        $notification = $db->GetRow($sql);
    }


    /* 创建 html editor */
    create_html_editor('FCKeditor1', $notification['content']);

    $smarty->assign('ur_here', $_LANG['17_notification']);
    $smarty->assign('action_link', array('text' => $_LANG['17_notification'], 'href' => 'notification.php?act=list'));
    $smarty->assign('form_action', 'edit');
    $smarty->assign('notification', $notification);

    assign_query_info();
    $smarty->display('notification_add.htm');
}
if ($_REQUEST['act'] == 'update')
{
    /* 权限判断 */
//    admin_priv('booking');

    $content = $_POST['FCKeditor1'];
    $title = $_POST['title'];

    $notifiaction_id = $_POST['id'];
    $admin_id = $_SESSION[admin_id];
    $time = time();
    if (!$notifiaction_id)
    {
        $extend_field_str = "('{$title}', '{$content}','{$admin_id}','{$time}')";
        $sql = 'INSERT INTO ' . $ecs->table('notification') . ' (`title`, `content`, `admin_id`,`time`) VALUES' . $extend_field_str;
    }
    else
    {
        $sql = "UPDATE  " . $ecs->table('notification') .
                " SET title='{$title}" . "', content='" . $content . "', time='{$time}'" .
                " WHERE notification_id='" . $notifiaction_id . "'";
    }


    $db->query($sql);

    ecs_header("Location: ?act=list");
}
/*------------------------------------------------------ */
//--商品明细列表
/*------------------------------------------------------ */
else
{
    /* 权限判断 */
//    admin_priv('sale_order_stats');
}

/**
 * 获取通知列表
 *
 * @access  public
 *
 * @return array
 */
function get_notification()
{
    /* 查询条件 */
    $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
    if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
    {
        $filter['keywords'] = json_str_iconv($filter['keywords']);
    }
    $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'time' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('notification');
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    /* 获取活动数据 */
    $sql = 'SELECT n.notification_id, n.admin_id, n.title,n.content, n.time ' .
            "FROM " . $GLOBALS['ecs']->table('notification') . " AS n " .
            "ORDER BY $filter[sort_by] $filter[sort_order] " .
            "LIMIT " . $filter['start'] . ", $filter[page_size]";
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {
        $row[$key]['time'] = local_date($GLOBALS['_CFG']['time_format'], $val['time']);
    }
    $arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    return $arr;
}
?>