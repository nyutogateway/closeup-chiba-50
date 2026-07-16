/* ===== main.js ｜ サイト共通の挙動（WordPress版） =====
   ヒーローのスポットライト演出・一覧カルーセル・スクロール表示
   （カード等のHTMLはPHP側で出力済み。ここでは既存DOMに挙動を付けるだけ） */

/* ---- ヒーローのスポットライト演出（タイルはPHPが出力済み） ---- */
(function(){
  const wall=document.getElementById('logowall');
  if(!wall)return;

  const marks=[...wall.querySelectorAll('.p-hero__mark')];

  const RM = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(RM){ marks.slice(0,4).forEach(m=>m.classList.add('is-lit')); return; }

  // スポットライトが中心のまわりを周回し、光が当たった各社ロゴだけを点灯させる。
  const hero=document.querySelector('.p-hero');
  const spot=document.getElementById('spot');

  // タイル中心を hero 基準で事前計算（リサイズ時のみ再計算）
  let cen=[], HW=0, HH=0, MIND=0;
  function measure(){
    const hr=hero.getBoundingClientRect();
    HW=hr.width; HH=hr.height; MIND=Math.min(HW,HH);
    cen=marks.map(m=>{ const r=m.getBoundingClientRect();
      return {x:r.left-hr.left+r.width/2, y:r.top-hr.top+r.height/2}; });
    if(spot){ const s=MIND*0.72; spot.style.width=s+'px'; spot.style.height=s+'px'; }
  }
  measure();
  window.addEventListener('resize', measure);

  const PERIOD=12000;   // 横方向の周回時間(ms)
  const AMP=0.48;       // 振幅(hero比) 端まで届かせる
  const HITR=0.22;      // 点灯判定半径(短辺比)
  let start=performance.now();

  function frame(now){
    const t=(now-start)/PERIOD*Math.PI*2;                // 累積時間（周期でリセットしない）
    const sx=HW*(0.5 + AMP*Math.cos(t));                 // 横は等速で往復
    const sy=HH*(0.5 + AMP*Math.sin(t*0.618));           // 縦は黄金比の周期でずらし、全面を走査
    if(spot){ spot.style.left=sx+'px'; spot.style.top=sy+'px'; }
    const rr2=(MIND*HITR)*(MIND*HITR);
    for(let i=0;i<cen.length;i++){
      const c=cen[i], dx=c.x-sx, dy=c.y-sy;
      if(dx*dx+dy*dy<rr2) marks[i].classList.add('is-lit');
      else marks[i].classList.remove('is-lit');
    }
    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
})();

/* ---- 一覧（グループ）のカルーセル ---- */
// 各グループの自動カルーセル（3枚表示・数秒送り・無限ループ・ボタン）
function initCarousel(grp){
  const track=grp.querySelector('.p-group__track');
  const curEl=grp.querySelector('.p-group__current');
  const originals=[...track.children];
  const len=originals.length;
  const SHOW=5;                              // クローン数（最大表示枚数）
  const cols=()=>{const w=window.innerWidth;return w<=640?1:w<=1280?3:5;};// 表示枚数（SP1・〜1280は3・それ以上5）
  if(len<=SHOW)return;
  originals.slice(-SHOW).map(c=>c.cloneNode(true)).forEach(c=>{c.setAttribute('aria-hidden','true');track.insertBefore(c,track.firstChild);});
  originals.slice(0,SHOW).map(c=>c.cloneNode(true)).forEach(c=>{c.setAttribute('aria-hidden','true');track.appendChild(c);});
  let pos=SHOW, animating=false, timer=null, doneTimer=null, rzTimer=null;
  const RM=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const view=grp.querySelector('.p-group__view');
  const stepPx=()=>{const f=track.children[0];const gap=parseFloat(getComputedStyle(track).gap)||0;return f.offsetWidth+gap;};
  function apply(anim){
    track.style.transition=anim?'transform .6s cubic-bezier(.22,.61,.36,1)':'none';
    let tx=-pos*stepPx();
    // SP（1枚表示）はstep基準だと左寄せになるため、余白の半分だけ右へずらして中央寄せに
    if(cols()===1){
      const cs=getComputedStyle(view);
      const inner=view.clientWidth-(parseFloat(cs.paddingLeft)||0)-(parseFloat(cs.paddingRight)||0);
      tx+=(inner-track.children[0].offsetWidth)/2;
    }
    track.style.transform=`translateX(${tx}px)`;
    const c0=pos+Math.floor(cols()/2);
    [...track.children].forEach((c,i)=>{
      c.classList.toggle('is-center',i===c0);
      c.classList.toggle('is-prev',i===c0-1);
      c.classList.toggle('is-next',i===c0+1);
    });
    if(curEl)curEl.textContent=((c0-SHOW)%len+len)%len+1;
  }
  // pos を有効範囲[SHOW, len+SHOW)へ補正（中断時に近いカードへ戻す）
  function normalize(){ if(pos<SHOW||pos>=len+SHOW) pos=SHOW+(((pos-SHOW)%len)+len)%len; }
  // アニメーション完了処理（無限ループの折り返しもここで）
  function onDone(){
    clearTimeout(doneTimer);
    animating=false;
    if(pos>=len+SHOW){pos=SHOW;apply(false);}
    else if(pos<SHOW){pos=len+SHOW-1;apply(false);}
  }
  function go(n){
    if(animating)return;
    animating=true; pos+=n; apply(true);
    // transitionendが来なくても必ず復帰させる保険（ボタン/自動送りが固まる不具合対策）
    clearTimeout(doneTimer); doneTimer=setTimeout(onDone,720);
  }
  track.addEventListener('transitionend',e=>{
    if(e.target!==track||e.propertyName!=='transform')return;  // カード側のtransition（scale等）は無視
    onDone();
  });
  function start(){stop();if(!RM)timer=setInterval(()=>go(1),1000);}  // reduced-motion時は自動送りしない
  function stop(){if(timer)clearInterval(timer);timer=null;}
  grp.querySelector('.p-group__prev').addEventListener('click',()=>{go(-1);start();});
  grp.querySelector('.p-group__next').addEventListener('click',()=>{go(1);start();});
  grp.addEventListener('mouseenter',stop);
  grp.addEventListener('mouseleave',start);
  // リロード・画面幅変更・アニメ中断など、どんな状況でも現在のカードを中央へ戻して動き出す
  function reset(){
    clearTimeout(doneTimer);
    animating=false;      // 固まったフラグを解除
    normalize();          // 近いカードへ位置を補正
    apply(false);         // 新レイアウトで中央に再配置（アニメなし）
    start();              // 自動送り再開
  }
  window.addEventListener('resize',()=>{ clearTimeout(rzTimer); rzTimer=setTimeout(reset,150); });
  apply(false);
  start();
}

// CONCEPTに入った瞬間、背景の千葉県地図をふわっと表示（トップのみ）
(function(){
  const concept=document.getElementById('concept');
  const home=document.getElementById('home');
  if(!concept||!home)return;
  if(!('IntersectionObserver' in window)){home.classList.add('is-map-on');return;}
  const io=new IntersectionObserver(es=>{
    if(es.some(e=>e.isIntersecting)){home.classList.add('is-map-on');io.disconnect();}
  },{threshold:.4});
  io.observe(concept);
})();

// PHPが出力した各グループにカルーセルの挙動を付ける
document.querySelectorAll('.p-group').forEach(initCarousel);

// スクロールで各要素をふわっと表示
(function(){
  const targets=document.querySelectorAll('.u-reveal');
  if(!('IntersectionObserver' in window)){targets.forEach(t=>t.classList.add('is-in'));return;}
  const io=new IntersectionObserver((es,obs)=>{
    es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('is-in');obs.unobserve(e.target);}});
  },{threshold:.12,rootMargin:'0px 0px -6% 0px'});
  targets.forEach(t=>io.observe(t));
})();
