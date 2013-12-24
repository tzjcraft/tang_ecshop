<?php

/**
 * ECSHOP 用户中心
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user.php 16643 2009-09-08 07:02:13Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

$act = isset($_GET['act']) ? $_GET['act'] : '';

/* 用户登陆 */
if ($act == 'do_login')
{
    $user_name = !empty($_POST['username']) ? $_POST['username'] : '';
    $pwd = !empty($_POST['pwd']) ? $_POST['pwd'] : '';
    if (empty($user_name) || empty($pwd))
    {
        $login_faild = 1;
    }
    else
    {
        if ($user->check_user($user_name, $pwd) > 0)
        {
            $user->set_session($user_name);
            $user->set_cookie($user_name);
            update_user_info();
            show_user_center();
        }
        else
        {
            $login_faild = 1;
        }
    }
}

elseif ($act == 'order_list')
{
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = {$_SESSION['user_id']}");
    if ($record_count > 0)
    {
        include_once(ROOT_PATH . 'includes/lib_transaction.php');
        $page_num = '10';
        $page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
        $pages = ceil($record_count / $page_num);

        if ($page <= 0)
        {
            $page = 1;
        }
        if ($pages == 0)
        {
            $pages = 1;
        }
        if ($page > $pages)
        {
            $page = $pages;
        }
        $pagebar = get_wap_pager($record_count, $page_num, $page, 'user.php?act=order_list', 'page');
        $smarty->assign('pagebar' , $pagebar);
        /* 订单状态 */
        $_LANG['os'][OS_UNCONFIRMED] = '未确认';
        $_LANG['os'][OS_CONFIRMED] = '已确认';
        $_LANG['os'][OS_SPLITED] = '已确认';
        $_LANG['os'][OS_SPLITING_PART] = '已确认';
        $_LANG['os'][OS_CANCELED] = '已取消';
        $_LANG['os'][OS_INVALID] = '无效';
        $_LANG['os'][OS_RETURNED] = '退货';

        $_LANG['ss'][SS_UNSHIPPED] = '未发货';
        $_LANG['ss'][SS_PREPARING] = '配货中';
        $_LANG['ss'][SS_SHIPPED] = '已发货';
        $_LANG['ss'][SS_RECEIVED] = '收货确认';
        $_LANG['ss'][SS_SHIPPED_PART] = '已发货(部分商品)';
        $_LANG['ss'][SS_SHIPPED_ING] = '配货中'; // 已分单

        $_LANG['ps'][PS_UNPAYED] = '未付款';
        $_LANG['ps'][PS_PAYING] = '付款中';
        $_LANG['ps'][PS_PAYED] = '已付款';
        $_LANG['cancel'] = '取消订单';
        $_LANG['pay_money'] = '付款';
        $_LANG['view_order'] = '查看订单';
        $_LANG['received'] = '确认收货';
        $_LANG['ss_received'] = '已完成';
        $_LANG['confirm_received'] = '你确认已经收到货物了吗？';
        $_LANG['confirm_cancel'] = '您确认要取消该订单吗？取消后此订单将视为无效订单';

        $orders = get_user_orders($_SESSION['user_id'], $page_num, $page_num * ($page - 1));
        if (!empty($orders))
        {
            foreach ($orders as $key => $val)
            {
                $orders[$key]['total_fee'] = encode_output($val['total_fee']);
            }
        }
        //$merge  = get_user_merge($_SESSION['user_id']);

        $smarty->assign('orders', $orders);
    }
    $smarty->assign('footer', get_footer());
    $smarty->display('order_list.html');
    exit;
}

/* 取消订单 */
elseif ($act == 'cancel_order')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    if (cancel_order($order_id, $_SESSION['user_id']))
    {
        ecs_header("Location: user.php?act=order_list\n");
        exit;
    }
}

/* 确认收货 */
elseif ($act == 'affirm_received')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    $_LANG['buyer'] = '买家';
    if (affirm_received($order_id, $_SESSION['user_id']))
    {
        ecs_header("Location: user.php?act=order_list\n");
        exit;
    }

}

/* 退出会员中心 */
elseif ($act == 'logout')
{
    if (!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

    $user->logout();
    $Loaction = 'index.php';
    ecs_header("Location: $Loaction\n");

}
/* 显示会员注册界面 */
elseif ($act == 'register')
{
    if (!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

    /* 取出注册扩展字段 */
    $sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
    $extend_info_list = $db->getAll($sql);
    $smarty->assign('extend_info_list', $extend_info_list);
    /* 密码找回问题 */
    $_LANG['passwd_questions']['friend_birthday'] = '我最好朋友的生日？';
    $_LANG['passwd_questions']['old_address']     = '我儿时居住地的地址？';
    $_LANG['passwd_questions']['motto']           = '我的座右铭是？';
    $_LANG['passwd_questions']['favorite_movie']  = '我最喜爱的电影？';
    $_LANG['passwd_questions']['favorite_song']   = '我最喜爱的歌曲？';
    $_LANG['passwd_questions']['favorite_food']   = '我最喜爱的食物？';
    $_LANG['passwd_questions']['interest']        = '我最大的爱好？';
    $_LANG['passwd_questions']['favorite_novel']  = '我最喜欢的小说？';
    $_LANG['passwd_questions']['favorite_equipe'] = '我最喜欢的运动队？';
    /* 密码提示问题 */
    $smarty->assign('passwd_questions', $_LANG['passwd_questions']);
    $smarty->assign('footer', get_footer());
    $smarty->display('user_passport.html');
}
/* 注册会员的处理 */
elseif ($act == 'act_register')
{
        include_once(ROOT_PATH . 'includes/lib_passport.php');

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
        $other['msn'] = isset($_POST['extend_field1']) ? $_POST['extend_field1'] : '';
        $other['qq'] = isset($_POST['extend_field2']) ? $_POST['extend_field2'] : '';
        $other['office_phone'] = isset($_POST['extend_field3']) ? $_POST['extend_field3'] : '';
        $other['home_phone'] = isset($_POST['extend_field4']) ? $_POST['extend_field4'] : '';
        $other['mobile_phone'] = isset($_POST['extend_field5']) ? $_POST['extend_field5'] : '';
        $sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
        $passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';

        $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';

        if (m_register($username, $password, $email, $other) !== false)
        {
            /*把新注册用户的扩展信息插入数据库*/
            $sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有自定义扩展字段的id
            $fields_arr = $db->getAll($sql);

            $extend_field_str = '';    //生成扩展字段的内容字符串
            foreach ($fields_arr AS $val)
            {
                $extend_field_index = 'extend_field' . $val['id'];
                if(!empty($_POST[$extend_field_index]))
                {
                    $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
                    $extend_field_str .= " ('" . $_SESSION['user_id'] . "', '" . $val['id'] . "', '" . compile_str($temp_field_content) . "'),";
                }
            }
            $extend_field_str = substr($extend_field_str, 0, -1);

            if ($extend_field_str)      //插入注册扩展数据
            {
                $sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
                $db->query($sql);
            }

            /* 写入密码提示问题和答案 */
            if (!empty($passwd_answer) && !empty($sel_question))
            {
                $sql = 'UPDATE ' . $ecs->table('users') . " SET `passwd_question`='$sel_question', `passwd_answer`='$passwd_answer'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";
                $db->query($sql);
            }

            $ucdata = empty($user->ucdata)? "" : $user->ucdata;
            $Loaction = 'index.php';
            ecs_header("Location: $Loaction\n");
        }
}
/* 密码找回-->输入用户名界面 */
elseif ($act == 'qpassword_name')
{
    //显示输入要找回密码的账号表单
    $smarty->assign('action', $act);
    $smarty->display('user_passport_forget.html');
}
/* 密码找回-->根据注册用户名取得密码提示问题界面 */
elseif ($act == 'get_passwd_question')
{
    if (empty($_POST['user_name']))
    {
        echo '您没有设置密码提示问题，无法通过这种方式找回密码';
        echo '<br />';
        echo '<a href="user.php">返回登录页面</a>';
        exit;
    }
    else
    {
        $user_name = trim($_POST['user_name']);
    }

    //取出会员密码问题和答案
    $sql = 'SELECT user_id, user_name, passwd_question, passwd_answer FROM ' . $ecs->table('users') . " WHERE user_name = '" . $user_name . "'";
    $user_question_arr = $db->getRow($sql);

    //如果没有设置密码问题，给出错误提示
    if (empty($user_question_arr['passwd_answer']))
    {
        echo '您没有设置密码提示问题，无法通过这种方式找回密码';
        echo '<br />';
        echo '<a href="user.php">返回登录页面</a>';
        exit;
    }

    $_SESSION['temp_user'] = $user_question_arr['user_id'];  //设置临时用户，不具有有效身份
    $_SESSION['temp_user_name'] = $user_question_arr['user_name'];  //设置临时用户，不具有有效身份
    $_SESSION['passwd_answer'] = $user_question_arr['passwd_answer'];   //存储密码问题答案，减少一次数据库访问
    $_SESSION['passwd_question'] = $_LANG['passwd_questions'][$user_question_arr['passwd_question']];
    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }
    $smarty->assign('action', $act);
    $smarty->assign('passwd_question', $_LANG['passwd_questions'][$user_question_arr['passwd_question']]);
    $smarty->display('user_passport_forget.html');
}
/* 密码找回-->根据提交的密码答案进行相应处理 */
elseif ($act == 'check_answer')
{
    //验证码
//    $captcha = intval($_CFG['captcha']);
//    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
//    {
//        if (empty($_POST['captcha']))
//        {
//            show_message($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
//        }
//
//        /* 检查验证码 */
//        include_once(ROOT_PATH . '/includes/cls_captcha.php');
//
//        $validator = new captcha();
//        $validator->session_word = 'captcha_login';
//        if (!$validator->check_word($_POST['captcha']))
//        {
//            show_message($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
//        }
//    }

    if (empty($_POST['passwd_answer']) || $_POST['passwd_answer'] != $_SESSION['passwd_answer'])
    {
        $message = '您输入的密码答案是错误的';
        $smarty->assign('action', 'get_passwd_question');
        $smarty->assign('passwd_question', $_SESSION['passwd_question']);
        $smarty->display('user_passport_forget.html');
    }
    else
    {
        $_SESSION['user_id'] = $_SESSION['temp_user'];
        $_SESSION['user_name'] = $_SESSION['temp_user_name'];
        unset($_SESSION['temp_user']);
        unset($_SESSION['temp_user_name']);
        $smarty->assign('uid', $_SESSION['user_id']);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport_forget.html');
    }
}
/* 修改会员密码 */
elseif ($act == 'act_edit_password')
{
    include_once(ROOT_PATH . '/includes/lib_passport.php');
    $status = false;
    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : null;
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $user_id = isset($_POST['uid']) ? intval($_POST['uid']) : $user_id;
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';

    if (strlen($new_password) < 6)
    {
        $message = '登录密码不能少于 6 个字符。';
    }
    else
    {

        $user_info = $user->get_profile_by_id($user_id); //论坛记录
        if (($user_info && (!empty($code) && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) == $code)) || ($_SESSION['user_id'] > 0 && $_SESSION['user_id'] == $user_id && $user->check_user($_SESSION['user_name'],
                        $old_password)))
        {

            if ($user->edit_user(array('username' => (empty($code) ? $_SESSION['user_name'] : $user_info['user_name']), 'old_password' => $old_password, 'password' => $new_password),
                            empty($code) ? 0 : 1))
            {
                $sql = "UPDATE " . $ecs->table('users') . "SET `ec_salt`='0' WHERE user_id= '" . $user_id . "'";
                $db->query($sql);
                $user->logout();
                $message = '您的新密码已设置成功！';
                $status = true;
            }
            else
            {
                $message = '您输入的原密码不正确！';
            }
        }
        else
        {
            $message = '您输入的原密码不正确！';
        }
    }
    $smarty->assign('action', 'reset_password');
    $smarty->assign('message', $message);
    $smarty->assign('status', $status);
    $smarty->display('user_passport_forget.html');
}
/* 密码找回-->修改密码界面 */
elseif ($act == 'get_password')
{
    include_once(ROOT_PATH . '/includes/lib_passport.php');

    if (isset($_GET['code']) && isset($_GET['uid'])) //从邮件处获得的act
    {
        $code = trim($_GET['code']);
        $uid = intval($_GET['uid']);

        /* 判断链接的合法性 */
        $user_info = $user->get_profile_by_id($uid);
        if (empty($user_info) || ($user_info && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) != $code))
        {
//            show_message($_LANG['parm_error'], $_LANG['back_home_lnk'], './', 'info');
            $message = '参数错误，请返回！';
        }

        $smarty->assign('uid', $uid);
        $smarty->assign('code', $code);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport_forget.html');
    }
    else
    {
        //显示用户名和email表单
        $smarty->assign('action', 'get_password');
        $smarty->display('user_passport_forget.html');
    }
}
elseif ($act == 'validate_user')
{
    $username = trim($_POST['user_name']);
    $mobile = trim($_POST['mobile']);
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('users') . "WHERE user_name = '" . $username . "'";
    $user_info = $GLOBALS['db']->getRow($sql, true);
    if (!$user_info || $mobile != $user_info['mobile_phone'])
    {
        $message = '用户名不存在或与手机号码不匹配';
        $smarty->assign('action', 'get_password');
        $smarty->assign('message', $message);
        $smarty->display('user_passport_forget.html');
    }
    else
    {
        error_reporting(0);
        require(ROOT_PATH . '/includes/cls_captcha.php');
        $img = new captcha(ROOT_PATH . 'data/captcha/', $_CFG['captcha_width'], $_CFG['captcha_height']);
        $captcha = $img->generateCaptchaString();
        $message = $captcha;

        include_once(ROOT_PATH . '/includes//cls_sms.php');
        $sms = new sms();
        $sms->send($mobile, $message);
        $_SESSION['forget_passwod_uid'] = $user_info['user_id'];
        $smarty->assign('action', 'send_sms_captcha');
        $smarty->display('user_passport_forget.html');
    }
}
elseif ($act == 'send_sms_captcha')
{
    include_once(ROOT_PATH . '/includes/lib_passport.php');
    $status = false;
    $captcha = trim($_POST['captcha']);
    if (!$captcha || strtoupper($captcha) != $_SESSION['sms_captcha'])
    {
        $message = '验证码错误';
        $smarty->assign('action', 'send_sms_captcha');
        $smarty->assign('message', $message);
        $smarty->display('user_passport_forget.html');
    }
    else
    {
        $message = '';
        $smarty->assign('action', 'reset_password_by_captcha');
        $smarty->assign('message', $message);
        $smarty->assign('status', $status);
        $smarty->display('user_passport_forget.html');
    }
}
elseif ($act == 'reset_password_by_captcha')
{
    $newpass = trim($_POST['new_password']);
    $confirmPass = trim($_POST['confirm_password']);
    $user_id = $_SESSION['forget_passwod_uid'];

    if (strlen($newpass) < 6)
    {
        $message = '登录密码不能少于 6 个字符。';
    }
    else
    {

        $user_info = $user->get_profile_by_id($user_id); //论坛记录
        if ($user_info && $newpass && $newpass == $confirmPass)
        {

            if ($user->edit_user(array('username' => $user_info['user_name'], 'password' => $newpass)))
            {
                $sql = "UPDATE " . $ecs->table('users') . "SET `ec_salt`='0' WHERE user_id= '" . $user_id . "'";
                $db->query($sql);
                $user->logout();
                $message = '您的新密码已设置成功！';
                $status = true;
            }
            else
            {
                $message = '您输入的密码不匹配！';
            }
        }
        else
        {
            $message = '您输入的密码不匹配！';
        }
    }
    $smarty->assign('action', 'reset_password_by_captcha');
    $smarty->assign('message', $message);
    $smarty->assign('status', $status);
    $smarty->display('user_passport_forget.html');
}
/* 发送密码修改确认邮件 */
elseif ($act == 'send_pwd_email')
{
    $status = false;
    include_once(ROOT_PATH . '/includes/lib_passport.php');
    $message = null;
    /* 初始化会员用户名和邮件地址 */
    $user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $email = !empty($_POST['email']) ? trim($_POST['email']) : '';

    //用户名和邮件地址是否匹配
    $user_info = $user->get_user_info($user_name);

    if ($user_info && $user_info['email'] == $email)
    {
        //生成code
        //$code = md5($user_info[0] . $user_info[1]);

        $code = md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']);
        //发送邮件的函数
        if (send_pwd_email($user_info['user_id'], $user_name, $email, $code))
        {
//            show_message($_LANG['send_success'] . $email, $_LANG['back_home_lnk'], './', 'info');
            $message = '重置密码的邮件已经发到您的邮箱：' . $email;
            $status = true;
        }
        else
        {
            //发送邮件出错
            $message = '发送邮件出错，请与管理员联系！';
        }
    }
    else
    {
        //用户名与邮件地址不匹配
        $message = '您填写的用户名与电子邮件地址不匹配，请重新输入！';
    }
    $smarty->assign('action', 'get_password');
    $smarty->assign('status', $status);
    $smarty->assign('message', $message);
    $smarty->display('user_passport_forget.html');
}
/* 用户中心 */
else
{
    if ($_SESSION['user_id'] > 0)
    {
        show_user_center();
    }
    else
    {
        $smarty->assign('footer', get_footer());
        $smarty->display('login.html');
    }
}

/**
 * 用户中心显示
 */
function show_user_center()
{
    $best_goods = get_recommend_goods('best');
    if (count($best_goods) > 0)
    {
        foreach  ($best_goods as $key => $best_data)
        {
            $best_goods[$key]['shop_price'] = encode_output($best_data['shop_price']);
            $best_goods[$key]['name'] = encode_output($best_data['name']);
        }
    }
    $GLOBALS['smarty']->assign('best_goods' , $best_goods);
    $GLOBALS['smarty']->assign('footer', get_footer());
    $GLOBALS['smarty']->display('user.html');
}

/**
 * 手机注册
 */
function m_register($username, $password, $email, $other = array())
{
    /* 检查username */
    if (empty($username))
    {
        echo '用户名不能为空';
        $Loaction = 'user.php?act=register';
        ecs_header("Location: $Loaction\n");
        return false;
    }
    if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username))
    {
        echo '用户名错误';
        $Loaction = 'user.php?act=register';
        ecs_header("Location: $Loaction\n");
        return false;
    }

    /* 检查email */
    if (empty($email))
    {
        echo 'email不能为空';
        $Loaction = 'user.php?act=register';
        ecs_header("Location: $Loaction\n");
        return false;
    }
    if(!is_email($email))
    {
        echo 'email错误';
        $Loaction = 'user.php?act=register';
        ecs_header("Location: $Loaction\n");
        return false;
    }

    /* 检查是否和管理员重名 */
    if (admin_registered($username))
    {
        echo '此用户已存在！';
        $Loaction = 'user.php?act=register';
        ecs_header("Location: $Loaction\n");
        return false;
    }

    if (!$GLOBALS['user']->add_user($username, $password, $email))
    {
        echo '注册失败！';
        $Loaction = 'user.php?act=register';
        ecs_header("Location: $Loaction\n");
        //注册失败
        return false;
    }
    else
    {
        //注册成功

        /* 设置成登录状态 */
        $GLOBALS['user']->set_session($username);
        $GLOBALS['user']->set_cookie($username);

     }

        //定义other合法的变量数组
        $other_key_array = array('msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone');
        $update_data['reg_time'] = local_strtotime(local_date('Y-m-d H:i:s'));
        if ($other)
        {
            foreach ($other as $key=>$val)
            {
                //删除非法key值
                if (!in_array($key, $other_key_array))
                {
                    unset($other[$key]);
                }
                else
                {
                    $other[$key] =  htmlspecialchars(trim($val)); //防止用户输入javascript代码
                }
            }
            $update_data = array_merge($update_data, $other);
        }
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $update_data, 'UPDATE', 'user_id = ' . $_SESSION['user_id']);

        update_user_info();      // 更新用户信息

        return true;

}
?>