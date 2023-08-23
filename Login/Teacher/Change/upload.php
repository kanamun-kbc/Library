<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["uploadedFile"])) {
    $targetDirectory = "../../../Image/"; // 画像を保存するパス

    // 新しいファイル名を取得
    $newFileName = $_FILES["uploadedFile"]["name"];

    // ファイルを保存
    if (move_uploaded_file($_FILES["uploadedFile"]["tmp_name"], $targetDirectory . $newFileName)) {
        echo "The file " . $newFileName . " has been uploaded.";
    }
}
?>
