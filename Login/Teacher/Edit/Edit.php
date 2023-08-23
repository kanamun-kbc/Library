<?php
// DAO.php,USER.php,Book.phpに保存されているPHPスクリプトを取り込む
require_once("../../../Test_DB/db.php");
require_once("../../../Test_DB/User.php");
require_once("../../../Test_DB/Book.php");

session_start();

// user_idがNULLまたは空の時、ログインページに遷移させる
if (!isset($_SESSION["user_id"])) {
  header("Location: ../../Login.php");
  exit;
}
// DAOクラスをインスタンス化
$dao = new DAO();
// 引数にセッション変数のuser_idを指定してDAOクラスのgetUserメソッドを実行しUserインスタンスを変数userに格納
$user = $dao->getUser($_SESSION["user_id"]);
//引数に本をクリックした際に送信されたbook_idを指定して、クリックした本の詳細を変数bookに格納
$book = $dao->clickBook($_POST["book_id"]);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>白石学園ポータルサイト</title>
  <link rel="stylesheet" href="Edit.css" type="text/css">
</head>

<body>
  <div class="main">
    <!--ヘッダー-->
    <!-- Header.phpを呼び出す -->
    <?php include "../../../Test_DB/Header.php"; ?>
  </div><br>
  <!--パンくずリスト-->
  <ul class="breadcrumb">
    <li class="pan"><a href="../Home/TeacherHome.php">ホーム > 詳細ページ</a></li>
  </ul>
  <div class="books"> <!--本の詳細-->
    <img src="<?php echo '../../../image/' . $book["image"]; ?>" + class="example1" style="vertical-align:top">
    <div class="text">
      <p>
      <h2>著書名:
        <?php echo $book["book_name"]; ?>
      </h2>
      </p>
      <p>
      <h2>著者名 :
        <?php echo $book["author"]; ?>
      </h2>
      </p>
      <p>
      <h2>出版社：
        <?php echo $book["publisher"]; ?>
      </h2>
      <p>
      <p>
      <h2>貸出状況：
        <?php echo $book["lending_status"]; ?>
      </h2>
      </p>
      <p>
      <h2>ISBN：
        <?php echo $book["ISBN"]; ?>
      </h2>
      </p>
    </div>
  </div> <!--貸出、戻るボタン-->
  <div class="buttonContainer">
    <button id="editButton" onclick="editDialog()">編集</button>
    <button id="deleteButton" onclick="deleteDialog()">削除</button>
    <button id="returnButton" onclick="redirectToHome()">戻る</button>
  </div>
  <!--ダイアログボックスの表示-->
  <div id="dialog" class="dialog">
    <div class="dialog-content">
      <!--現在の日付から一週間後の日付の表氏-->
      <?php
      // 結果を表示
      echo '<p style="margin-top: -50px; margin-left: -10px;white-space: nowrap;"> この本を削除しますか？';
      ?>
      <button id="yesButton" onclick=openComplete()>はい</button>
      <button id="noButton" onclick="closeDialog()">いいえ</button>
    </div>
  </div>
  <div id="dialog2">
    <div class="dialog2-content">
      <button id="completeButton" onclick="closeDialog2()">完了しました</button>
    </div>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

  <script>
    //はいいいえダイアログ
    function deleteDialog() {
      var dialog = document.getElementById("dialog");
      console.log(dialog);
      dialog.style.display = "block";
    }
    //完了ダイアログ
    function openComplete() {

      //ポップアップ「完了しました」を出す
      var dialog = document.getElementById("dialog2");

      //book_idを取ってくる
      const book_id = <?php echo $book["book_id"]; ?>;

      // 非同期通信でAjax.phpにuser_idとbook_idを送信
      $.ajax({

        type: 'post',
        url: "../../../Test_DB/Ajax.php",
        data: {
          "processing": "delete", //"貸出処理か返却処理かをAjax.phpで判断するためにprocessing変数を用意
          "book_id": book_id

        }
      });
      closeDialog();
      dialog.style.display = "block";
    }

    function editDialog() {
      //別ページにリダイレクト]
      let link = "../Change/Change.php";
      window.location.href = link;
      let f = document.createElement("form");
      f.method = 'POST';
      f.action = link;
      f.innerHTML = '<input name="book_id" value=' + <?php echo $book["book_id"]; ?> + '>';
      document.body.append(f);
      f.submit();
    }

    function closeDialog() {
      var dialog = document.getElementById("dialog");
      dialog.style.display = "none";
    }

    function closeDialog2() {
      // 別のページにリダイレクト（移動）
      window.location.href = "../Home/TeacherHome.php";
    }
    //ホームに戻る
    function redirectToHome() {
      // 別のページにリダイレクト（移動）
      window.location.href = "../Home/TeacherHome.php";
    }
  </script>
</body>