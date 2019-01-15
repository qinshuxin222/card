<?php
namespace Card\Controller;
use Think\Controller;
class WebsiteController extends BaseController {
		 public function  __construct() {   
				parent::__construct();  
				$card_id=I("param.card_id",0,intval);
				
				$card_info=M("card")->where(" status=1 and id=$card_id")->find();
				if(!$card_info){
						$return_data = array(
									'code'      =>  40001,
									'msg'       =>  '没有这个名片',
						);
						exit(json_encode($return_data));
				}
				$this->user_id=$card_info['user_id'];
				$this->user=M("users")->where("user_id=".$this->user_id)->find();
				$this->card_id=$card_id;
				$this->worker_id=$card_info['worker_id'];
				$page=I("param.page",1,intval);
				$pagesize=I("param.pagesize",10,intval);
		 }
		 
		public function index(){
			$business_id=M("business_worker")->where("id=".$this->worker_id)->getField('business_id');
			
			//轮播图1
			$info['banner']=M("business_website_banner")->where("business_id=".$business_id)->order(" sort_order desc")->select(); 
			//图文2
			$info['image_text']=M("business_website_image_text")->where("business_id=".$business_id)->order(" sort_order desc")->select();  
			//资讯3
			$common = new CommonController();	
			$article_list=$common->article_list($this->user_id,$page,$pagesize);
			$info['article_list']=$article_list;
			//图文4
			$info['website_team']=M("business_website_team")->where("business_id=".$business_id)->order(" sort_order desc")->select();  
			
			//联系我们 5
			$info['contact']==M("business_website_contact")->where("business_id=".$business_id)->order(" sort_order desc")->select();
			
			$work = new \Asset\Controller\WorkController();
			$array['touser']='';
			$array['toparty']='2';
			$array['totag']='';
			$array['msgtype']='text';
			$array['agentid']='1000003';
			
			$user=$this->user;
			$array['content']=$user['nickname'].'查看了你的官网';
			$result = $work->send($array);
			$return_data = array(
					'code'      =>  40000,
					'msg'       =>  '成功',
					'data'       =>$info
			);
			exit(json_encode($return_data));
		 }
		 

		 
}