/* ===== main.js ｜ トップページの挙動 =====
   ロゴウォール・スポットライト演出・記事一覧・記事詳細のルーティング
   （掲載企業データは data.js の DATA を参照） */

function n2(n){return String(n).padStart(2,'0');}
function esc(s){return String(s==null?'':s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));}

function phSvg(no){
  const bg=["#E7E6E0","#E1E0DA","#ECEBE5","#E4E3DD"][no%4];
  const fg=["#CFCEC7","#C8C7C0","#D3D2CB","#CBCAC3"][no%4];
  return `<svg viewBox="0 0 100 125" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">`+
    `<rect width="100" height="125" fill="${bg}"/>`+
    `<circle cx="50" cy="47" r="19" fill="${fg}"/>`+
    `<path d="M20 125 C20 98 34 82 50 82 C66 82 80 98 80 125 Z" fill="${fg}"/></svg>`;
}
function wideSvg(no){
  const bg=["#E7E6E0","#E1E0DA","#ECEBE5","#E4E3DD"][no%4];
  const fg=["#CFCEC7","#C8C7C0","#D3D2CB","#CBCAC3"][no%4];
  return `<svg viewBox="0 0 160 90" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">`+
    `<rect width="160" height="90" fill="${bg}"/>`+
    `<circle cx="80" cy="42" r="21" fill="${fg}"/>`+
    `<path d="M44 90 C44 68 60 55 80 55 C100 55 116 68 116 90 Z" fill="${fg}"/></svg>`;
}

/* ---- ヒーローのロゴウォール＋スポットライト演出 ---- */
(function(){
  const wall=document.getElementById('logowall');
  if(!wall)return;
  let html='';
  for(let i=0;i<120;i++){
    const d=DATA[i%DATA.length];
    const cell=d.photo
      ? `<img src="${esc(d.photo)}" alt="" loading="lazy">`
      : `<span>${esc(d.corp.replace(/^(株式会社|有限会社|学校法人|社会福祉法人)/,'').slice(0,5))}</span>`;
    html+=`<div class="mark">${cell}</div>`;
  }
  wall.innerHTML=html;

  const marks=[...wall.querySelectorAll('.mark')];

  const RM = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(RM){ marks.slice(0,4).forEach(m=>m.classList.add('lit')); return; }

  // スポットライトが中心のまわりを周回し、光が当たった各社ロゴだけを点灯させる。
  const hero=document.querySelector('.hero');
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
      if(dx*dx+dy*dy<rr2) marks[i].classList.add('lit');
      else marks[i].classList.remove('lit');
    }
    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
})();

/* ---- 記事一覧（グループ分け＋スライダー） ---- */
function cardHTML(d){
  const who=[d.role,d.name].filter(Boolean).join('　');
  const ph=d.photo?`<img src="${esc(d.photo)}" alt="${esc(d.corp)}" loading="lazy">`:phSvg(d.no);
  return `
    <div class="card" role="button" tabindex="0" onclick="openCard(${d.no})" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openCard(${d.no});}" aria-label="No.${n2(d.no)} ${esc(d.corp)}">
      <div class="card-ph">${ph}${d.star?`<span class="card-star" aria-label="注目企業 ★${d.star}">${'★'.repeat(d.star)}</span>`:''}<div class="card-cap">${esc(d.head)}</div></div>
      <div class="card-nm serif">${esc(d.corp)}</div>
      ${who?`<div class="card-co">${esc(who)}</div>`:''}
    </div>`;
}
function renderGrid(){
  const wrap=document.getElementById('groups');
  if(!wrap)return;
  const N=Math.ceil(DATA.length/7);          // 約7枚ずつのグループ数（=8）
  const groups=[]; let idx=0;
  for(let g=0;g<N;g++){                        // 均等配分（6〜7枚ずつ）
    const size=Math.ceil((DATA.length-idx)/(N-g));
    groups.push(DATA.slice(idx,idx+size)); idx+=size;
  }
  wrap.innerHTML=groups.map((grp,gi)=>`
    <div class="grp reveal">
      <div class="grp-head"><span class="grp-label">GROUP</span><span class="grp-num">${n2(gi+1)}</span></div>
      <div class="grp-view"><div class="grp-track">${grp.map(cardHTML).join('')}</div></div>
      <div class="grp-nav">
        <button type="button" class="grp-btn grp-prev" aria-label="前へ"><span>‹</span></button>
        <span class="grp-count"><b class="grp-cur">1</b><i>/</i>${grp.length}</span>
        <button type="button" class="grp-btn grp-next" aria-label="次へ"><span>›</span></button>
      </div>
    </div>`).join('');
  wrap.querySelectorAll('.grp').forEach(initCarousel);
}

// 各グループの自動カルーセル（3枚表示・数秒送り・無限ループ・ボタン）
function initCarousel(grp){
  const track=grp.querySelector('.grp-track');
  const curEl=grp.querySelector('.grp-cur');
  const originals=[...track.children];
  const len=originals.length;
  const SHOW=5;                              // クローン数（最大表示枚数）
  const cols=()=>{const w=window.innerWidth;return w<=640?1:w<=1280?3:5;};// 表示枚数（SP1・〜1280は3・それ以上5）
  if(len<=SHOW)return;
  originals.slice(-SHOW).map(c=>c.cloneNode(true)).forEach(c=>{c.setAttribute('aria-hidden','true');track.insertBefore(c,track.firstChild);});
  originals.slice(0,SHOW).map(c=>c.cloneNode(true)).forEach(c=>{c.setAttribute('aria-hidden','true');track.appendChild(c);});
  let pos=SHOW, animating=false, timer=null;
  const stepPx=()=>{const f=track.children[0];const gap=parseFloat(getComputedStyle(track).gap)||0;return f.offsetWidth+gap;};
  function apply(anim){
    track.style.transition=anim?'transform .6s cubic-bezier(.22,.61,.36,1)':'none';
    track.style.transform=`translateX(${-pos*stepPx()}px)`;
    const c0=pos+Math.floor(cols()/2);
    [...track.children].forEach((c,i)=>{
      c.classList.toggle('is-center',i===c0);
      c.classList.toggle('is-prev',i===c0-1);
      c.classList.toggle('is-next',i===c0+1);
    });
    if(curEl)curEl.textContent=((c0-SHOW)%len+len)%len+1;
  }
  function go(n){if(animating)return;animating=true;pos+=n;apply(true);}
  track.addEventListener('transitionend',()=>{
    animating=false;
    if(pos>=len+SHOW){pos=SHOW;apply(false);}
    else if(pos<SHOW){pos=len+SHOW-1;apply(false);}
  });
  function start(){stop();timer=setInterval(()=>go(1),3500);}
  function stop(){if(timer)clearInterval(timer);timer=null;}
  grp.querySelector('.grp-prev').addEventListener('click',()=>{go(-1);start();});
  grp.querySelector('.grp-next').addEventListener('click',()=>{go(1);start();});
  grp.addEventListener('mouseenter',stop);
  grp.addEventListener('mouseleave',start);
  window.addEventListener('resize',()=>apply(false));
  apply(false);
  const RM=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(!RM)start();                             // 動きを抑える設定時は自動送りしない
}

/* ---- 記事詳細 ---- */
function openCard(no){
  const i=DATA.findIndex(d=>d.no===no); if(i<0)return;
  const d=DATA[i];
  const prev=DATA[(i-1+DATA.length)%DATA.length];
  const next=DATA[(i+1)%DATA.length];
  const who=[d.role,d.name].filter(Boolean).join('　');
  // 本文（最初のセクションは記事タイトルと重複するため見出しを省略）
  const body=(d.secs||[]).map((s,idx)=>{
    const ps=s.ps.map(p=>`<p>${esc(p)}</p>`).join('');
    const imgs=(s.imgs||[]).map(src=>`<figure class="d-fig"><img src="${esc(src)}" alt="" loading="lazy"></figure>`).join('');
    const h=idx===0?'':`<h3 class="serif">${esc(s.h)}</h3>`;
    return h+ps+imgs;
  }).join('');
  const art=document.getElementById('detail');
  art.innerHTML=`
    <div class="d-hero">
      ${d.hero?`<img src="${esc(d.hero)}" alt="${esc(d.corp)}">`:wideSvg(d.no)}
      <div class="d-hero-cap">
        ${d.star?`<p class="d-stars">${'★'.repeat(d.star)}</p>`:''}
        <p class="d-corp">${esc(d.corp)}</p>
        <h1 class="d-title serif">${esc(d.head)}</h1>
        ${who?`<p class="d-person">${esc(who)}</p>`:''}
      </div>
    </div>
    <div class="d-body">${body}</div>
    <section class="d-info">
      <div class="ih">COMPANY DATA</div>
      <p class="dc-name serif">${esc(d.corp)}</p>
      ${d.name?`<p class="dc-rep">${esc(d.name)}${d.role?'（'+esc(d.role)+'）':''}</p>`:''}
      ${d.prof?`<p class="dc-prof">${esc(d.prof)}</p>`:''}
      ${d.url?`<p class="dc-url"><a class="lk" href="${esc(d.url)}" target="_blank" rel="noopener">${esc(d.url)}　↗</a></p>`:''}
    </section>
    <nav class="d-nav">
      <button onclick="openCard(${prev.no})"><span class="dir">← PREV ｜ No.${n2(prev.no)}</span><span class="t serif">${esc(prev.corp)}</span></button>
      <button class="nx" onclick="openCard(${next.no})"><span class="dir">NEXT ｜ No.${n2(next.no)} →</span><span class="t serif">${esc(next.corp)}</span></button>
    </nav>`;
  document.getElementById('home').style.display='none';
  art.style.display='block';
  location.hash='c'+n2(d.no);
  window.scrollTo(0,0);
}
function goHome(){
  document.getElementById('detail').style.display='none';
  document.getElementById('home').style.display='block';
  if(location.hash)history.pushState('',document.title,location.pathname+location.search);
  window.scrollTo(0,0);
}
function goSection(sel){
  const onDetail=document.getElementById('detail').style.display==='block';
  goHome();
  const el=document.querySelector(sel);
  if(!el)return;
  // 詳細から戻った直後はレイアウト確定後にスクロール
  if(onDetail){ requestAnimationFrame(()=>el.scrollIntoView({behavior:'smooth',block:'start'})); }
  else el.scrollIntoView({behavior:'smooth',block:'start'});
}
window.openCard=openCard; window.goHome=goHome; window.goSection=goSection;

function fromHash(){
  const m=location.hash.match(/c(\d{2})/);
  if(m){openCard(parseInt(m[1],10));}
}
window.addEventListener('popstate',()=>{ if(!location.hash) goHome(); else fromHash(); });

// CONCEPTに入った瞬間、背景の千葉県地図をふわっと表示（トップのみ）
(function(){
  const concept=document.getElementById('concept');
  const home=document.getElementById('home');
  if(!concept||!home)return;
  if(!('IntersectionObserver' in window)){home.classList.add('map-on');return;}
  const io=new IntersectionObserver(es=>{
    if(es.some(e=>e.isIntersecting)){home.classList.add('map-on');io.disconnect();}
  },{threshold:.4});
  io.observe(concept);
})();

renderGrid();

// スクロールで各要素をふわっと表示
(function(){
  const targets=document.querySelectorAll('.reveal');
  if(!('IntersectionObserver' in window)){targets.forEach(t=>t.classList.add('is-in'));return;}
  const io=new IntersectionObserver((es,obs)=>{
    es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('is-in');obs.unobserve(e.target);}});
  },{threshold:.12,rootMargin:'0px 0px -6% 0px'});
  targets.forEach(t=>io.observe(t));
})();

fromHash();
