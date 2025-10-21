(function($){
  // Draw page: create Stripe session and redirect
  $(document).on('click','#omikuji-pro-draw', async function(){
    const pub = $(this).data('publishable');
    if(!pub){ alert('Publishable Key未設定です'); return; }
    try {
      const res = await fetch(OMIKUJI_PRO_VARS.restUrl + '/create-session', { method:'POST' });
      if(!res.ok){ const t = await res.text(); alert('セッション作成失敗: ' + res.status); return; }
      const data = await res.json();
      const stripe = Stripe(pub);
      const ret = await stripe.redirectToCheckout({ sessionId: data.id });
      if(ret.error) alert(ret.error.message);
    } catch(e){ alert('通信エラー'); }
  });

  // Result page: show video/image then finalize
  $(function(){
    const sid = window.OMIKUJI_SESSION_ID || '';
    if(!sid) return;

    const $loading = $('#omikuji-loading');
    const $result  = $('#omikuji-result');
    const $text    = $('#result-text');
    const $status  = $('#omikuji-status-text');
    const $videoEl = $('.omikuji-wait-video').get(0);

    $loading.show();
    $result.hide();

    let resultText = '—', hasResult=false;
    fetch(OMIKUJI_PRO_VARS.restUrl + '/finalize', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ session_id: sid })
    })
    .then(r=>r.json())
    .then(d=>{ if(d && d.status==='ok'){ resultText=d.result_text; hasResult=true; } })
    .catch(()=>{ if($status.length) $status.text('エラーが発生しました。'); });

    const showResult = ()=>{
      const wait = ()=>{
        if(hasResult){
          $loading.fadeOut(600, ()=>{ $text.text(resultText); $result.fadeIn(600); });
        } else setTimeout(wait,120);
      }; wait();
    };

    if($videoEl){
      try{$videoEl.loop=false;}catch(e){}
      let ended=false;
      $videoEl.addEventListener('ended',()=>{ ended=true; showResult(); });
      setTimeout(()=>{ if(!ended) showResult(); }, 3000); // fallback
    }else{
      setTimeout(showResult, 1500);
    }
  });
})(jQuery);
