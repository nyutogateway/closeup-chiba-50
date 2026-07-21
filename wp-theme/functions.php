<?php

get_template_part('lib/init');
get_template_part('lib/scf_func_post');
get_template_part('lib/temp_func');

remove_action('wp_head', 'wp_generator');
add_filter('emoji_svg_url', '__return_false');
// WP標準のcanonical出力を止める（canonicalは og.php で chiba-tv 固定で出すため）
remove_action('wp_head', 'rel_canonical');

// URL設定（chiba-tv本番 と ローカル/テスト の両対応）
//   本番運用は前任と同じ（umi.design/prod で動かし chiba-tv へ配信）。
//   ・本番(umi.design or chiba-tv でアクセス): 前任と同じく chiba-tv 固定
//   ・テスト/ローカル(yutophp.net 等)         : 実ドメインを使い、サイト内を回遊できる
$cu_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
if (strpos($cu_host, 'chiba-tv.com') !== false || strpos($cu_host, 'umi.design') !== false) {
  // 本番: 前任と同じ chiba-tv 固定（テーマフォルダ名は自動取得）
  define('BASE_URL', 'https://www.chiba-tv.com/closeup/wp-content/themes/' . get_template());
  define('HOME_URL', 'https://www.chiba-tv.com/closeup/');
} else {
  // テスト/ローカル: 実際に動いているドメイン
  define('BASE_URL', get_template_directory_uri());
  define('HOME_URL', home_url('/'));
}

// 公開URL（canonical専用）。環境に関係なく必ず本番(chiba-tv)へ向けるため固定値。
define('PROD_URL', 'https://www.chiba-tv.com/closeup/');

// 画像の書き出し品質
function custom_wp_editor_set_quality($quality){
  return 100;
}
add_filter('wp_editor_set_quality', 'custom_wp_editor_set_quality');

add_filter('wp_calculate_image_srcset_meta', '__return_null');

// 絶対パス→相対パス
function make_href_root_relative($input) {
  return preg_replace('!http(s)?://' . $_SERVER['SERVER_NAME'] . '/prod/!', '/', $input);
}
// パーマリンク絶対パス→相対パス
function root_relative_permalinks($input) {
  return make_href_root_relative($input);
}
add_filter('the_permalink', 'root_relative_permalinks');

// 本文中の画像パスを相対化
add_filter('the_content', 'rewrite_image_paths');
function rewrite_image_paths($content) {
  return preg_replace('/http(s)?:\/\/umi\.design\/prod/', '', $content);
}

/* 本文中の figure に静的版と同じクラスを付与（CSSを変えずに同じ見た目を保つため） */
add_filter('the_content', 'cu_article_figure_class');
function cu_article_figure_class($content) {
  if (!is_single()) return $content;
  return preg_replace('/<figure\b[^>]*>/i', '<figure class="p-article__figure">', $content);
}

add_theme_support('post-thumbnails');

?>
