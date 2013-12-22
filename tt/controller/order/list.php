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
 GZ_Api::authSession();

$user_id = $_SESSION['user_id'];
require_once(EC_PATH . '/languages/' .$_CFG['lang']. '/user.php');
include_once(EC_PATH . '/includes/lib_order.php');
include_once(EC_PATH . '/includes/lib_transaction.php');
include_once(EC_PATH . '/includes/lib_clips.php');
include_once(EC_PATH . '/includes/lib_payment.php');

$page_parm = GZ_Api::$pagination;
$page = $page_parm['page'];
$type = _POST('type', 'await_pay');
//await_pay 待付款
//await_ship 待发货
//shipped 待收货
//finished 历史订单
if (!in_array($type, array('await_pay', 'await_ship', 'shipped', 'finished', 'unconfirmed', 'await_comment')))
{
    GZ_Api::outPut(101);
}

if ($type == 'await_comment')
{
    $orders = GZ_get_user_all_orders($user_id);
    foreach ($orders as $key => $value)
    {
        unset($orders[$key]['order_status']);
        $orders[$key]['order_time'] = formatTime($value['order_time']);
        $goods_list = GZ_order_goods($value['order_id']);
        $goods_commented = GZ_order_commented_goods($value['order_id'], $user_id);
        //$orders[$key]['ss'] = $goods_list;
        $goods_list_t = array();
        // $goods_list = API_DATA("SIMPLEGOODS", $goods_list);
        foreach ($goods_list as $v)
        {
            if (in_array($v['goods_id'], $goods_commented))
                continue;
            $goods_list_t[] = array(
                "goods_id" => $v['goods_id'],
                "name" => $v['goods_name'],
                "goods_number" => $v['goods_number'],
                "subtotal" => price_format($v['subtotal'], false),
                "formated_shop_price" => price_format($v['goods_price'], false),
                "img" => array(
                    'small' => API_DATA('PHOTO', $v['goods_thumb']),
                    'thumb' => API_DATA('PHOTO', $v['goods_img']),
                    'url' => API_DATA('PHOTO', $v['original_img'])
                )
            );
        }

        if (!$goods_list_t)
        {
            unset($orders[$key]);
            continue;
        }
        $orders[$key]['goods_list'] = $goods_list_t;
        $order_detail = get_order_detail($value['order_id'], $user_id);
        $orders[$key]['formated_integral_money'] = $order_detail['formated_integral_money']; //积分 钱
        $orders[$key]['formated_bonus'] = $order_detail['formated_bonus']; //红包 钱
        $orders[$key]['formated_shipping_fee'] = $order_detail['formated_shipping_fee']; //运送费

        if ($order_detail['pay_id'] > 0)
        {
            $payment = payment_info($order_detail['pay_id']);
        }


        $subject = $orders[$key]['goods_list'][0]['name'] . '等' . count($orders[$key]['goods_list']) . '种商品';

        $orders[$key]['order_info'] = array(
            'pay_code' => $payment['pay_code'],
            'order_amount' => $order_detail['order_amount'],
            'order_id' => $order_detail['order_id'],
            'subject' => $subject,
            'desc' => $subject,
            'order_sn' => $order_detail['order_sn']
        );
    }

    //page
    $sortOrder = array();
    foreach ($orders as $order)
    {
        $sortOrder[] = $order;
    }
    $record_count = count($sortOrder);
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page, $page_parm['count']);
    $sortOrder = array_slice($sortOrder, $pager['start'], $pager['size']);
    $pagero = array(
        "total" => $pager['record_count'],
        "count" => count($sortOrder),
        "more" => empty($pager['page_next']) ? 0 : 1
    );
    GZ_Api::outPut($sortOrder, $pagero);
}


$records = $db->getAll("SELECT order_id FROM " . $ecs->table('order_info') . " WHERE user_id = '$user_id'" . GZ_order_query_sql($type));
$record_count = count($records);

// $order_all = $db->getAll("SELECT * FROM ".$ecs->table('order_info')." WHERE user_id='$user_id'");
// foreach ($order_all[0] as $key => $val) {
//   if ($order_all[0][$key] == $order_all[1][$key]) {
//     unset($order_all[0][$key]);
//     unset($order_all[1][$key]);
//   }
// }
// $sql = "SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id'". GZ_order_query_sql($type);
//  print_r($sql);exit;
$pager  = get_pager('user.php', array('act' => $action), $record_count, $page, $page_parm['count']);
$orders = GZ_get_user_orders($user_id, $pager['size'], $pager['start'], $type);
// print_r($orders);exit;
foreach ($orders as $key => $value) {
	unset($orders[$key]['order_status']);
	$orders[$key]['order_time'] = formatTime($value['order_time']);
	$goods_list = GZ_order_goods($value['order_id']);
	//$orders[$key]['ss'] = $goods_list;
	$goods_list_t = array();
	// $goods_list = API_DATA("SIMPLEGOODS", $goods_list);
	foreach ($goods_list as $goods_key => $v)
    {
        $goods_list_t[$goods_key] = array(
            "goods_id" => $v['goods_id'],
		  "name" => $v['goods_name'],
		  "goods_number" => $v['goods_number'],
		  "subtotal" => price_format($v['subtotal'], false),
		  "formated_shop_price" => price_format($v['goods_price'], false),
		  "img" => array(
      'small'=>API_DATA('PHOTO', $v['goods_thumb']),
			'thumb'=>API_DATA('PHOTO', $v['goods_img']),
			'url' => API_DATA('PHOTO', $v['original_img'])
			)
		);
    }

	$orders[$key]['goods_list'] = $goods_list_t;
	$order_detail = get_order_detail($value['order_id'], $user_id);
	$orders[$key]['formated_integral_money']   = $order_detail['formated_integral_money'];//积分 钱
	$orders[$key]['formated_bonus']   = $order_detail['formated_bonus'];//红包 钱
	$orders[$key]['formated_shipping_fee']   = $order_detail['formated_shipping_fee'];//运送费

  if ($order_detail['pay_id'] > 0) {
      $payment = payment_info($order_detail['pay_id']);
  }
  

  $subject = $orders[$key]['goods_list'][0]['name'].'等'.count($orders[$key]['goods_list']).'种商品';

  $orders[$key]['order_info'] = array(
         'pay_code' => $payment['pay_code'],
         'order_amount' => $order_detail['order_amount'],
         'order_id' => $order_detail['order_id'],
         'subject' => $subject,
         'desc' => $subject,
         'order_sn' => $order_detail['order_sn']
     );

 }
 // print_r($orders);exit;
$pagero = array(
		"total"  => $pager['record_count'],	 
		"count"  => count($orders),
		"more"   => empty($pager['page_next']) ? 0 : 1
    );
GZ_Api::outPut($orders, $pagero);




////function
/**
 *  获取用户指定范围的订单列表
 *
 * @access  public
 * @param   int         $user_id        用户ID号
 * @param   int         $num            列表最大数量
 * @param   int         $start          列表起始位置
 * @return  array       $order_list     订单列表
 */
function GZ_get_user_orders($user_id, $num = 10, $start = 0, $type = 'await_pay')
{
    /* 取得订单列表 */
    $arr    = array();

    $sql = "SELECT order_id, order_sn, order_status, shipping_status, pay_status, add_time, " .
           "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee ".
           " FROM " .$GLOBALS['ecs']->table('order_info') .
           " WHERE user_id = '$user_id' " . GZ_order_query_sql($type) . " ORDER BY add_time DESC";
    // print_r($sql);exit;
    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {

        $row['shipping_status'] = ($row['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $row['shipping_status'];
        $row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . ',' . $GLOBALS['_LANG']['ps'][$row['pay_status']] . ',' . $GLOBALS['_LANG']['ss'][$row['shipping_status']];

        $arr[] = array('order_id'       => $row['order_id'],
                       'order_sn'       => $row['order_sn'],
                       'order_time'     => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']),
                       'order_status'   => $row['order_status'],
                       'total_fee'      => price_format($row['total_fee'], false));
    }

    return $arr;
}





/**
 * 取得订单商品
 * @param   int     $order_id   订单id
 * @return  array   订单商品数组
 */
function GZ_order_goods($order_id)
{
    $sql = "SELECT o.*, " .
            "o.goods_price * o.goods_number AS subtotal,g.goods_thumb,g.original_img,g.goods_img " .
            "FROM " . $GLOBALS['ecs']->table('order_goods') . " as o LEFT JOIN ".$GLOBALS['ecs']->table('goods') . " AS g ON o.goods_id = g.goods_id" .
            " WHERE o.order_id = '$order_id'";

    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['extension_code'] == 'package_buy')
        {
            $row['package_goods_list'] = get_package_goods($row['goods_id']);
        }
        $goods_list[] = $row;

    }
    //return $GLOBALS['db']->getAll($sql);
    return $goods_list;
}

function get_commented_order($user_id)
{
    $sql = "SELECT o.order_id,og.goods_id, c.comment_id FROM " . $GLOBALS['ecs']->table('order_info') . " AS o
join " . $GLOBALS['ecs']->table('order_goods') . " AS og on o.order_id = og.order_id
join " . $GLOBALS['ecs']->table('comment') . " AS c on c.id_value = og.goods_id and c.comment_type = 0
where o.user_id = " . $user_id . " AND o.`shipping_status`=2 AND c.user_id = " . $user_id . " GROUP BY o.order_id";
    $res = $GLOBALS['db']->query($sql);
    $orderIds = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $orderIds[] = $row['order_id'];
    }
    return $orderIds;
}

function GZ_get_user_all_orders($user_id)
{
    /* 取得订单列表 */
    $arr = array();

    $sql = "SELECT order_id, order_sn, order_status, shipping_status, pay_status, add_time, " .
            "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee " .
            " FROM " . $GLOBALS['ecs']->table('order_info') .
            " WHERE user_id = '$user_id' ORDER BY add_time DESC";
    // print_r($sql);exit;
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {

        $row['shipping_status'] = ($row['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $row['shipping_status'];
        $row['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . ',' . $GLOBALS['_LANG']['ps'][$row['pay_status']] . ',' . $GLOBALS['_LANG']['ss'][$row['shipping_status']];

        $arr[] = array('order_id' => $row['order_id'],
            'order_sn' => $row['order_sn'],
            'order_time' => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']),
            'order_status' => $row['order_status'],
            'total_fee' => price_format($row['total_fee'], false));
    }

    return $arr;
}

function GZ_order_commented_goods($order_id, $user_id)
{
    $sql = "SELECT og.goods_id FROM " . $GLOBALS['ecs']->table('order_goods') . " AS og 
                LEFT JOIN " . $GLOBALS['ecs']->table('comment') . " AS c ON og.goods_id = c.id_value and c.comment_type = 0 
                WHERE og.order_id = '$order_id' and c.user_id = '$user_id'";

    $res = $GLOBALS['db']->query($sql);
    $goods_list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $goods_list[] = $row['goods_id'];
    }
    return $goods_list;
}

