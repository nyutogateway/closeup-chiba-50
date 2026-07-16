<?php
/* 投稿詳細（掲載企業の記事）
   ※出力するHTMLは静的版 js/main.js の openCard() と同一マークアップ。
     デザインを変えないため、クラス名・階層・要素種別は変更しないこと。 */

$d = cu_get_entry(get_the_ID());
$who = cu_who($d);
$no  = cu_entry_no(get_the_ID());

/* PREV/NEXT は静的版と同じく循環（最初/最後でも途切れない） */
$entries = cu_get_entries(50);
$ids = array();
foreach ($entries as $e) { $ids[] = $e->ID; }
$cnt = count($ids);
$cur = array_search(get_the_ID(), $ids, true);
$prev_id = ($cnt && $cur !== false) ? $ids[($cur - 1 + $cnt) % $cnt] : 0;
$next_id = ($cnt && $cur !== false) ? $ids[($cur + 1) % $cnt] : 0;
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php the_title(); ?> | <?php bloginfo('name'); ?></title>
  <?php get_template_part('lib/tpl/js', 'js'); ?>
  <?php get_template_part('lib/tpl/css', 'css'); ?>
  <?php get_template_part('lib/tpl/og', 'og'); ?>
  <?php wp_head(); ?>
</head>

<!-- is-detail：静的版の記事表示時と同じくチバテレ背景バッジを隠す -->
<body class="is-detail">
<?php get_template_part('lib/tpl/body_before', 'body_before'); ?>
<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<!-- #detail はCSSで既定 display:none のため、実ページでは表示指定する -->
<article id="detail" class="l-container" style="display:block">
  <div class="p-article__hero">
    <?php if ($d['hero']) : ?>
      <img src="<?php echo esc_url($d['hero']); ?>" alt="<?php echo esc_attr($d['corp']); ?>">
    <?php else : echo cu_wide_svg($no); endif; ?>
    <div class="p-article__hero-caption">
      <?php if ($d['star']) : ?><p class="p-article__stars"><?php echo str_repeat('★', $d['star']); ?></p><?php endif; ?>
      <p class="p-article__corp"><?php echo esc_html($d['corp']); ?></p>
      <h1 class="p-article__title u-serif"><?php echo esc_html($d['head']); ?></h1>
      <?php if ($who) : ?><p class="p-article__person"><?php echo esc_html($who); ?></p><?php endif; ?>
    </div>
  </div>

  <div class="p-article__body"><?php the_content(); ?></div>

  <section class="p-article__info">
    <div class="p-article__data-head">COMPANY DATA</div>
    <p class="p-article__data-name u-serif"><?php echo esc_html($d['corp']); ?></p>
    <?php if ($d['name']) : ?>
      <p class="p-article__data-rep"><?php echo esc_html($d['name']) . ($d['role'] ? '（' . esc_html($d['role']) . '）' : ''); ?></p>
    <?php endif; ?>
    <?php if ($d['prof']) : ?>
      <p class="p-article__data-prof"><?php echo esc_html($d['prof']); ?></p>
    <?php endif; ?>
    <?php if ($d['url']) : ?>
      <p class="p-article__data-url"><a class="c-link" href="<?php echo esc_url($d['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($d['url']); ?>　↗</a></p>
    <?php endif; ?>
  </section>

  <?php if ($prev_id && $next_id) : ?>
  <nav class="p-article__nav">
    <button type="button" onclick="location.href='<?php echo esc_url(get_permalink($prev_id)); ?>'">
      <span class="p-article__nav-dir">← PREV ｜ No.<?php echo cu_n2(cu_entry_no($prev_id)); ?></span>
      <span class="p-article__nav-title u-serif"><?php echo esc_html(get_the_title($prev_id)); ?></span>
    </button>
    <button type="button" class="p-article__nav-next" onclick="location.href='<?php echo esc_url(get_permalink($next_id)); ?>'">
      <span class="p-article__nav-dir">NEXT ｜ No.<?php echo cu_n2(cu_entry_no($next_id)); ?> →</span>
      <span class="p-article__nav-title u-serif"><?php echo esc_html(get_the_title($next_id)); ?></span>
    </button>
  </nav>
  <?php endif; ?>
</article>
<?php endwhile; endif; ?>

<?php get_footer(); ?>
<?php get_template_part('lib/tpl/foot_js', 'foot_js'); ?>
<?php wp_footer(); ?>
</body>

</html>
