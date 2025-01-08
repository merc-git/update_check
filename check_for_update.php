<?php
// 更新チェックの結果を返すJSONレスポンスを返すスクリプト

// URLをフォームから受け取る
$url = isset($_GET['url']) ? $_GET['url'] : '';  // URLが渡されていない場合は空にする

if ($url) {
    while (true) {
        // ページのHTMLを取得
        $html = file_get_contents($url);

        // ページのハッシュを計算
        $currentHash = hash('sha256', $html);

        // 前回のハッシュをファイルから取得
        $hashFile = 'page_hash.txt';
        $lastHash = file_exists($hashFile) ? file_get_contents($hashFile) : '';

        // ハッシュが異なれば、更新を通知
        if ($currentHash !== $lastHash) {
            echo json_encode(['updateAvailable' => true]);
            // 更新後、ハッシュをファイルに保存
            file_put_contents($hashFile, $currentHash);
        } else {
            echo json_encode(['updateAvailable' => false]);
        }

        // 1時間待つ
        sleep(3600);  // 3600秒 = 1時間
    }
} else {
    echo json_encode(['error' => 'URLが指定されていません。']);
}
?>
