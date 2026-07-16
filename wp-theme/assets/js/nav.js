/* ===== nav.js ｜ SPヘッダーのハンバーガーメニュー開閉 =====
   全ページ共通。641px以上ではボタン非表示のため実質無効。 */
(function(){
  const header=document.querySelector('.l-header');
  const toggle=document.querySelector('.l-nav__toggle');
  const nav=document.getElementById('gnav');
  if(!header||!toggle||!nav)return;

  function setOpen(open){
    header.classList.toggle('is-menu-open',open);
    toggle.setAttribute('aria-expanded',open?'true':'false');
  }
  // ハンバーガーで開閉
  toggle.addEventListener('click',e=>{
    e.stopPropagation();
    setOpen(!header.classList.contains('is-menu-open'));
  });
  // メニュー内リンクをタップしたら閉じる
  nav.addEventListener('click',e=>{ if(e.target.closest('a')) setOpen(false); });
  // メニュー外タップで閉じる
  document.addEventListener('click',e=>{ if(!e.target.closest('.l-header')) setOpen(false); });
  // PC幅に広がったら閉じてリセット
  window.addEventListener('resize',()=>{ if(window.innerWidth>=641) setOpen(false); });
})();
