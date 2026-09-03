(() => {
  'use strict';

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const header = document.querySelector('[data-site-header]');
  const toggle = document.querySelector('[data-nav-toggle]');
  const menu = document.querySelector('[data-nav-menu]');

  const updateHeader = () => header?.classList.toggle('scrolled', window.scrollY > 24);
  updateHeader();
  window.addEventListener('scroll', updateHeader, { passive: true });

  toggle?.addEventListener('click', () => {
    const open = !menu.classList.contains('open');
    menu.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', String(open));
    document.body.classList.toggle('menu-open', open);
  });
  menu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    menu.classList.remove('open');
    toggle?.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
  }));

  document.querySelectorAll('[data-carousel]').forEach((root) => {
    const slides = [...root.querySelectorAll('[data-slide]')];
    const dots = [...root.querySelectorAll('[data-dot]')];
    if (slides.length < 2) return;
    let current = Math.max(0, slides.findIndex((slide) => slide.classList.contains('active')));
    let timer;
    const show = (index) => {
      current = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => slide.classList.toggle('active', i === current));
      dots.forEach((dot, i) => dot.classList.toggle('active', i === current));
    };
    const play = () => {
      if (reducedMotion) return;
      clearInterval(timer);
      timer = setInterval(() => show(current + 1), Number(root.dataset.interval) || 5000);
    };
    root.querySelector('[data-prev]')?.addEventListener('click', () => { show(current - 1); play(); });
    root.querySelector('[data-next]')?.addEventListener('click', () => { show(current + 1); play(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); play(); }));
    root.addEventListener('mouseenter', () => clearInterval(timer));
    root.addEventListener('mouseleave', play);
    play();
  });

  document.querySelectorAll('[data-review-carousel]').forEach((root) => {
    const track = root.querySelector('.review-track');
    const cards = [...root.querySelectorAll('.review-card')];
    const dots = [...root.querySelectorAll('[data-review-dot]')];
    if (!track || cards.length < 2) return;
    let current = 0;
    let timer;
    const visible = () => window.innerWidth <= 720 ? 1 : window.innerWidth <= 1050 ? 2 : 3;
    const show = (index) => {
      const max = Math.max(0, cards.length - visible());
      current = index > max ? 0 : index < 0 ? max : index;
      const cardWidth = cards[0].getBoundingClientRect().width + 16;
      track.style.transform = `translate3d(${-current * cardWidth}px,0,0)`;
      dots.forEach((dot, i) => dot.classList.toggle('active', i === current));
    };
    const play = () => {
      if (reducedMotion) return;
      clearInterval(timer);
      timer = setInterval(() => show(current + 1), Number(root.dataset.interval) || 5200);
    };
    dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); play(); }));
    window.addEventListener('resize', () => show(current));
    root.addEventListener('mouseenter', () => clearInterval(timer));
    root.addEventListener('mouseleave', play);
    show(0);
    play();
  });

  const animated = document.querySelectorAll('.reveal, .stagger');
  if ('IntersectionObserver' in window && !reducedMotion) {
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('in-view');
      observer.unobserve(entry.target);
    }), { threshold: 0.12, rootMargin: '0px 0px -30px' });
    animated.forEach((node) => observer.observe(node));
  } else {
    animated.forEach((node) => node.classList.add('in-view'));
  }

  const submitJsonForm = async (form, endpoint, payload) => {
    const feedback = form.querySelector('[data-form-feedback]');
    const button = form.querySelector('button[type="submit"]');
    feedback.className = 'form-feedback';
    feedback.textContent = '';
    button.disabled = true;
    const original = button.innerHTML;
    button.textContent = 'กำลังส่งข้อมูล...';
    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json().catch(() => ({}));
      if (!response.ok) {
        const firstError = result.errors ? Object.values(result.errors).flat()[0] : null;
        throw new Error(firstError || result.message || 'ไม่สามารถส่งข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
      }
      form.reset();
      feedback.classList.add('success');
      feedback.textContent = result.message || 'ส่งข้อมูลเรียบร้อยแล้ว';
    } catch (error) {
      feedback.classList.add('error');
      feedback.textContent = error instanceof Error ? error.message : 'ไม่สามารถส่งข้อมูลได้ กรุณาลองใหม่อีกครั้ง';
    } finally {
      button.disabled = false;
      button.innerHTML = original;
    }
  };

  document.querySelectorAll('[data-contact-form]').forEach((form) => form.addEventListener('submit', (event) => {
    event.preventDefault();
    const data = new FormData(form);
    submitJsonForm(form, '/api/contact-leads', {
      name: data.get('name'), phone: data.get('phone'), email: data.get('email') || null,
      service_type: data.get('service_type') || null, message: data.get('message') || null,
      source_url: window.location.href, website: data.get('website') || null,
    });
  }));

  document.querySelectorAll('[data-comment-form]').forEach((form) => form.addEventListener('submit', (event) => {
    event.preventDefault();
    const data = new FormData(form);
    submitJsonForm(form, form.dataset.endpoint, {
      article_title: data.get('article_title'), author_name: data.get('author_name'),
      author_email: data.get('author_email') || null, body: data.get('body'), website: data.get('website') || null,
    });
  }));

  const filterRoot = document.querySelector('[data-design-filters]');
  if (filterRoot) {
    const cards = [...document.querySelectorAll('[data-design-card]')];
    const empty = document.querySelector('[data-filter-empty]');
    const update = () => {
      const style = filterRoot.querySelector('[data-filter="style"]').value;
      const budget = filterRoot.querySelector('[data-filter="budget"]').value;
      const search = filterRoot.querySelector('[data-filter="search"]').value.trim().toLocaleLowerCase('th');
      let count = 0;
      cards.forEach((card) => {
        const show = (!style || card.dataset.style === style) && (!budget || card.dataset.budget === budget)
          && (!search || card.dataset.search.includes(search));
        card.hidden = !show;
        count += Number(show);
      });
      if (empty) empty.hidden = count > 0;
    };
    filterRoot.querySelectorAll('input,select').forEach((field) => field.addEventListener('input', update));
    filterRoot.querySelector('[data-filter-reset]')?.addEventListener('click', () => { filterRoot.reset?.(); filterRoot.querySelectorAll('input,select').forEach((field) => { field.value = ''; }); update(); });
  }

  document.querySelectorAll('[data-faq-tab]').forEach((tab) => tab.addEventListener('click', () => {
    document.querySelectorAll('[data-faq-tab]').forEach((item) => item.classList.toggle('active', item === tab));
    document.querySelectorAll('[data-faq-group]').forEach((group) => { group.hidden = group.dataset.faqGroup !== tab.dataset.faqTab; });
  }));

  const galleryItems = [...document.querySelectorAll('[data-lightbox-item]')];
  const lightbox = document.querySelector('[data-lightbox]');
  if (galleryItems.length && lightbox) {
    const image = lightbox.querySelector('[data-lightbox-image]');
    const caption = lightbox.querySelector('[data-lightbox-caption]');
    let current = 0;
    const show = (index) => {
      current = (index + galleryItems.length) % galleryItems.length;
      const item = galleryItems[current];
      image.src = item.dataset.src;
      image.alt = item.dataset.alt || '';
      caption.textContent = item.dataset.caption || `${current + 1} / ${galleryItems.length}`;
      lightbox.hidden = false;
      document.body.classList.add('menu-open');
    };
    const close = () => { lightbox.hidden = true; document.body.classList.remove('menu-open'); };
    galleryItems.forEach((item, index) => item.addEventListener('click', () => show(index)));
    lightbox.querySelector('[data-lightbox-close]').addEventListener('click', close);
    lightbox.querySelector('[data-lightbox-prev]').addEventListener('click', () => show(current - 1));
    lightbox.querySelector('[data-lightbox-next]').addEventListener('click', () => show(current + 1));
    lightbox.addEventListener('click', (event) => { if (event.target === lightbox) close(); });
    document.addEventListener('keydown', (event) => {
      if (lightbox.hidden) return;
      if (event.key === 'Escape') close();
      if (event.key === 'ArrowLeft') show(current - 1);
      if (event.key === 'ArrowRight') show(current + 1);
    });
  }

  const storage = {
    get: (key, session = false) => { try { return (session ? sessionStorage : localStorage).getItem(key); } catch { return null; } },
    set: (key, value, session = false) => { try { (session ? sessionStorage : localStorage).setItem(key, value); } catch {} },
    remove: (key) => { try { localStorage.removeItem(key); } catch {} },
  };
  const cookieBanner = document.querySelector('[data-cookie-banner]');
  const openCookies = () => { if (cookieBanner) cookieBanner.hidden = false; };
  if (cookieBanner && !storage.get('bm-cookie-consent')) openCookies();
  document.querySelector('[data-cookie-accept]')?.addEventListener('click', () => { storage.set('bm-cookie-consent', 'all'); cookieBanner.hidden = true; });
  document.querySelector('[data-cookie-reject]')?.addEventListener('click', () => { storage.set('bm-cookie-consent', 'necessary'); cookieBanner.hidden = true; });
  document.querySelectorAll('[data-cookie-settings]').forEach((button) => button.addEventListener('click', () => { storage.remove('bm-cookie-consent'); openCookies(); }));

  const popup = document.querySelector('[data-welcome-popup]');
  if (popup && !storage.get(popup.dataset.popupKey, true)) {
    window.setTimeout(() => { popup.hidden = false; }, 700);
    popup.querySelector('[data-popup-close]')?.addEventListener('click', () => { popup.hidden = true; storage.set(popup.dataset.popupKey, 'closed', true); });
    popup.addEventListener('click', (event) => { if (event.target === popup) { popup.hidden = true; storage.set(popup.dataset.popupKey, 'closed', true); } });
  }
})();
