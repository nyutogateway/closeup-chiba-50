<!-- フッターは #home / #detail の外に置き、一覧でも記事でも常に表示する -->
<footer class="l-footer">
  <div class="l-container l-footer__inner">
    <a class="l-footer__plate" href="https://www.chiba-tv.com/" target="_blank" rel="noopener" aria-label="チバテレ 千葉テレビ放送 公式サイト"><img class="l-footer__logo" src="<?php echo BASE_URL; ?>/assets/img/footer_logo.png" alt="チバテレ 千葉テレビ放送"></a>
    <div class="l-footer__links">
      <a href="<?php echo HOME_URL; ?>contact/">CONTACT</a>
      <a href="<?php echo HOME_URL; ?>policy/">PRIVACY POLICY</a>
    </div>
    <div class="l-footer__copyright">Copyright © Chiba Television broadcasting Corp.</div>
  </div>
</footer>
<?php // チバテレ背景バッジは地図に重ねるトップ演出のため front-page.php でのみ出力（contact/policy/記事では出さない） ?>
