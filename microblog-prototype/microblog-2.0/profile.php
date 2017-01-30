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
following:aid(bid)
follower:bid(aid)
*/

/*
0、获取用户名
1、查询id
2、查询此id，是否在following集合中
*/
//粉主的username

$r = connredis();

$u = G('u');
//获取当前粉主的uid
$prouid = $r->get('user:username:'.$u.':userid');
if(!$prouid){
    error('非法用户');
    exit;
}
//判断集合中是否有此用户
$isf = $r->sismember('following:'.$user['userid'],$prouid);
$isfstatus = $isf ? '0' : '1';
$isfword = $isf ? '取消关注' : '关注ta';






?>
<div id="navbar">
<a href="index.php">主页</a>
| <a href="timeline.php">热点</a>
| <a href="logout.php">退出</a>
</div>
</div>
<h2 class="username">test</h2>
<a href="follow.php?uid=<?php echo $prouid; ?>&f=<?php echo $isfstatus; ?>" class="button"><?php echo $isfword; ?></a>

<div class="post">
<a class="username" href="profile.php?u=test">test</a> 
world<br>
<i>11 分钟前 通过 web发布</i>
</div>

<div class="post">
<a class="username" href="profile.php?u=test">test</a>
hello<br>
<i>22 分钟前 通过 web发布</i>
</div>

<?php

include('./footer.php');

?>
