<?php
$pageUrl = "";
if ( is_single() || is_page() ) {
  global $post;
  $pageUrl = $post->post_name;
}
?>

<link rel="canonical" href="<?php echo HOME_URL.$pageUrl; ?>" />
