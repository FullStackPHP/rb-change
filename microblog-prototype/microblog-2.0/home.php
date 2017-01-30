<?php
include('./lib.php');
include('./header.php');

if(($user = isLogin()) == false){
    header('location:index.php');
    exit;
}
$r = connredis();
//取出自己发的和粉主推送过来的信息
/**
$r->ltrim('recivepost:'.$user['userid'],0,49);   //接收前50条信息
$newpost = $r->sort('receivepost:'.$user['userid'],array('sort'=>'desc','get'=>'post:postid:*:content'));   //排序
**/
/**拉取微博**/
//1、获取自己关注的人
$star = $r->smembers('following:'.$user['userid']);
$star[] = $user['userid'];   //将自己压进去

$lastpull = $r->get('lastpull:userid:'.$user['userid']);
if(!$lastpull){
    $lastpull = 0;
}

//循环取出数据
//2、拉取最新数据
$latest = array();
foreach($star as $s){
 $latest = array_merge($latest,$r->zrangebyscore('starpost:userid:'.$s,$lastpull+1,1<<32-1));   
}
sort($latest,SORT_NUMERIC);   //排序
//更新lastpull
if(!empty($latest)){
    $r->set('lastpull:userid:'.$user['userid'],end($latest));
}
//循环把$latest放到自己主页应该收取的微博链接里
foreach($latest as $l){
    $r->lpush('receivepost:'.$user['userid'],$l);
}
//保持个人主页，最多收取1000条微博
$r->ltrim('receivepost:'.$user['userid'],0,999);

$newpost = $r->sort('receivepost:'.$user['userid'],array('sort'=>'desc'));   //排序

//计算粉丝量和关注量
$myfans = $r->sCard('follower:'.$user['userid']);
$myfoucs = $r->sCard('following:'.$user['userid']);

?>
<div id="navbar">
<a href="index.php">主页</a>
| <a href="timeline.php">热点</a>
| <a href="logout.php">退出</a>
</div>
</div>
<div id="postform">
<form method="POST" action="post.php">
<?php echo $user['username'] ?>, 有啥感想?
<br>
<table>
<tr><td><textarea cols="70" rows="3" name="status"></textarea></td></tr>
<tr><td align="right"><input type="submit" name="doit" value="Update"></td></tr>
</table>
</form>
<div id="homeinfobox">
<?php echo $myfans; ?> 粉丝<br>
<?php echo $myfoucs; ?> 关注<br>
</div>
</div>
<?php
 foreach($newpost as $postid){ 
    $p = $r->hmget('post:postid:'.$postid,array('userid','username','time','content'));     
?>
<div class="post">
<a class="username" href="profile.php?u=test"><?php echo $p['username'] ?></a><?php echo $p['content']; ?><br>
<i><?php echo formattime($p['time']) ?> 前 通过 web发布</i>
</div>
<?php } ?>
<?php
include('footer.php');
?>
