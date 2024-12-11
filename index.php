<?php
// メール通知停止リストのファイル
$stopListFile = 'stop_list.txt'; 

// フォームが送信された場合の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit_watch'])) {
        // 監視のフォームが送信された場合
        $url = $_POST['page_url'];
        $email = $_POST['email'];

        // ページのHTMLを取得
        $html = file_get_contents($url);
        
        // ページのハッシュを計算
        $currentHash = hash('sha256', $html);

        // 前回のハッシュをファイルから取得
        $hashFile = 'page_hash.txt';
        $lastHash = file_exists($hashFile) ? file_get_contents($hashFile) : '';

        // ハッシュが異なれば、更新を通知
        if ($currentHash !== $lastHash) {
            file_put_contents($hashFile, $currentHash);

            // 停止リストをチェック
            $stopList = file($stopListFile, FILE_IGNORE_NEW_LINES);

            // 停止リストにメールアドレスが含まれていない場合のみ通知を送信
            if (!in_array($email, $stopList)) {
                sendEmailNotification($email, $url);
                // メール送信後のメッセージ
                $message = "更新があり次第メール通知します。";
            } else {
                $message = "このメールアドレスには通知は送信されません。";
            }
        } else {
            $message = "ページは更新されていません。";
        }
    } elseif (isset($_POST['submit_stop'])) {
        // 停止のフォームが送信された場合
        $stopEmail = $_POST['stop_email'];

        // 停止リストにメールアドレスを追加
        $stopList = file($stopListFile, FILE_IGNORE_NEW_LINES);
        if (!in_array($stopEmail, $stopList)) {
            file_put_contents($stopListFile, $stopEmail . PHP_EOL, FILE_APPEND);
            $message = "通知を停止しました。";
        } else {
            $message = "すでに通知は停止されています。";
        }
    }

    // フォーム送信後にメッセージを表示
    header('Location: index.php?message=' . urlencode($message));
    exit;
}

// メール通知関数
function sendEmailNotification($email, $url) {
    $subject = "ページが更新されました";
    $message = "指定したページに変更がありました。確認はこちら: " . $url;
    $headers = "From: no-reply@example.com\r\n";

    // メール送信
    if (!mail($email, $subject, $message, $headers)) {
        echo "メール送信に失敗しました。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ページ更新通知システム</title>

<style>
body {
  background-image: url("back.jpg");
  background-size: cover;
}

body {
    font-family:  "ヒラギノ角ゴ ProN W3", 游ゴシック, "Yu Gothic", メイリオ, Meiryo, Verdana, sans-serif;
}

</style>


</head>
<body>
<center>
    <h1>ページ更新通知アプリ</h1>
指定したURLページに更新があった場合、メールで通知します。<br><br>

    <!-- フォーム送信後のメッセージ表示 -->
    <?php
    if (isset($_GET['message'])) {
        echo "<p>{$_GET['message']}</p>";
    }
    ?>

    <!-- ページ更新監視フォーム -->
    <h2>ページ更新監視</h2>
    <form action="index.php" method="post">
        <label for="page_url">監視するページのURL:</label><br>
        <input type="text" id="page_url" name="page_url" required><br><br>

        <label for="email">通知先のメールアドレス:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <input type="submit" name="submit_watch" value="ページを監視する">
    </form>

    <hr>

    <!-- メール通知停止フォーム -->
    <h2>通知停止</h2>
    <form action="index.php" method="post">
        <label for="stop_email">通知停止を希望するメールアドレス:</label><br>
        <input type="email" id="stop_email" name="stop_email" required><br><br>

        <input type="submit" name="submit_stop" value="通知を停止する">
    </form>
</center>



</body>
</html>
