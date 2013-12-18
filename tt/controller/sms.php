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
require(EC_PATH . '/includes/init.php');
$mobile_phone = isset($_REQUEST['mobile_phone']) ? $_REQUEST['mobile_phone'] : null;
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;

error_reporting(0);
require(ROOT_PATH . '/includes/cls_captcha.php');
$img = new captcha(ROOT_PATH . 'data/captcha/', $_CFG['captcha_width'], $_CFG['captcha_height']);
$captcha = $img->generateCaptchaString();
/* 注册时手机号码验证 */
if ($type == 1)
{
    $message = $captcha;
}
/* 添加/修改取货地址时手机号码验证 */
elseif ($type == 2)
{
    $message = $captcha;
}

include_once(ROOT_PATH . '/includes//cls_sms.php');
$sms = new sms();
$out = $sms->send($mobile_phone, $message);

echo json_encode($out);
//GZ_Api::outPut($out);