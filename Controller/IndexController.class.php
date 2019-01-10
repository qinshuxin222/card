<?php
namespace Card\Controller;
use Think\Controller;
class IndexController extends BaseController {
		 //得到名片
		 public function index(){
					$user_id=I("param.user_id",0,intval);
					$page=I("param.page",1,intval);  //默认第几页
					$pagesize=I("param.pagesize",10,intval);  //每页条数

					$sql="  b.status=1 and bw.status=1 and card.status=1 and  b.id=".session("business.id");
					if($user_id){
						$sql.=" and card.user_id=$user_id ";
					}else{
						$sql.=" and card.is_default=$user_id ";
					}
					$count=M('card')->alias('card')
							->join("wlyy_business_worker bw on bw.id=card.worker_id")
							->join("wlyy_business b on b.id=bw.business_id")
							->where($sql)
							->count();
							

					$list=M('card')->alias('card')
							->join("wlyy_business_worker bw on bw.id=card.worker_id")
							->join("wlyy_business b on b.id=bw.business_id")
							->where($sql)
							->field("b.*,bw.*,card.is_top,card.status")
							->order("card.is_top desc,card.id desc")
							->limit($pagesize)
							->page($page)
							->select();
					if(!$list){
						 //foreach($list as &$row){
							//$row['business_name']=M("business")->where("id=".$row['business_id'])->getField("business_name");
						 //}
						 $return_data = array(
								'code'      =>  40010,
								'msg'       =>  '暂无数据'
							);
					}else{
						$return_data = array(
								'code'      =>  40000,
								'msg'       =>  '成功',
								'count'       =>  $count,
								'data'      =>  $list
						 );
					}
					exit(json_encode($return_data));
		 }
		 
		 //名片详细
		 public function info(){
					$user_id=I("param.user_id",0,intval);
					$card_id=I("param.card_id",0,intval);

					$sql="  card.id=$card_id and card.status=1  and  bw.business_id=".session("business.id");
		
					$info=M('card')->alias('card')
							->join("wlyy_business_worker bw on bw.id=card.worker_id")
							->join("wlyy_business b on b.id=bw.business_id")
							->where($sql)
							->field("bw.*,card.is_top,card.status")
							->find();
					if(!$info){
						 $return_data = array(
								'code'      =>  40010,
								'msg'       =>  '暂无数据'
							);
					}else{
						$common = new CommonController();	
						//1名片分享
						$share_card=$common->share_card($user_id,$card_id);
						$info['share_card']=$share_card['data'];	
						//2 产品列表
						$product_list=$common->product_list($user_id,$card_id,'',1,10);
						$info['product_list']=$product_list['data'];
						//3访问记录
						$info['visit_list']=M("card_visit")->alias("cv")
							->join("wlyy_users u on u.user_id=cv.user_id")
							->where("cv.card_id=$card_id")
							->order("cv.id desc")
							->field("u.*")
							->select();
							
						//4个人简介	
						$voice=M("business_worker_data")->where("type=2 and worker_id=".$info['id'])->find();
						$tag_list=M("business_worker_data")->where("type=2 and worker_id=".$info['id'])->order(" id desc")->select();
						$info['summary']['voice']=$voice['content'];  //声音
						$info['summary']['introduce']=$info['introduce'];
						$info['summary']['tag_list']=$tag_list;
						
						//5我的视频
						$video=M("business_worker_data")->where("type=3 and worker_id=".$info['id'])->find();
						$info['video']=$video['content'];
						
						//6我的图片
						$image_list=M("business_worker_data")->where("type=1 and worker_id=".$info['id'])->order(" id desc")->select();
						$info['image_list']=$image_list;
						
						$return_data = array(
								'code'      =>  40000,
								'msg'       =>  '成功',
								'data'      =>  $info
						 );
					}
					exit(json_encode($return_data));
		 }
		 
}