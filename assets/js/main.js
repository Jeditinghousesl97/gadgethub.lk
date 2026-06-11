/* Sticky Header */
function initStickyHeader() {
  const h = document.getElementById('siteHeader');
  if (!h) return;
  window.addEventListener('scroll', () => h.classList.toggle('scrolled', scrollY > 60), { passive: true });
}

/* Hamburger */
function initHamburger() {
  const btn = document.getElementById('hamburger');
  const nav = document.getElementById('mobileNav');
  if (!btn || !nav) return;

  btn.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    btn.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });
  document.addEventListener('click', e => {
    if (nav.classList.contains('open') && !nav.contains(e.target) && !btn.contains(e.target)) {
      nav.classList.remove('open');
      btn.classList.remove('open');
      document.body.style.overflow = '';
    }
  });
}

/* Hero Background Images (graceful fallback) */
function initHeroBg() {
  document.querySelectorAll('.hero-slide[data-bg]').forEach(slide => {
    const img = new Image();
    img.onload = () => { slide.style.backgroundImage = `url(${slide.dataset.bg})`; };
    img.src = slide.dataset.bg;
  });
}

/* Hero Slider */
function initHero() {
  const track    = document.getElementById('heroTrack');
  const slides   = document.querySelectorAll('.hero-slide');
  const dots     = document.querySelectorAll('.hero-dot');
  const prev     = document.getElementById('heroPrev');
  const next     = document.getElementById('heroNext');
  const bar      = document.getElementById('heroProgress');
  const heroEl   = document.querySelector('.hero');
  if (!track || !slides.length) return;

  let cur = 0, timer;
  const total = slides.length;

  function goTo(i) {
    slides[cur].classList.remove('active');
    dots[cur]?.classList.remove('active');
    cur = ((i % total) + total) % total;
    slides[cur].classList.add('active');
    dots[cur]?.classList.add('active');
    track.style.transform = `translateX(-${cur * 100}%)`;
    if (bar) { bar.classList.remove('run'); void bar.offsetWidth; bar.classList.add('run'); }
  }

  function play()  { timer = setInterval(() => goTo(cur + 1), 5000); }
  function pause() { clearInterval(timer); }

  prev?.addEventListener('click',  () => { goTo(cur - 1); pause(); play(); });
  next?.addEventListener('click',  () => { goTo(cur + 1); pause(); play(); });
  dots.forEach((d, i) => d.addEventListener('click', () => { goTo(i); pause(); play(); }));
  heroEl?.addEventListener('mouseenter', pause);
  heroEl?.addEventListener('mouseleave', play);

  let tx = 0;
  heroEl?.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
  heroEl?.addEventListener('touchend', e => {
    const dx = tx - e.changedTouches[0].clientX;
    if (Math.abs(dx) > 48) { dx > 0 ? goTo(cur + 1) : goTo(cur - 1); pause(); play(); }
  });

  goTo(0); play();
}

/* Brands Slider */
function initBrandsSlider() {
  const track = document.getElementById('brandsTrack');
  const prev  = document.getElementById('brandsPrev');
  const next  = document.getElementById('brandsNext');
  if (!track) return;

  const slides = track.querySelectorAll('.brand-slide');
  let cur = 0, timer;

  function getVisible() {
    return innerWidth >= 1200 ? 5 : innerWidth >= 900 ? 4 : innerWidth >= 600 ? 3 : 2;
  }

  function setWidths() {
    const v = getVisible();
    slides.forEach(s => s.style.flex = `0 0 ${100 / v}%`);
  }

  function goTo(i) {
    const v   = getVisible();
    const max = slides.length - v;
    cur = Math.max(0, Math.min(i, max));
    track.style.transform = `translateX(-${cur * (100 / getVisible())}%)`;
  }

  function play()  { timer = setInterval(() => { goTo(cur + 1 > slides.length - getVisible() ? 0 : cur + 1); }, 3000); }
  function pause() { clearInterval(timer); }

  prev?.addEventListener('click',  () => { goTo(cur - 1); pause(); play(); });
  next?.addEventListener('click',  () => { goTo(cur + 1); pause(); play(); });
  track.parentElement?.addEventListener('mouseenter', pause);
  track.parentElement?.addEventListener('mouseleave', play);

  setWidths();
  window.addEventListener('resize', () => { setWidths(); goTo(0); });
  play();
}

/* Product Tab Filters */
function initTabs() {
  const tabs  = document.querySelectorAll('.tab-btn');
  const cards = document.querySelectorAll('.product-card[data-category]');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const f = tab.dataset.filter;
      cards.forEach(c => {
        const categories = (c.dataset.categories || c.dataset.category || '').split(',').map(s => s.trim()).filter(Boolean);
        const show = f === 'all' || categories.includes(f);
        if (show) {
          c.style.display = 'flex';
          c.style.opacity = '0'; c.style.transform = 'scale(.94)';
          requestAnimationFrame(() => {
            c.style.transition = 'opacity .28s,transform .28s';
            c.style.opacity = '1'; c.style.transform = 'scale(1)';
          });
        } else {
          c.style.opacity = '0'; c.style.transform = 'scale(.94)';
          setTimeout(() => { c.style.display = 'none'; }, 260);
        }
      });
    });
  });
}

/* Scroll Reveal */
function initReveal() {
  const els = document.querySelectorAll('[data-anim]');
  if (!('IntersectionObserver' in window)) { els.forEach(e => e.classList.add('visible')); return; }
  const obs = new IntersectionObserver(entries => {
    entries.forEach(en => { if (en.isIntersecting) { en.target.classList.add('visible'); obs.unobserve(en.target); } });
  }, { threshold: 0.1, rootMargin: '0px 0px -36px 0px' });
  els.forEach(e => obs.observe(e));
}

/* Stats Counter */
function initCounters() {
  const els = document.querySelectorAll('[data-count]');
  if (!els.length) return;
  const obs = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (!en.isIntersecting) return;
      const el = en.target, end = +el.dataset.count, inc = end / (1800 / 16);
      let val = 0;
      const t = setInterval(() => { val += inc; if (val >= end) { el.textContent = end.toLocaleString() + '+'; clearInterval(t); } else el.textContent = Math.floor(val).toLocaleString() + '+'; }, 16);
      obs.unobserve(el);
    });
  }, { threshold: 0.5 });
  els.forEach(e => obs.observe(e));
}

/* Scroll to Top */
function initScrollTop() {
  const btn = document.getElementById('scrollTopBtn');
  if (!btn) return;
  window.addEventListener('scroll', () => btn.classList.toggle('show', scrollY > 420), { passive: true });
  btn.addEventListener('click', () => scrollTo({ top: 0, behavior: 'smooth' }));
}

/* Cart */
function initCart() {
  function ensureCartPopup() {
    let popup = document.getElementById('ghCartPopup');
    if (popup) return popup;
    popup = document.createElement('div');
    popup.id = 'ghCartPopup';
    popup.className = 'gh-cart-popup';
    popup.innerHTML = `
      <div class="gh-cart-popup-card" role="dialog" aria-modal="true" aria-label="Item added to cart">
        <button class="gh-cart-popup-close" type="button" aria-label="Close"><i class="fas fa-times"></i></button>
        <div class="gh-cart-popup-ico"><i class="fas fa-check"></i></div>
        <h4>Added to Cart</h4>
        <p>Your item was added successfully.</p>
        <div class="gh-cart-popup-actions">
          <a href="cart.php" class="gh-cart-popup-view">View Cart</a>
          <button type="button" class="gh-cart-popup-continue">Continue Shopping</button>
        </div>
      </div>
    `;
    document.body.appendChild(popup);

    const close = () => popup.classList.remove('show');
    popup.addEventListener('click', e => {
      if (e.target === popup || e.target.closest('.gh-cart-popup-close') || e.target.closest('.gh-cart-popup-continue')) close();
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') close();
    });
    return popup;
  }

  function showCartPopup() {
    const popup = ensureCartPopup();
    popup.classList.add('show');
  }
  window.GadgetHubShowCartPopup = showCartPopup;

  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-cart');
    if (!btn) return;
    e.preventDefault();

    const card = btn.closest('.product-card');
    if (!card || !window.GadgetHubCart) return;

    GadgetHubCart.addItem({
      productId: card.dataset.id ? parseInt(card.dataset.id, 10) : null,
      name:     card.dataset.name     || 'Product',
      category: card.dataset.categoryLabel || card.dataset.category || '',
      price:    parseFloat(card.dataset.price) || 0,
      weight_kg: parseFloat(card.dataset.weight || card.dataset.weightKg) || 0,
      free_delivery: card.dataset.freeDelivery === '1',
      icon:     (card.querySelector('.product-img-placeholder i') || {}).className || 'fas fa-box'
    });

    // Button feedback
    const icon = btn.querySelector('i');
    if (icon) {
      const origClass = icon.className;
      icon.className = 'fas fa-check';
      btn.style.background  = '#16a34a';
      btn.style.color       = '#fff';
      btn.style.borderColor = 'transparent';
      setTimeout(() => {
        icon.className    = origClass;
        btn.style.background  = '';
        btn.style.color       = '';
        btn.style.borderColor = '';
      }, 1400);
    }

    showCartPopup();
  }, true);
}

/* Wishlist */
function initWishBtns() {
  // Restore saved heart states on page load
  function syncHearts() {
    if (!window.GadgetHubWishlist) return;
    document.querySelectorAll('.p-hover-btn[title="Add to Wishlist"], .p-hover-btn[title="Wishlist"]').forEach(btn => {
      const card = btn.closest('.product-card');
      if (!card) return;
      const active = GadgetHubWishlist.has(card.dataset.name || '');
      const icon   = btn.querySelector('i');
      if (!icon) return;
      icon.className      = active ? 'fas fa-heart' : 'far fa-heart';
      btn.style.background  = active ? '#ef4444' : '';
      btn.style.color       = active ? '#fff'    : '';
      btn.style.borderColor = active ? 'transparent' : '';
    });
  }

  document.addEventListener('click', e => {
    const btn = e.target.closest('.p-hover-btn');
    if (!btn || !btn.title.includes('Wishlist')) return;
    e.stopPropagation();
    if (!window.GadgetHubWishlist) return;

    const card = btn.closest('.product-card');
    if (!card) return;

    const added = GadgetHubWishlist.toggle({
      productId: card.dataset.id ? parseInt(card.dataset.id, 10) : null,
      name:     card.dataset.name     || 'Product',
      category: card.dataset.categoryLabel || card.dataset.category || '',
      price:    parseFloat(card.dataset.price) || 0,
      weight_kg: parseFloat(card.dataset.weight || card.dataset.weightKg) || 0,
      free_delivery: card.dataset.freeDelivery === '1',
      icon:     (card.querySelector('.product-img-placeholder i') || {}).className || 'fas fa-box'
    });

    const icon = btn.querySelector('i');
    if (icon) icon.className = added ? 'fas fa-heart' : 'far fa-heart';
    btn.style.background  = added ? '#ef4444'     : '';
    btn.style.color       = added ? '#fff'         : '';
    btn.style.borderColor = added ? 'transparent'  : '';
  });

  document.addEventListener('DOMContentLoaded', syncHearts);
}

/* Newsletter */
function initNewsletter() {
  document.addEventListener('click', e => {
    const btn = e.target.closest('.nl-form button');
    if (!btn) return;
    const input = btn.closest('.nl-form').querySelector('input');
    if (!input.value.trim()) { input.focus(); input.style.borderColor = '#ef4444'; setTimeout(() => input.style.borderColor = '', 1500); return; }
    btn.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
    btn.style.background = '#16a34a';
    input.value = '';
    setTimeout(() => { btn.innerHTML = '<i class="fas fa-paper-plane"></i> Subscribe'; btn.style.background = ''; }, 3000);
  });
}

/* Mobile Tap Guard (prevents first-tap misses on touch devices) */
function initTapGuard() {
  const isTouchLike = window.matchMedia('(hover: none) and (pointer: coarse)').matches;
  if (!isTouchLike) return;

  let startX = 0, startY = 0, moved = false, startTarget = null;

  document.addEventListener('touchstart', e => {
    if (!e.touches || e.touches.length !== 1) return;
    const t = e.touches[0];
    startX = t.clientX;
    startY = t.clientY;
    moved = false;
    startTarget = e.target;
  }, { passive: true });

  document.addEventListener('touchmove', e => {
    if (!e.touches || !e.touches.length) return;
    const t = e.touches[0];
    if (Math.abs(t.clientX - startX) > 10 || Math.abs(t.clientY - startY) > 10) moved = true;
  }, { passive: true });

  document.addEventListener('touchend', e => {
    if (!startTarget || moved) return;
    if (e.defaultPrevented) return;

    const skip = startTarget.closest('input, textarea, select, label, button, a');
    if (skip) return;

    const tapEl = startTarget.closest('.product-card, .bcat, .brand-pg-card, .feat-slide-card');
    if (!tapEl) return;

    e.preventDefault();
    tapEl.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, view: window }));
  }, { passive: false });
}

/* Boot */
document.addEventListener('DOMContentLoaded', () => {
  initStickyHeader();
  initHamburger();
  initHero();
  initTabs();
  initReveal();
  initCounters();
  initScrollTop();
  initCart();
  initWishBtns();
  initNewsletter();
  initTapGuard();
});
