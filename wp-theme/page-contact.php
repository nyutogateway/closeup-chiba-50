<?php
/*
Template Name: お問い合わせページテンプレート
Template Post Type: page
*/
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>お問い合わせ | <?php bloginfo('name'); ?></title>
  <?php get_template_part('lib/tpl/js', 'js'); ?>
  <?php get_template_part('lib/tpl/css', 'css'); ?>
  <?php get_template_part('lib/tpl/og', 'og'); ?>
  <?php wp_head(); ?>
</head>

<body>
<?php get_template_part('lib/tpl/body_before', 'body_before'); ?>
<?php get_header(); ?>

<main class="l-container">
  <div class="p-page__inner">
    <a class="p-article__back" href="<?php echo HOME_URL; ?>"><span>←</span> トップへ戻る</a>
    <p class="p-page__kick">CONTACT</p>
    <h1 class="p-page__title u-serif">お問い合わせ</h1>
    <p class="p-page__lead">クローズアップCHIBA50への取材のご依頼、番組・記事に関するご意見・ご要望は、下記の運営事務局までお願いいたします。</p>
    <dl class="p-page__info">
      <dt>運営</dt><dd>クローズアップCHIBA 事務局</dd>
    </dl>
    <?php
    /* 本文にフォーム系プラグインのショートコードを入れる想定。
       未入力の場合は静的版と同じデザイン確認用フォームを表示する。 */
    $has_content = false;
    if (have_posts()) : while (have_posts()) : the_post();
      $c = trim(get_the_content());
      if ($c !== '') { $has_content = true; ?>
        <div class="p-form"><?php the_content(); ?></div>
      <?php }
    endwhile; endif;
    if (!$has_content) : ?>
    <form class="p-form" onsubmit="return cformSubmit(this)">
      <h2 class="p-form__heading">フォームからのお問い合わせ</h2>
      <div class="p-form__row">
        <label for="cfName">お名前 <span class="p-form__required">必須</span></label>
        <input id="cfName" type="text" name="name" required autocomplete="name">
      </div>
      <div class="p-form__row">
        <label for="cfMail">メールアドレス <span class="p-form__required">必須</span></label>
        <input id="cfMail" type="email" name="email" required autocomplete="email" placeholder="example@chiba-tv.com">
      </div>
      <div class="p-form__row">
        <label for="cfBody">お問い合わせ内容 <span class="p-form__required">必須</span></label>
        <textarea id="cfBody" name="body" rows="6" required></textarea>
      </div>
      <button type="submit" class="p-form__submit">送信する</button>
      <p class="p-form__done" id="cfDone" hidden>お問い合わせを受け付けました。担当者より折り返しご連絡いたします。<br><small>※ これはデザイン確認用のプロトタイプです。実際には送信されません。</small></p>
    </form>
    <?php endif; ?>
    <p class="p-page__note">※ 当運営事務局は、ウェブサイトに情報を掲載するにあたり十分に確認作業を行っておりますが、その内容の正確性・有用性・安全性等について保証するものではありません。掲載情報の利用により生じたトラブルや損害について、当社は一切責任を負いません。</p>
  </div>
</main>

<?php get_footer(); ?>
<script>
function cformSubmit(f){
  f.querySelector('.p-form__submit').disabled=true;
  document.getElementById('cfDone').hidden=false;
  document.getElementById('cfDone').scrollIntoView({behavior:'smooth',block:'center'});
  return false; // プロトタイプのため実送信はしない
}
</script>
<?php get_template_part('lib/tpl/foot_js', 'foot_js'); ?>
<?php wp_footer(); ?>
</body>

</html>
