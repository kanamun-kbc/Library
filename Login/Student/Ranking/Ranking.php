<?php
session_start();
// DAO.php,USER.php,Book.phpに保存されているPHPスクリプトを取り込む
require_once("../../../Test_DB/db.php");
require_once("../../../Test_DB/User.php");
require_once("../../../Test_DB/Book.php");

// user_idがNULLまたは空の時、ログインページに遷移させる
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
// セッション変数からuser_idを取得し変数user_idに格納
$user_id = $_SESSION["user_id"];
// DAOクラスをインスタンス化
$dao = new DAO();
// 引数にuser_idを指定してDAOクラスのgetUserメソッドを実行しUserインスタンスを変数userに格納
$user = $dao->getUser($user_id);
// userのgetUserIdメソッドを実行し、取得したuser_idを文字列型に変換し,json_user_idに格納
$json_user_id = json_encode($user->getUserId());
// 引数にuserを指定しdaoのgetAllBooksメソッドを実行し、ログインしているユーザーのクラスにある本を全て取得
$allBooks = $dao->getAllBooks($user);
// 引数にuserを指定しdaoのgetMyBooksメソッドを実行し、ログインしているユーザーが現在借りている本を全て取得
$myBooks = $dao->getMyBooks($user);
// 引数にuserを指定しdaoのgetLentNowBooksメソッドを実行し、ログインしているユーザーのクラスで現在借りられている本を全て取得
$lentBooks = $dao->getLentNowBooks($user);

// Header.phpを読み込む
include "../../../Test_DB/Header.php";

// 本の貸し出し回数が多い順に上位3件の本を取得
$top_borrowed_books = $dao->getTopBorrowedBooks();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>白石学園ポータルサイト</title>
<link rel="stylesheet" href="Ranking.css" type="text/css">
</head>
<body>
<div class="main">
</div><br>

<div class="main2">
    <h2>貸出ランキングTOP３</h2>
    <table>
        <tr>
            <th>順位</th>
            <th>　　本の画像</th> 
            <th>本のタイトル</th>
            <th>貸し出し回数</th>
        </tr>
        <?php
        // 上位3件の貸出ランキングのデータを表示するループ処理

        $rank = 1;
        foreach ($top_borrowed_books as $book) {
            $book_id = $book['book_id'];// 本のidを取得
            $image = $book['image']; // 本の画像を取得
            $book_name = $book['book_name'];// 本のタイトルを取得
            $borrow_count = $book['borrow_count'];// 何回借りられたかを取得
        ?>
            <tr>
                <td><?php echo $rank; ?></td>
                <td>
                <img src="<?php echo '../../../image/' . $book["image"]; ?>" class="example1" >
        <div class="image-text"></div>
                </td>
                <td><?php echo $book_name; ?></td>
                <td><?php echo $borrow_count; ?></td>
            </tr>
        <?php
            $rank++;
        }
        ?>
    </table>
</div>
</div>
<script>
  

  </script>
</body>
</html>
