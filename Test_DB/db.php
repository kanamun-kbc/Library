<?php

require_once("User.php");
require_once("Book.php");

class DAO
{
    private $user = "root";
    private $pwd = "moku2440";

    private $dsn = "mysql:host=localhost;port=3306;dbname=library;";
    private $conn;

    // このクラスをインスタンス化時にデータベースに接続
    public function __construct()
    {
        $this->conn = new PDO($this->dsn, $this->user, $this->pwd);
    }

    // ログインしたユーザーの情報を取得(User型で返す)
    public function getUser($login_id): User
    {
        $stmt = $this->conn->prepare('SELECT u.user_id, u.affiliation_id, a.affiliation_name, ut.user_type_name, u.user_type_id, u.user_name, u.password
         FROM `users` AS u
         LEFT JOIN affiliation AS a
         ON u.affiliation_id = a.affiliation_id
         LEFT JOIN user_type as ut
         ON u.user_type_id = ut.user_type_id
         WHERE u.user_id = :login_id');

        $stmt->bindValue(":login_id", $login_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $user = new User($result["user_id"], $result["user_name"], $result["affiliation_id"], $result["affiliation_name"], $result["user_type_name"], $result["user_type_id"], $result["password"]);

        return $user;
    }

    // ログインしているユーザーが現在借りている本の情報を取得するためのSQL文
    public function getMyBooks(&$user)
    {
        $stmt = $this->conn->prepare("SELECT l.user_id, b.book_id, b.book_name, b.author, b.publisher, b.remarks, b.image, l.lending_status
                                        FROM book AS b
                                        INNER JOIN lent AS l
                                        ON l.book_id = b.book_id
                                        WHERE b.affiliation_id = :affiliation_id
                                        AND l.user_id = :user_id
                                        AND l.lending_status = '貸出中'
                                        ORDER BY b.book_id");

        $stmt->bindValue(":user_id", $user->getUserId());
        $stmt->bindValue(":affiliation_id", $user->getAffiliationId());
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return json_encode($result);
    }

    // ログインしているユーザーのクラスの本の情報を取得するためのSQL文
    public function getAllBooks(&$user)
    {
        $stmt = $this->conn->prepare("SELECT l.user_id, b.book_id, b.book_name, b.author, b.publisher, b.remarks, b.image, l.lending_status
                                         FROM book AS b
                                         LEFT JOIN lent2 AS l
                                         ON l.book_id = b.book_id 
                                         WHERE affiliation_id = :affiliation_id
                                         ORDER BY b.book_id");

        $stmt->bindValue(":affiliation_id", $user->getAffiliationId());
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return json_encode($result);
    }

    // ログインしているユーザーが所属しているクラスの全ての貸出中の本を取得するためのSQL文
    public function getLentNowBooks(&$user)
    {
        $stmt = $this->conn->prepare("SELECT l.user_id, b.book_id, b.book_name, b.author, b.publisher, b.remarks, b.image, l.lending_status
                                            FROM book AS b         
                                            LEFT JOIN lent AS l            
                                            ON l.book_id = b.book_id           
                                            WHERE b.affiliation_id = :affiliation_id 
                                            AND l.lending_status = '貸出中'  
                                            ORDER BY b.book_id");

        $stmt->bindValue(":affiliation_id", $user->getAffiliationId());
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return json_encode($result);
    }

    // 選択した本の情報を取得
    public function clickBook($book_id): array
    {
        $sql = "SELECT l.user_id, b.book_id, b.book_name, b.author, b.publisher, b.remarks, b.image, b.ISBN, COALESCE(l.return_due_date, '- - -') AS return_due_date, COALESCE(l.lending_status, '貸出可能') AS lending_status
                FROM book as b
                LEFT JOIN lent2 as l
                ON b.book_id = l.book_id
                WHERE b.book_id = :book_id;";
        // SQL実行準備
        $stmt = $this->conn->prepare($sql);
        // :book_idにセッション変数から取得したbook_idを代入
        $stmt->bindValue(":book_id", $book_id);
        // SQL文を実行
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    // 貸出処理
    public function lendingProcess($user_id, $book_id)
    {
        $sql = "INSERT INTO lent(book_id, user_id, lent_time, return_due_date,lending_status)
                 VALUES (:book_id, :user_id, :lent_time, :return_due_date,'貸出中')";
        // SQL実行準備
        $stmt = $this->conn->prepare($sql);

        $lent_date = new DateTime();
        // 時間を日本時間に変更
        date_default_timezone_set('Asia/Tokyo');
        // 貸出日を取得
        $lent_time = new DateTime();
        // 返却予定日を取得
        $return_due_date = new DateTime("+7 day");
        // 貸出日・返却予定日をDateTime型から文字列型に変換
        $lent_time = $lent_time->format('Y-m-d');
        $return_due_date = $return_due_date->format('Y-m-d');

        $stmt->bindValue(":book_id", $book_id);
        $stmt->bindValue(":user_id", $user_id);
        $stmt->bindValue(":lent_time", $lent_time);
        $stmt->bindValue(":return_due_date", $return_due_date);
        $stmt->execute();
    }

    // 返却処理
    public function returnProcess($user_id, $book_id)
    {
        $sql = "UPDATE lent
                SET return_due_date='- - -',return_time=:return_time,lending_status='貸出可能'
                WHERE user_id = :user_id AND book_id = :book_id AND lending_status = '貸出中'";

        $stmt = $this->conn->prepare($sql);

        $return_time = new DateTime("+7 day");
        $return_time = $return_time->format('Y-m-d');

        $stmt->bindValue(":return_time", $return_time);
        $stmt->bindValue(":user_id", $user_id);
        $stmt->bindValue(":book_id", $book_id);

        $stmt->execute();
    }

    // ログインしているユーザーの貸出履歴を取得する関数
    public function getHistory($user_id)
    {
        // 貸出履歴の取得
        $sql = "SELECT l.lent_id, l.book_id, l.return_time, l.return_due_date, b.image, b.book_name 
                FROM lent AS l
                INNER JOIN book AS b
                ON l.book_id = b.book_id
                WHERE l.user_id = :user_id 
                ORDER BY l.lent_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":user_id", $user_id);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }


    //ISBNコードからbook_idを返す関数（実装中）
    public function searchISBN($ISBN)
    //book_idの若い順に並べる（SQL8.0のみ有効）
    {

        //ISBNからROW_NUMBER_OVERを取ってくる
        $sql = "SELECT *
                FROM book2
                WHERE ISBN = :ISBN";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":ISBN", $ISBN);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $result = $result['num'];



        return $result;
    }


    public function deleteProcess($book_id)
    {
        // lentテーブルから同じbook_idのものを削除（参照制約の関係）
        $sql = "DELETE FROM lent WHERE book_id = :book_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":book_id", $book_id);
        $stmt->execute();

        // bookテーブルから同じbook_idのものを削除
        $sql = "DELETE FROM book WHERE book_id = :book_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":book_id", $book_id);
        $stmt->execute();

    }


    // ログインしているユーザのクラスの先月の貸出情報を取得
    public function getLastMonthLending($affiliation_id)
    {
        // 貸出履歴の取得
        $sql = "SELECT l.lent_time, COUNT(*) as count FROM lent AS l 
                INNER JOIN book AS b 
                ON l.book_id = b.book_id 
                WHERE lent_time LIKE :lent_time 
                AND b.affiliation_id = :affiliation_id 
                GROUP BY l.lent_time 
                ORDER BY l.lent_time";

        $stmt = $this->conn->prepare($sql);

        // 先月の月を求める
        // $lastMonth = new DateTime("-1 month");
        $lastMonth = new DateTime();
        $lastMonth = $lastMonth->format("Y-m");
        $lastMonth = $lastMonth . '%';
        $stmt->bindValue(":lent_time", $lastMonth);
        $stmt->bindValue(":affiliation_id", $affiliation_id);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    // 滞納者一覧を取得する関数
    public function getDelinquentUsers(): array
    {
        // 現在の日付と時刻を取得
        $currentDateTime = new DateTime();
        // タイムゾーンを日本に設定
        $currentDateTime->setTimezone(new DateTimeZone('Asia/Tokyo'));
        $currentDateTimeString = $currentDateTime->format('Y-m-d');

        $sql = "SELECT u.user_name, b.book_name, l.return_due_date,
                DATEDIFF(:current_date, l.return_due_date) AS overdue_days
                FROM lent AS l
                INNER JOIN book AS b ON l.book_id = b.book_id
                INNER JOIN users AS u ON l.user_id = u.user_id
                WHERE l.lending_status = '貸出中' AND :current_date > l.return_due_date";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":current_date", $currentDateTimeString);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    //最新のbook_idを取得して1を追加する関数
    public function getLastBookId()
    {
        $sql = "SELECT MAX(book_id) + 1 FROM book";
        $stmt = $this->conn->query($sql);
        $lastBookId = $stmt->fetchColumn();
        return $lastBookId;
    }

    public function AddBook($book_id,$affiliation_id,$book_name,$author,$publisher,$image,$ISBN)
    {
        $sql = "INSERT INTO book(book_id, affiliation_id, book_name, author, publisher, remarks, image , ISBN)
        VALUES (:book_id, :affiliation_id, :book_name, :author, :publisher, :remarks, :image, :ISBN)";

        // SQL実行準備
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":book_id", $book_id);
        $stmt->bindValue(":affiliation_id", $affiliation_id);
        $stmt->bindValue(":book_name", $book_name);
        $stmt->bindValue(":author", $author);
        $stmt->bindValue(":publisher", $publisher);
        $stmt->bindValue(":remarks", null, PDO::PARAM_NULL);
        $stmt->bindValue(":image", $image);
        $stmt->bindValue(":ISBN", $ISBN);
        $stmt->execute();
    }

    public function getTopBorrowedBooks()
{
    $sql = "SELECT b.book_id, b.book_name, COUNT(l.book_id) AS borrow_count, b.image
            FROM book AS b
            LEFT JOIN lent AS l ON b.book_id = l.book_id
            GROUP BY b.book_id
            ORDER BY borrow_count DESC
            LIMIT 3";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}
}