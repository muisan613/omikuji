<?php
$pub = esc_attr(get_option('omikuji_pro_pub_key',''));
?>
<div class="omikuji-wrap">
  <div class="omikuji-card">
    <div class="omikuji-title">電子おみくじ（Pro）</div>
    <button id="omikuji-pro-draw" class="omikuji-btn" data-publishable="<?php echo $pub; ?>">おみくじを引く</button>
  </div>
</div>
