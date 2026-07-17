<?php
/**
 * 掲載企業50投稿を一括生成する（WP-CLI）。
 * タイトル・本文・カスタムフィールド・画像（ロゴ/メイン/本文）を投入する。
 *
 * 前提:
 *   1) entries-full.json を tools/ に置く（このリポジトリに同梱）
 *   2) 画像フォルダ（assets/photos の中身）をサーバーの分かる場所に置く
 *      例: wp-content/uploads/_import_photos/
 *
 * 使い方（WPルートで実行）:
 *   wp eval-file tools/wp-import-posts.php -- --photos=/絶対パス/_import_photos
 *        … dry-run（作成内容の確認のみ）
 *   wp eval-file tools/wp-import-posts.php -- --photos=/絶対パス/_import_photos --apply
 *        … 実際に作成
 *
 * 補足:
 *   - 同名タイトルの投稿が既にあればスキップ（重複作成しない）。
 *   - 画像は同名ファイルが既にメディアにあれば再アップせず使い回す。
 *   - detail_head_role / detail_star / detail_head_copy などSCF/ACF両対応
 *     （update_field があれば使用、無ければ update_post_meta）。
 */

if (!defined('WP_CLI') || !WP_CLI) { echo "WP-CLI で実行してください。\n"; exit; }

$apply     = in_array('--apply', $args, true);
$photosDir = '';
foreach ($args as $a) {
  if (strpos($a, '--photos=') === 0) $photosDir = rtrim(substr($a, 9), '/');
}
if (!$photosDir || !is_dir($photosDir)) {
  WP_CLI::error('--photos=画像フォルダの絶対パス を指定してください（assets/photos の中身を置いた場所）。');
}

$json = __DIR__ . '/entries-full.json';
if (!file_exists($json)) WP_CLI::error('entries-full.json が見つかりません: ' . $json);
$entries = json_decode(file_get_contents($json), true);
if (!$entries) WP_CLI::error('entries-full.json を読めませんでした。');

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$norm = function ($s) { return preg_replace('/[\s　]+/u', '', (string) $s); };

/* 既存投稿タイトル → ID */
$existing = array();
foreach (get_posts(array('post_type' => 'post', 'posts_per_page' => -1, 'post_status' => 'any')) as $p) {
  $existing[$norm($p->post_title)] = $p->ID;
}

/* 画像ファイル名 → 添付ID（同名は使い回してメディア重複を防ぐ） */
$mediaCache = array();
function cu_import_media($filename, $photosDir, $post_id, $apply) {
  global $mediaCache;
  if (!$filename) return 0;
  if (isset($mediaCache[$filename])) return $mediaCache[$filename];

  // 既にメディアにある同名ファイルを探す
  $found = get_posts(array(
    'post_type' => 'attachment', 'posts_per_page' => 1, 'post_status' => 'inherit',
    'meta_query' => array(array('key' => '_wp_attached_file', 'value' => $filename, 'compare' => 'LIKE')),
  ));
  if ($found) { $mediaCache[$filename] = $found[0]->ID; return $found[0]->ID; }

  $path = $photosDir . '/' . $filename;
  if (!file_exists($path)) { WP_CLI::warning('  画像なし: ' . $filename); return 0; }
  if (!$apply) { return -1; } // dry-run

  $tmp = wp_tempnam($filename);
  copy($path, $tmp);
  $file_array = array('name' => $filename, 'tmp_name' => $tmp);
  $id = media_handle_sideload($file_array, $post_id);
  if (is_wp_error($id)) { @unlink($tmp); WP_CLI::warning('  取込失敗: ' . $filename); return 0; }
  $mediaCache[$filename] = $id;
  return $id;
}

/* カスタムフィールド保存（ACF/SCF/plain 兼用） */
function cu_set_field($post_id, $name, $value) {
  if (function_exists('update_field')) update_field($name, $value, $post_id);
  else update_post_meta($post_id, $name, $value);
}

$created = 0; $skipped = 0;

foreach ($entries as $e) {
  $key = $norm($e['corp']);
  if (isset($existing[$key])) {
    WP_CLI::log(sprintf('[SKIP] No.%02d %s（既存）', $e['no'], $e['corp']));
    $skipped++;
    continue;
  }

  WP_CLI::log(sprintf('%s No.%02d %s', $apply ? '[作成]' : '[確認]', $e['no'], $e['corp']));

  if (!$apply) { continue; }

  // 投稿を作成（本文は画像取込後に更新）
  $post_id = wp_insert_post(array(
    'post_type'   => 'post',
    'post_status' => 'publish',
    'post_title'  => $e['corp'],
    'post_content'=> '',
  ), true);
  if (is_wp_error($post_id)) { WP_CLI::warning('  作成失敗: ' . $e['corp']); continue; }

  // 画像（ロゴ・メイン）
  $logo_id  = cu_import_media($e['logo'],  $photosDir, $post_id, $apply);
  $thumb_id = cu_import_media($e['thumb'], $photosDir, $post_id, $apply);

  // 本文HTMLを組み立て（静的版 openCard と同じ構造：最初のセクションは見出し省略）
  $body = '';
  foreach ($e['secs'] as $i => $s) {
    if ($i !== 0 && !empty($s['h'])) $body .= '<h3>' . esc_html($s['h']) . "</h3>\n";
    foreach ($s['ps'] as $p) $body .= '<p>' . esc_html($p) . "</p>\n";
    foreach ($s['imgs'] as $img) {
      $iid = cu_import_media($img, $photosDir, $post_id, $apply);
      if ($iid > 0) {
        $u = wp_get_attachment_image_url($iid, 'full');
        $body .= '<figure class="p-article__figure"><img src="' . esc_url($u) . '" alt="" /></figure>' . "\n";
      }
    }
  }
  wp_update_post(array('ID' => $post_id, 'post_content' => $body));

  // カスタムフィールド
  if ($thumb_id > 0) cu_set_field($post_id, 'detail_head_thumb', $thumb_id);
  if ($logo_id  > 0) cu_set_field($post_id, 'detail_head_logo',  $logo_id);
  cu_set_field($post_id, 'detail_head_copy',       $e['head']);
  cu_set_field($post_id, 'detail_head_name',       $e['name']);
  cu_set_field($post_id, 'detail_head_role',       $e['role']);
  cu_set_field($post_id, 'detail_head_profiletxt', $e['prof']);
  cu_set_field($post_id, 'detail_info_url',        $e['url']);
  cu_set_field($post_id, 'detail_info_name',       $e['name']);
  cu_set_field($post_id, 'detail_star',            (string) $e['star']);

  $created++;
}

if ($apply) WP_CLI::success("作成 {$created} 件 / スキップ {$skipped} 件");
else WP_CLI::success("dry-run（未作成）。実行するには末尾に --apply を付けてください。");
