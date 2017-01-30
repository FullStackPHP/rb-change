<?php

include('./lib.php');
include('./header.php');

if(($user = isLogin())==false){
    header('Location:index.php');
    exit;
}

/*
思路：
每人有自己的粉丝记录，使用集合记录 set
每人有自己的关注记录，使用集合，set

aid 关注 bid
表字段
following:aid(bid)  关注列表
follower:bid(aid)   粉丝表
*/

/*
0、获取用户名
1、查询id
2、查询此id，是否在following集合中
*/
//粉主的username


$uid = G('uid');
$f = G('f');

/**
1、判断uid “f”是否合法
2、uid是否是自己的
**/

$r = connredis();
if($f == 1){
    //关注
    $r->sadd('following:'.$user['userid'],$uid);
    $r->sadd('follower:'.$uid,$user['userid']);      //对方被我关注
}else{
    //取消关注
    $r->srem('following:'.$user['userid'],$uid);
    $r->srem('follower:'.$uid,$user['userid']);
}
$uname = $r->get('user:userid:'.$uid.':username');  //对方的用户名

header('Location:profile.php?u='.$uname);

include('footer.php');
?>
