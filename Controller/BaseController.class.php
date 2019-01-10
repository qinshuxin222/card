<?php
 /**
 * 公共控制器  必须得到appID
 * @author Qin 
 */
namespace Card\Controller;
use Think\Controller;
class BaseController extends Controller {
		protected function _initialize(){
				$appid=I("param.appid");
				$secret = I('param.secret');
				if(!$appid){
						$return['code']=40001;
						$return['msg']='小程序ID不能为空';
						exit(json_encode($return));
				}
				/*if(!$secret){
						$return['code']=40002;
						$return['msg']='密钥有问题';
						exit(json_encode($return));
				}
				*/
				$business=M("business")->where("appid='$appid'")->find();
				if(!$business){
						$return['code']=40003;
						$return['msg']='没有这个公司的小程序ID';
						exit(json_encode($return));
				}
				/*
				if($secret!==$business['secret']){
						$return['code']=40004;
						$return['msg']='内部调用密码错误';
						exit(json_encode($return));
				} */
				session("business",$business);
				//print_r(session("business"));exit;
		}	
}
