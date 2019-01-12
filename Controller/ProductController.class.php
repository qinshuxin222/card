<?php
namespace Card\Controller;
use Think\Controller;
class ProductController extends BaseController {
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
				$this->card_id=$card_id;
				$this->user_id=$card_info['user_id'];
				$this->worker_id=$card_info['worker_id'];
		 }
		 
		 //得到产品
		 public function index(){
					$page=I("param.page",1,intval);  //默认第几页
					$pagesize=I("param.pagesize",10,intval);  //每页条数
					
					$common = new CommonController();	
					$return_data=$common->product_list($this->user_id,$this->card_id,'',$page,$pagesize);
					
					exit(json_encode($return_data));
		 }
		 
		 
		 //得到产品详情
		 public function detail(){
					$id=I("param.id",0,intval);
					$product_info=M("business_product")->where("id=$id")->find();
					if(!$product_info){
							$return_data = array(
										'code'      =>  40001,
										'msg'       =>  '没有这个产品',
							);
							exit(json_encode($return_data));
					}
					$return_data = array(
									'code'      =>  40001,
									'msg'       =>  '没有这个名片',
									'data'       => $product_info
					);

					
					exit(json_encode($return_data));
		 }
		 
		 
}