<?php
session_start();
// DAO.php,USER.phpを読み込み
require_once("../../../Test_DB/db.php");
require_once("../../../Test_DB/User.php");
require_once("../../../Test_DB/Book.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$user_id =  $_SESSION["user_id"];
$dao = new DAO();
$user = $dao->getUser($user_id);
$json_user_id = json_encode($user->getUserId());
// クラスの本を取得
//echo $user->getAffiliationId();
$allBooks = $dao->getAllBooks($user);
// 自分が借りている本を取得
$myBooks = $dao->getMyBooks($user);
// 貸し出し中の本を取得
$lentBooks = $dao->getLentNowBooks($user);

?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="Home.css">
</head>

<body>
    <!--ヘッダー-->
    <!--ログアウト-->
    <button id="history-button" class="history-button" onclick="window.location.href='../../Logout.php'">ログアウト</button><br>
    <div class="main">
        <div id="contents"><a href="Home.php">白石学園ポータルサイト</a>
        </div>
        <div id="login">
            ログイン者:<?php echo $user->getUserName() ?><br>
            区分:<?php echo $user->getAffiliationName() ?><br>
            学科:<?php echo $user->getUserTypeName() ?>
        </div>
    </div>
    <!--履歴ボタン-->
    <button id="history-button" class="history-button" onclick="window.location.href='../History/History.php'">履歴</button>
    <button id="Qr-button" class="Qr-button" onclick="openQrCodeWindow();">本のQRコード読み取り</button><br>
    <!--パンくず-->
    <ul class="breadcrumb">
        <li><a href="../Home/StudentHome.php">ホーム</a></li>
    </ul>
    <!--検索欄と検索ボタン-->
    <div class="search">
        <input type="text" id="search-input" class="search-input" placeholder="検索欄">
        <button id="search-button" class="search-button" onclick="searchImages()">検索</button>
    </div><br>
    <p class ="showbook">あなたが貸出中の本</p>
    <br>
    <div class="my-image-container"></div>
    <hr style="border-top: 1px solid #007BFF; ;height:1px;width:100%;"><!--横線-->
    <p class ="showbook">全ての本</p>
    <div class="all-image-container"></div>
    <script>
        //文字を改行する関数
        function insertLineBreaks(text, maxLength) {
            let result = '';
            let count = 0;
            for (let i = 0; i < text.length; i++) {
                result += text[i];
                count++;
                if (count === maxLength && i !== text.length - 1) {
                    result += '\n';
                    count = 0;
                }
            }
            return result;
        }
        // 画像を表示する関数
        function displayImages(totalImages, imageDirectoryPath, flag) {
            //flagがtrueの時はimage-containerのクラスを参照,そうでないときはimage-container1を参照しsetClassに代入
            let setClass = "";
            if (flag) {
                setClass = ".my-image-container";
            } else {
                setClass = ".all-image-container";
            }
            let container = document.querySelector(setClass);
            //totalImages配列の要素数よりimageIndexが小さいとき,画像とテキストを表示する
            for (let imageIndex = 0; imageIndex < totalImages.length; imageIndex++) {
                //imageWrapper変数に新規のdivタグを代入
                const imageWrapper = document.createElement("div");
                //imageWrapperにimage-wrapperクラスを追加
                imageWrapper.classList.add("image-wrapper");
                //imgタグを生成
                const imageElement = new Image();
                //book_idが貸出中の本のbook_idと等しいとき画像をグレーにする
                for (let i = 0; i < lentBooks.length; i++) {
                    // 貸出中のbook_idであれば、画像を灰色にする
                    if ((lentBooks[i].book_id == totalImages[imageIndex].book_id)) {
                        imageElement.classList.add("grayAdd");
                    }
                }
                //pathに画像パスを代入
                let path = imageDirectoryPath + totalImages[imageIndex].image;
                //imgタグのsrcにpathを代入
                imageElement.src = path;
                let book = totalImages[imageIndex];

                imageElement.addEventListener("click", () => {
                    // 遷移先リンク
                    Golink(totalImages, imageIndex);
                });
                //imageWrapperの子要素にimageElementを追加
                imageWrapper.appendChild(imageElement);
                //imageText変数に新規のdivタグを代入
                const imageText = document.createElement("div");
                //imageText変数にimage-textクラスを追加
                imageText.classList.add("image-text");
                //textContentにテキストを追加
                const textContent = totalImages[imageIndex].book_name;
                const formattedText = insertLineBreaks(textContent, 10);
                imageText.textContent = formattedText;
                imageWrapper.appendChild(imageText);
                container.appendChild(imageWrapper);
            }
        }
        // 検索する関数
        function searchImages() {
            const searchText = document.getElementById("search-input").value.toLowerCase();
            const imageWrappers = document.querySelectorAll(".image-wrapper");
            // 書籍名と検索テキストが一致したら検索できる
            for (let i = 0; i < imageWrappers.length; i++) {
                const imageText = imageWrappers[i].querySelector(".image-text").textContent.toLowerCase();
                // 改行文字を削除して一致判定を行う
                const formattedSearchText = searchText.replace(/\n/g, '');
                const formattedImageText = imageText.replace(/\n/g, '');
                if (formattedImageText.includes(formattedSearchText)) {
                    imageWrappers[i].style.display = "block";
                } else {
                    imageWrappers[i].style.display = "none";
                }
            }
        }
        // 履歴ボタンがクリックされたときの処理関数
        function showHistory() {
            window.location.href = "history.html";
        }
        //表示
        document.getElementById("search-button").addEventListener("click", searchImages);
        //PHPからJSONとして受け取ったlentNow(借りている本)をlentNowに配列として代入
        var myBooks = <?php echo $myBooks; ?>;
        //PHPからJSONとして受け取ったallBooks(所属しているクラスの全ての本)をallBooksに配列として代入
        var allBooks = <?php echo $allBooks; ?>;
        //PHPからJSONとして受け取ったallBooks(所属しているクラスの全ての本)をlentBooksに配列として代入
        var lentBooks = <?php echo $lentBooks; ?>;
        displayImages(myBooks, "../../../Image/", true);
        displayImages(allBooks, "../../../Image/", false);
        let user_id = <?php echo $json_user_id; ?>;
        ////貸出、返却、その他の判別
        function Golink(totalImages, imageIndex) {
            let link = "../Lent/Lent.php";
            // mybooksもしくはallBooksの要素分for文を回す
            for (let i = 0; i < lentBooks.length; i++) {
                // lentBooksの要素数分for文を回す
                if (lentBooks[i].book_id === totalImages[imageIndex].book_id) {
                    // 貸し出しているのは自分か？
                    if (parseInt(totalImages[imageIndex].user_id) === parseInt(user_id)) {
                        link = "../ReturnLent/ReturnLent.php";
                        break;
                    } else {
                        link = "Test.php";
                        break;
                    }
                }
            }
            window.location.href = link;
            let f = document.createElement("form");
            f.method = 'POST';
            f.action = link;
            f.innerHTML = '<input name="book_id" value=' + totalImages[imageIndex].book_id + '>';
            document.body.append(f);
            f.submit();
        }

        function openQrCodeWindow() {
            var qrCodeWindow = window.open('Qrcode.html', '_blank', 'width=600,height=400');
            qrCodeWindow.onbeforeunload = function() {
                var qrCodeResult = qrCodeWindow.document.getElementById('qr').value;
                var qrCodeValue = parseInt(qrCodeResult);
                Golink(allBooks, qrCodeValue);
            };
        }

        // document.getElementById('Qr-button').addEventListener('click', () => {

        // });
    </script>
</body>

</html>