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
  <?php get_footer(); ?>
  <?php get_template_part('lib/tpl/foot_js', 'foot_js'); ?>
  <?php wp_footer(); ?>
</body>

</html>
