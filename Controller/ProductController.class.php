<?php
namespace Card\Controller;
use Think\Controller;
class ProductController extends BaseController {
		 //得到名片
		 public function index(){
					$user_id=I("param.user_id",0,intval);
					$card_id=I("param.card_id",0,intval);
					$page=I("param.page",1,intval);  //默认第几页
					$pagesize=I("param.pagesize",10,intval);  //每页条数
					
					$card_info=M("card")->where("id=$card_id")->find();
					if(!$card_info){
							$return_data = array(
								'code'      =>  40001,
								'msg'       =>  '没有这个名片'
							);
							exit(json_encode($return_data));
					}
					$sql=" is_on_sale=1 ";
					$sql.=" and  business_id=".session("business.id")." or worker_id=".$card_info['worker_id'];
					
					$count=M('business_product')
							->where($sql)
							->count();
							

					$list=M('business_product')
							->where($sql)
							->order("id desc")
							->limit($pagesize)
							->page($page)
							->select();
					if(!$list){
						 $return_data = array(
								'code'      =>  40010,
								'msg'       =>  '暂无数据'
							);
					}else{
						$return_data = array(
								'code'      =>  40000,
								'msg'       =>  '成功',
								'count'      =>  $count,
								'data'      =>  $list
						 );
					}
					exit(json_encode($return_data));
		 }
		 
		 
}