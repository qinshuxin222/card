<?php
namespace Card\Controller;
use Think\Controller;
class ArticleController extends BaseController {
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
		 }
		 
		public function index(){
					$page=I("param.page",1,intval);
					$pagesize=I("param.pagesize",10,intval);
					
					
					$common = new CommonController();	
					$return_data=$common->article_list($this->user_id,$page,$pagesize);

					$this->send();
					exit(json_encode($return_data));
		 }
		 
		 //新消息
		 public function news(){
					$range=I("param.range",3000,intval);
					
					$longitude=I("param.longitude");
					$latitude=I("param.latitude");
					$time=I("param.time");   //传过来的时间 格式如上2018-4-9 10:25:31
					
					$sql=" is_open=1";
					if($time){
						  $get_time=strtotime($time);
						  $sql.=" and add_time>=$get_time";
					}
					
					$count=M("article")->where($sql)->count();
					$return_data = array(
							'code'      =>  40000,
							'msg'       =>  '成功',
							'count'      =>  $count
					 );
					 exit(json_encode($return_data));
		 }
		 
		 
		 //文章详情
		 public function detail(){
				   $article_id=I("param.id",0,intval);

					
				   $common = new CommonController();	
				   $return_data=$common->article_view($this->user_id,$article_id);

				   $this->send();
				   exit(json_encode($return_data));	
		 }
		 
		 
		 
		 //点赞
		 public function zan(){
					$type=I("param.type",1,intval);		//1文章 2评论
					$correlation_id=I("param.correlation_id",0,intval);
					$user_id=I("param.user_id",0,intval);
					
					$User=M('users')->where("user_id=".$user_id)->find();
					if(!$User){
							$return_data = array(
								'code'      =>  40001,
								'msg'       =>  '不存在这个用户'
							);
							exit(json_encode($return_data));
					}
					$AZ=M('article_zan')->where(" type=$type and correlation_id=$correlation_id and user_id=$user_id")->find();
					if($AZ){
							/*if($AZ['type']==1){
										$get_user_id=M('article')->where(" article_id=$correlation_id")->getField('user_id');
							}else if($AZ['type']==2){
										$get_user_id=M('article_comment')->where(" comment_id=$correlation_id")->getField('user_id');
							}
							$res2=M('message')->where(" category=3 and correlation_id=".$AZ['id']." and user_id=$get_user_id")->delete(); */
							
							$res1=M('article_zan')->where(" type=$type and correlation_id=$correlation_id and user_id=$user_id")->delete();
							$res2=M('message')->where("  correlation_id=".$AZ['id'])->delete();
							
							//print_r("correlation_id=".$AZ['id']." get_user_id=$get_user_id");exit;
							//file_put_contents("qsx_".time().".txt","correlation_id=$correlation_id user_id=$user_id"); 
							if($res1 and $res2){
									$return_data = array(
										'code'      =>  40000,
										'msg'       =>  '取消点赞扬成功'
									);;
							}else{
									$return_data = array(
										'code'      =>  40010,
										'msg'       =>  '取消点赞扬失败'
									);
							}
							exit(json_encode($return_data));		
					}
					
					if($type==1){  //从文章得到发布人ID
							$get_user_id=M("article")->where("article_id=".$correlation_id)->getField('user_id');
					}else{
							$get_user_id=M("article_comment")->where("comment_id=".$correlation_id)->getField('user_id');
					}
					if(!$get_user_id){
							$return_data = array(
								'code'      =>  40002,
								'msg'       =>  '没有这个主题'
							);
							exit(json_encode($return_data));
					}
					
					$data['type']=$type;
					$data['correlation_id']=$correlation_id;
					$data['user_id']=$user_id;
					$data['add_time']=time();
				    $res=M("article_zan")->add($data);
					if(!$res){
							$return_data = array(
								'code'      =>  40010,
								'msg'       =>  '操作失败'
							);
					}else{
							//加入点赞消息
							message_add($get_user_id,$res,3);
							$return_data = array(
								'code'      =>  40000,
								'msg'       =>  '成功'
							);
					}
					exit(json_encode($return_data));
		 }
		 
		
		
		//首页
		public function send(){
			$corpid="ww7e3f8c00c3326ad6";  //企业ID
			$corpsecret="jDbXLyJeRWP3J3Lowum4Umi5m7ENVQl5GEN-_nEJPWU";
			
			$url="https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpid&corpsecret=$corpsecret";
			
			$res=vegt($url);
			$res=json_decode($res,true);
			
			$access_token=$res['access_token'];
			//print_r($access_token);
			
		
			//推送信息
			$url2="https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=$access_token";
		    $postData = array(
				'touser' => '',
				'toparty' => '2',
				'totag' => '',
				'msgtype' => 'text',
				'agentid' => '1000003',
				'text' => array(
					'content' => urlencode("寺城查看了你的企业动态,查看时间".date('Y-m-d H:i:s'))
				)
			);
			
				$data_string = urldecode(json_encode($postData));
				
				
				
				$res2=curlPost($url2,$data_string);
				
				

				$return_data = array(
						'code'      =>  40000,
						'msg'       =>  '发送成功',
						'data'       =>  $res2
				 );
				 exit(json_encode($return_data));
				
		}

		 
}