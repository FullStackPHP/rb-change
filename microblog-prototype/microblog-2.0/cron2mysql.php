<?php
/**
冷热数据交换
**/
include('/var/www/html/weibo-2.0/lib.php');

$r = connredis();

/**连接mysql**/
$link = mysqli_connect('127.0.0.1','root','','test');
if(!$link){
    printf("Can't connnect to MySQL Server . Errorcode: %s ",mysqli_connect_error());
    exit;
}
$link->set_charset('utf8');

while($r->llen('global:store') >= 1000){
    $sql = 'insert into post(postid,userid,username,ptime,content) values ';
    $i = 0;
    while($i++<1000){
        $postid = $r->rpop('global:store');  //将最后一个弹出
        $post = $r->hmget('post:postid:'.$postid,array('userid','username','time','content'));
        $sql .= "($postid," . $post['userid'] .",'" . $post['username'] ."'," .$post['time'] . ",'" . $post['content']."'),";
}
    $sql = substr($sql,0,-1);  //截取最后的逗号
    $link->query($sql);
}
echo 'ok';

/**
if($i == 0){
   echo 'no job';
   exit;
}
**/

?>
