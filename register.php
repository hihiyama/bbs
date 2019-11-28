<?php
session_start();
require('dbconnect.php');

if(!empty($_POST)){
    if($_POST['name'] === ''){
        $error['name'] = 'blank';
    }
    if($_POST['email'] === ''){
        $error['email'] = 'blank';
    }
    if(strlen($_POST['password']) < 4){
        $error['password'] = 'length';
    }
    if($_POST['password'] === ''){
        $error['password'] = 'blank';
    }

    //アカウント重複チェック
    if(empty($error)){
        $member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');
        $member->execute(array($_POST['email']));
        $record = $member->fetch();
        if($record['cnt'] > 0){
            $error['email'] = 'duplicate';
        }
    }

    if(empty($error)){
         $_SESSION['join'] = $_POST;
        header('Location: check.php');
        exit();
    }
}

if($_REQUEST['action'] == 'rewrite' && isset($_SESSION['join'])){
    $_POST = $_SESSION['join'];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>会員登録</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css" />
</head>
<body>

<h1>会員登録</h1>

<p>次のフォームに必要事項をご記入ください。</p>
<form action="" method="post">
	<dl>
		<dt>名前</dt>
		<dd>
        	<input type="text" name="name" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['name'], ENT_QUOTES)); ?>" />
            <?php if($error['name'] === 'blank'): ?>
            <p class="error">*　名前を入力してください</p>
            <?php endif; ?>
		</dd>
		<dt>メールアドレス</dt>
		<dd>
        	<input type="text" name="email" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['email'], ENT_QUOTES)); ?>" />
            <?php if($error['email'] === 'blank'): ?>
            <p class="error">*　メールアドレスを入力してください</p>
            <?php endif; ?>
            <?php if($error['email'] === 'duplicate'): ?>
            <p class="error">*　指定されたメールアドレスは、すでに登録されています</p>
            <?php endif; ?>
        </dd>
		<dt>パスワード</dt>
		<dd>
        	<input type="password" name="password" size="10" maxlength="20" value="<?php print(htmlspecialchars($_POST['password'], ENT_QUOTES)); ?>" />
            <?php if($error['password'] === 'length'): ?>
            <p class="error">*　パスワードを4文字以上で入力してください</p>
            <?php endif; ?>
            <?php if($error['password'] === 'blank'): ?>
            <p class="error">*　パスワードを入力してください</p>
            <?php endif; ?>
        </dd>
	</dl>
	<div><input type="submit" value="入力内容を確認する" /></div>
</form>
</body>
</html>