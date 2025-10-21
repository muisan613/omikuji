<?php
$sid = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';
$img = esc_url( get_option('omikuji_pro_drawing_image_url','') );
$vid = esc_url( get_option('omikuji_pro_drawing_video_url','') );
?>
<div class="omikuji-result-wrap" data-state="loading">
  <div class="omikuji-stage">
    <div class="omikuji-loading" id="omikuji-loading" style="display:block;">
      <div class="omikuji-media-box">
        <?php if ($vid): ?>
          <video class="omikuji-wait-video" playsinline autoplay muted <?php echo $img ? 'poster="'.esc_attr($img).'"' : ''; ?>>
            <source src="<?php echo $vid; ?>" type="video/mp4">
          </video>
        <?php elseif ($img): ?>
          <img class="omikuji-wait-image" src="<?php echo $img; ?>" alt="おみくじを引いています">
        <?php else: ?>
          <div class="omikuji-drum"></div>
        <?php endif; ?>
      </div>
      <div class="omikuji-wait-text" id="omikuji-status-text">おみくじを引いています…</div>
    </div>
    <div class="omikuji-result" id="omikuji-result" style="display:none;">
      <div class="result-badge">結果</div>
      <div class="result-text" id="result-text">—</div>
      <a class="omikuji-again" href="<?php echo esc_url( home_url('/') ); ?>">トップへ戻る</a>
    </div>
  </div>
  <script>window.OMIKUJI_SESSION_ID = "<?php echo esc_js($sid); ?>";</script>
</div>
