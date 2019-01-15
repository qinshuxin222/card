<?php
 /**
 * 公共控制器
 * @author Q
 */
namespace Card\Controller;
use Think\Controller;
class PublicController extends BaseController {
	
	public function getWxcode(){
		header('content-type:image/jpeg');
        $ACCESS_TOKEN=$this->getWxAccessToken();
        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$ACCESS_TOKEN;
        $param = json_encode(array("scene"=>"123","path"=>"pages/index/index?id=123","width"=> 150));
		
      
        $result=$this->httpRequest($url,$param);
		//生成二维码
		//file_put_contents("qrcode.png", $result);
		$base64_image ="data:image/jpeg;base64,".base64_encode( $result );

		echo $result;
        //echo '<image src='.$base64_image.'></image>';
    }
 //把请求发送到微信服务器换取二维码
  function httpRequest($url, $data='', $method='POST'){
    $curl = curl_init();  
    curl_setopt($curl, CURLOPT_URL, $url);  
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);  
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);  
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);  
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);  
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);  
    if($method=='POST')
    {
        curl_setopt($curl, CURLOPT_POST, 1); 
        if ($data != '')
        {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);  
        }
    }

    curl_setopt($curl, CURLOPT_TIMEOUT, 30);  
    curl_setopt($curl, CURLOPT_HEADER, 0);  
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  
    $result = curl_exec($curl);  
    curl_close($curl);  
    return $result;
  } 
//二进制转图片image/png
public function data_uri($contents, $mime)
{
    $base64   = base64_encode($contents);
    return ('data:' . $mime . ';base64,' . $base64);
}


	public function getWxAccessToken(){
		$appid=session("company.appid");
		$appsecret=session("company.appsecret");
		$access_token=session("company.access_token_app");
		$expire_time=session("company.expire_time_app");
		
		if($access_token && $expire_time>time()){
			//echo 111;
			return $access_token;
		}else{
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
			$result = vegt($url);
			$result = json_decode($result,true);
	
			//echo 222;
			//print_r($result);exit;
			$data['access_token_app']=$result['access_token'];
			$data['expire_time_app']=time()+$result['expires_in'];
			$res=M('crm_company')->where("id=".session("company.id"))->save($data);
			if($res){
				session("company.access_token_app",$data['access_token_app']);
				session("company.expire_time_app",$data['expire_time_app']);
			}
			return $access_token;
		}
	}
	public function getPhoneNumber(){  
					$APPID = session("business.appid");  
					$AppSecret = session("business.appsecret");
					
					$session_key = I('param.session_key');  
					   
					$encryptedData = $this->define_str_replace($_GET['encryptedData']);  
					$iv = $this->define_str_replace($_GET['iv']);  
					
					// 获取信息，对接口进行解密  
					Vendor("PHP.wxBizDataCrypt");  
					$pc = new \WXBizDataCrypt($APPID,$session_key);  
					$errCode = $pc->decryptData($encryptedData,$iv,$data);  
					
					$data = json_decode($data,true);  
					$return_data = array(
								'code'      =>  40000,
								'msg'       =>  '得到手机号成功',
								'phoneNumber'      =>  $data['phoneNumber']
					);
					exit(json_encode($return_data)); 	
	}
	public function login(){  
					$APPID = session("company.appid");  
					$AppSecret = session("company.appsecret");
				

					$code = I('get.code');  
					$url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$APPID.'&secret='.$AppSecret.'&js_code='.$code.'&grant_type=authorization_code';  
					$arr = vegt($url);  
				  
					$arr = json_decode($arr,true);  
					// $openid = $arr['openid'];  
					$session_key = $arr['session_key'];  
				  
					// 数字签名校验  
					$signature = I('get.signature');  
					$signature2 = sha1($_GET['rawData'].$session_key);  
					if($signature != $signature2){  
						 $return_data = array(
								'code'      =>  40001,
								'msg'       =>  '数字签名失败',
								'data'      =>  ''
						 );
						 exit(json_encode($return_data)); 
					}
					// 获取信息，对接口进行解密  
					Vendor("PHP.wxBizDataCrypt");  
					$encryptedData = $this->define_str_replace($_GET['encryptedData']);  
					$iv = $this->define_str_replace($_GET['iv']);  
					if(empty($signature) || empty($encryptedData) || empty($iv)){  ; 
						$return_data = array(
								'code'      =>  40002,
								'msg'       =>  '传递信息不全',
								'data'      =>  ''
						 );
						 exit(json_encode($return_data)); 						
					}   
					$pc = new \WXBizDataCrypt($APPID,$session_key);  
					$errCode = $pc->decryptData($encryptedData,$iv,$data);  
					if($errCode != 0){ 
							$return_data = array(
									'code'      =>  40003,
									'msg'       =>  '解密数据失败',
									'data'      =>  ''
							 );
							 exit(json_encode($return_data)); 	 
					}else {  
						$data = json_decode($data,true);  
						//session('myinfo',$data);  
						$save['openid'] = $data['openId'];  
						$save['nickname'] = $data['nickName'];  
						$save['sex'] = $data['gender'];  
						$save['address'] = $data['city'];  
						$save['avatar_url'] = $data['avatarUrl'];
						$save['unionid'] = $data['unionid'];
						$save['last_login'] = time();
						$save['last_ip'] = get_client_ip();
						$save['reg_time'] = time();  
						$map['openid'] = $data['openId'];  
						//!empty($data['unionId']) && $save['unionId'] = $data['unionId'];  
				  
						$res = M('users') -> where($map) -> find();  
						if(!$res){  
								$db = M('users') -> add($save);   
						}else{  //更新用户信息
								$save2['nickname'] = $data['nickName'];  
								$save2['sex'] = $data['gender'];  
								$save2['address'] = $data['city'];  
								$save2['avatar_url'] = $data['avatarUrl'];
								$save2['unionid'] = $data['unionid'];
								$save2['last_login'] = time();
								$save2['last_ip'] = get_client_ip();								
								$db2 = M('users')-> where($map)  -> save($save2); 
						}
						$res = M('users') -> where($map) -> find(); 
						$res['picture_maxSize']=2048;  //以k为单位
						$res['video_maxSize']=10240;	  //10M				
						$return_data = array(
								'code'      =>  40000,
								'msg'       =>  '登录成功',
								'data'      =>  $res
						 );
						 exit(json_encode($return_data));
					}  
					//生成第三方3rd_session  
				/*	$session3rd  = null;  
					$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";  
					$max = strlen($strPol)-1;  
					for($i=0;$i<16;$i++){  
						$session3rd .=$strPol[rand(0,$max)];  
					}  
					echo $session3rd;  */
	}
	
	  /**
     * 请求过程中因为编码原因+号变成了空格
     * 需要用下面的方法转换回来
     */
    function define_str_replace($data)
    {
        return str_replace(' ','+',$data);
    }
	
	
	public function wxlogin(){
			//声明CODE，获取小程序传过来的CODE
			$code = $_POST["code"];
			//配置appid
			$appid = session("company.appid");
			//配置appscret
			$appsecret = session("company.appsecret");
			//api接口
			$api = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appsecret}&js_code={$code}&grant_type=authorization_code";
			//发送
			$result = vegt($api);
			$data=json_decode($result,true);
			if($data['openid']){
					$return_data = array(
							'code'      =>  40000,
							'msg'       =>  '成功登录json_本地',
							'openid'       =>$data['openid'],
							'session_key'       =>$data['session_key'],
					 );	
			}else{
					$return_data = array(
						'code'      =>  40010,
						'msg'       =>  '失败,从json登录'.$code,
					 );	

				
			}
			exit(json_encode($return_data));
		
	}


		
}
