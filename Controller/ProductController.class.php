<?php
namespace Card\Controller;
use Think\Controller;
class ProductController extends BaseController {
		 //得到产品
		 public function index(){
					$user_id=I("param.user_id",0,intval);
					$card_id=I("param.card_id",0,intval);
					$page=I("param.page",1,intval);  //默认第几页
					$pagesize=I("param.pagesize",10,intval);  //每页条数
					
					$common = new CommonController();	
					$return_data=$common->product_list($user_id,$card_id,'',$page,$pagesize);
					
					exit(json_encode($return_data));
		 }
		 
		 
}