<?php
 /**
 * 公共控制器
 * @author Q
 */
namespace Card\Controller;
use Think\Controller;
class UserController extends BaseController {
	public function detail(){
			$user_id = I("param.user_id",0,intval);
			$info=M("users")->where("user_id=$user_id")->find();
			if(!$info){
					$return_data = array(
							'code'      =>  40010,
							'msg'       =>  '失败',
					 );	
			}else{
					$return_data = array(
							'code'      =>  40000,
							'msg'       =>  '成功',
							'data'       =>  $info,
					 );	
			}
			exit(json_encode($return_data));
	}
	
	
	public function update(){
			$avatar_url = I("param.avatar_url");
			$nickname = I("param.nickname");
			$gender = I("param.sex");
			$map['openid'] = session("user_auth.openid");  
			//echo session("user_auth.openid");exit;
			if($nickname){
						$save['avatar_url'] = $avatar_url;  
						$save['nickname'] = $nickname;  
						$save['sex'] = $gender;    //性别 
						$save['last_login'] = time();
						$save['last_ip'] = get_client_ip();
						$db2 = M('users')-> where($map) -> save($save); 
			}
			$return_data = array(
					'code'      =>  40000,
					'msg'       =>  '成功得到用户信息'.$nickname.session("user_auth.openid"),
			 );
			 exit(json_encode($return_data));
		
	}
	

	public function getPhoneNumber(){ 
					$work = new \Asset\Controller\WorkController();
					$array['touser']='';
					$array['toparty']='2';
					$array['totag']='';
					$array['msgtype']='text';
					$array['agentid']='1000003';
					
					$array['content']="寺城向你授权了ta的手机号13760832719已同步到该客户资料详情";
					$result = $work->send($array);
			
					$APPID = session("business.appid");  
					$AppSecret = session("business.appsecret");
					
					$session_key = I('param.session_key');  
					   
					$encryptedData = define_str_replace($_GET['encryptedData']);  
					$iv = define_str_replace($_GET['iv']);  
					
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
}
