<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>支付宝即时到账交易接口接口</title>
</head>
<?php
/* *
 * 功能：即时到账交易接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
 * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
 * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */

require_once("alipay.config.php");
require_once("lib/alipay_submit.class.php");

define('IN_ECS', true);
require('../includes/init.php');
/**************************调用授权接口alipay.wap.trade.create.direct获取授权码token**************************/
	
//返回格式
$format = "xml";
//必填，不需要修改

//返回格式
$v = "2.0";
//必填，不需要修改

//请求号
$req_id = date('Ymdhis');
//必填，须保证每次请求都是唯一

//**req_data详细信息**

//订单名称
//http://www.99-k.com/aip/alipayapi.php?subject=2013121820061&out_trade_no=201312182006171
$subject = $_GET['subject'];
if(empty($subject))
{
	die('订单号为空');
}
$sql = 'SELECT * FROM ecs_order_info where order_sn='.$subject;
$order = $db->GetAll($sql);

//必填
//商户订单号
//商户网站订单系统中唯一订单号，必填
//$out_trade_no = $_POST['WIDout_trade_no'];
$out_trade_no = $_GET["out_trade_no"];
if(empty($out_trade_no)){
	die('订单号为空');
}

//付款金额
//$total_fee = $_POST['WIDtotal_fee'];
$total_fee = $_GET['price'];
if(empty($total_fee)){
	$total_fee = $order[0]['order_amount'];
}

//服务器异步通知页面路径
$notify_url = "http://www.99-k.com/aip/notify_url.php";
/*
if(empty($notify_url)){
	//$notify_url = "http://www.99-k.com/mobile/respond.php?code=alipay&out_trade_no=".$out_trade_no."&subject=".$subject;
	$notify_url = "http://www.99-k.com/mobile/respond.php?code=alipay";
}
*/
//需http://格式的完整路径，不允许加?id=123这类自定义参数

//页面跳转同步通知页面路径
/*$call_back_url = "http://www.99-k.com/aip/call_back_url.php";*/
//$call_back_url = "http://www.99-k.com/mobile/respond.php";

$call_back_url = $_GET["return_url"];
if(empty($call_back_url)){
	//$call_back_url = "http://www.99-k.com/mobile/respond.php?code=alipay&out_trade_no=".$out_trade_no."&subject=".$subject;
	$call_back_url = "http://www.99-k.com/mobile/respond.php?code=alipay";
}
//需http://格式的完整路径，不允许加?id=123这类自定义参数

//操作中断返回地址
$merchant_url = "http://www.99-k.com/aip/alipayapi.php";
//用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数

//卖家支付宝帐户
//$seller_email = $_POST['WIDseller_email'];
$seller_email = $_GET["seller_email"];
if(empty($seller_email)){
	$seller_email = 'ttjm@jingyagroup.com';
}
//必填

//请求业务参数详细
$req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';
//必填

/************************************************************/

//构造要请求的参数数组，无需改动
$para_token = array(
		"service" => "alipay.wap.trade.create.direct",
		"partner" => trim($alipay_config['partner']),
		"sec_id" => trim($alipay_config['sign_type']),
		"format"	=> $format,
		"v"	=> $v,
		"req_id"	=> $req_id,
		"req_data"	=> $req_data,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestHttp($para_token);

//URLDECODE返回的信息
$html_text = urldecode($html_text);

//解析远程模拟提交后返回的信息
$para_html_text = $alipaySubmit->parseResponse($html_text);

//获取request_token
$request_token = $para_html_text['request_token'];

/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/

//业务详细
$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
//必填

//构造要请求的参数数组，无需改动
$parameter = array(
		"service" => "alipay.wap.auth.authAndExecute",
		"partner" => trim($alipay_config['partner']),
		"sec_id" => trim($alipay_config['sign_type']),
		"format"	=> $format,
		"v"	=> $v,
		"req_id"	=> $req_id,
		"req_data"	=> $req_data,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);
//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');
echo $html_text;
?>
</body>
</html>