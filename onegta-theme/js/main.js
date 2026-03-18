/**
 * OneGTA main.js
 */
document.addEventListener('DOMContentLoaded', () => {

  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const vars = window.onegtaVars || {};

  /* ── CURSOR ──────────────────────────────────── */
  const cursor = $('#cursor'), trail = $('#cursorTrail');
  if (cursor && trail && matchMedia('(hover:hover)').matches) {
    let tx = 0, ty = 0, cx = 0, cy = 0;
    document.addEventListener('mousemove', e => {
      tx = e.clientX; ty = e.clientY;
      cursor.style.cssText = `left:${tx}px;top:${ty}px`;
    });
    const animTrail = () => {
      cx += (tx - cx) * .12; cy += (ty - cy) * .12;
      trail.style.cssText = `left:${cx}px;top:${cy}px`;
      requestAnimationFrame(animTrail);
    };
    animTrail();
    $$('a,button,.game-card,.news-card,.post-card,.file-card').forEach(el => {
      el.addEventListener('mouseenter', () => { cursor.style.width = cursor.style.height = '18px'; cursor.style.opacity = '.6'; });
      el.addEventListener('mouseleave', () => { cursor.style.width = cursor.style.height = '8px'; cursor.style.opacity = '1'; });
    });
  }

  /* ── HEADER SCROLL ───────────────────────────── */
  const header = $('#siteHeader');
  if (header) {
    const onScroll = () => header.classList.toggle('shadow', scrollY > 30);
    addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ── MOBILE NAV ──────────────────────────────── */
  const navToggle = $('#navToggle'), mainNav = $('#mainNav');
  if (navToggle && mainNav) {
    navToggle.addEventListener('click', () => {
      const open = mainNav.classList.toggle('open');
      navToggle.classList.toggle('active', open);
      navToggle.setAttribute('aria-expanded', String(open));
    });
    // Mobile dropdown toggles
    $$('.has-dropdown > a').forEach(a => {
      a.addEventListener('click', e => {
        if (window.innerWidth < 768) {
          e.preventDefault();
          a.closest('.has-dropdown').classList.toggle('open');
        }
      });
    });
    document.addEventListener('click', e => {
      if (!header?.contains(e.target)) {
        mainNav.classList.remove('open');
        navToggle.classList.remove('active');
        navToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ── USER DROPDOWN ───────────────────────────── */
  const userMenu    = $('#userMenu');
  const menuTrigger = $('#userMenuTrigger');
  if (userMenu && menuTrigger) {
    menuTrigger.addEventListener('click', () => {
      const open = userMenu.classList.toggle('open');
      menuTrigger.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', e => {
      if (!userMenu.contains(e.target)) {
        userMenu.classList.remove('open');
        menuTrigger.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ── AUTH MODAL ──────────────────────────────── */
  const overlay  = $('#authOverlay');
  const modal    = $('#authModal');

  function openAuth(tab = 'login') {
    if (!overlay) return;
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    switchTab(tab);
    setTimeout(() => {
      const first = modal?.querySelector(`[data-tab="${tab}"] .form-input`);
      first?.focus();
    }, 300);
  }
  function closeAuth() {
    overlay?.classList.remove('open');
    document.body.style.overflow = '';
  }

  // Open triggers
  $('#openAuthBtn')     ?.addEventListener('click', () => openAuth('login'));
  $('#openRegisterBtn') ?.addEventListener('click', () => openAuth('register'));
  $('#authClose')       ?.addEventListener('click', closeAuth);
  $('#footerLoginBtn')  ?.addEventListener('click', e => { e.preventDefault(); openAuth('login'); });
  $('#footerRegisterBtn')?.addEventListener('click', e => { e.preventDefault(); openAuth('register'); });
  $('#communityJoinBtn')?.addEventListener('click', () => openAuth('register'));
  overlay?.addEventListener('click', e => { if (e.target === overlay) closeAuth(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAuth(); });

  // Tab switch
  function switchTab(name) {
    $$('.auth-tab').forEach(t => { t.classList.toggle('active', t.dataset.tab === name); t.setAttribute('aria-selected', String(t.dataset.tab === name)); });
    $$('.auth-form').forEach(f => f.classList.toggle('active', f.dataset.tab === name));
    clearAuthAlert();
  }
  $$('.auth-tab').forEach(t => t.addEventListener('click', () => switchTab(t.dataset.tab)));
  $$('[data-switch-tab]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); switchTab(a.dataset.switchTab); }));

  // Alert helpers
  function setAuthAlert(msg, type = 'error') {
    const el = $('#authAlert');
    if (el) el.innerHTML = `<div class="alert alert--${type}">${msg}</div>`;
  }
  function clearAuthAlert() {
    const el = $('#authAlert');
    if (el) el.innerHTML = '';
  }

  // Login
  $('#loginForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true; btn.textContent = 'Входим…';
    clearAuthAlert();
    const fd = new FormData(e.target);
    fd.append('action', 'onegta_login');
    fd.append('nonce',  vars.nonce);
    try {
      const r = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
      const d = await r.json();
      if (d.success) { location.href = d.data.redirect; }
      else { setAuthAlert(d.data.message); btn.disabled = false; btn.textContent = 'Войти'; }
    } catch { setAuthAlert('Ошибка соединения'); btn.disabled = false; btn.textContent = 'Войти'; }
  });

  // Register
  $('#registerForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true; btn.textContent = 'Создаём…';
    clearAuthAlert();
    const fd = new FormData(e.target);
    fd.append('action', 'onegta_register');
    fd.append('nonce',  vars.nonce);
    try {
      const r = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
      const d = await r.json();
      if (d.success) { location.href = d.data.redirect; }
      else { setAuthAlert(d.data.message); btn.disabled = false; btn.textContent = 'Создать аккаунт'; }
    } catch { setAuthAlert('Ошибка соединения'); btn.disabled = false; btn.textContent = 'Создать аккаунт'; }
  });

  // Logout
  $('#logoutBtn')?.addEventListener('click', async () => {
    const fd = new FormData();
    fd.append('action', 'onegta_logout');
    fd.append('nonce',  vars.nonce);
    const r = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
    const d = await r.json();
    if (d.success) location.href = d.data.redirect;
  });

  /* ── SCROLL REVEAL ───────────────────────────── */
  if ('IntersectionObserver' in window) {
    const obs = new IntersectionObserver((entries) => {
      entries.forEach((en, i) => {
        if (en.isIntersecting) {
          setTimeout(() => {
            en.target.style.opacity  = '1';
            en.target.style.transform = 'translateY(0)';
          }, (i % 6) * 70);
          obs.unobserve(en.target);
        }
      });
    }, { threshold: .07 });
    $$('.game-card,.news-card,.post-card,.file-card,.feature-item,.quote-card').forEach(el => {
      el.style.cssText += 'opacity:0;transform:translateY(18px);transition:opacity .5s ease,transform .5s ease';
      obs.observe(el);
    });
  }

  /* ── PROFILE TABS ────────────────────────────── */
  $$('.profile-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      $$('.profile-tab-btn').forEach(b => b.classList.remove('active'));
      $$('.profile-tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      $(`#tab-${btn.dataset.tab}`)?.classList.add('active');
    });
  });

  /* ── PROFILE SETTINGS FORM ───────────────────── */
  const profileForm = $('#profileSettingsForm');
  if (profileForm) {
    profileForm.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = profileForm.querySelector('[type=submit]');
      btn.disabled = true; btn.textContent = 'Сохраняем…';
      const fd = new FormData(profileForm);
      fd.append('action', 'onegta_update_profile');
      const r  = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
      const d  = await r.json();
      const al = $('#profileAlert');
      if (al) al.innerHTML = `<div class="alert alert--${d.success ? 'success':'error'}">${d.data.message}</div>`;
      btn.disabled = false; btn.textContent = 'Сохранить изменения';
    });
  }

  /* ── SUBMIT POST FORM ────────────────────────── */
  async function handleSubmitPost(form) {
    const btn = form.querySelector('[type=submit]');
    const alert = $('#submitAlert');
    btn.disabled = true;
    const origText = btn.textContent;
    btn.textContent = 'Публикуем…';
    const fd = new FormData(form);
    fd.append('action', 'onegta_submit_post');
    try {
      const r = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
      const d = await r.json();
      if (d.success) {
        if (alert) alert.innerHTML = `<div class="alert alert--success">${d.data.message} <a href="${d.data.url}">Смотреть →</a></div>`;
        form.reset();
        clearPreviews();
      } else {
        if (alert) alert.innerHTML = `<div class="alert alert--error">${d.data.message}</div>`;
      }
    } catch { if (alert) alert.innerHTML = '<div class="alert alert--error">Ошибка соединения</div>'; }
    btn.disabled = false; btn.textContent = origText;
    alert?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  const submitPostForm = $('#submitPostForm');
  submitPostForm?.addEventListener('submit', e => { e.preventDefault(); handleSubmitPost(e.target); });

  /* ── SUBMIT FILE FORM ────────────────────────── */
  const submitFileForm = $('#submitFileForm');
  if (submitFileForm) {
    submitFileForm.addEventListener('submit', async e => {
      e.preventDefault();
      const btnSpan = submitFileForm.querySelector('[type=submit] span') || submitFileForm.querySelector('[type=submit]');
      const alert   = $('#submitAlert');
      const origText= btnSpan.textContent;
      const yadiskMode = submitFileForm.querySelector('[name=_yadisk_mode]')?.value === '1';
      const action  = yadiskMode ? 'onegta_submit_file_yadisk' : 'onegta_submit_file';

      btnSpan.textContent = yadiskMode ? '☁️ Загружаем на Яндекс.Диск…' : 'Загружаем…';

      const progress    = $('#yadiskProgress');
      const progressBar = $('#yadiskProgressBar');
      if (yadiskMode && progress) {
        progress.style.display = 'block';
        let pct = 0;
        const iv = setInterval(() => {
          pct = Math.min(pct + Math.random() * 8, 85);
          if (progressBar) progressBar.style.width = pct + '%';
        }, 400);
        submitFileForm._iv = iv;
      }

      const fd = new FormData(submitFileForm);
      fd.set('action', action);
      try {
        const r = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
        const d = await r.json();
        if (yadiskMode && progress) {
          clearInterval(submitFileForm._iv);
          if (progressBar) progressBar.style.width = '100%';
          setTimeout(() => { if (progress) progress.style.display = 'none'; if (progressBar) progressBar.style.width = '0%'; }, 600);
        }
        if (d.success) {
          let msg = d.data.message;
          if (d.data.on_yadisk) msg += ' ☁️';
          if (alert) alert.innerHTML = `<div class="alert alert--success">${msg} <a href="${d.data.url}">Смотреть →</a></div>`;
          submitFileForm.reset(); clearPreviews();
        } else {
          if (alert) alert.innerHTML = `<div class="alert alert--error">${d.data.message}</div>`;
        }
      } catch {
        if (yadiskMode && progress) { clearInterval(submitFileForm._iv); if (progress) progress.style.display='none'; }
        if (alert) alert.innerHTML = '<div class="alert alert--error">Ошибка загрузки</div>';
      }
      btnSpan.textContent = origText;
    });
  }

  /* ── FILE UPLOAD UI ──────────────────────────── */
  function initFileUpload(dropId, inputId, previewId, { isImage = false } = {}) {
    const drop    = $(`#${dropId}`);
    const input   = $(`#${inputId}`);
    const preview = $(`#${previewId}`);
    if (!drop || !input || !preview) return;

    drop.addEventListener('click', () => input.click());
    drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('drag-over'); });
    drop.addEventListener('dragleave', () => drop.classList.remove('drag-over'));
    drop.addEventListener('drop', e => {
      e.preventDefault(); drop.classList.remove('drag-over');
      if (e.dataTransfer.files.length) {
        const dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
      }
    });
    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      if (isImage) {
        const reader = new FileReader();
        reader.onload = e2 => {
          preview.innerHTML = `<div class="img-preview-wrap"><img src="${e2.target.result}" alt=""><button type="button" class="img-preview-remove" onclick="this.closest('.img-preview-wrap').remove()">✕</button></div>`;
        };
        reader.readAsDataURL(file);
      } else {
        const size = file.size > 1048576 ? (file.size/1048576).toFixed(1)+' MB' : (file.size/1024).toFixed(0)+' KB';
        preview.innerHTML = `<div class="file-preview"><span class="file-preview__name">📦 ${file.name}</span><span class="file-preview__size">${size}</span><button type="button" class="file-preview__remove" onclick="this.closest('.file-preview').remove()">✕</button></div>`;
      }
    });
  }

  function clearPreviews() {
    $$('[id$="Preview"]').forEach(p => p.innerHTML = '');
  }

  initFileUpload('fileDropArea',     'fileInput',      'filePreview');
  initFileUpload('thumbDropArea',    'thumbInput',     'thumbPreview',    { isImage: true });
  initFileUpload('postThumbArea',    'postThumbInput', 'postThumbPreview', { isImage: true });
  initFileUpload('vidThumbArea',     'vidThumbInput',  'vidThumbPreview',  { isImage: true });

  // Trigger avatar upload
  $('#avatarUploadInput')?.addEventListener('change', async function() {
    const fd = new FormData();
    fd.append('action', 'onegta_update_profile');
    fd.append('nonce',  vars.nonce);
    fd.append('avatar', this.files[0]);
    await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
    location.reload();
  });

  /* ── DOWNLOAD BUTTONS ────────────────────────── */
  $$('[data-post-id]').forEach(btn => {
    if (!btn.classList.contains('download-btn') && btn.id !== 'mainDownloadBtn') return;
    btn.addEventListener('click', async () => {
      const pid = btn.dataset.postId;
      const fd  = new FormData();
      fd.append('action',  'onegta_download');
      fd.append('nonce',   vars.nonce);
      fd.append('post_id', pid);
      const r = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
      const d = await r.json();
      if (d.success && d.data.url) {
        const a = document.createElement('a');
        a.href = d.data.url; a.download = '';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
      }
    });
  });

  /* ── CHEATS: COPY ────────────────────────────── */
  $$('.cheat-copy-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const code = btn.dataset.copy;
      try { await navigator.clipboard.writeText(code); }
      catch { const ta = Object.assign(document.createElement('textarea'), {value:code}); document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove(); }
      const orig = btn.textContent;
      btn.textContent = '✓'; btn.style.background = 'var(--orange)'; btn.style.color = '#fff';
      setTimeout(() => { btn.textContent = orig; btn.style.background = ''; btn.style.color = ''; }, 1600);
    });
  });

  /* ── CHEATS: PLATFORM SWITCH ─────────────────── */
  $$('[data-platform]').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('.filter-bar__inner') || btn.parentElement;
      $$('[data-platform]', group).forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const plat = btn.dataset.platform;
      $$('[data-platform-code]').forEach(el => {
        el.style.display = el.dataset.platformCode === plat ? 'flex' : 'none';
      });
    });
  });

  /* ── CHEATS: GAME FILTER ─────────────────────── */
  $$('[data-game]').forEach(btn => {
    if (!btn.classList.contains('filter-btn')) return;
    btn.addEventListener('click', () => {
      $$('[data-game].filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const game = btn.dataset.game;
      $$('.cheat-card[data-game]').forEach(card => {
        card.style.display = (game === 'all' || card.dataset.game === game) ? '' : 'none';
      });
    });
  });

  /* ── SMOOTH SCROLL ───────────────────────────── */
  $$('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const t = document.querySelector(a.getAttribute('href'));
      if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
    });
  });

});

  /* ── LIVE SEARCH ─────────────────────────────── */
  const searchInput    = document.getElementById('headerSearchInput');
  const searchDropdown = document.getElementById('searchDropdown');
  const searchBox      = document.getElementById('headerSearchBox');
  let searchTimer;

  if (searchInput && searchDropdown) {
    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimer);
      const q = searchInput.value.trim();
      if (q.length < 2) { searchDropdown.style.display = 'none'; return; }
      searchTimer = setTimeout(async () => {
        const fd = new FormData();
        fd.append('action', 'onegta_live_search');
        fd.append('nonce',  vars.nonce);
        fd.append('query',  q);
        const r = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
        const d = await r.json();
        if (!d.success) return;
        const results = d.data.results;
        if (!results.length) {
          searchDropdown.innerHTML = '<div class="search-no-results">Ничего не найдено</div>';
        } else {
          searchDropdown.innerHTML = results.map(item => `
            <a href="${item.url}" class="search-result-item">
              <div class="search-result-item__thumb">
                ${item.thumb ? `<img src="${item.thumb}" alt="">` : item.typeIcon}
              </div>
              <div class="search-result-item__info">
                <div class="search-result-item__title">${item.title}</div>
                <div class="search-result-item__meta">${item.typeIcon} ${item.cat || ''}</div>
              </div>
            </a>
          `).join('') + `<div class="search-results-footer" onclick="document.getElementById('headerSearchForm').submit()">Все результаты для «${q}» →</div>`;
        }
        searchDropdown.style.display = 'block';
        if (searchBox) searchBox.style.borderColor = 'var(--orange)';
      }, 280);
    });

    searchInput.addEventListener('focus', () => {
      if (searchDropdown.innerHTML) searchDropdown.style.display = 'block';
    });
    document.addEventListener('click', e => {
      if (!document.getElementById('headerSearchWrap')?.contains(e.target)) {
        searchDropdown.style.display = 'none';
        if (searchBox) searchBox.style.borderColor = '';
      }
    });
    searchInput.addEventListener('keydown', e => {
      if (e.key === 'Escape') { searchDropdown.style.display = 'none'; searchInput.blur(); }
    });
  }

  /* ── MODERATION BUTTONS ──────────────────────── */
  async function moderatePost(postId, action) {
    const fd = new FormData();
    fd.append('action',          'onegta_moderate_post');
    fd.append('nonce',           vars.nonce);
    fd.append('post_id',         postId);
    fd.append('moderate_action', action);
    const r  = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
    const d  = await r.json();
    if (d.success) {
      const item = document.getElementById('moditem-' + postId);
      if (item) {
        item.style.opacity = '0';
        item.style.transform = 'translateX(20px)';
        item.style.transition = 'all .3s ease';
        setTimeout(() => {
          item.remove();
          const list = document.getElementById('moderationList');
          if (list && !list.children.length) {
            list.style.display = 'none';
            const done = document.getElementById('moderationDone');
            if (done) done.style.display = 'block';
          }
        }, 300);
      }
    }
    return d;
  }

  document.addEventListener('click', async e => {
    if (e.target.classList.contains('mod-approve-btn')) {
      e.target.disabled = true;
      e.target.textContent = '…';
      await moderatePost(e.target.dataset.id, 'approve');
    }
    if (e.target.classList.contains('mod-reject-btn')) {
      if (!confirm('Отклонить материал?')) return;
      e.target.disabled = true;
      e.target.textContent = '…';
      await moderatePost(e.target.dataset.id, 'reject');
    }
  });

  /* ── USER ROLE CHANGE ────────────────────────── */
  document.querySelectorAll('.user-role-select').forEach(sel => {
    sel.addEventListener('change', async () => {
      const userId  = sel.dataset.userId;
      const newRole = sel.value;
      const fd = new FormData();
      fd.append('action',   'onegta_change_role');
      fd.append('nonce',    vars.nonce);
      fd.append('user_id',  userId);
      fd.append('new_role', newRole);
      const r = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
      const d = await r.json();
      const al = document.getElementById('usersAlert');
      if (al) {
        al.innerHTML = `<div class="alert alert--${d.success?'success':'error'}">${d.data.message}</div>`;
        setTimeout(() => al.innerHTML = '', 3000);
      }
    });
  });

  /* ── AJAX COMMENTS ───────────────────────────── */
  const commentForm = document.getElementById('onegtaCommentForm');
  if (commentForm) {
    const commentParent = document.getElementById('comment_parent');
    const cancelReply   = document.getElementById('cancelReply');
    const replyNotice   = document.getElementById('replyNotice');

    // Reply links
    document.addEventListener('click', e => {
      const replyBtn = e.target.closest('.comment-reply-link, [data-reply-to]');
      if (!replyBtn) return;
      e.preventDefault();
      const commentId = replyBtn.closest('.comment-item')?.id?.replace('comment-','') || '0';
      const authorEl  = replyBtn.closest('.comment-item')?.querySelector('.comment-item__author');
      const author    = authorEl?.textContent || '';
      if (commentParent) commentParent.value = commentId;
      if (replyNotice) { replyNotice.textContent = `↩ Ответ для ${author}`; replyNotice.style.display = 'block'; }
      if (cancelReply) cancelReply.style.display = 'inline-block';
      document.getElementById('commentText')?.focus();
      commentForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    cancelReply?.addEventListener('click', () => {
      if (commentParent) commentParent.value = '0';
      if (replyNotice)   { replyNotice.textContent = ''; replyNotice.style.display = 'none'; }
      cancelReply.style.display = 'none';
    });

    commentForm.addEventListener('submit', async e => {
      e.preventDefault();
      const btn     = commentForm.querySelector('[type=submit]');
      const content = document.getElementById('commentText')?.value.trim();
      const postId  = commentForm.querySelector('[name=comment_post_ID]')?.value;
      const parent  = commentParent?.value || '0';
      if (!content) return;

      btn.disabled = true; btn.textContent = '…';
      const fd = new FormData();
      fd.append('action',  'onegta_comment');
      fd.append('nonce',   vars.nonce);
      fd.append('post_id', postId);
      fd.append('comment', content);
      fd.append('parent',  parent);

      const r = await fetch(vars.ajaxUrl, { method: 'POST', body: fd });
      const d = await r.json();
      if (d.success) {
        document.getElementById('commentText').value = '';
        if (commentParent) commentParent.value = '0';
        if (replyNotice)   { replyNotice.textContent = ''; replyNotice.style.display = 'none'; }
        if (cancelReply)   cancelReply.style.display = 'none';
        // Insert comment
        const items = document.querySelector('.comments-items');
        if (items) {
          const temp = document.createElement('div');
          temp.innerHTML = d.data.html;
          items.appendChild(temp.firstElementChild);
        }
        if (d.data.pending) {
          alert('Комментарий отправлен на модерацию');
        }
      } else {
        alert(d.data?.message || 'Ошибка');
      }
      btn.disabled = false; btn.textContent = 'Отправить';
    });
  }


  /* ── FORUM: CREATE TOPIC ─────────────────────── */
  const newTopicForm = document.getElementById('newTopicForm');
  if (newTopicForm) {
    newTopicForm.addEventListener('submit', async e => {
      e.preventDefault();
      const btn  = newTopicForm.querySelector('[type=submit]');
      const alert= document.getElementById('newTopicAlert');
      btn.disabled = true; btn.textContent = 'Создаём…';
      const fd = new FormData(newTopicForm);
      fd.append('action', 'onegta_forum_create_topic');
      try {
        const r = await fetch(vars.ajaxUrl, {method:'POST',body:fd});
        const d = await r.json();
        if (d.success) {
          if (alert) alert.innerHTML = `<div class="alert alert--success">${d.data.message}</div>`;
          setTimeout(() => location.href = d.data.url, 800);
        } else {
          if (alert) alert.innerHTML = `<div class="alert alert--error">${d.data.message}</div>`;
          btn.disabled = false; btn.textContent = 'Создать тему';
        }
      } catch { btn.disabled = false; btn.textContent = 'Создать тему'; }
    });
  }

  /* ── FORUM: REPLY ────────────────────────────── */
  const replyForm = document.getElementById('forumReplyForm');
  if (replyForm) {
    let quoteText = '';

    // Quote button
    document.addEventListener('click', e => {
      const qBtn = e.target.closest('.forum-quote-btn');
      if (!qBtn) return;
      const author = qBtn.dataset.author;
      const text   = qBtn.dataset.text;
      quoteText = `[quote=${author}]${text}[/quote]\n`;
      const preview = document.getElementById('quotePreview');
      const clear   = document.getElementById('clearQuote');
      if (preview) { preview.innerHTML = `<strong>${author}:</strong> ${text}…`; preview.style.display='block'; }
      if (clear)   clear.style.display = 'inline-block';
      document.getElementById('replyContent')?.focus();
      replyForm.scrollIntoView({behavior:'smooth',block:'nearest'});
    });

    document.getElementById('clearQuote')?.addEventListener('click', () => {
      quoteText = '';
      const p = document.getElementById('quotePreview');
      const c = document.getElementById('clearQuote');
      if (p) { p.innerHTML=''; p.style.display='none'; }
      if (c) c.style.display='none';
    });

    replyForm.addEventListener('submit', async e => {
      e.preventDefault();
      const btn    = replyForm.querySelector('[type=submit]');
      const alert  = document.getElementById('forumAlert');
      const content= document.getElementById('replyContent');
      btn.disabled = true; btn.textContent = 'Отправляем…';

      const fd = new FormData(replyForm);
      fd.set('content', (quoteText + (content?.value || '')).trim());
      fd.append('action', 'onegta_forum_reply');

      try {
        const r = await fetch(vars.ajaxUrl, {method:'POST',body:fd});
        const d = await r.json();
        if (d.success) {
          const replies = document.getElementById('forumReplies');
          if (replies) replies.insertAdjacentHTML('beforeend', d.data.html);
          if (content) content.value = '';
          quoteText = '';
          const p = document.getElementById('quotePreview');
          const c = document.getElementById('clearQuote');
          if (p) { p.innerHTML=''; p.style.display='none'; }
          if (c) c.style.display='none';
          // Обновляем счётчик
          document.querySelectorAll('.forum-reply-count').forEach(el => el.textContent = d.data.count);
          replies?.lastElementChild?.scrollIntoView({behavior:'smooth',block:'nearest'});
        } else {
          if (alert) alert.innerHTML = `<div class="alert alert--error">${d.data.message}</div>`;
        }
      } catch { if (alert) alert.innerHTML = '<div class="alert alert--error">Ошибка соединения</div>'; }
      btn.disabled = false; btn.textContent = 'Отправить ответ';
    });
  }

  /* ── FORUM: LIKE ─────────────────────────────── */
  document.addEventListener('click', async e => {
    const likeBtn = e.target.closest('.forum-like-btn:not(.forum-like-btn--static)');
    if (!likeBtn) return;
    const replyId = likeBtn.dataset.replyId;
    if (!replyId) return;
    likeBtn.disabled = true;
    const fd = new FormData();
    fd.append('action',   'onegta_forum_like');
    fd.append('nonce',    vars.nonce);
    fd.append('reply_id', replyId);
    try {
      const r = await fetch(vars.ajaxUrl, {method:'POST',body:fd});
      const d = await r.json();
      if (d.success) {
        likeBtn.classList.toggle('liked', d.data.liked);
        const countEl = likeBtn.querySelector('.forum-like-count');
        if (countEl) countEl.textContent = d.data.count;
      }
    } catch {}
    likeBtn.disabled = false;
  });

  /* ── FORUM: MODERATE TOPIC ───────────────────── */
  document.querySelectorAll('.forum-mod-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const action  = btn.dataset.action;
      const topicId = btn.dataset.topicId;
      if (action === 'delete' && !confirm('Удалить тему со всеми ответами?')) return;

      btn.disabled = true;
      const fd = new FormData();
      fd.append('action',     'onegta_forum_moderate_topic');
      fd.append('nonce',      vars.nonce);
      fd.append('topic_id',   topicId);
      fd.append('mod_action', action);
      try {
        const r = await fetch(vars.ajaxUrl, {method:'POST',body:fd});
        const d = await r.json();
        if (d.success) {
          if (d.data.redirect) { location.href = d.data.redirect; return; }
          const alert = document.getElementById('forumAlert');
          if (alert) alert.innerHTML = `<div class="alert alert--success">${d.data.message}</div>`;
          setTimeout(() => location.reload(), 1000);
        }
      } catch {}
      btn.disabled = false;
    });
  });

  /* ── FORUM: DELETE REPLY ─────────────────────── */
  document.addEventListener('click', async e => {
    const delBtn = e.target.closest('.forum-delete-reply-btn');
    if (!delBtn) return;
    if (!confirm('Удалить ответ?')) return;
    const replyId = delBtn.dataset.replyId;
    const fd = new FormData();
    fd.append('action',   'onegta_forum_delete_reply');
    fd.append('nonce',    vars.nonce);
    fd.append('reply_id', replyId);
    try {
      const r = await fetch(vars.ajaxUrl, {method:'POST',body:fd});
      const d = await r.json();
      if (d.success) {
        const el = document.getElementById('reply-' + replyId);
        if (el) { el.style.opacity='0'; el.style.transform='translateX(20px)'; el.style.transition='all .3s'; setTimeout(()=>el.remove(),300); }
      }
    } catch {}
  });

  /* ── FORUM: ONLINE USERS ─────────────────────── */
  async function loadForumOnline() {
    const el = document.getElementById('forumOnlineCount');
    const statsEl = document.getElementById('statsOnline');
    if (!el && !statsEl) return;
    const fd = new FormData();
    fd.append('action', 'onegta_forum_online');
    fd.append('nonce',  vars.nonce);
    try {
      const r = await fetch(vars.ajaxUrl, {method:'POST',body:fd});
      const d = await r.json();
      if (d.success) {
        if (el) el.textContent = d.data.count;
        if (statsEl) statsEl.textContent = d.data.count;
      }
    } catch {}
  }
  loadForumOnline();
  setInterval(loadForumOnline, 30000);

  /* ── FORUM: SEARCH ───────────────────────────── */
  const forumSearch  = document.getElementById('forumSearchInput');
  const forumDropdown= document.getElementById('forumSearchDropdown');
  let forumTimer;
  if (forumSearch && forumDropdown) {
    forumSearch.addEventListener('input', () => {
      clearTimeout(forumTimer);
      const q = forumSearch.value.trim();
      if (q.length < 2) { forumDropdown.style.display='none'; return; }
      forumTimer = setTimeout(async () => {
        const fd = new FormData();
        fd.append('action', 'onegta_forum_search');
        fd.append('nonce',  vars.nonce);
        fd.append('query',  q);
        try {
          const r = await fetch(vars.ajaxUrl, {method:'POST',body:fd});
          const d = await r.json();
          if (!d.success || !d.data.results.length) {
            forumDropdown.innerHTML='<div class="search-no-results">Ничего не найдено</div>';
          } else {
            forumDropdown.innerHTML = d.data.results.map(t => `
              <a href="${t.url}" class="search-result-item">
                <div class="search-result-item__thumb">💬</div>
                <div class="search-result-item__info">
                  <div class="search-result-item__title">${t.title}</div>
                  <div class="search-result-item__meta">${t.replies} ответов · ${t.date}</div>
                </div>
              </a>
            `).join('');
          }
          forumDropdown.style.display = 'block';
        } catch {}
      }, 300);
    });
    document.addEventListener('click', e => {
      if (!forumSearch.closest('.forum-search-wrap')?.contains(e.target))
        forumDropdown.style.display = 'none';
    });
  }

  /* ── FORUM: LOGIN PROMPT ─────────────────────── */
  document.getElementById('forumLoginBtn')?.addEventListener('click', () => {
    document.getElementById('openAuthBtn')?.click();
  });

