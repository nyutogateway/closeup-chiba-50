<?php
$pageUrl = "";
if ( is_single() || is_page() ) {
  global $post;
  $pageUrl = $post->post_name;
}
// canonical は環境に関係なく必ず本番(chiba-tv)を指す（ステージング経由でも chiba-tv に届く）
$canonical_base = defined('PROD_URL') ? PROD_URL : HOME_URL;
?>

<link rel="canonical" href="<?php echo $canonical_base . $pageUrl; ?>" />
