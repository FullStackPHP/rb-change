<?php
include('./lib.php');
include('./header.php');

/**
incr global:postid   自增id
set post:postid:$postid:time timestamp
set post:postid:$postid:content $content
set post:postid:$postid:userid $userid
思路：
0、判断是否登录
1、接收post内容
2、set redis
**/
$content = P('status');
if(!$content){
    error('请填写内容');
}
if( ($user = isLogin()) == false ){
    header('Location:index.php');
    exit;
}

$r = connredis();
$postid = $r->incr('global:postid');
/**
$r->set('post:postid:'.$postid.':userid',$user['userid']);
$r->set('post:postid:'.$postid.':time',time());
$r->set('post:postid:'.$postid.':content',$content);
**/
//使用哈希存储数据
$r->hmset('post:postid:'.$postid,array('userid'=>$user['userid'],'username'=>$user['username'] ,'time'=>time(),'content'=>$content));
/**
//把微博推给自己的粉丝
$fans = $r->smembers('follower:'.$user['userid']);
//print_r($fans);exit;
$fans[] = $user['userid'];   //粉丝团包括自己
foreach($fans as $fansid){
   $r->lpush('receivepost:'.$fansid,$postid);   //将推送文章另存储一张表
}**/

//把自己发的微博维护在一个有序集合里,只要前20个，用于向粉丝展示
$r->zadd('starpost:userid:'.$user['userid'],$postid,$postid);
if($r->zcard('starpost:userid:'.$user['userid']) > 20){
    $r->zremrangebyrank('starpost:userid:'.$user['userid'],0,0);  //将旧的微博删除，剩下前20条
}

//把自己的微博id，放到一个链表里，1000个，供自己浏览
//1000个的旧微博，放到mysql中
$r->lpush('mypost:userid:'.$user['userid'],$postid);
if($r->llen('mypost:userid:'.$user['userid']) > 100){
    $r->rpoplpush('mypost:userid:'.$user['userid'],'global:store');
}

header('Location:home.php');
exit();


include('./footer.php');


?>
