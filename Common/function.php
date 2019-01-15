<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

//微信小程序计算两点间的距离
/**
 * 计算两点地理坐标之间的距离
 * @param  Decimal $longitude1 起点经度
 * @param  Decimal $latitude1  起点纬度
 * @param  Decimal $longitude2 终点经度 
 * @param  Decimal $latitude2  终点纬度
 * @param  Int     $unit       单位 1:米 2:公里
 * @param  Int     $decimal    精度 保留小数位数
 * @return Decimal
 */
function get_distance111($from, $to, $unit=1, $decimal=0){
	$longitude1=$from[0];
	$latitude1=$from[1];
	
	$longitude2=$to[0];
	$latitude2=$to[1];
	
    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI /180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if($unit==2){
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);

}



/** 
 * 根据起点坐标和终点坐标测距离[http://lbs.qq.com/webservice_v1/guide-distance.html] 
 * @param  [array]   $from [起点坐标(经纬度),例如:array(118.012951,36.810024)] 
 * @param  [array]   $to [终点坐标(经纬度)] 
 * @return [string]  距离数值 
 
 状态码，0为正常,
310请求参数信息有误，
311Key格式错误,
306请求有护持信息请检查字符串,
110请求来源未被授权

120  每秒5次
121此key每秒请求量已达到上限

 */  
function get_distance($from,$to){
	//return get_distance111($from,$to);exit;
	$from2 = $from;
	$to2 = $to;
    sort($from);  
    sort($to);  
    
    $from = implode(",", $from);  
    $to = implode(",", $to);
	
	//今天的数据  删除一个三天前的数据 
	$endToday = mktime(0,0,0,date('m'),date('d')-3,date('Y'));  
	$res1=M("cache_distance")->where("add_time<=$endToday or (status>0 and status!=373)")->delete();
	
	if($from==',' or $from=='0,0'){
				$aa='';
	}else{
				//echo $res1;exit;
				$CD=M("cache_distance")->where(" str_from='$from' and str_to='$to' and (status=0 or status=373)")->find();
				if($CD){
						$aa=$CD['distance'];
				}else{
						//driving（驾车）、walking（步行）
					/*	$curl = 'http://apis.map.qq.com/ws/distance/v1/?mode=driving&from='.$from.'&to='.$to.'&key=XI5BZ-AG2K6-VLESZ-EMBY2-DROYS-ZJBWH';  
						$content = vegt($curl);  
						$result = json_decode($content,true);  
						if($result['status']==0){
								$aa=$result['result']['elements'][0]['distance'];	//少于5km
						}else if($result['status']==373){
								$aa=11000;   //超长距离 十公里，不显示
						}else{
								$aa=$result['message'];
						} */
						$aa=get_distance111($from2,$to2);
						//if($from!=',' or $from!='0,0'){
							$article_id=M('article')->where(" longitude='".$to2[0]."' and latitude='".$to2[1]."'")->getField('article_id');
							$data['str_from']=$from;
							$data['str_to']=$to;
							$data['distance']=$aa;
							$data['status']=$result['status'];
							$data['message']=$result['message']." article_id=$article_id from_user_id=".I('user_id');
							$data['add_time']=time();
							$res=M("cache_distance")->add($data);
						//}			
				}
	}
	//print_r($result);exit;
	return $aa;
} 





 //消息,保存到消息表
function message_add($user_id,$correlation_id,$category=1){
		if($category==1 or $category==2){
				$article_id=M('article_comment')->where("comment_id=$correlation_id")->getField('article_id');
		}else if($category==3){
				$AZ=M('article_zan')->where("id=$correlation_id")->find();  
				if($AZ['type']==1){  //如果点赞的是动态则直接读取关联ID
						$article_id=$AZ['correlation_id'];
				}else{
						$article_id=M('article_comment')->where("comment_id=$correlation_id")->getField('article_id');
				}	
		}
		$data['category']=$category;
		$data['correlation_id']=$correlation_id;
		$data['user_id']=$user_id;
		$data['add_time']=time();
		$data['article_id']=$article_id;
		$res=M('message')->add($data);
		if($res){
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
		return $return_data;
 }
 
//得到距离
function get_juli($range){  
		$return_data=$range."米";
		if($range>=500 and $range<=5000){
			$return_data=round($range/1000,1)."km";
		}else if($range>5000){
			$return_data=">5km";
		}else if($range<100){	
			$return_data="<100米";
		}else if($range<200){	
			$return_data="<200米";
		}else if($range<300){	
			$return_data="<300米";
		}else if($range<400){	
			$return_data="<400米";
		}else if($range<500){	
			$return_data="<500米";
		}
		//$return_data=$range."米";
		return $return_data;
}


function time_tran($the_time) {
	date_default_timezone_set("Asia/Shanghai");   //设置时区
    $now_time = date("Y-m-d H:i:s", time());  
    //echo $now_time;  
    $now_time = strtotime($now_time);  
    $show_time = strtotime($the_time);  
    $dur = $now_time - $show_time;  
    
        if ($dur < 60) {  
            return  '刚刚';  
        } else {  
            if ($dur < 3600) {  
                return floor($dur / 60) . '分钟前';  
            } else {
				//php获取今日开始时间戳和结束时间戳
				$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
				$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                if (($show_time>=$beginToday) and ($show_time<=$endToday)) {  
                    return '今天 '.date('H:i',$show_time);  
                } else {  
                    return date('m月d日 H:i',$show_time);  
                }  
            }  
        }   
}  



//得到后缀名
function get_extension($file){ 
	return end(explode('.', $file)); 
}

//删除沉余图片,超过三天的,没文章相关的	
function over(){ 
			$endToday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
				
			$map=" article_id=0 and add_time<=$endToday";
			$list=M('article_images')->where($map)->select();
			if($list){
					foreach($list as &$row){
						  unlink('.'.M('article_images')->where(" id=".$row['id'])->getField("image_url"));
						  //echo $row['id']."<br>";
					}
					$res=M('article_images')->where($map)->delete();
			}
}


/**
     * Curl Post数据
     * @param string $url 接收数据的api
     * @param string $vars 提交的数据
     * @param int $second 要求程序必须在$second秒内完成,负责到$second秒后放到后台执行
     * @return string or boolean 成功且对方有返回值则返回
     */
    function curlPost($url, $vars, $second=30)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
//            'Content-Type: application/json; charset=utf-8',  
//            'Content-Length: ' . strlen($vars))  
//        ); 
        $data = curl_exec($ch);
        curl_close($ch);
        if($data)
            return $data;
        return false;
    }



