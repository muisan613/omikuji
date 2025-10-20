(function($){
  $(document).on('click','#omikuji-draw', async function(){
    const publishable=$(this).data('publishable');
    if(!publishable){ alert('Stripe Publishable Keyが未設定です'); return; }
    try{
      const res=await fetch(OMIKUJI_VARS.restUrl+'/create-session',{ method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({}) });
      if(!res.ok){ const t=await res.text(); alert('通信エラー: '+res.status+'\n'+t.substring(0,280)); return; }
      const data=await res.json(); const stripe=Stripe(publishable);
      const {error}=await stripe.redirectToCheckout({ sessionId:data.id }); if(error) alert(error.message);
    }catch(e){ alert('通信エラー: '+(e&&e.message?e.message:'unknown')); }
  });
  $(function(){
    if(typeof window.OMIKUJI_SESSION_ID==='undefined') return;
    const fortunes=['大吉 🎉 最高の運気！大事な一歩を踏み出そう','中吉 😊 いい流れ。小さな準備が実る日','小吉 🍀 穏やかな運気。感謝が鍵','吉 ✨ 焦らず、整えれば道は開く','末吉 🌱 学びの時。種まきで未来が変わる','凶 🌧 無理せず休息を。体を労って吉'];
    setTimeout(function(){ $('#omikuji-video').attr('hidden',true); $('#omikuji-result').attr('hidden',false); const pick=fortunes[Math.floor(Math.random()*fortunes.length)]; $('#result-text').text(pick); },3000);
  });
})(jQuery);
