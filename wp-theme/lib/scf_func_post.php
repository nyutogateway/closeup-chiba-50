<?php

/**
 * カスタムフィールド 投稿（掲載企業）
 * ※前任テーマのフィールド名を踏襲し、リニューアルで必要な
 *   「記事見出し」「代表役職」「星の数」を追加している。
 */

function my_register_fields_entry($settings, $type, $id, $meta_type) {
  if ($type == 'post') {
    $Setting = SCF::add_setting('entry_setting', '設定');
    $Setting->add_group('entry_setting', false, array(
      // head
      array(
        'type'  => 'image',
        'name'  => 'detail_head_thumb',
        'size'  => 'full',
        'label' => 'メインイメージ',
      ),
      array(
        'type'  => 'image',
        'name'  => 'detail_head_logo',
        'size'  => 'full',
        'label' => '会社ロゴ',
      ),
      array(
        'type'  => 'textarea',
        'name'  => 'detail_head_copy',
        'label' => '記事見出し（キャッチコピー）',
      ),
      array(
        'type'  => 'text',
        'name'  => 'detail_head_name',
        'label' => '代表氏名',
      ),
      array(
        'type'  => 'text',
        'name'  => 'detail_head_role',
        'label' => '代表役職',
      ),
      array(
        'type'  => 'textarea',
        'name'  => 'detail_head_profiletxt',
        'label' => '代表のプロフィール',
      ),
      array(
        'type'  => 'text',
        'name'  => 'detail_info_url',
        'label' => '企業URL',
      ),
      array(
        'type'  => 'text',
        'name'  => 'detail_info_name',
        'label' => '代表者',
      ),
      array(
        'type'    => 'select',
        'name'    => 'detail_star',
        'label'   => '星の数',
        'choices' => array('0', '2', '3'),
        'default' => '0',
      ),
    ));
    $settings[] = $Setting;
  }

  return $settings;
}
add_filter('smart-cf-register-fields', 'my_register_fields_entry', 10, 4);

?>
