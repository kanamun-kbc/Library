<?php

// 非同期通信で行う処理を記述しているよ

require_once("db.php");

// ReturnLentから送信されたuser_id,book_id,processingを受け取る
$user_id = filter_input(INPUT_POST, 'user_id');
$book_id = filter_input(INPUT_POST, 'book_id');
$process = filter_input(INPUT_POST, 'processing');

$dao = new DAO();

// process変数がlentと等しいとき、貸出処理を行う
if (strcmp($process, "lent") == 0) {
    echo $dao->lendingProcess($user_id, $book_id);
    // process変数がreturnLentと等しいとき、返却処理を行う
} else if (strcmp($process, "returnLent") == 0) {
    // 返却処理を実行
    echo $dao->returnProcess($user_id, $book_id);
}

exit;
