<?php
/**
 * 既存WP投稿へ、リニューアルで追加したカスタムフィールドを流し込むスクリプト。
 *   detail_head_copy … 記事見出し（キャッチコピー）
 *   detail_head_role … 代表役職
 *   detail_star      … 星の数（0/2/3）
 *
 * 既存の前任フィールド（メイン画像・氏名・プロフィール・ロゴ・URL）は触らない。
 *
 * 使い方（WPのルートで実行）:
 *   wp eval-file tools/wp-import-fields.php              … 実行内容の確認のみ（dry-run）
 *   wp eval-file tools/wp-import-fields.php -- --apply   … 実際に更新
 *
 * ※ 投稿タイトル（会社名）で突き合わせる。
 */

$apply = in_array('--apply', $args ?? [], true);
$json  = __DIR__ . '/entries.json';

if (!file_exists($json)) {
  WP_CLI::error('entries.json が見つかりません: ' . $json);
}
$entries = json_decode(file_get_contents($json), true);
if (!$entries) {
  WP_CLI::error('entries.json を読めませんでした。');
}

/* タイトル → 投稿ID の対応表を作る（全角/半角スペースの揺れを吸収） */
$norm = function ($s) {
  $s = preg_replace('/[\s　]+/u', '', (string) $s);
  return $s;
};
$posts = get_posts([
  'post_type'      => 'post',
  'posts_per_page' => -1,
  'post_status'    => ['publish', 'draft', 'private'],
]);
$byTitle = [];
foreach ($posts as $p) {
  $byTitle[$norm($p->post_title)] = $p->ID;
}

$updated = 0;
$missing = [];

foreach ($entries as $e) {
  $key = $norm($e['corp']);
  if (!isset($byTitle[$key])) {
    $missing[] = $e['corp'];
    continue;
  }
  $id = $byTitle[$key];

  WP_CLI::log(sprintf(
    '%s No.%02d %s ｜ 見出し:%s ／ 役職:%s ／ 星:%d',
    $apply ? '[更新]' : '[確認]',
    $e['no'],
    $e['corp'],
    mb_strimwidth($e['head'], 0, 34, '…'),
    $e['role'] !== '' ? $e['role'] : '(なし)',
    $e['star']
  ));

  if ($apply) {
    update_post_meta($id, 'detail_head_copy', $e['head']);
    update_post_meta($id, 'detail_head_role', $e['role']);
    update_post_meta($id, 'detail_star', (string) $e['star']);
    $updated++;
  }
}

if ($missing) {
  WP_CLI::warning('WPに該当投稿が見つからなかった会社（タイトル要確認）:');
  foreach ($missing as $m) { WP_CLI::log('  - ' . $m); }
}

if ($apply) {
  WP_CLI::success($updated . ' 件を更新しました。');
} else {
  WP_CLI::success('dry-run（未更新）。実行するには末尾に -- --apply を付けてください。');
}
