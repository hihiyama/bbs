<?php
session_start();
require('dbconnect.php');

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()){
  $_SESSION['time'] = time();

  $members = $db->prepare('SELECT * FROM members WHERE id=?');
  $members->execute(array($_SESSION['id']));
  $member = $members->fetch();
}else{
  header('Location: login.php');
  exit();
}

if(!empty($_POST)){
  
  if($_POST['message'] !== '' && $_POST['image'] == ''){
      $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_message_id=?, created=NOW()');
      $message->execute(array(
      $member['id'],
      $_POST['message'],
      $_POST['reply_post_id']
      ));
  }elseif($_POST['message'] == '' && $_POST['image'] !== ''){
      $ext = pathinfo($_FILES['image']['name']);
      $perm = ['gif', 'jpg', 'png'];
      if($_FILES['image']['error'] !== UPLOAD_ERR_OK){
          $msg = [
              UPLOAD_ERR_INI_SIZE => 'upload_max_filesizeの制限を超えています',
              UPLOAD_ERR_FORM_SIZE => 'MAX_FILE_SIZEの制限を超えています',
              UPLOAD_ERR_PARTIAL => 'ファイルが一部しかアップロードされていません',
              UPLOAD_ERR_NO_FILE => 'ファイルはアップロードされていません',
              UPLOAD_ERR_NO_TMP_DIR => '一時保存フォルダが存在しません',
              UPLOAD_ERR_CANT_WRITE => 'ディスクへの書き込みに失敗しました',
              UPLOAD_ERR_EXTENSION => '拡張モジュールによってアップロードが中断されました'
          ];
          $err_msg = $msg[$_FILES['image']['error']];
      }elseif(!in_array(strtolower($ext['extension']), $perm)){
          $err_msg = '画像以外のファイルはアップロードできません';
      }elseif(!@getimagesize($_FILES['image']['tmp_name'])){
          $err_msg = 'ファイルの内容が画像ではありません';
      }else{
          $src = $_FILES['image']['tmp_name'];
          $dest = mb_convert_encoding($_FILES['image']['name'], 'SJIS-WIN', 'UTF-8');
          if(!move_uploaded_file($src, 'user_picture/'. date('YmdHis') . $dest)){
              $err_msg = 'アップロード処理に失敗しました';
          }
          $filename = 'user_picture/'. date('YmdHis') . $dest;
          
      }
      if(isset($err_msg)){
          die('<div style="color:Red;">'.$err_msg.'</div>');
      }

      $message = $db->prepare('INSERT INTO posts SET member_id=?, picture=?, reply_message_id=?, created=NOW()');
      $message->execute(array(
      $member['id'],
      $filename,
      $_POST['reply_post_id']
      ));
  }
  header('Location: index.php');
  exit();
}

$page = $_REQUEST['page'];
if($page == ''){
  $page = 1;
}
$page = max($page, 1);

$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 10);
$page = min($page, $maxPage);

$start = ($page - 1) * 10;

$posts = $db->prepare('SELECT m.name, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,10');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

if(isset($_REQUEST['res'])){
  //返信の処理
  $response = $db->prepare('SELECT m.name, p. * FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
  $response->execute(array($_REQUEST['res']));
  $table = $response->fetch();
  $message = '@' . $table['name'] . ' ' . $table['message'];
}
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
  	<div style="text-align: right"><a href="login.php">ログアウト</a></div>
    <form action="" method="post" enctype="multipart/form-data">
      <dl>
        <dt><?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?>さん、メッセージをどうぞ</dt>
          <textarea name="message" cols="50" rows="5"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></textarea>
          <input type="hidden" name="reply_post_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
        <dt>      
          <input type="file" name="image" value="1000000">
        </dt>
      </dl>
      <div style="color:Red;">※メッセージと画像は同時に投稿することができません</div>
      <p><input type="submit" value="投稿する" /></p>
      <div style="text-align: right"><a href="download.php">背景のダウンロード</a></div>
    </form>

<!-- 投稿一覧 -->
<?php foreach ($posts as $post): ?>
  <div class="msg">
  
    <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?> (<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>) [<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a>]</p>
    
  <?php if($post['picture'] !== ''): ?>
    <img src="<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>" width="100" height="100" />
  <?php endif; ?>
    
  <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>

  <?php if ($post['reply_message_id'] > 0): ?>
    <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'], ENT_QUOTES)); ?>">
    返信元のメッセージ</a>
  <?php endif; ?>

  <?php if ($_SESSION['id'] == $post['member_id']): ?>
    [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'])); ?>"
    style="color: #F33;">削除</a>]
  <?php endif; ?>
  </p>
  </div>
<?php endforeach; ?>

<div class="paging">
<?php if($page > 1): ?>
  <a href="index.php?page=<?php print($page-1); ?>">前のページへ</a>
<?php else: ?>
  前のページへ
<?php endif; ?>

<?php if($page < $maxPage): ?>
  <a href="index.php?page=<?php print($page+1); ?>">次のページへ</a>
<?php else: ?>
  次のページへ
<?php endif; ?>
</div>
</body>
</html>