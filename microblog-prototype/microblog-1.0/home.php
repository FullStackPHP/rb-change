<?php
include('./lib.php');
include('./header.php');

if(($user = isLogin()) == false){
    header('location:index.php');
    exit;
}
$r = connredis();
//取出自己发的和粉主推送过来的信息
$r->ltrim('recivepost:'.$user['userid'],0,49);   //接收前50条信息
/**
$newpost = $r->sort('receivepost:'.$user['userid'],array('sort'=>'desc','get'=>'post:postid:*:content'));   //排序
**/

$newpost = $r->sort('receivepost:'.$user['userid'],array('sort'=>'desc'));   //排序
//循环取出数据

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
