<?php

// このファイルは非同期通信で貸出処理や返却処理を行うためのファイル

// DAO.php,USER.php,Book.phpに保存されているPHPスクリプトを取り込む
require_once("db.php");

$process = filter_input(INPUT_POST, 'processing');

switch ($process) {
    case 'lent': //Processingがlentの場合
        //user_idとbook_idを受け取る
        $user_id = filter_input(INPUT_POST, 'user_id');
        $book_id = filter_input(INPUT_POST, 'book_id');
        // DAOクラスをインスタンス化
        $dao = new DAO();
        // 貸出処理を実行
        echo $dao->lendingProcess($user_id, $book_id);
        break;

    case 'returnLent': //ProcessingがReturnLentの場合
        //user_idとbook_idを受け取る
        $user_id = filter_input(INPUT_POST, 'user_id');
        $book_id = filter_input(INPUT_POST, 'book_id');
        // DAOクラスをインスタンス化
        $dao = new DAO();
        // 返却処理を実行
        echo $dao->returnProcess($user_id, $book_id);
        break;

    case 'delete': //Processingがdeleteの場合
        //book_idを受け取る
        $book_id = filter_input(INPUT_POST, 'book_id');
        // DAOクラスをインスタンス化
        $dao = new DAO();
        //削除を実行
        echo $dao->deleteProcess($book_id);
        break;


    case 'ISBN': //ProcessingがISBNの場合
        //ISBNを受け取る
        $ISBN = filter_input(INPUT_POST, 'ISBN');
        // DAOクラスをインスタンス化
        $dao = new DAO();
        //ISBNからimageを取り出す処理を実行
        echo $dao->searchISBN($ISBN);
        break;



        // openGraphプロセスでは今月の総貸出冊数を予測したグラフを表示する
    case 'openGraph':

        $affiliation_id = filter_input(INPUT_POST, 'affiliation_id');
        $dao = new DAO();
        // DAOクラスのgetLastMonthLending関数を呼び出し、先月のクラスの貸出情報を取得
        $lastMonthLending = $dao->getLastMonthLending($affiliation_id);

        $lent_time = [];
        $count = [];

        // データベースから取得したデータをデータの種類別にそれぞれのリストに追加
        foreach ($lastMonthLending as $last) {
            $lent_time[] = $last["lent_time"];
            $count[] = $last["count"];
        }
        //連想配列を作成
        $data = array(

            "lent_time" => $lent_time,
            "count" => $count
        );
        //連想配列をjson化(pythonにデータを渡すため)
        $json_data = json_encode($data);

        // index.pyにjson_dataを渡す準備
        $command = "echo $json_data | python ../Login/Teacher/LastMonthLending/index.py";
        // index.pyにjson_dataを渡す。pythonでprintしたものをoutputで受け取る
        echo exec($command, $output);

        break;

    case 'add':
         //book_idを受け取る
         $book_id = filter_input(INPUT_POST, 'book_id');
         $affiliation_id = filter_input(INPUT_POST, 'affiliation_id');
         $book_name = filter_input(INPUT_POST, 'book_name');
         $author = filter_input(INPUT_POST, 'author');
         $publisher = filter_input(INPUT_POST, 'publisher');
         $image = filter_input(INPUT_POST, 'image');
         $ISBN = filter_input(INPUT_POST, 'ISBN');
         // DAOクラスをインスタンス化
         $dao = new DAO();
         //削除を実行
         echo $dao->AddBook($book_id,$affiliation_id,$book_name,$author,$publisher,$image,$ISBN);
         break;

         case 'change':
        //book_idを受け取る
         $book_id = filter_input(INPUT_POST, 'book_id');
         $book_name = filter_input(INPUT_POST, 'book_name');
         $author = filter_input(INPUT_POST, 'author');
         $publisher = filter_input(INPUT_POST, 'publisher');
         $ISBN = filter_input(INPUT_POST, 'ISBN');
         // DAOクラスをインスタンス化
         $dao = new DAO();
         //削除を実行
         echo $dao->ChangeBook($book_id,$book_name,$author,$publisher,$ISBN);
         break;
}


// このファイルの処理を終了する
exit;
