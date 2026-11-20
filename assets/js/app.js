/* EcoVillage — interaksi halaman (tanpa framework) */
(function () {
  'use strict';

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var root = document.documentElement;

  var SVG = {
    sun: '<svg class="i" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>',
    moon: '<svg class="i" viewBox="0 0 24 24"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>',
    pin: {
      'Kantor Desa': '<svg viewBox="0 0 24 24"><path d="M10 18v-7M14 18v-7M18 18v-7M6 18v-7M3 22h18M12 2l8 5H4z"/></svg>',
      'Pendidikan': '<svg viewBox="0 0 24 24"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
      'Kesehatan': '<svg viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
      'Fasilitas Umum': '<svg viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/></svg>',
      'Wisata': '<svg viewBox="0 0 24 24"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>',
      'UMKM': '<svg viewBox="0 0 24 24"><path d="M2 7h20l-2 5H4L2 7zM4 12v8h16v-8M9 20v-5h6v5"/></svg>'
    }
  };
  var PIN_COLORS = { 'Kantor Desa': '#B91C1C', 'Pendidikan': '#2563EB', 'Kesehatan': '#DB2777', 'Fasilitas Umum': '#0F766E', 'Wisata': '#7C3AED', 'UMKM': '#D97706' };

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* ---------- Tema ---------- */
  function currentTheme() {
    var t = root.getAttribute('data-theme');
    if (t === 'dark' || t === 'light') return t;
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  function paintThemeBtn() {
    $$('[data-theme-toggle]').forEach(function (b) {
      var dark = currentTheme() === 'dark';
      b.innerHTML = dark ? SVG.sun : SVG.moon;
      b.setAttribute('aria-label', dark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
      b.setAttribute('aria-pressed', String(dark));
    });
  }
  $$('[data-theme-toggle]').forEach(function (b) {
    b.addEventListener('click', function () {
      var next = currentTheme() === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      try { localStorage.setItem('ecovillage-theme', next); } catch (e) {}
      paintThemeBtn();
      document.dispatchEvent(new CustomEvent('themechange'));
    });
  });
  paintThemeBtn();

  /* ---------- Drawer menu ---------- */
  var drawer = $('#drawer');
  function setDrawer(open) {
    if (!drawer) return;
    drawer.classList.toggle('is-open', open);
    drawer.setAttribute('aria-hidden', String(!open));
    $$('[data-drawer-open]').forEach(function (b) { b.setAttribute('aria-expanded', String(open)); });
    document.body.classList.toggle('no-scroll', open);
    if (open) { var f = $('a, button', drawer.querySelector('.drawer__panel')); if (f) f.focus(); }
  }
  $$('[data-drawer-open]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(true); }); });
  $$('[data-drawer-close]').forEach(function (b) { b.addEventListener('click', function () { setDrawer(false); }); });

  /* ---------- Pencarian ---------- */
  var searchPanel = $('#search-panel');
  if (searchPanel) {
    var sForm = $('[data-search-form]', searchPanel);
    $$('[data-search-open]').forEach(function (b) {
      b.addEventListener('click', function () { searchPanel.classList.add('is-open'); setTimeout(function () { $('#global-search').focus(); }, 30); });
    });
    searchPanel.addEventListener('click', function (e) { if (e.target === searchPanel) searchPanel.classList.remove('is-open'); });
    $$('[data-search-target]', searchPanel).forEach(function (b) {
      b.addEventListener('click', function () {
        $$('[data-search-target]', searchPanel).forEach(function (x) { x.classList.remove('is-active'); });
        b.classList.add('is-active');
        sForm.action = b.getAttribute('data-search-target');
        $('#global-search').name = b.getAttribute('data-search-name') || 'cari';
        $('#global-search').focus();
      });
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    setDrawer(false);
    if (searchPanel) searchPanel.classList.remove('is-open');
    $$('.modal.is-open').forEach(closeModal);
    var full = $('.map-wrap.is-full'); if (full) toggleFull(full);
  });

  /* ---------- Modal ---------- */
  var lastFocus = null;
  function openModal(m) {
    lastFocus = document.activeElement;
    m.classList.add('is-open');
    document.body.classList.add('no-scroll');
    var c = $('[data-modal-close]', m); if (c) c.focus();
  }
  function closeModal(m) {
    m.classList.remove('is-open');
    if (!$('.modal.is-open') && !(drawer && drawer.classList.contains('is-open'))) document.body.classList.remove('no-scroll');
    if (lastFocus) lastFocus.focus();
  }
  $$('.modal').forEach(function (m) {
    m.addEventListener('click', function (e) { if (e.target === m || e.target.closest('[data-modal-close]')) closeModal(m); });
  });

  var modal = $('#modal');
  function showModalHtml(html) {
    if (!modal) return;
    $('[data-modal-content]', modal).innerHTML = html;
    openModal(modal);
  }

  document.addEventListener('click', function (e) {
    var n = e.target.closest('[data-news]');
    var p = e.target.closest('[data-place]');
    if ((n || p) && modal && !e.ctrlKey && !e.metaKey) {
      e.preventDefault();
      var url = n ? '/api/berita/' + n.getAttribute('data-news') : '/api/jelajah/' + p.getAttribute('data-place');
      showModalHtml('<div class="modal__body"><p class="muted">Memuat…</p></div>');
      fetch(url, { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (d) {
        if (d.error) throw new Error(d.error);
        if (n) {
          showModalHtml('<img class="modal__img" src="' + esc(d.image) + '" alt="">' +
            '<div class="modal__body"><div class="media-item__meta"><span class="badge">' + esc(d.category) + '</span><span>' + esc(d.date) + '</span><span>' + esc(d.author || 'Admin Desa') + '</span></div>' +
            '<h2 id="modal-title" style="margin-top:10px">' + esc(d.title) + '</h2><div class="prose">' + d.html + '</div>' +
            '<a class="btn btn--soft btn--sm" href="/berita?baca=' + d.id + '">Buka halaman berita</a></div>');
        } else {
          var rows = [['map-pin', 'Lokasi', d.location], ['tag', 'Harga / tiket', d.price], ['clock', 'Jam buka', d.hours], ['phone', 'Kontak', d.contact]]
            .filter(function (r) { return r[2]; })
            .map(function (r) { return '<dt>' + esc(r[1]) + '</dt><dd>' + esc(r[2]) + '</dd>'; }).join('');
          var wa = d.contact ? String(d.contact).replace(/\D/g, '').replace(/^0/, '62') : '';
          showModalHtml('<img class="modal__img" src="' + esc(d.image) + '" alt="">' +
            '<div class="modal__body"><span class="badge">' + esc(d.category) + (d.subcategory ? ' · ' + esc(d.subcategory) : '') + '</span>' +
            '<h2 id="modal-title" style="margin-top:10px">' + esc(d.name) + '</h2><p>' + esc(d.description) + '</p>' +
            '<dl class="kv" style="margin-bottom:16px">' + rows + '</dl>' +
            (wa.length > 8 ? '<a class="btn" target="_blank" rel="noopener" href="https://wa.me/' + wa + '?text=' + encodeURIComponent('Halo, saya tertarik dengan ' + d.name + ' (dari website Desa Marga Mulya)') + '">Hubungi via WhatsApp</a>' : '') +
            '</div>');
        }
      }).catch(function () {
        showModalHtml('<div class="modal__body"><p>Gagal memuat data. Silakan coba lagi.</p></div>');
      });
    }
  });

  /* ---------- Lightbox ---------- */
  var lightbox = $('#lightbox');
  document.addEventListener('click', function (e) {
    var img = e.target.closest('img[data-lightbox]');
    if (!img || !lightbox) return;
    $('[data-lightbox-img]', lightbox).src = img.currentSrc || img.src;
    $('[data-lightbox-img]', lightbox).alt = img.alt;
    $('[data-lightbox-cap]', lightbox).textContent = img.getAttribute('data-caption') || img.alt;
    openModal(lightbox);
  });

  /* ---------- Upload: pratinjau + perkecil ukuran ---------- */
  function resizeImage(file, maxSide, quality) {
    return new Promise(function (resolve) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) return resolve(file);
      createImageBitmap(file).then(function (bmp) {
        var scale = Math.min(1, maxSide / Math.max(bmp.width, bmp.height));
        if (scale === 1 && file.size < 350000) return resolve(file);
        var c = document.createElement('canvas');
        c.width = Math.round(bmp.width * scale); c.height = Math.round(bmp.height * scale);
        c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
        c.toBlob(function (blob) {
          if (!blob || blob.size >= file.size) return resolve(file);
          resolve(new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }));
        }, 'image/jpeg', quality);
      }).catch(function () { resolve(file); });
    });
  }

  $$('input[type=file][data-resize]').forEach(function (input) {
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      var box = input.closest('.upload');
      if (!file) { if (box) box.classList.remove('has-file'); return; }
      var name = box && $('.upload__name', box);
      if (name) name.textContent = 'Memproses foto…';
      resizeImage(file, 1280, 0.72).then(function (out) {
        if (out !== file && window.DataTransfer) {
          try { var dt = new DataTransfer(); dt.items.add(out); input.files = dt.files; } catch (e) {}
        }
        var f = input.files[0];
        if (box) {
          box.classList.add('has-file');
          var prev = $('.upload__preview', box);
          if (prev) prev.src = URL.createObjectURL(f);
          if (name) name.textContent = f.name + ' · ' + Math.round(f.size / 1024) + ' KB';
        }
        if (f.size > 3000000) {
          alert('Ukuran foto masih terlalu besar (maks. 3 MB). Silakan pilih foto lain.');
          input.value = '';
          if (box) box.classList.remove('has-file');
        }
      });
    });
  });

  $$('form[data-upload-form]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = $('button[type=submit]', form);
      if (btn) { btn.disabled = true; btn.dataset.label = btn.innerHTML; btn.innerHTML = 'Mengirim…'; }
    });
  });

  /* ---------- Penghitung karakter ---------- */
  $$('[data-counter]').forEach(function (t) {
    var out = t.parentNode.querySelector('[data-counter-out]');
    var min = parseInt(t.getAttribute('minlength') || '0', 10);
    var max = t.getAttribute('maxlength');
    function upd() { if (out) out.textContent = t.value.length + (max ? '/' + max : '') + ' karakter' + (min ? ' · minimal ' + min : ''); }
    t.addEventListener('input', upd);
    upd();
  });

  /* ---------- Salin teks ---------- */
  $$('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var txt = b.getAttribute('data-copy');
      var done = function () { var o = b.innerHTML; b.textContent = 'Tersalin ✓'; setTimeout(function () { b.innerHTML = o; }, 1600); };
      if (navigator.clipboard) navigator.clipboard.writeText(txt).then(done, done); else done();
    });
  });

  /* ---------- Login: tab, peran, tampilkan sandi ---------- */
  $$('[data-tab]').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = b.getAttribute('data-tab');
      $$('[data-tab]').forEach(function (x) { x.setAttribute('aria-selected', String(x === b)); });
      $$('[data-tab-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-tab-panel') !== t; });
    });
  });
  $$('[data-role-radio]').forEach(function (r) {
    r.addEventListener('change', function () {
      var admin = r.value === 'admin' && r.checked;
      $$('[data-admin-only]').forEach(function (x) { x.hidden = !admin; var i = $('input', x); if (i) i.required = admin; });
    });
  });
  $$('[data-toggle-pass]').forEach(function (b) {
    b.addEventListener('click', function () {
      var i = document.getElementById(b.getAttribute('data-toggle-pass'));
      i.type = i.type === 'password' ? 'text' : 'password';
    });
  });

  /* ---------- Konfirmasi ---------- */
  document.addEventListener('submit', function (e) {
    var f = e.target;
    var msg = f.getAttribute && f.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  }, true);

  /* ---------- Muat lebih banyak (galeri) ---------- */
  $$('[data-load-more]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var grid = $('[data-load-grid]');
      a.textContent = 'Memuat…';
      fetch(a.href).then(function (r) { return r.text(); }).then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        $$('[data-load-grid] > *', doc).forEach(function (el) { grid.appendChild(document.importNode(el, true)); });
        var next = $('[data-load-more]', doc);
        if (next) { a.href = next.getAttribute('href'); a.textContent = 'Muat lebih banyak'; } else { a.closest('[data-load-wrap]').remove(); }
      }).catch(function () { window.location = a.href; });
    });
  });

  /* ---------- Grafik (Chart.js) ---------- */
  var PALETTE = ['#16803C', '#F59E0B', '#2563EB', '#E5484D', '#0EA5A4', '#8B5CF6', '#84CC16', '#EC4899', '#64748B', '#F97316'];
  function cssVar(n) { return getComputedStyle(root).getPropertyValue(n).trim(); }
  function fmt(v) { return Number(v).toLocaleString('id-ID', { maximumFractionDigits: 2 }); }
  var charts = [];

  function buildChart(canvas) {
    if (!window.Chart || canvas.dataset.ready) return;
    var cfg;
    try { cfg = JSON.parse(canvas.getAttribute('data-chart')); } catch (e) { return; }
    canvas.dataset.ready = '1';
    var type = cfg.type, unit = cfg.unit ? ' ' + cfg.unit : '';
    var round = type === 'pie' || type === 'doughnut';
    var multi = cfg.datasets.length > 1;
    var text = cssVar('--text-muted') || '#4B6355';
    var grid = cssVar('--border') || '#DCE8E0';
    var datasets = cfg.datasets.map(function (ds, i) {
      var pal = cfg.colors || PALETTE;
      var color = multi ? pal[i % pal.length] : (round ? cfg.labels.map(function (_, j) { return pal[j % pal.length]; }) : pal[0]);
      return {
        label: ds.label, data: ds.data, backgroundColor: color, borderColor: type === 'line' ? color : (round ? cssVar('--card') || '#fff' : color),
        borderWidth: round ? 2 : (type === 'line' ? 3 : 0), borderRadius: round ? 0 : 6, tension: .35, pointRadius: type === 'line' ? 4 : 0,
        maxBarThickness: 46, fill: false
      };
    });
    var chartType = type === 'hbar' || type === 'stacked' ? 'bar' : type;
    var c = new Chart(canvas, {
      type: chartType,
      data: { labels: cfg.labels, datasets: datasets },
      options: {
        responsive: true, maintainAspectRatio: false, indexAxis: type === 'hbar' ? 'y' : 'x',
        cutout: type === 'doughnut' ? '62%' : undefined,
        animation: { duration: 700 },
        plugins: {
          legend: { display: cfg.legend === false ? false : (round || multi), position: round ? 'right' : 'bottom', labels: { color: text, boxWidth: 12, boxHeight: 12, usePointStyle: true, font: { family: 'Inter', size: 11 } } },
          tooltip: { callbacks: { label: function (ctx) {
            var v = ctx.parsed && typeof ctx.parsed === 'object' ? (type === 'hbar' ? ctx.parsed.x : ctx.parsed.y) : ctx.parsed;
            var s = (ctx.dataset.label && (multi) ? ctx.dataset.label + ': ' : (round ? ctx.label + ': ' : '')) + fmt(v) + unit;
            if (round) { var sum = ctx.dataset.data.reduce(function (a, b) { return a + Number(b); }, 0); if (sum) s += ' (' + fmt(v / sum * 100) + '%)'; }
            return s;
          } } }
        },
        scales: round ? {} : {
          x: { stacked: type === 'stacked', grid: { display: type === 'hbar', color: grid }, ticks: { color: text, font: { size: 11 } } },
          y: { stacked: type === 'stacked', beginAtZero: true, grid: { color: grid, display: type !== 'hbar' }, ticks: { color: text, font: { size: 11 }, callback: function (v, i) { return type === 'hbar' ? this.getLabelForValue(v) : fmt(v); } } }
        }
      }
    });
    if (window.innerWidth < 640 && round) { c.options.plugins.legend.position = 'bottom'; c.update(); }
    charts.push(c);
  }

  function initCharts(scope) {
    var canvases = $$('canvas[data-chart]', scope);
    if (!canvases.length || !window.Chart) return;
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) { if (en.isIntersecting) { buildChart(en.target); io.unobserve(en.target); } });
      }, { rootMargin: '120px' });
      canvases.forEach(function (c) { if (!c.closest('[hidden]')) io.observe(c); });
    } else {
      canvases.forEach(buildChart);
    }
  }
  initCharts(document);
  document.addEventListener('themechange', function () {
    charts.forEach(function (c) { c.destroy(); });
    charts = [];
    $$('canvas[data-chart]').forEach(function (c) { delete c.dataset.ready; });
    initCharts(document);
  });

  /* ---------- Header transparan → solid saat scroll ---------- */
  var header = $('[data-header]');
  if (header) {
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 24); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- Toast ---------- */
  $$('.toast').forEach(function (t, i) {
    var close = function () { t.style.transition = 'opacity .3s'; t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 300); };
    var b = $('[data-toast-close]', t); if (b) b.addEventListener('click', close);
    setTimeout(close, 6500 + i * 800);
  });

  /* ---------- Link pindah tab (login) ---------- */
  $$('[data-tab-link]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var b = $('[data-tab="' + a.getAttribute('data-tab-link') + '"]'); if (b) b.click();
    });
  });

  /* ---------- Dropzone ---------- */
  $$('[data-dropzone]').forEach(function (z) {
    ['dragenter', 'dragover'].forEach(function (ev) { z.addEventListener(ev, function () { z.classList.add('is-drag'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { z.addEventListener(ev, function () { z.classList.remove('is-drag'); }); });
  });

  /* ---------- Form Lapor: langkah & titik peta ---------- */
  var rf = $('[data-report-form]');
  if (rf) {
    var stepCheck = {
      category: function () { return !!rf.category.value; },
      location: function () { return rf.location.value.trim().length >= 3; },
      description: function () { return rf.description.value.trim().length >= 20; },
      photo: function () { return rf.photo.files && rf.photo.files.length > 0; }
    };
    var paintSteps = function () {
      $$('[data-step]', rf).forEach(function (li) {
        var ok = stepCheck[li.getAttribute('data-step')]();
        li.classList.toggle('is-done', ok);
        $('.steps__dot', li).innerHTML = ok ? '✓' : $('.steps__dot', li).getAttribute('data-n') || $('.steps__dot', li).textContent;
      });
    };
    $$('.steps__dot', rf).forEach(function (d) { d.setAttribute('data-n', d.textContent); });
    rf.addEventListener('input', paintSteps);
    rf.addEventListener('change', paintSteps);
    paintSteps();
  }
  $$('[data-report-map]').forEach(function (el) {
    if (!window.L) return;
    var form = el.closest('form');
    var lat = form.querySelector('input[name=lat]'), lng = form.querySelector('input[name=lng]');
    var start = lat.value && lng.value ? [parseFloat(lat.value), parseFloat(lng.value)] : JSON.parse(el.getAttribute('data-center'));
    var m = L.map(el, { scrollWheelZoom: false, doubleClickZoom: false }).setView(start, 13);
    tileLayer().addTo(m);
    fetch('/api/peta').then(function (r) { return r.json(); }).then(function (d) {
      d.regions.forEach(function (r) { if (r.level === 'desa' && r.coords.length > 2) L.polygon(r.coords, { color: '#17803F', weight: 2, fillOpacity: .06, interactive: false }).addTo(m); });
    }).catch(function () {});
    var mk = lat.value ? L.marker(start, { icon: pinIcon('Fasilitas Umum') }).addTo(m) : null;
    m.on('click', function (e) {
      if (form.querySelector('fieldset[disabled]')) return;
      lat.value = e.latlng.lat.toFixed(7); lng.value = e.latlng.lng.toFixed(7);
      if (mk) mk.setLatLng(e.latlng); else mk = L.marker(e.latlng, { icon: pinIcon('Fasilitas Umum') }).addTo(m);
    });
  });

  /* ---------- Tab bagian informasi A–H ---------- */
  $$('[data-section-tab]').forEach(function (tab) {
    tab.addEventListener('click', function (e) {
      e.preventDefault();
      var code = tab.getAttribute('data-section-tab');
      $$('[data-section-tab]').forEach(function (t) { t.setAttribute('aria-selected', String(t === tab)); });
      $$('[data-section-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-section-panel') !== code; });
      if (history.replaceState) history.replaceState(null, '', '?bagian=' + code);
      var panel = $('[data-section-panel="' + code + '"]');
      initCharts(panel);
      if (window.innerWidth < 1000) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  /* ---------- Peta (Leaflet) ---------- */
  function tileLayer() {
    return L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    });
  }
  function pinIcon(type) {
    return L.divIcon({
      className: '', iconSize: [34, 34], iconAnchor: [17, 34], popupAnchor: [0, -30],
      html: '<span class="map-pin" style="background:' + (PIN_COLORS[type] || '#16803C') + '">' + (SVG.pin[type] || SVG.pin['Fasilitas Umum']) + '</span>'
    });
  }
  function toggleFull(wrap) {
    var on = !wrap.classList.contains('is-full');
    wrap.classList.toggle('is-full', on);
    document.body.classList.toggle('no-scroll', on);
    if (wrap._map) setTimeout(function () { wrap._map.invalidateSize(); }, 60);
  }

  var vm = $('[data-village-map]');
  if (vm && window.L) {
    var map = L.map(vm, { zoomControl: false, scrollWheelZoom: false });
    tileLayer().addTo(map);
    var wrap = vm.closest('[data-map-wrap]'); wrap._map = map;
    var layers = { desa: L.layerGroup(), rw: L.layerGroup(), rt: L.layerGroup() };
    var points = L.layerGroup().addTo(map);
    var bounds = null, current = 'desa';
    var legend = $('[data-map-legend]');
    var office = (vm.getAttribute('data-office') || '').split(',').map(Number);
    var meMarker = null;

    fetch('/api/peta').then(function (r) { return r.json(); }).then(function (d) {
      map.setView(d.center, d.zoom);
      d.regions.forEach(function (r) {
        if (!r.coords || r.coords.length < 3) return;
        var poly = L.polygon(r.coords, { color: r.color, weight: r.level === 'desa' ? 3 : 2, fillColor: r.color, fillOpacity: r.level === 'desa' ? .12 : .28, dashArray: r.level === 'desa' ? null : '4' });
        var info = '<strong>' + esc(r.name) + '</strong>' + (r.parent_name && r.level !== 'rw' ? ' · ' + esc(r.parent_name) : '') +
          (r.leader ? '<br>Ketua: ' + esc(r.leader) : '') + (r.population ? '<br>Penduduk: ' + fmt(r.population) + ' jiwa' : '') + (r.households ? '<br>KK: ' + fmt(r.households) : '');
        poly.bindPopup(info);
        if (r.level !== 'desa') poly.bindTooltip(esc(r.name), { permanent: true, direction: 'center', className: 'map-label' });
        poly.addTo(layers[r.level]);
        poly._meta = r;
        if (r.level === 'desa') bounds = poly.getBounds();
      });
      d.points.forEach(function (p) {
        L.marker([p.lat, p.lng], { icon: pinIcon(p.type), title: p.name }).bindPopup('<strong>' + esc(p.name) + '</strong><br><span style="color:#666">' + esc(p.type) + '</span>' + (p.description ? '<br>' + esc(p.description) : '')).addTo(points);
      });
      showLevel('desa');
      if (bounds) map.fitBounds(bounds, { padding: [20, 20] });
    }).catch(function () { vm.innerHTML = '<p class="empty">Peta gagal dimuat.</p>'; });

    function showLevel(level) {
      current = level;
      Object.keys(layers).forEach(function (k) { map.removeLayer(layers[k]); });
      layers.desa.addTo(map);
      if (level !== 'desa') layers[level].addTo(map);
      points.bringToFront && points.bringToFront();
      var items = [];
      layers[level].eachLayer(function (l) { items.push('<span><i style="background:' + l._meta.color + '"></i>' + esc(l._meta.name) + '</span>'); });
      var pts = Object.keys(PIN_COLORS).map(function (k) { return '<span><i style="background:' + PIN_COLORS[k] + ';border-radius:50%"></i>' + k + '</span>'; });
      if (legend) legend.innerHTML = items.join('') + pts.join('');
    }

    $$('[data-map-level]').forEach(function (b) {
      b.addEventListener('click', function () {
        $$('[data-map-level]').forEach(function (x) { x.classList.toggle('is-active', x === b); x.setAttribute('aria-selected', String(x === b)); });
        showLevel(b.getAttribute('data-map-level'));
      });
    });
    $$('[data-map-zoom]', wrap).forEach(function (b) { b.addEventListener('click', function () { b.getAttribute('data-map-zoom') === 'in' ? map.zoomIn() : map.zoomOut(); }); });
    var fb = $('[data-map-full]', wrap); if (fb) fb.addEventListener('click', function () { toggleFull(wrap); });
    var ob = $('[data-map-office]', wrap);
    if (ob) ob.addEventListener('click', function () {
      if (office.length !== 2 || isNaN(office[0])) return;
      map.flyTo(office, 17);
      points.eachLayer(function (m) { var ll = m.getLatLng(); if (Math.abs(ll.lat - office[0]) < 1e-4 && Math.abs(ll.lng - office[1]) < 1e-4) m.openPopup(); });
    });
    var lb = $('[data-map-locate]', wrap);
    if (lb) lb.addEventListener('click', function () {
      if (!navigator.geolocation) { alert('Perangkat tidak mendukung deteksi lokasi.'); return; }
      lb.disabled = true;
      navigator.geolocation.getCurrentPosition(function (pos) {
        lb.disabled = false;
        var ll = [pos.coords.latitude, pos.coords.longitude];
        if (meMarker) map.removeLayer(meMarker);
        meMarker = L.marker(ll, { icon: L.divIcon({ className: '', html: '<div class="me-dot"></div>', iconSize: [18, 18], iconAnchor: [9, 9] }) })
          .bindPopup('Lokasi Anda saat ini').addTo(map).openPopup();
        map.flyTo(ll, 15);
      }, function () { lb.disabled = false; alert('Lokasi tidak dapat diakses. Pastikan izin lokasi diaktifkan.'); }, { enableHighAccuracy: true, timeout: 10000 });
    });
    map.on('focus', function () { map.scrollWheelZoom.enable(); });
    map.on('blur', function () { map.scrollWheelZoom.disable(); });
  }

  // Peta kantor desa (halaman kontak)
  $$('[data-office-map]').forEach(function (el) {
    if (!window.L) return;
    var ll = [parseFloat(el.getAttribute('data-lat')), parseFloat(el.getAttribute('data-lng'))];
    var m = L.map(el, { scrollWheelZoom: false }).setView(ll, 16);
    tileLayer().addTo(m);
    L.marker(ll, { icon: pinIcon('Kantor Desa') }).addTo(m).bindPopup('<strong>' + esc(el.getAttribute('data-title')) + '</strong>').openPopup();
  });

  /* ---------- Admin: pemilih titik & poligon ---------- */
  $$('[data-latlng-picker]').forEach(function (el) {
    if (!window.L) return;
    var lat = $('input[name=lat]'), lng = $('input[name=lng]');
    var start = lat.value && lng.value ? [parseFloat(lat.value), parseFloat(lng.value)] : JSON.parse(el.getAttribute('data-center'));
    var m = L.map(el).setView(start, 15);
    tileLayer().addTo(m);
    var mk = lat.value ? L.marker(start).addTo(m) : null;
    m.on('click', function (e) {
      lat.value = e.latlng.lat.toFixed(7); lng.value = e.latlng.lng.toFixed(7);
      if (mk) mk.setLatLng(e.latlng); else mk = L.marker(e.latlng).addTo(m);
    });
  });

  $$('[data-polygon-picker]').forEach(function (el) {
    if (!window.L) return;
    var ta = document.getElementById(el.getAttribute('data-target'));
    var color = function () { var c = $('input[name=color]'); return c ? c.value : '#16803C'; };
    var pts = [];
    try { pts = JSON.parse(ta.value || '[]') || []; } catch (e) { pts = []; }
    var m = L.map(el, { doubleClickZoom: false }).setView(pts.length ? pts[0] : JSON.parse(el.getAttribute('data-center')), 14);
    tileLayer().addTo(m);
    var ctx = L.layerGroup().addTo(m);
    fetch('/api/peta').then(function (r) { return r.json(); }).then(function (d) {
      d.regions.forEach(function (r) { if (r.coords.length > 2) L.polygon(r.coords, { color: '#999', weight: 1, fillOpacity: .03, interactive: false }).addTo(ctx); });
    });
    var layer = L.layerGroup().addTo(m);
    function draw() {
      layer.clearLayers();
      if (pts.length > 2) L.polygon(pts, { color: color(), fillOpacity: .3 }).addTo(layer);
      else if (pts.length > 1) L.polyline(pts, { color: color() }).addTo(layer);
      pts.forEach(function (p) { L.circleMarker(p, { radius: 5, color: color(), fillOpacity: 1 }).addTo(layer); });
      ta.value = pts.length ? JSON.stringify(pts) : '';
    }
    if (pts.length > 2) m.fitBounds(L.polygon(pts).getBounds(), { padding: [30, 30] });
    draw();
    m.on('click', function (e) { pts.push([+e.latlng.lat.toFixed(6), +e.latlng.lng.toFixed(6)]); draw(); });
    var undo = $('[data-poly-undo]'), clear = $('[data-poly-clear]');
    if (undo) undo.addEventListener('click', function () { pts.pop(); draw(); });
    if (clear) clear.addEventListener('click', function () { pts = []; draw(); });
    ta.addEventListener('change', function () { try { pts = JSON.parse(ta.value) || []; draw(); } catch (e) {} });
  });

  /* ---------- Admin: tabel baris data grafik ---------- */
  var rowsTable = $('[data-rows]');
  if (rowsTable) {
    var tpl = $('#row-template');
    $('[data-add-row]').addEventListener('click', function () {
      rowsTable.appendChild(tpl.content.cloneNode(true));
      var last = rowsTable.lastElementChild; var i = $('input', last); if (i) i.focus();
    });
    rowsTable.addEventListener('click', function (e) {
      var b = e.target.closest('[data-remove-row]');
      if (b) b.closest('tr').remove();
    });
  }
})();
