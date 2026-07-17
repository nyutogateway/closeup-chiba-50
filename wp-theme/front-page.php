<?php
/*
Template Name: TOPページテンプレート
Template Post Type: page
*/

$entries = cu_get_entries(50);
$total   = count($entries);

/* グループ分け（静的版 renderGrid と同一ロジック：約7枚ずつ均等配分） */
$groups = array();
if ($total) {
  $n = (int) ceil($total / 7);
  $idx = 0;
  for ($g = 0; $g < $n; $g++) {
    $size = (int) ceil(($total - $idx) / ($n - $g));
    $groups[] = array_slice($entries, $idx, $size);
    $idx += $size;
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php bloginfo('name'); ?></title>
  <?php get_template_part('lib/tpl/js', 'js'); ?>
  <?php get_template_part('lib/tpl/css', 'css'); ?>
  <?php get_template_part('lib/tpl/og', 'og'); ?>
  <?php wp_head(); ?>
</head>

<body>
<?php get_template_part('lib/tpl/body_before', 'body_before'); ?>
<?php get_header(); ?>

<main id="home">
  <section class="p-hero">
    <div class="p-hero__wall" id="logowall" aria-hidden="true"><?php
      /* ヒーロー背景のロゴウォール（静的版と同じく120枚を巡回して敷き詰める） */
      if ($total) {
        for ($i = 0; $i < 120; $i++) {
          $p = $entries[$i % $total];
          $d = cu_get_entry($p->ID);
          echo '<div class="p-hero__mark">';
          if ($d['photo']) {
            echo '<img src="' . esc_url($d['photo']) . '" alt="" loading="lazy">';
          } else {
            $label = preg_replace('/^(株式会社|有限会社|学校法人|社会福祉法人)/u', '', $d['corp']);
            echo '<span>' . esc_html(mb_substr($label, 0, 5)) . '</span>';
          }
          echo '</div>';
        }
      }
    ?></div>
    <div class="p-hero__spot" id="spot" aria-hidden="true"></div>
    <div class="p-hero__vignette" aria-hidden="true"></div>
    <div class="p-hero__content">
      <img class="p-hero__logo" src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="クローズアップ千葉50 ― 千葉を牽引するものたち">
      <div class="p-hero__prism"></div>
    </div>
    <div class="p-hero__scroll">SCROLL</div>
  </section>

  <div class="l-container">
    <section class="p-concept" id="concept">
      <p class="p-concept__kick u-reveal">CONCEPT</p>
      <h2 class="p-concept__lead u-serif u-reveal">クローズアップCHIBA50とは？</h2>
      <div class="p-concept__body u-reveal">
        <p>今なお世界有数の大都市圏を形成する、日本に誇る都道府県のひとつ、千葉県。古くは将軍家のお膝元となった江戸の発展を支えてきました。</p>
        <p>そんな千葉県を牽引する今注目の企業や専門家等をクローズアップし、さらなる千葉県の成長のヒントを探っていきます。</p>
      </div>
    </section>

    <section class="p-leaders" id="leaders">
      <div class="p-leaders__bar u-reveal">
        <p class="p-leaders__kick">LEADERS</p>
        <h2 class="u-serif">千葉を牽引する<span class="p-leaders__num">50</span>社</h2>
        <div class="p-leaders__line"></div>
      </div>
    </section>

    <div id="groups"><?php
      foreach ($groups as $gi => $grp) :
        $count = count($grp);
    ?>
      <div class="p-group u-reveal">
        <div class="p-group__head"><span class="p-group__label">GROUP</span><span class="p-group__num"><?php echo str_pad($gi + 1, 2, '0', STR_PAD_LEFT); ?></span></div>
        <div class="p-group__view"><div class="p-group__track"><?php
          foreach ($grp as $p) { echo cu_card_html($p->ID); }
        ?></div></div>
        <div class="p-group__nav">
          <button type="button" class="p-group__btn p-group__prev" aria-label="前へ"><span>‹</span></button>
          <span class="p-group__count"><b class="p-group__current">1</b><i>/</i><?php echo $count; ?></span>
          <button type="button" class="p-group__btn p-group__next" aria-label="次へ"><span>›</span></button>
        </div>
      </div>
    <?php endforeach; ?></div>
  </div>
</main>

<?php get_footer(); ?>
<?php get_template_part('lib/tpl/foot_js', 'foot_js'); ?>
<?php wp_footer(); ?>
</body>

</html>
