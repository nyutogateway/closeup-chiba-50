<?php

get_template_part('lib/init');
get_template_part('lib/scf_func_post');
get_template_part('lib/temp_func');

remove_action('wp_head', 'wp_generator');
add_filter('emoji_svg_url', '__return_false');

// BASE URL設定
// テーマ/サイトの実際の場所を指すようにする（本番・ローカルどちらでもCSS/JSが読める）。
// 本番で固定パスに寄せる必要が出たら下のコメント値に差し替える。
define('BASE_URL', get_template_directory_uri());
define('HOME_URL', home_url('/'));
// define('BASE_URL', 'https://www.chiba-tv.com/closeup/wp-content/themes/theme');
// define('HOME_URL', 'https://www.chiba-tv.com/closeup/');

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
