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
$book = $dao->clickBook($_POST["book_id"]);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>白石学園ポータルサイト</title>
    <link rel="stylesheet" href="Change.css" type="text/css">
</head>

<body>
    <div class="main">
        <!--ヘッダー-->
        <!-- Header.phpを呼び出す -->
        <?php include "../../../Test_DB/Header.php"; ?>
    </div><br>
    <!--パンくずリスト-->
    <ul class="breadcrumb">
        <li class="pan"><a href="../Home/TeacherHome.php">ホーム ></a><a href="#" onclick="redirectToHome()"> 詳細ページ ></a><a>編集</a></li>
    </ul>
    <div class="books"> <!-- 本の詳細 -->
        <div class="books"> <!-- 本の詳細 -->
            <img id="bookImage" src="../../../Image/<?php echo $book["book_id"]; ?>.jpg" class="example1"
                style="vertical-align:top" alt="ファイルを選択">
            <input type="file" id="fileInput" name="uploadedFile" class="hidden" onchange="updateImage(event)">
        </div>
        <div class="text">
            <p>
            <h2>著書名:<input type="text" id="book_name" name="book_name" value="<?php echo $book["book_name"]; ?>"></h2>
            </p>
            <p>
            <h2>著者名:<input type="text" id="author" name="author" value="<?php echo $book["author"] ?>"></h2>
            </p>
            <p>
            <h2>出版社:<input type="text" id="publisher" name="publisher" value="<?php echo $book["publisher"]; ?>"></h2>
            </p>
            <p>
            <h2>ISBN:<input type="text" id="ISBN" name="ISBN" value="<?php echo $book["ISBN"]; ?>"></h2><br>
            <button id="ISBN-button" class="ISBN-button" onclick="openBarCodeWindow()">ISBNをカメラで入力</button>
            </p>
        </div>
    </div>
    <!--貸出、戻るボタン-->
    <div class="buttonContainer">
        <button id="rentButton" onclick="openDialog()">保存</button>
        <button id="returnButton" onclick="deleteDialog()">戻る</button>
    </div>
    <!--ダイアログボックスの表示-->
    <div id="dialog" class="dialog">
        <div class="dialog-content">
            <p class=AddText>変更内容を保存しますか？</p>
            <button id="yesButton" onclick=openComplete()>はい</button>
            <button id="noButton" onclick="closeDialog()">いいえ</button>
        </div>
    </div>
    <div id="dialog2">
        <div class="dialog2-content">
            <p class=AddButtonText>保存が完了しました</p>
            <button id="completeButton" onclick="redirectToHome()">OK</button>
        </div>
    </div>
    <div id="dialog3" class="dialog3">
        <div class="dialog-content">
            <p class=AddText>編集内容を破棄しますか？</p>
            <button id="yesButton" onclick="deleteOkDialog()">はい</button>
            <button id="noButton" onclick="closeDialog2()">いいえ</button>
        </div>
    </div>
    <div id="dialog4">
        <div class="dialog2-content">
            <p class=AddButtonText>編集内容を破棄しました。</p>
            <button id="completeButton" onclick="redirectToHome()">OK</button>
        </div>
    </div>
    <div id="dialog5">
        <div class="dialog2-content">
            <p class=AddButtonText2>入力されていない情報があります</p>
            <button id="completeButton" onclick="closeDialog3()">OK</button>
        </div>
    </div>
    <div id="dialog6">
        <div class="dialog2-content">
            <p class=AddButtonText3>これは画像ではありません</p>
            <button id="completeButton" onclick="closeDialog4()">OK</button>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <script>


        function openBarCodeWindow() {
            var BarCodeWindow = window.open('../../../Barcode/Barcode.html', '_blank', 'width=600,height=400');
            BarCodeWindow.onbeforeunload = function () {
                var BarCodeResult = BarCodeWindow.document.getElementById('jan').value;
                var ISBNResultElement = document.getElementsByName('ISBN')[0]; // input要素を取得
                ISBNResultElement.value = parseInt(BarCodeResult); // 取得したISBN情報をinput要素に表示
            }
        }

        // 画像をクリックしたときの処理
        document.getElementById('bookImage').addEventListener('click', function () {
            document.getElementById('fileInput').click();
        });

        // ファイル選択時の処理
        function updateImage(event) {
            const file = event.target.files[0];
            if (file) {
                // ファイルが画像であるかをMIMEタイプで判定
                if (!file.type.startsWith('image/')) {
                    // ファイルが画像でない場合、ダイアログを表示
                    const dialog = document.getElementById('dialog6');
                    dialog.style.display = 'block';
                    return; // 処理を中断して画像のアップロードをキャンセル
                }

                // 画像ファイルを読み込んで表示
                const reader = new FileReader();
                reader.onload = function (e) {
                    const bookImage = document.getElementById('bookImage');
                    bookImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // フォームデータを作成し、画像を非同期でアップロード
        function uploadImage() {
            const fileInput = document.getElementById('fileInput');
            const file = fileInput.files[0];
            const formData = new FormData();
            // ファイル名を指定する
            formData.append('uploadedFile', file, <?php echo $book["book_id"]; ?>+".jpg");

            // XMLHttpRequestを使って非同期でファイルをアップロード
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        console.log(xhr.responseText); // アップロード結果のレスポンスを表示
                    } else {
                        console.error('アップロードに失敗しました。');
                    }
                }
            };
            xhr.send(formData);


        }

        //はいいいえダイアログ
        function openDialog() {
            var book_name = document.getElementById("book_name").value;
            var author = document.getElementById("author").value;
            var publisher = document.getElementById("publisher").value;
            var ISBN = document.getElementById("ISBN").value;
            if (!book_name || !author || !publisher || !ISBN) {
                var dialog = document.getElementById("dialog5");
                closeDialog3();
                dialog.style.display = "block";
            } else {
                var dialog = document.getElementById("dialog");
                console.log(dialog);
                dialog.style.display = "block";
            }
        }

        // はいボタンが押されたときの処理
        function openComplete() {
            var dialog = document.getElementById("dialog2");

            var book_id = <?php echo $book["book_id"]; ?>;
            var book_name = document.getElementById("book_name").value;
            var author = document.getElementById("author").value;
            var publisher = document.getElementById("publisher").value;
            var ISBN = document.getElementById("ISBN").value;

            // 非同期通信でAjax.php
            $.ajax({

                type: 'post',
                url: "../../../Test_DB/Ajax.php",
                data: {
                    "processing": "change", //"貸出処理か返却処理かをAjax.phpで判断するためにprocessing変数を用意
                    "book_id": book_id,
                    "book_name": book_name,
                    "author": author,
                    "publisher": publisher,
                    "ISBN": ISBN
                }
            });
            closeDialog();
            dialog.style.display = "block";

            // 画像を非同期でアップロード
            uploadImage();
        }

        //いいえ
        function closeDialog() {
            var dialog = document.getElementById("dialog");
            dialog.style.display = "none";
        }


        function closeDialog2() {
            var dialog = document.getElementById("dialog3");
            dialog.style.display = "none";
        }

        function closeDialog3() {
            var dialog = document.getElementById("dialog5");
            dialog.style.display = "none";
        }

        function closeDialog4() {
            var dialog = document.getElementById("dialog6");
            dialog.style.display = "none";
        }

        function deleteDialog() {
            var img = document.getElementById('fileInput').value;
            var book_name = document.getElementById("book_name").value;
            var author = document.getElementById("author").value;
            var publisher = document.getElementById("publisher").value;
            var ISBN = document.getElementById("ISBN").value;
            var dialog = document.getElementById("dialog3");
            if (img || book_name || author || publisher || ISBN) {
                dialog3.style.display = "block"; // 画像がアップロードされていればdeleteDialog()を実行
            } else {
                redirectToHome(); // 画像がアップロードされていなければredirectToHome()を実行
            }
        }

        function deleteOkDialog() {
            var dialog = document.getElementById("dialog4");
            deleteinfo()
            closeDialog2();
            dialog.style.display = "block";
        }

        //情報を削除する関数
        function deleteinfo() {
            document.getElementById('fileInput').value = '';
            document.getElementById('book_name').value = '';
            document.getElementById('author').value = '';
            document.getElementById('publisher').value = '';
            document.getElementById('ISBN').value = '';
        }

        //ホームに戻る
        function redirectToHome() {
            // 別のページにリダイレクト（移動）
            let link = "../Edit/Edit.php";
            window.location.href = link;
            let f = document.createElement("form");
            f.method = 'POST';
            f.action = link;
            f.innerHTML = '<input name="book_id" value=' + <?php echo $book["book_id"]; ?> + '>';
            document.body.append(f);
            f.submit();
        }
    </script>
</body>

</html>