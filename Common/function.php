<?php
function get_card_log_title($cat_id,$nickname,$product_name='',$count=0) {
				if($cat_id==1){  
					 $remark=$nickname."正在查看".$product_name.",尽快把握商机";
				}else if($cat_id==2){
					 $remark=$nickname."查看了你公司的官网,看来Ta对公司感兴趣";
				}else if($cat_id==3){
					 $remark=$nickname."复制了你的微信，留意微信新朋友消息";
				}else if($cat_id==4){
					 $remark=$nickname."转发了你的名片，你的人脉圈正在裂变";
				}else if($cat_id==5){
					 $remark=$nickname."查看你的名片第".$count."次,成交在望";
				}else if($cat_id==6){
					 $remark=$nickname."查看了你的企业动态";
				}else if($cat_id==7){
					 $remark=$nickname."向你咨询";
				}else if($cat_id==8){
					 $remark=$nickname."保存了你的电话，可以考虑拜访";
				}else if($cat_id==9){
					 $remark=$nickname."觉得你非常靠谱";
				}else if($cat_id==10){
					 $remark=$nickname."拨打你的手机";
				}else if($cat_id==11){
					 $remark=$nickname."耐心听完了你的语音介绍，快联系Ta吧";
				}else if($cat_id==12){
					 $remark=$nickname."复制了你的邮箱";
				}
				
				return $remark;
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


  /**
     * 请求过程中因为编码原因+号变成了空格
     * 需要用下面的方法转换回来
     */
    function define_str_replace($data){
        return str_replace(' ','+',$data);
    }
	
