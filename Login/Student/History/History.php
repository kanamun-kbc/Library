<?php
require_once("../../../Test_DB/db.php");
require_once("../../../Test_DB/User.php");


session_start();

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../Login.php");
  exit;
}

$dao = new DAO();
$user = $dao->getUser($_SESSION["user_id"]);
// 今まで借りた本を取得
$history = $dao->getHistory($user->getUserId());

?>

<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="History.css">

  <script>
    window.addEventListener("DOMContentLoaded", (event) => {
      const bookImages = document.querySelectorAll(".book-image img");
      bookImages.forEach((img) => {
        img.addEventListener("load", () => {
          const aspectRatio = img.naturalWidth / img.naturalHeight;
          const desiredWidth = 200;
          const desiredHeight = desiredWidth / aspectRatio;
          img.setAttribute("width", desiredWidth);
          img.setAttribute("height", desiredHeight);
        });
      });
    });
  </script>
</head>

<body>

  <!--ヘッダー-->
  <?php include "../../../Test_DB/Header.php"; ?><br>
  <!--パンくずリスト-->
  <ul class="breadcrumb">
    <li class="pan"><a href="../Home/StudentHome.php">ホーム > 貸出ページ</a></li>
  </ul>
  <div class="header-divider"></div>
  <div class="main">
    <!--<p class="breadcrumbs">
      <a href="../Home/Home.php">ホーム</a> &gt; 履歴
    </p>-->
    <h2>貸出履歴</h2>
    <?php foreach ($history as $item) : ?>
      <div class="history-item">
        <div class="book-image">
          <img src="../../../Image/<?php echo $item['image']; ?>" alt="Book Image">
        </div>
        <div class="book-details">
          <h3><?php echo $item['book_name']; ?></h3>
          <?php if ($item['return_time'] === null) : ?>
            <p class="rental-status">レンタル中</p>
            <p class="return-date">返却日：<?php echo $item['return_due_date']; ?></p>
          <?php else : ?>
            <p class="rental-status return-status">返却済み</p>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</body>

</html>