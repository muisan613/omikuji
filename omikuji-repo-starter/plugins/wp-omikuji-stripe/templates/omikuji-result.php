<?php $session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : ''; ?>
<div class="omikuji-result-wrap">
  <div class="omikuji-stage">
    <div class="omikuji-video" id="omikuji-video">
      <div class="omikuji-drum"></div>
      <div class="omikuji-confetti"></div>
      <div class="omikuji-confetti confetti-2"></div>
      <div class="omikuji-confetti confetti-3"></div>
      <div class="omikuji-text">おみくじを振っています…</div>
    </div>
    <div class="omikuji-result" id="omikuji-result" hidden>
      <div class="result-badge">結果</div>
      <div class="result-text" id="result-text">—</div>
      <a class="omikuji-again" href="javascript:history.back()">もう一度引く</a>
    </div>
  </div>
  <script>window.OMIKUJI_SESSION_ID = "<?php echo esc_js($session_id); ?>";</script>
</div>
