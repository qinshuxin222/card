<?php
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



