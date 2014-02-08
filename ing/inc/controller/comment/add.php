<?php
define('INIT_NO_USERS', true);

require(EC_PATH . '/includes/init.php');
include_once(EC_PATH . '/includes/lib_order.php');
include_once(EC_PATH . '/includes/lib_goods.php');
$commentArray = $_POST;
$content = isset($commentArray['content']) ? $commentArray['content'] : null;
$goods_id = isset($commentArray['goods_id']) ? $commentArray['goods_id'] : null;
$rating = isset($commentArray['rating']) ? $commentArray['rating'] : null;
GZ_Api::authSession();
$result = add_comment_by_api($goods_id, $content, $rating);

if ($result)
{
    $addStatus = array();
    $addStatus['status']['succeed'] = 1;
    $addStatus['data'] = '感谢您给出宝贵评价！恭喜获得'.$GLOBALS['_CFG']['content_points'].'积分！';
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
function add_comment_by_api($goods_id, $content, $rating = 5)
{
    /* 评论是否需要审核 */
    $status = 1 - $GLOBALS['_CFG']['comment_check'];

    $user_id = $_SESSION['user_id'];

    $userInfo = user_info($user_id);
    $goodsInfo = goods_info($goods_id);

    if (!is_numeric($rating))
    {
        $rating = 5;
    }
    if (!$content || !$goodsInfo || !$userInfo)
    {
        GZ_Api::outPut(101);
    }

    $email = htmlspecialchars($userInfo['email']);
    $user_name = htmlspecialchars($userInfo['user_name']);

    /* 保存评论内容 */
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('comment') .
            "(comment_type, id_value, email, user_name, content, comment_rank, add_time, ip_address, status, parent_id, user_id) VALUES " .
            "('0', '" . $goodsInfo['goods_id'] . "', '$email', '$user_name', '" . $content . "', '" . $rating . "', " . gmtime() . ", '" . real_ip() . "', '$status', '0', '$user_id')";

    $result = $GLOBALS['db']->query($sql);
    
    log_account_change($_SESSION['user_id'], 0, 0, $GLOBALS['_CFG']['content_points'], $GLOBALS['_CFG']['content_points'],"评价赠送".$GLOBALS['_CFG']['content_points']."积分");
      
    clear_cache_files('comments_list.lbi');
    return $result;
}

?>