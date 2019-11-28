<?php
session_start();
require('dbconnect.php');

if(empty($_REQUEST['id'])){
  header('Location: index.php');
  exit();
}
$posts = $db->prepare('SELECT m.name, p. * FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
$posts->execute(array($_REQUEST['id']));
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>掲示板</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <h1>掲示板</h1>
  <p>&laquo;<a href="index.php">戻る</a></p>

  <?php if ($post = $posts->fetch()): ?>
    <?php if($post['picture'] !== ''): ?>
      <img src="<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>" width="100" height="100" alt="" />
    <?php endif; ?>
    <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?>(<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>)</p>
    <p><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></p>
  <?php else: ?>
	  <p>その投稿は削除されたか、URLが間違えています</p>
  <?php endif; ?>
</body>
</html>
