<?php $publishable = get_option('omikuji_stripe_publishable_key',''); ?>
<div class="omikuji-wrap">
  <div class="omikuji-card">
    <div class="omikuji-title">電子おみくじ</div>
    <div class="omikuji-sub">ボタンを押して、おみくじを引こう</div>
    <button id="omikuji-draw" class="omikuji-btn" data-publishable="<?php echo esc_attr($publishable); ?>">くじを引く</button>
    <div class="omikuji-note">※ テストモードのデモ。実際の決済は行われません（Stripe Test）。</div>
  </div>
</div>
