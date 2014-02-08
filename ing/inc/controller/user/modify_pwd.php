<?php

define('INIT_NO_USERS', true);

require(EC_PATH . '/includes/init.php');
include_once(EC_PATH . '/includes/lib_order.php');
GZ_Api::authSession();


//GZ_Api::outPut(101);
$user_info = user_info($_SESSION['user_id']);
$password = _POST('password');
$new_password = _POST('n_password');
$result = modifiy_pwd($user_info['user_id'], $password, $new_password);
exit(json_encode($result));

function modifiy_pwd($user_id, $password, $new_password)
{
    $result = array();
    $user_info = user_info($user_id);
    if (!$user_info)
    {
        $result['status']['succeed'] = 0;
        $result['status']['error_code'] = 1;
        $result['status']['error_desc'] = '用户不存在！';
        return $result;
    }
    if (!$password)
    {
        $result['status']['succeed'] = 0;
        $result['status']['error_code'] = 2;
        $result['status']['error_desc'] = '旧密码输入错误！';
        return $result;
    }
    if (strlen($new_password) < 6)
    {
        $result['status']['succeed'] = 0;
        $result['status']['error_code'] = 3;
        $result['status']['error_desc'] = '登录密码不能少于 6 个字符！s';
        return $result;
    }
    $ec_salt = $user_info['ec_salt'];
    if ($ec_salt)
    {
        $password = md5(md5($password) . $ec_salt);
    }
    else
    {
        $password = md5($password);
    }
    if ($password != $user_info['password'])
    {
        $result['status']['succeed'] = 0;
        $result['status']['error_code'] = 2;
        $result['status']['error_desc'] = '旧密码输入错误！';
        return $result;
    }
    $sql = "UPDATE " . $GLOBALS['ecs']->table('users') . "SET `password`='" . md5($new_password) . "', `ec_salt`='0' WHERE user_id= '" . $user_info['user_id'] . "'";
    $GLOBALS['db']->query($sql);
    $sql = "UPDATE " . $GLOBALS['ecs']->table('users') . "SET `ec_salt`='0' WHERE user_id= '" . $user_id . "'";
    $GLOBALS['db']->query($sql);
    $result['status']['succeed'] = 1;
    return $result;
}