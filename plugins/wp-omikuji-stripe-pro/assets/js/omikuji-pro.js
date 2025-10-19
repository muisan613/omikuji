(function($){
  $(document).on('click','#omikuji-draw', async function(){
    const publishable=$(this).data('publishable'); if(!publishable){ alert('Publishable Key未設定'); return; }
    try{
      const res=await fetch(OMIKUJI_PRO_VARS.restUrl+'/create-session',{ method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':OMIKUJI_PRO_VARS.nonce}, body:JSON.stringify({}) });
      if(!res.ok){ const t=await res.text(); alert('REST error '+res.status+'\n'+t.substring(0,200)); return; }
      const data=await res.json(); const stripe=Stripe(publishable); const {error}=await stripe.redirectToCheckout({sessionId:data.id}); if(error) alert(error.message);
    }catch(e){ alert('通信エラー'); }
  });
  $(function(){
    if(typeof window.OMIKUJI_SESSION_ID==='undefined' || !window.OMIKUJI_SESSION_ID) return;
    setTimeout(async function(){
      try{
        const res=await fetch(OMIKUJI_PRO_VARS.restUrl+'/finalize',{ method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':OMIKUJI_PRO_VARS.nonce}, body:JSON.stringify({session_id:window.OMIKUJI_SESSION_ID}) });
        const data=await res.json(); if(data.status!=='ok') throw new Error('finalize error');
        $('#omikuji-video').attr('hidden',true); $('#omikuji-result').attr('hidden',false); $('#result-text').text(data.result_text);
      }catch(e){ $('#omikuji-video .omikuji-text').text('エラーが発生しました。'); }
    }, 2000);
  });
})(jQuery);
