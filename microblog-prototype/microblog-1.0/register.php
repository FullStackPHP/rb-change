<?php
/*
设计user表--对应的key规则


注册用户
set user:userid:1: username zhangsan
set user:userid:1: password 123456

用于登录时的用户名查询
set user:username:zhangsan: userid 1

userid生成
incr global:userid

具体步骤：
0：接收$_POST参数，判断用户名/密码是否合法
1：连接redis，查询该用户名，判断是否存在
2：写入redis
3；登录操作
*/
include('lib.php');
include('header.php');

if(isLogin()!=false){
    header('location:home.php');
    exit;
}

$username  = P('username');
$password  = P('password');
$password2 = P('password2');
if(!$username || !$password || !$password2){
    error('请输入完整注册信息');
}

//判断密码是否一致
if($password != $password2){
    error('两次输入的密码不一致');
}

//连接redis
$r = connredis();

//查询用户名是否已经注册
if($r->get('user:username:' . $username . ':userid')){
    error('用户名已被注册，请更换');
}

//获取userid
$userid = $r->incr('global:userid');
//写入注册信息
$r->set('user:userid:'.$userid.':username',$username);
$r->set('user:userid:'.$userid.':password',$password);
//单独存用户id
$r->set('user:username:'.$username.':userid',$userid);


//通过一个链表，维护50个最新的userid
$r->lpush('newuserlink',$userid);
$r->ltrim('newuserlink',0,49);    //截取link表，保持最新的前50个

include('footer.php');







?>
