(function($){
  function qs(name){ const u=new URLSearchParams(window.location.search); return u.get(name); }

  $(document).on('click','#omikuji-draw', async function(){
    const publishable=$(this).data('publishable'); if(!publishable){ alert('Publishable Key未設定'); return; }
    try{
      const res=await fetch(OMIKUJI_PRO_VARS.restUrl+'/create-session',{ method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':OMIKUJI_PRO_VARS.nonce}, body:JSON.stringify({}) });
      if(!res.ok){ const t=await res.text(); alert('REST error '+res.status+'\n'+t.substring(0,200)); return; }
      const data=await res.json(); const stripe=Stripe(publishable); const {error}=await stripe.redirectToCheckout({sessionId:data.id}); if(error) alert(error.message);
    }catch(e){ alert('通信エラー'); }
  });

  $(function(){
    var sid = window.OMIKUJI_SESSION_ID || qs('session_id') || '';
    var $video = $('#omikuji-video');
    var $result = $('#omikuji-result');
    var $status = $('#omikuji-status-text');
    if(!sid) return;

    $video.removeClass('hidden');
    $result.addClass('hidden');

    fetch(OMIKUJI_PRO_VARS.restUrl + '/finalize', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-WP-Nonce': OMIKUJI_PRO_VARS.nonce},
      body: JSON.stringify({ session_id: sid })
    })
    .then(async (res)=>{
      const data = await res.json().catch(()=>({}));
      if(!res.ok || !data || data.status!=='ok') { throw new Error((data && (data.message||data.code)) || 'finalize failed'); }
      setTimeout(function(){
        $video.addClass('fade-out');
        setTimeout(function(){
          $video.addClass('hidden').removeClass('fade-out');
          $('#result-text').text(data.result_text || '—');
          $result.removeClass('hidden').addClass('fade-in');
        }, 450);
      }, 1200);
    })
    .catch(function(){
      if ($status.length){ $status.text('エラーが発生しました。しばらくしてから再度お試しください。'); }
    });
  });
})(jQuery);
