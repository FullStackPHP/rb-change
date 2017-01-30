<?php
include('lib.php');
include('header.php');

/**
登录页面
步骤：
0、接收$_POST参数，判断完整性
1、查询用户名是否存在
2、查询密码是否匹配
3、设置cookie
**/

//如果已经登录，跳转到主页
if(isLogin()!=false){
    header('location:home.php');
    exit;
}
$username = P('username');
$password = P('password');

if(!$username || !$password){
    error('请输入完整信息');
}
$r = connredis();
$userid = $r->get('user:username:'.$username.':userid');

if(!$userid){
    error('用户名不存在');
}

$realpass = $r->get('user:userid:'.$userid.':password');
if($password != $realpass){
    error('密码错误');
}

//设置cookie，登录成功
$authsecret = randsecret();
$r->set('user:userid:'.$userid.':authsecret',$authsecret);   //将随机码存入redis，用于在登录时校验
setcookie('username',$username);
setcookie('userid',$userid);
setcookie('authsecret',$authsecret);    //防止篡改cookie
//跳转到主页
header('Location:home.php');



?>
