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
					if($row['message_type']==5){  //复制微信
							if($row['from_user_id']==$this->user_id){  //是本人的情况下
								$worker_name=M("business_worker")->where("id=".$row['to_user_id'])->getField('nickname');
								$row['content']="你已复制了".$worker_name."的微信";
							}
					}else if($row['message_type']==6){  //请求联系
							$worker_name=M("business_worker")->where("id=".$row['to_user_id'])->getField('nickname');
							$row['content']=$worker_name."已收到你的联系方式";
					}
			}
			$return_data = array(
					'code'      =>  40000,
					'msg'       =>  '成功',
					'data'       =>$list
			);
			//print_r(($return_data));
			exit(json_encode($return_data));
		}
		//对外发送聊天
		 public function add(){
				$message_type=I("param.message_type",1,intval);
				$content=I("param.content");  //内容
				
				$array['message_type']=$message_type;
				$array['content']=$content;
				$res=$this->message_add($array);
				if($res['code']==40000){
						$return_data = array(
							'code'      =>  40000,
							'msg'       =>  '成功',
						);
				}else{
						$return_data = array(
								'code'      =>  40010,
								'msg'       =>  '失败',
						);
				}
				exit(json_encode($return_data));
		 }
		 
		//增加聊天
		public function message_add($array){
					$from_user_id=$this->user_id;
					$to_user_id=$this->worker_id;
					
					$data['from_user_id']=$from_user_id;
					$data['to_user_id']=$to_user_id;
					$data['message_type']=$array['message_type'];
					$data['content']=$array['content'];
					$data['add_time']=time();
					$data['type']=1;  			//1发送方为会员表 2发送方为员工表
					$res=M("business_message")->add($data);
					if($res){
							$work = new \Asset\Controller\WorkController();
							$array['touser']='';
							$array['toparty']='2';
							$array['totag']='';
							$array['msgtype']='text';
							$array['agentid']='1000003';
							
							$array['content']=$array['content'];
							$result = $work->send($array);
						
							$return_data = array(
									'code'      =>  40000,
									'msg'       =>  '成功',
							);
					}else{
							$return_data = array(
									'code'      =>  40010,
									'msg'       =>  '失败',
							);

					}
					return $return_data;
		}
		 

		 
}