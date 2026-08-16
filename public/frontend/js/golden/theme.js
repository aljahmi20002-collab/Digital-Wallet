/* =========================================================
   1. Particle drift — gold dots
   ========================================================= */
(function(){
  const layer = document.getElementById('gdkParticles');
  if(!layer) return;
  const N = 32;
  for(let i=0;i<N;i++){
    const p = document.createElement('span');
    p.className = 'gdk-particle';
    const size = 1.5 + Math.random()*2.5;
    p.style.width = p.style.height = size+'px';
    p.style.left = (Math.random()*100)+'%';
    p.style.bottom = (-10 - Math.random()*30)+'%';
    const dur = 16 + Math.random()*24;
    p.style.animationDuration = dur+'s';
    p.style.animationDelay = (-Math.random()*dur)+'s';
    p.style.opacity = (0.35 + Math.random()*0.5);
    layer.appendChild(p);
  }
})();

/* =========================================================
   2. Reveal on scroll
   ========================================================= */
(function(){
  const io = new IntersectionObserver(entries=>{
    entries.forEach(e=>{
      if(e.isIntersecting){
        e.target.classList.add('is-in');
        io.unobserve(e.target);
      }
    });
  },{threshold:.12,rootMargin:'0px 0px -40px 0px'});
  document.querySelectorAll('.gdk-reveal').forEach(el=>io.observe(el));
})();

/* =========================================================
   3. Services carousel
   ========================================================= */
(function(){
  const track = document.getElementById('svcTrack');
  if(!track) return;
  const prev = document.getElementById('svcPrev');
  const next = document.getElementById('svcNext');
  let i = 0;
  function step(){
    const card = track.children[0];
    if(!card) return 0;
    const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 24;
    return card.getBoundingClientRect().width + gap;
  }
  function max(){
    const visible = Math.max(1, Math.floor(track.parentElement.clientWidth / step()));
    return Math.max(0, track.children.length - visible);
  }
  function update(){
    i = Math.max(0, Math.min(i, max()));
    track.style.transform = `translateX(${-i*step()}px)`;
  }
  prev.addEventListener('click', ()=>{ i--; update(); });
  next.addEventListener('click', ()=>{ i++; update(); });
  window.addEventListener('resize', update);
})();

/* =========================================================
   4. Counters animation
   ========================================================= */
(function(){
  const els = document.querySelectorAll('[data-count]');
  const io = new IntersectionObserver(entries=>{
    entries.forEach(e=>{
      if(!e.isIntersecting) return;
      const el = e.target;
      const target = parseFloat(el.dataset.count);
      const dec = parseInt(el.dataset.dec||'0',10);
      const suffix = el.dataset.suffix || '';
      const dur = 1800;
      const start = performance.now();
      function frame(t){
        const k = Math.min(1, (t-start)/dur);
        const v = target * (1 - Math.pow(1-k, 3));
        el.textContent = v.toFixed(dec) + suffix;
        if(k<1) requestAnimationFrame(frame);
        else el.textContent = target.toFixed(dec) + suffix;
      }
      requestAnimationFrame(frame);
      io.unobserve(el);
    });
  },{threshold:.4});
  els.forEach(el=>io.observe(el));
})();

/* =========================================================
   5. Plans segmented toggle
   ========================================================= */
(function(){
  const seg = document.getElementById('planToggle');
  if(!seg) return;
  const pill = document.getElementById('planPill');
  const btns = seg.querySelectorAll('.gdk-segmented__btn');
  function place(btn){
    const r = btn.getBoundingClientRect();
    const sr = seg.getBoundingClientRect();
    pill.style.width = r.width + 'px';
    pill.style.transform = `translateX(${r.left - sr.left - 6}px)`;
  }
  function setMult(m){
    document.querySelectorAll('.gdk-tier__amt').forEach(el=>{
      const base = parseFloat(el.dataset.base);
      const v = Math.round(base*m);
      el.textContent = v;
    });
  }
  btns.forEach(b=>{
    b.addEventListener('click',()=>{
      btns.forEach(x=>x.classList.remove('is-active'));
      b.classList.add('is-active');
      place(b);
      setMult(parseFloat(b.dataset.mult));
      document.querySelectorAll('.gdk-tier__billed').forEach(el=>{
        const note = b.dataset.disc ? `Billed ${b.textContent.toLowerCase().includes('annual')?'annually':'every 6 months'} · save ${b.dataset.disc.replace('-','')}` : 'Billed monthly';
        el.innerHTML = `<i class="fa-solid fa-circle-info"></i> ${note}`;
      });
    });
  });
  // initial placement
  requestAnimationFrame(()=>place(seg.querySelector('.is-active')));
  window.addEventListener('resize',()=>place(seg.querySelector('.is-active')));
})();

/* =========================================================
   6. Testimonials slider
   ========================================================= */
(function(){
  // Read testimonial slides from a data attribute on the stage element
  // so the Blade partial can hand-off server-rendered content.
  const stage = document.querySelector('[data-gdk-testimonials]');
  let data = [];
  if (stage) {
    try { data = JSON.parse(stage.getAttribute('data-gdk-testimonials') || '[]'); }
    catch (e) { data = []; }
  }
  if (!Array.isArray(data) || data.length === 0) { return; }

  const body  = document.getElementById('testiBody');
  const name  = document.getElementById('testiName');
  const role  = document.getElementById('testiRole');
  const photo = document.getElementById('testiPhoto');
  const card  = document.getElementById('testiCard');
  const dots  = document.querySelectorAll('#testiDots .gdk-testi__dot');
  const prev  = document.getElementById('testiPrev');
  const next  = document.getElementById('testiNext');
  if (!card || !body) { return; }

  let i = 0;
  function go(n){
    i = (n + data.length) % data.length;
    card.style.opacity = 0;
    card.style.transform = 'translateY(8px)';
    setTimeout(()=>{
      body.textContent = data[i].body || '';
      if (name)  { name.textContent  = data[i].name  || ''; }
      if (role)  { role.textContent  = data[i].role  || ''; }
      if (photo) {
        photo.style.setProperty('--gdk-img', data[i].photo ? `url('${data[i].photo}')` : 'none');
      }
      dots.forEach((d,j)=>d.classList.toggle('is-active', j===i));
      card.style.transition = 'opacity .55s ease, transform .55s ease';
      card.style.opacity = 1;
      card.style.transform = 'translateY(0)';
    },260);
  }
  if (prev) { prev.addEventListener('click',()=>go(i-1)); }
  if (next) { next.addEventListener('click',()=>go(i+1)); }
  dots.forEach(d=>d.addEventListener('click',()=>go(parseInt(d.dataset.i,10))));

  if (data.length > 1) {
    let timer = setInterval(()=>go(i+1), 7500);
    card.addEventListener('mouseenter',()=>clearInterval(timer));
    card.addEventListener('mouseleave',()=>{timer = setInterval(()=>go(i+1), 7500)});
  }
})();

/* =========================================================
   7. Language switcher dropdown
      Vanilla JS — the golden frontend layout does not load
      Bootstrap's dropdown JS, so we drive `.is-open` ourselves.
   ========================================================= */
(function(){
  const switchers = document.querySelectorAll('[data-gdk-lang]');
  if (!switchers.length) { return; }

  function closeAll(exceptEl) {
    switchers.forEach(function (el) {
      if (el !== exceptEl) {
        el.classList.remove('is-open');
        const t = el.querySelector('[data-gdk-lang-trigger]');
        if (t) { t.setAttribute('aria-expanded', 'false'); }
      }
    });
  }

  switchers.forEach(function (root) {
    const trigger = root.querySelector('[data-gdk-lang-trigger]');
    if (!trigger) { return; }

    trigger.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      const willOpen = !root.classList.contains('is-open');
      closeAll(root);
      root.classList.toggle('is-open', willOpen);
      trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    trigger.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        root.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.focus();
      }
    });
  });

  // Click anywhere outside any switcher to close.
  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-gdk-lang]')) { closeAll(null); }
  });

  // Escape from anywhere on the page also closes.
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeAll(null); }
  });
})();
