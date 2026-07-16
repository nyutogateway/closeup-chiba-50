<?php

/*---サイトURL---*/
function shortcode_siteurl() {
  return home_url('/');
}
add_shortcode('site_url', 'shortcode_siteurl');

/*---テンプレートURL---*/
function shortcode_templateurl() {
  return get_stylesheet_directory_uri();
}
add_shortcode('template_url', 'shortcode_templateurl');

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');

//
add_filter('allow_dev_auto_core_updates', '__return_false');
add_filter('allow_minor_auto_core_updates', '__return_false');
add_filter('auto_update_plugin', '__return_false');
add_filter('auto_update_theme', '__return_false');
add_filter('auto_update_translation', '__return_false');
add_filter('auto_update_core', '__return_false');
add_filter('automatic_updater_disabled', '__return_true');

//
add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');

if (is_admin()) {
  add_filter('allow_minor_auto_core_updates', '__return_true');
  add_filter('auto_update_plugin', '__return_true');
  add_filter('auto_update_theme', '__return_true');
  add_filter('auto_update_translation', '__return_true');
  add_filter('auto_update_core', '__return_true');
  add_filter('automatic_updater_disabled', '__return_false');
  if (function_exists('add_theme_support')) {
    add_theme_support('post-formats');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
  }
  add_filter('post_gallery', '__return_false');
}

// WordPress の投稿スラッグを自動的に生成する
function auto_post_slug($slug, $post_ID, $post_status, $post_type) {
  if ($post_type == 'post') {
    if (preg_match('/(%[0-9a-f]{2})+/', $slug)) {
      $slug = 'post-' . $post_ID;
    }
  } else {
    if (preg_match('/(%[0-9a-f]{2})+/', $slug)) {
      $slug = utf8_uri_encode($post_type) . '-' . $post_ID;
    }
  }
  return $slug;
}
add_filter('wp_unique_post_slug', 'auto_post_slug', 10, 4);

?>
