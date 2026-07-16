<?php

/**
 * 掲載企業まわりの共通ヘルパー
 * ※出力するHTMLは静的版（js/main.js の cardHTML / openCard）と完全に同一。
 *   デザインを変えないため、クラス名・階層・属性は変更しないこと。
 */

/* SCFの画像IDを相対パスのURLに変換（前任テーマと同じ方式） */
function cu_img_src($attachment_id) {
  if (!$attachment_id) return '';
  $img = wp_get_attachment_image_src($attachment_id, 'full', true);
  if (!$img) return '';
  return preg_replace('/https:\/\/umi\.design\/prod/', '', $img[0]);
}

/* 投稿から表示用データを取り出す */
function cu_get_entry($post_id = null) {
  $post_id = $post_id ? $post_id : get_the_ID();
  $name = SCF::get('detail_info_name', $post_id);
  if (!$name) $name = SCF::get('detail_head_name', $post_id);
  return array(
    'corp'  => get_the_title($post_id),
    'head'  => SCF::get('detail_head_copy', $post_id),
    'name'  => $name,
    'role'  => SCF::get('detail_head_role', $post_id),
    'prof'  => SCF::get('detail_head_profiletxt', $post_id),
    'url'   => SCF::get('detail_info_url', $post_id),
    'star'  => (int) SCF::get('detail_star', $post_id),
    'photo' => cu_img_src(SCF::get('detail_head_logo', $post_id)),
    'hero'  => cu_img_src(SCF::get('detail_head_thumb', $post_id)),
  );
}

/* 「役職　氏名」を全角スペース区切りで返す（静的版の who と同じ） */
function cu_who($d) {
  $parts = array_filter(array($d['role'], $d['name']));
  return implode('　', $parts);
}

/* 写真が無い場合のプレースホルダSVG（静的版 phSvg と同一） */
function cu_ph_svg($no) {
  $bg = array('#E7E6E0', '#E1E0DA', '#ECEBE5', '#E4E3DD');
  $fg = array('#CFCEC7', '#C8C7C0', '#D3D2CB', '#CBCAC3');
  $b = $bg[$no % 4];
  $f = $fg[$no % 4];
  return '<svg viewBox="0 0 100 125" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">'
    . '<rect width="100" height="125" fill="' . $b . '"/>'
    . '<circle cx="50" cy="47" r="19" fill="' . $f . '"/>'
    . '<path d="M20 125 C20 98 34 82 50 82 C66 82 80 98 80 125 Z" fill="' . $f . '"/></svg>';
}

/* 記事メイン画像が無い場合のプレースホルダSVG（静的版 wideSvg と同一） */
function cu_wide_svg($no) {
  $bg = array('#E7E6E0', '#E1E0DA', '#ECEBE5', '#E4E3DD');
  $fg = array('#CFCEC7', '#C8C7C0', '#D3D2CB', '#CBCAC3');
  $b = $bg[$no % 4];
  $f = $fg[$no % 4];
  return '<svg viewBox="0 0 160 90" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">'
    . '<rect width="160" height="90" fill="' . $b . '"/>'
    . '<circle cx="80" cy="42" r="21" fill="' . $f . '"/>'
    . '<path d="M44 90 C44 68 60 55 80 55 C100 55 116 68 116 90 Z" fill="' . $f . '"/></svg>';
}

/* 星（★の繰り返し）。0なら出力しない */
function cu_stars_html($star) {
  if (!$star) return '';
  return '<span class="p-card__star" aria-label="注目企業 ★' . $star . '">' . str_repeat('★', $star) . '</span>';
}

/**
 * 一覧カード1枚分のHTML（静的版 cardHTML と同一マークアップ）
 * 静的版は div+onclick だったが、WPでは実ページへのリンク(a)にする。
 * クラス名は変えていないためデザインは同一。
 */
function cu_card_html($post_id) {
  $d = cu_get_entry($post_id);
  $who = cu_who($d);
  $ph = $d['photo']
    ? '<img src="' . esc_url($d['photo']) . '" alt="' . esc_attr($d['corp']) . '" loading="lazy">'
    : cu_ph_svg(cu_entry_no($post_id));
  $html  = '<a class="p-card" href="' . esc_url(get_permalink($post_id)) . '" aria-label="' . esc_attr($d['corp']) . '">';
  $html .= '<div class="p-card__photo">' . $ph . cu_stars_html($d['star'])
        . '<div class="p-card__caption">' . esc_html($d['head']) . '</div></div>';
  $html .= '<div class="p-card__name u-serif">' . esc_html($d['corp']) . '</div>';
  if ($who) $html .= '<div class="p-card__company">' . esc_html($who) . '</div>';
  $html .= '</a>';
  return $html;
}

/* 掲載企業の投稿を全件取得（表示順は前任テーマと同じ DESC） */
function cu_get_entries($limit = 50) {
  return get_posts(array(
    'post_type'      => 'post',
    'posts_per_page' => $limit,
    'post_status'    => array('publish'),
    'has_password'   => false,
    'order'          => 'DESC',
  ));
}

/* 掲載番号（一覧と同じ並び順での1始まりの通し番号）。PREV/NEXTの No.XX 表示に使用 */
function cu_entry_no($post_id) {
  static $map = null;
  if ($map === null) {
    $map = array();
    $i = 1;
    foreach (cu_get_entries(50) as $p) { $map[$p->ID] = $i++; }
  }
  return isset($map[$post_id]) ? $map[$post_id] : 0;
}

/* No.XX の2桁ゼロ埋め（静的版 n2 と同じ） */
function cu_n2($n) {
  return str_pad((int) $n, 2, '0', STR_PAD_LEFT);
}

?>
