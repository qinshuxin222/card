<?php
namespace Api\Controller;
use Think\Controller;
class CommonController extends Controller {
		 //得到会员的基本信息
		 public function get_user_base_info($user_id){
					$User=M('users')->where("user_id=".$user_id)->field('avatar_url,nickname,sex,autograph')->find();
					if(!$User){
							$return_data = array(
								'code'      =>  40001,
								'msg'       =>  '没用这个用户'
							);
							exit(json_encode($return_data));
					}
					if(!$User['autograph']){
						$User['autograph']='';
					}
					$User['count_article']=M('article')->where("user_id=".$user_id)->count();   //文章数
					//$User['count_zan']=M('article_zan')->where(" user_id=".$user_id)->count();  //我点的，有错
					
					//计算文章点赞数
					$subQuery1=M('article')->where(" user_id=".$user_id)->field('article_id')->buildsql();  //本人发的动态
					$count_zan1=M('article_zan')->where(" type=1 and correlation_id in ($subQuery1)")->count();
					
					//计算评论和回复的点赞数
					$subQuery2=M('article_comment')->where(" user_id=".$user_id)->field('comment_id')->buildsql();  //本人发布的评论和回复
					$count_zan2=M('article_zan')->where(" type=2 and correlation_id in ($subQuery2)")->count();
					$User['count_zan']=$count_zan1+$count_zan2;
					
					$User['count_click']=M('article')->where(" user_id=".$user_id)->sum('click');
					$return_data = array(
						'code'      =>  40000,
						'msg'       =>  '成功',
						'data'      =>  $User
					);

					return $return_data;
		 }
		 
		//文章列表 
		public function article_list($longitude,$latitude,$range=3000,$page=1,$pagesize=10,$user_id=0){
					$sql=" is_open=1 ";
					
					$count=M('article')->where($sql)->count();
					$list=M('article')
							->where($sql)
							->order("article_id desc")
							->limit($pagesize)
							->page($page)
							->select();
					if(!$list){
						 $return_data = array(
								'code'      =>  40001,
								'msg'       =>  '暂无数据',
								'data'      =>  ''
							);
						 return $list;
					}
					$film_type=array('mp4','mpg');
					foreach($list as &$row){
								$row['add_time'] = time_tran(date('Y-m-d H:i:s',$row['add_time']));
								//$row['add_time'] = time_tran(date('Y-m-d H:i:s',$row['add_time'])).'article_id='.$row['article_id'].'='.$row['add_time'];
								$from=array($longitude,$latitude);
								$to=array($row['longitude'],$row['latitude']);
								$str_from=$longitude.','.$latitude;
								$str_to=$row['longitude'].','.$row['latitude'];
								$get_range=get_distance($from,$to);  //调用接口
								
								$row['get_range']=$get_range;
								
								if($range and $longitude){
									$row['range']=get_juli($get_range);
								}
								$row['count_zan']=M('article_zan')->where("type=1 and correlation_id=".$row['article_id'])->count();//点赞数量
								$row['count_comment']=M('article_comment')->where(" article_id=".$row['article_id'])->count();	//评论ID
								
								$User=M('users')->where("user_id=".$row['user_id'])->field('avatar_url,nickname')->find();
								$row['User']=$User;
								if($this->click($user_id,$row['article_id'],1)){
									$row['is_click']=1;
								}else{
									$row['is_click']=0;
								}
								$row['start_play']=0;
								//$row['content']=filterEmoji($row['content']);
								$row['comment_images']=M('article_images')->where(" article_id=".$row['article_id'])->order(" id desc ")->select();
								if($row['comment_images']){ 
									foreach($row['comment_images'] as $key=>&$r){ 
										if(in_array(get_extension($r['image_url']),$film_type)){
											$file_type=3;
										}else{
											$file_type=2;
										}
										$r['image_url'] = "https://".$_SERVER['HTTP_HOST'].$r['image_url'];
										$r['file_type']=$file_type;
									}
								}
								if($get_range<=$range){  //is_numeric($get_range) and
									$new_data[]=$row;
								}
									
					}
					
					$sort = array(
							 'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
							 'field'     => 'article_id',       //排序字段
					 );
					 $arrSort = array();
					 $i=0;
					 foreach($new_data AS $uniqid => $row){
						 foreach($row AS $key=>$value){
							 $arrSort[$key][$uniqid] = $value;
						 }
						 $i++;
					 }
					 if($sort['direction']){
						 array_multisort($arrSort[$sort['field']], constant($sort['direction']), $new_data);
					 }
 


					//print_r($new_data);exit;
					$return_data = array(
						'code'      =>  40000,
						'msg'       =>  '成功',
						'total'      =>  $i,
						'data'      =>  $new_data
					);

					return $return_data;
		 }
		 
		 
		 
		 //我的文章和他的文章 
		public function my_article($user_id,$page=1,$pagesize=10){
					$sql=" 1=1 ";
					$user_id=intval($user_id);
					if($user_id){
						$sql.=" and user_id=$user_id";
					}
					$count=M('article')->where($sql)->count();
					$list=M('article')
							->where($sql)
							->order("article_id desc")
							->limit($pagesize)
							->page($page)
							->select();
					if(!$list){
						 $return_data = array(
								'code'      =>  40001,
								'msg'       =>  '暂无数据',
								'data'      =>  ''
							);
						 return $list;
					}
					$film_type=array('mp4','mpg');
					foreach($list as &$row){
								$row['add_time'] = time_tran(date('Y-m-d H:i:s',$row['add_time']));
								$row['count_zan']=M('article_zan')->where("type=1 and correlation_id=".$row['article_id'])->count();//点赞数量
								$row['count_comment']=M('article_comment')->where(" article_id=".$row['article_id'])->count();	//评论ID
								
								$User=M('users')->where("user_id=".$row['user_id'])->field('avatar_url,nickname')->find();
								$row['User']=$User;
								if($this->click($user_id,$row['article_id'],1)){
									$row['is_click']=1;
								}else{
									$row['is_click']=0;
								}
								$row['comment_images']=M('article_images')->where(" article_id=".$row['article_id'])->order(" id desc ")->select();
								if($row['comment_images']){ 
									foreach($row['comment_images'] as $key=>&$r){ 
										if(in_array(get_extension($r['image_url']),$film_type)){
											$file_type=3;
										}else{
											$file_type=2;
										}
										$r['image_url'] = "https://".$_SERVER['HTTP_HOST'].$r['image_url'];
										$r['file_type']=$file_type;
									}
								}	
					}
					
					$return_data = array(
						'code'      =>  40000,
						'msg'       =>  '成功',
						'total'      =>  $count,
						'data'      =>  $list
					);

					return $return_data;
		 }
		 //文章详情
		 public function article_view($article_id,$user_id=0,$longitude=0,$latitude=0){
					$sql=" 1=1 and article_id=$article_id";
					$info=M('article')->where($sql)->find();
					if(!$info){
						 $return_data = array(
								'code'      =>  40001,
								'msg'       =>  '暂无数据',
								'data'      =>  ''
							);
						 return $return_data;
					}
					//增加围观数量
					M('article')->where($sql)->setInc('click',1);
					$info['add_time'] = time_tran(date('Y-m-d H:i:s',$info['add_time']));
					$info['count_zan']=M('article_zan')->where("type=1 and correlation_id=".$article_id)->count();//点赞数量
					$info['count_comment']=M('article_comment')->where("  article_id=".$article_id)->count();	//评论ID
					
					
					$from=array($longitude,$latitude);
					$to=array($info['longitude'],$info['latitude']);
					
					$get_range=get_distance($from,$to);  //调用接口
					$info['get_range']=$get_range;		
					if($range and $longitude){
						$info['range']=get_juli($get_range);
					}
					
					if($this->click($user_id,$info['article_id'],1)){
						$info['is_click']=1;
					}else{
						$info['is_click']=0;
					}
					$film_type=array('mp4','mpg');
					$info['comment_images']=M('article_images')->where(" article_id=".$info['article_id'])->order(" id desc ")->select();
					if($info['comment_images']){ 
						foreach($info['comment_images'] as $key=>&$r){ 
							if(in_array(get_extension($r['image_url']),$film_type)){
								$file_type=3;
							}else{
								$file_type=2;
							}
							$r['image_url'] = "https://".$_SERVER['HTTP_HOST'].$r['image_url'];
							$r['file_type']=$file_type;
						}
					}
					
					$User=M('users')->where("user_id=".$info['user_id'])->field('avatar_url,nickname')->find();
					$info['User']=$User;
					$info['article_comment']=M("article_comment")->where(" article_id=".$info['article_id'])->order("comment_id desc")->select();  //评论列表
					
					if($info['article_comment']){
							foreach($info['article_comment'] as &$row){
								$row['count_zan']=M('article_zan')->where("type=2 and correlation_id=".$row['comment_id'])->count();//点赞数量
								$User=M('users')->where("user_id=".$row['user_id'])->find();
								$row['User']=$User;
								if($this->click($user_id,$row['comment_id'],2)){
									$row['is_click']=1;
								}else{
									$row['is_click']=0;
								}
								$row['add_time']=date('Y-m-d H:i:s',$row['add_time']);
								if($row['user_id_to']){
									$row['User2']=M('users')->where("user_id=".$row['user_id_to'])->find();
									
									$AC2=M("article_comment")->where(" comment_id=".$row['parent_id'])->find();
									$row['Content2']=$AC2['content'];
								}
							}
					}
					return $info;
					
		 }
		 
		 
	
		 
		 
		  //是否已经点击
		 public function click($user_id,$correlation_id,$type=1){
				$is_click=M("article_zan")->where("user_id=$user_id and correlation_id=$correlation_id and type=$type")->count();
				return $is_click;
		 }
		 
	



		/**
		 * 删除文件及相关数据
		*/
		function del_article(){
					$user_id=I("param.user_id",0,intval);
					$article_id=I("param.id",0,intval);
					$map=" user_id=$user_id and article_id=$article_id ";
					
					//echo 111;exit;
					$User=M('users')->where("user_id=".$user_id)->find();
					if(!$User){
							$return_data = array(
								'code'      =>  40001,
								'msg'       =>  '没用这个用户'
							);
							exit(json_encode($return_data));
					}

					$Ar=M('article')->where($map)->find();
					if(!$Ar){
							$return_data = array(
								'code'      =>  40002,
								'msg'       =>  '没有这个文章'
							);
							exit(json_encode($return_data));
					}
					
					
					
					$res1=M('article')->where($map)->delete();		//删除文章
					$res2=M('article_comment')->where("article_id=$article_id ")->delete(); //删除评论和回复
					$res3=M('article_zan')->where("type=1  and correlation_id=$article_id ")->delete();  //删除点赞
					$res4=M('message')->where("article_id=$article_id ")->delete();
					
					$this->del_article_images($user_id,$article_id);  //删除相关附件
					if($res1){
							$return_data = array(
								'code'      =>  40000,
								'msg'       =>  '成功'
							);
					}else{
							$return_data = array(
								'code'      =>  40010,
								'msg'       =>  '失败'
							);
					}
					exit(json_encode($return_data));
		}


		/**
		 * 删除文件及相关数据
		*/
		function del_article_images($user_id,$article_id){
					$map=" user_id=$user_id and article_id=$article_id ";
					
					
					
					$list=M('article_images')->where($map)->select();
					if($list){
							foreach($list as &$row){
								  unlink('.'.M('article_images')->where(" id=".$row['id'])->getField("image_url"));
							}
							$res=M('article_images')->where($map)->delete();
					}
		}

		

		//得到省
		function get_province(){
					$list=M('province')->field('provinceID,province')->order("id asc")->select();
					
					$return_data = array(
						'code'      =>  40000,
						'msg'       =>  '成功',
						'data'       =>  $list
					);
					
					exit(json_encode($return_data));
		}

		//得到城市
		function get_city($father=0){
					$map=" 1=1 ";
					if($father){
							$map.=" and father='$father'";
					}
					$list=M('city')->where($map)->field('cityID,city')->order("id asc")->select();
					
					$return_data = array(
						'code'      =>  40000,
						'msg'       =>  '成功',
						'data'       =>  $list
					);
					
					exit(json_encode($return_data));
		}

		//得到区域
		function get_area($father=0){
					$map=" 1=1 ";
					if($father){
							$map.=" and father='$father'";
					}
					$list=M('area')->where($map)->field('areaID,area')->order("id asc")->select();
					
					$return_data = array(
						'code'      =>  40000,
						'msg'       =>  '成功',
						'data'       =>  $list
					);
					
					exit(json_encode($return_data));
		}


	
		 
}