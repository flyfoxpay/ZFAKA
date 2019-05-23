<?php
/**
 * File: flyfoxpay.php
 * Functionality: 翔狐科技
 * Author: 翔狐科技
 * Date: 2019-5-14
 */
namespace Pay\flyfoxpay;

use \Pay\notify;
class flyfoxpay
{
	private $paymethod ="flyfoxpay";
	//处理请求
	public function pay($payconfig,$params)
	{
		try{
			$url = "https://sc-i.pw/api/";//API位置
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
 array("key"=>$payconfig['app_secret'], //商家KEY
       "id"=>$payconfig['app_id'], //商家ID
       "mail"=>$payconfig['configure3'], //商家EMAIL
       "trade_no"=>"zfaka".time(), //商家訂單ID
       "amount"=>$params['money']*5.6, //訂單金額(需大於50)
       "trade_name"=>"付费商品-".$params['orderid'], //訂單名稱
       "type"=>"all", //指定付款方式，預設為all
       "customize1"=>$params['orderid'],
       "customize2"=>$params['money'],
       "return"=>$config['weburl']."/query/auto/".$params['orderid'].".html"//支付完成返回網址
      ))); 
$output = curl_exec($ch); 
curl_close($ch);
/*
回傳格式:
//成功
{"status":"200","url":"https://sc-i.pw/pay/?sign=*****"}
//重複訂單
{"status":"204","error":"重複訂單內容","url":"https://sc-i.pw/pay/?sign=*****"}
//重複訂單ID(trade_no相同)
{"status":"206","error":"重複訂單ID"}
//以下為錯誤項目
{"status":"404","error":"未設置KEY或是ID或MAIL"}
{"status":"400","error":"請檢查ID或是KEY或MAIL是否有誤"}
{"status":"315","error":"請檢查TYPE欄位是否錯誤"}
{"status":"406","error":"金額不可低於50"}
*/ 
$json=json_decode($output, true);
//echo $json['url'];//url可以換成你要輸出的欄位
			
			if(is_array($json) AND isset($json['url']) AND $json['url']){
				$urlip = "http://www.geoplugin.net/json.gp?ip=".get_client_ip();
    $json = json_decode(file_get_contents($urlip));
    $country = $json->{"geoplugin_countryCode"};
    $countrys = "CN";
				if($country==$countrys){
				$qr="https://www.kuaizhan.com/common/encode-png?large=true&data=".$json['url'];
				}else{
				$qr="http://chart.apis.google.com/chart?cht=qr&chs=2000x200&chl=".$json['url'];}
				$result_params = array('type'=>0,'subjump'=>0,'paymethod'=>$this->paymethod,'qr'=>$qr,'payname'=>'二维码扫描器','overtime'=>'0','money'=>$params['money']);
				return array('code'=>1,'msg'=>'success','data'=>$result_params);
			} else {
				return array('code'=>1002,'msg'=>'支付接口请求失败，'.$json['error'],'data'=>'');
			}
		} catch (\Exception $e) {
			return array('code'=>1000,'msg'=>$e->getMessage(),'data'=>'');
		}
	}
	
	public function notify(array $payconfig)
	{
		try {
          if($_REQUEST['orderid']=='' OR $_REQUEST['orderid']==null){ return '{"code":0,"msg":"success3"}'; }else{
          $url = "https://sc-i.pw/api/check/";//API位置
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
 array("key"=>$payconfig['app_secret'], //商家KEY
       "id"=>$payconfig['app_id'], //商家ID
       "mail"=>$payconfig['configure3'], //商家EMAIL
       "trade_no"=>$_REQUEST['orderid'], //商家訂單ID
       ))); 
$output = curl_exec($ch); 
curl_close($ch);
/*
回傳格式:
//成功
{"status":"200","status_trade":"noapy","sign":"90e5f1f7ef87cd2e43729ba4378656b5"}
{"status":"200","trade_no":"1278217527512","type":"o_alipay","status_trade":"payok","sign":"*****"}
//以下為錯誤項目
{"status":"404","error":"未設置KEY或是ID或MAIL"}
{"status":"400","error":"請檢查ID或是KEY或MAIL是否有誤"}
{"status":"416","error":"請檢查訂單ID是否有誤"}
*/ 
$security1  = array();

$security1['mchid']      = $payconfig['app_id'];//商家ID

$security1['status']        = "7";//驗證，請勿更改

$security1['mail']      = $payconfig['configure3'];//商家EMAIL

$security1['trade_no']      = $_REQUEST['orderid'];//商家訂單ID

foreach ($security1 as $k=>$v)

{

    $o.= "$k=".($v)."&";

}

$sign1 = md5(substr($o,0,-1).$payconfig['app_secret']);//**********請替換成商家KEY
$json=json_decode($output, true);
if($json['sign']==$sign1){
  if($json['status_trade']=='payok'){$config = array('paymethod'=>$this->paymethod,'tradeid'=>$json['trade_no'],'paymoney'=>$json['customize2'],'orderid'=>$json['customize1'] );
					$notify = new \Pay\notify();
					$data = $notify->run($config);
					return '{"code":0,"msg":"success"}';
                                    }else{return '{"code":0,"msg":"success1"}'; }
  
}else{
 return '{"code":0,"msg":"success2"}'; 
}}
			
		} catch (\Exception $e) {
			return 'error|Exception:'.$e->getMessage();
		}
	}
	
}