<?php
 /**
 * 公共控制器
 * @author Q
 */
namespace Card\Controller;
use Think\Controller;
class UserController extends BaseController {
	public function info(){
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
	

		
}
