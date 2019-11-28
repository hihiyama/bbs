<?php
session_start();
require('dbconnect.php');

if($_COOKIE['email'] !== ''){
    $email = $_COOKIE['email'];
}

if(!empty($_POST)){
    $email = $_POST['email'];
    if($_POST['email'] !== '' && $_POST['password'] !== ''){
        $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');
        $login->execute(array(
            $_POST['email'],
            sha1($_POST['password'])
        ));
        $member = $login->fetch();

        if($member){
            $_SESSION['id'] = $member['id'];
            $_SESSION['time'] = time();

            if($_POST['save'] === 'on'){
                setcookie('email', $_POST['email'], time()+60*60*24*14);
            }

            header('Location: index.php');
            exit();
        }else{
            $error['login'] = 'failed';
        }
    }else{
        $error['login'] = 'blank';
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ログイン</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <h1>ログインする</h1>
    <p>メールアドレスとパスワードを記入してログインしてください。</p>
    <p>入会手続きがまだの方はこちらからどうぞ。</p>
    <p>&raquo;<a href="register.php">入会手続きをする</a></p>
   
    <form action="" method="post">
      <dl>
        <dt>メールアドレス</dt>
        <dd>
          <input type="text" name="email" size="35" maxlength="255" value="<?php print(htmlspecialchars($email, ENT_QUOTES)); ?>" />
          <?php if($error['login'] === 'blank'): ?>
          <p class="error">* メールアドレスとパスワードをご記入ください</p>
          <?php endif; ?>
          <?php if($error['login'] === 'failed'): ?>
          <p class="error">* ログインに失敗しました。正しくご記入ください</p>
          <?php endif; ?>
        </dd>
        <dt>パスワード</dt>
        <dd>
          <input type="password" name="password" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['password'], ENT_QUOTES)); ?>" />
        </dd>
        <dt>ログイン情報の記録</dt>
        <dd>
          <input id="save" type="checkbox" name="save" value="on">
          <label for="save">次回からは自動的にログインする</label>
        </dd>
      </dl>
        <input type="submit" value="ログインする" />
    </form>
</body>
</html>
