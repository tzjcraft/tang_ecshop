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
include_once(EC_PATH . '/includes/lib_goods.php');
//GZ_Api::authSession();
$json = _POST('json', null);

$commentData = json_decode($json);
if (!$commentData)
{
    GZ_Api::outPut(101);
}

$result = add_comment_by_api($commentData);

if ($result)
{
    $addStatus = array();
    $addStatus['status']['succeed'] = 1;
    $addStatus['data'] = '您的评论已经成功发表，请等待管理员的审核! ';
    GZ_Api::outPut($addStatus);
}
else
{
    GZ_Api::outPut(101);
}

/**
 * 添加评论内容
 *
 * @access  public
 * @param   object  $cmt
 * @return  void
 */
function add_comment_by_api($commentData)
{
    /* 评论是否需要审核 */
    $status = 1 - $GLOBALS['_CFG']['comment_check'];

    $content = isset($commentData->content) ? $commentData->content : '';
    $goods_id = isset($commentData->goods_id) ? $commentData->goods_id : null;
    $session = isset($commentData->session) ? $commentData->session : null;
    $user_id = $session && isset($session->uid) && $session->uid ? $session->uid : null;

    $userInfo = user_info($user_id);
    $goodsInfo = goods_info($goods_id);
    if (!$content || !$goodsInfo || !$userInfo)
    {
        GZ_Api::outPut(101);
    }
    
    $email = htmlspecialchars($userInfo['email']);
    $user_name = htmlspecialchars($userInfo['user_name']);

    /* 保存评论内容 */
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('comment') .
            "(comment_type, id_value, email, user_name, content, comment_rank, add_time, ip_address, status, parent_id, user_id) VALUES " .
            "('0', '" . $goodsInfo['goods_id'] . "', '$email', '$user_name', '" . $content . "', '', " . gmtime() . ", '" . real_ip() . "', '$status', '0', '$user_id')";

    $result = $GLOBALS['db']->query($sql);
    clear_cache_files('comments_list.lbi');
    return $result;
}

?>