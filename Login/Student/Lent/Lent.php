<?php
// DAO.php,USER.phpを読み込み
require_once("../../../Test_DB/db.php");
require_once("../../../Test_DB/User.php");
require_once("../../../Test_DB/Book.php");

session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$dao = new DAO();
$user = $dao->getUser($_SESSION["user_id"]);
$book = $dao->clickBook($_POST["book_id"]);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>白石学園ポータルサイト</title>
  <link rel="stylesheet" href="Lent.css" type="text/css">
</head>

<body>
  <div class="main">
    <!--ヘッダー-->
    <div class="main">
      <div id="contents"><a href="../Home/StudentHome.php">白石学園ポータルサイト</a>
      </div>
      <div id="login">
        ログイン者:<?php echo $user->getUserName();  ?><br>
        区分:<?php echo $user->getAffiliationName(); ?><br>
        学科:<?php echo $user->getUserTypeName(); ?>
      </div>
    </div>
  </div> <!--パンくずリスト-->
  <ul class="breadcrumb">
    <li><a href="../Home/StudentHome.php">ホーム</a></li>
  </ul>
  <div class="books"> <!--本の詳細-->
    <img src="<?php echo '../../../image/' . $book["image"]; ?>" + class="example1" style="vertical-align:top">
    <div class="text">
      <p>
      <h2>著書名:<?php echo $book["book_name"]; ?></h2>
      </p>
      <p>
      <h2>著者名 :<?php echo $book["author"]; ?></h2>
      </p>
      <p>
      <h2>出版社：<?php echo $book["publisher"]; ?></h2>
      <p>
      <p>
      <h2>貸出状況： <?php echo $book["lending_status"]; ?></h2>
      </p>
      <p>
      <h2>返却予定日： <?php echo $book["return_due_date"]; ?></h2>
      </p>
    </div>
  </div> <!--貸出、戻るボタン-->
  <div class="buttonContainer">
    <button id="rentButton" onclick="openDialog()">貸出する</button>
    <button id="returnButton" onclick="redirectToHome()">戻る</button>
  </div>
  <!--ダイアログボックスの表示-->
  <div id="dialog" class="dialog">
    <div class="dialog-content">
      <!--現在の日付から一週間後の日付の表氏-->
      <?php
      $currentDate = date('Y-m-d');
      $oneWeekLater = date('Y年m月d日', strtotime('+1 week', strtotime($currentDate)));
      // 結果を表示
      echo '<p style="margin-top: -50px; margin-left: -10px;">' . $oneWeekLater  . " まで
    この本を借りますか？";
      ?>
      <button id="yesButton" onclick="openComplete()">はい</button>
      <button id="noButton" onclick="closeDialog()">いいえ</button>
      <script>
      </script>
    </div>
  </div>
  <div id="dialog2">
    <div class="dialog2-content">
      <button id="complete" onclick="closeDialog2()">完了しました</button>
    </div>
  </div>
  <script>
    //はいいいえダイアログ
    function openDialog() {
      var dialog = document.getElementById("dialog");
      console.log(dialog);
      dialog.style.display = "block";
    }
    //完了ダイアログ
    function openComplete() {
      var dialog = document.getElementById("dialog2");
      closeDialog();
      dialog.style.display = "block";
    }

    function closeDialog() {
      var dialog = document.getElementById("dialog");
      dialog.style.display = "none"
    }

    function closeDialog2() {
      var dialog = document.getElementById("dialog2");
      dialog.style.display = "none";
    }
    //ホームに戻る
    function redirectToHome() {
      // 別のページにリダイレクト（移動）
      window.location.href = "../Home/StudentHome.php";
    }
  </script>
</body>

</html>