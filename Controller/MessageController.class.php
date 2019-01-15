<?php
namespace Card\Controller;
use Think\Controller;
class MessageController extends BaseController {
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
				$this->card_id=$card_id;
				$this->worker_id=$card_info['worker_id'];
				$page=I("param.page",1,intval);
				$pagesize=I("param.pagesize",10,intval);
		 }
		 
		//聊天列表 
		public function index(){
			$sql=" (from_user_id={$this->user_id} and  to_user_id={$this->worker_id}) ";
			$sql.=" or (from_user_id={$this->worker_id} and  to_user_id={$this->user_id})";
			$list=M("business_message")->where($sql)->order("id desc")->select();
			
			foreach($list as &$row){
				
			}
			$return_data = array(
					'code'      =>  40000,
					'msg'       =>  '成功',
					'data'       =>$list
			);
			exit(json_encode($return_data));
		}
		
		
		//增加聊天
		public function message_add(){
			$sql=" (from_user_id={$this->user_id} and  to_user_id={$this->worker_id}) ";
			$sql.=" or (from_user_id={$this->worker_id} and  to_user_id={$this->user_id})";
			$list=M("business_message")->where($sql)->order("id desc")->select();
			
			foreach($list as &$row){
				
			}
			$return_data = array(
					'code'      =>  40000,
					'msg'       =>  '成功',
					'data'       =>$list
			);
			exit(json_encode($return_data));
		}
		 

		 
}