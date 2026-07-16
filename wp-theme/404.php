<?php
/* 404ページ表示ファイル */
//トップへリダイレクト
$home_url = HOME_URL;
header('Location:' .$home_url, true, 303);
exit;
?>
