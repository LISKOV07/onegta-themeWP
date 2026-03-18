<?php
/**
 * OneGTA Mini Chat Widget
 * inc/chat.php
 */
defined('ABSPATH') || exit;

/* ── AJAX: LOAD MESSAGES ─────────────────────── */
add_action('wp_ajax_onegta_chat_load',        'onegta_chat_load');
add_action('wp_ajax_nopriv_onegta_chat_load', 'onegta_chat_load');
function onegta_chat_load() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $since    = absint($_POST['since'] ?? 0);
    $messages = onegta_get_chat_messages($since);
    wp_send_json_success(['messages' => $messages, 'time' => time()]);
}

/* ── AJAX: SEND MESSAGE ──────────────────────── */
add_action('wp_ajax_onegta_chat_send', 'onegta_chat_send');
function onegta_chat_send() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Войди чтобы писать в чат']);

    $text = sanitize_text_field(wp_strip_all_tags($_POST['message'] ?? ''));
    if (!$text || mb_strlen($text) > 300) wp_send_json_error(['message' => 'Пустое или слишком длинное сообщение']);

    // Flood control — не чаще раза в 5 секунд
    $user_id  = get_current_user_id();
    $last_msg = get_transient('onegta_chat_flood_' . $user_id);
    if ($last_msg) wp_send_json_error(['message' => 'Не так быстро! Подожди пару секунд.']);
    set_transient('onegta_chat_flood_' . $user_id, 1, 5);

    $user    = wp_get_current_user();
    $role    = $user->roles[0] ?? 'subscriber';
    $message = [
        'id'      => uniqid('msg_'),
        'user_id' => $user_id,
        'name'    => $user->display_name,
        'role'    => $role,
        'avatar'  => get_avatar_url($user_id, ['size' => 32]),
        'text'    => $text,
        'time'    => time(),
    ];

    onegta_save_chat_message($message);
    wp_send_json_success(['message' => $message]);
}

/* ── AJAX: DELETE MESSAGE (moderator) ────────── */
add_action('wp_ajax_onegta_chat_delete', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!current_user_can('onegta_moderate_content') && !current_user_can('administrator'))
        wp_send_json_error(['message' => 'Нет прав']);

    $msg_id   = sanitize_text_field($_POST['msg_id'] ?? '');
    $messages = get_option('onegta_chat_messages', []);
    $messages = array_filter($messages, fn($m) => $m['id'] !== $msg_id);
    update_option('onegta_chat_messages', array_values($messages));
    wp_send_json_success();
});

/* ── STORAGE: last 60 messages in wp_options ─── */
function onegta_get_chat_messages($since = 0) {
    $all = get_option('onegta_chat_messages', []);
    if ($since) $all = array_filter($all, fn($m) => $m['time'] > $since);
    return array_values(array_slice($all, -60));
}

function onegta_save_chat_message($msg) {
    $all   = get_option('onegta_chat_messages', []);
    $all[] = $msg;
    // Keep last 200
    if (count($all) > 200) $all = array_slice($all, -200);
    update_option('onegta_chat_messages', $all, false);
}

/* ── RENDER CHAT HTML ────────────────────────── */
add_action('wp_footer', 'onegta_chat_render', 20);
function onegta_chat_render() {
    $is_logged = is_user_logged_in();
    $user      = $is_logged ? wp_get_current_user() : null;
    $can_mod   = $is_logged && (current_user_can('onegta_moderate_content') || current_user_can('administrator'));
    ?>
    <!-- ── ONEGTA MINI CHAT ── -->
    <div id="gtaChat" class="gta-chat" aria-label="Мини-чат OneGTA" role="complementary">

        <!-- Toggle button -->
        <button class="gta-chat__toggle" id="chatToggle" aria-haspopup="true" aria-expanded="false" aria-controls="chatWindow">
            <span class="gta-chat__toggle-icon" id="chatIcon">💬</span>
            <span class="gta-chat__toggle-label">Чат</span>
            <span class="gta-chat__badge" id="chatBadge" style="display:none;">0</span>
        </button>

        <!-- Chat window -->
        <div class="gta-chat__window" id="chatWindow" role="dialog" aria-modal="false" aria-label="Чат">
            <!-- Header -->
            <div class="gta-chat__header">
                <div class="gta-chat__header-left">
                    <div class="gta-chat__status" id="chatStatus"></div>
                    <span class="gta-chat__title">OneGTA Чат</span>
                </div>
                <div class="gta-chat__header-right">
                    <span class="gta-chat__online" id="chatOnlineCount">
                        <span class="gta-chat__online-dot"></span>
                        <span id="onlineNum">1</span> онлайн
                    </span>
                    <button class="gta-chat__close" id="chatClose" aria-label="Закрыть">✕</button>
                </div>
            </div>

            <!-- Messages -->
            <div class="gta-chat__messages" id="chatMessages">
                <div class="gta-chat__loading" id="chatLoading">
                    <div class="gta-chat__dots"><span></span><span></span><span></span></div>
                </div>
            </div>

            <!-- Input -->
            <div class="gta-chat__footer">
                <?php if ($is_logged) : ?>
                    <div class="gta-chat__input-wrap">
                        <div class="gta-chat__user-avatar">
                            <img src="<?php echo esc_url(get_avatar_url($user->ID, ['size'=>24])); ?>" alt="">
                        </div>
                        <input
                            type="text"
                            class="gta-chat__input"
                            id="chatInput"
                            placeholder="Написать сообщение…"
                            maxlength="300"
                            autocomplete="off"
                            aria-label="Сообщение"
                        >
                        <button class="gta-chat__send" id="chatSend" aria-label="Отправить">➤</button>
                    </div>
                <?php else : ?>
                    <div class="gta-chat__login-prompt">
                        <a href="#" onclick="document.getElementById('openAuthBtn')?.click();return false;">Войди</a> чтобы писать в чат
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
    /* ── CHAT WIDGET ──────────────────────────── */
    .gta-chat {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 800;
        font-family: 'DM Sans', sans-serif;
    }

    /* Toggle button */
    .gta-chat__toggle {
        display: flex;
        align-items: center;
        gap: .5rem;
        background: var(--orange);
        color: #fff;
        border: none;
        padding: 12px 20px;
        cursor: pointer;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.1rem;
        letter-spacing: 3px;
        box-shadow: 0 4px 20px rgba(245,92,0,.4);
        transition: all .25s;
        position: relative;
    }
    .gta-chat__toggle:hover {
        background: var(--orange-h);
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(245,92,0,.5);
    }
    .gta-chat__toggle-icon { font-size: 1.1rem; line-height: 1; }
    .gta-chat__badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: #dc2626;
        color: #fff;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: .65rem;
        font-family: 'DM Sans', sans-serif;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
    }

    /* Window */
    .gta-chat__window {
        position: absolute;
        bottom: calc(100% + 12px);
        right: 0;
        width: 340px;
        background: #fff;
        border: 1px solid var(--border);
        box-shadow: 0 16px 48px rgba(0,0,0,.15);
        display: flex;
        flex-direction: column;
        opacity: 0;
        pointer-events: none;
        transform: translateY(12px) scale(.97);
        transition: all .25s cubic-bezier(.4,0,.2,1);
        transform-origin: bottom right;
        max-height: 480px;
    }
    .gta-chat.open .gta-chat__window {
        opacity: 1;
        pointer-events: all;
        transform: translateY(0) scale(1);
    }

    /* Header */
    .gta-chat__header {
        background: var(--orange);
        padding: .8rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .gta-chat__header-left { display: flex; align-items: center; gap: .5rem; }
    .gta-chat__header-right { display: flex; align-items: center; gap: .8rem; }
    .gta-chat__status {
        width: 8px; height: 8px;
        background: #4ade80;
        border-radius: 50%;
        box-shadow: 0 0 0 2px rgba(74,222,128,.3);
        animation: chatPulse 2s ease-in-out infinite;
    }
    @keyframes chatPulse {
        0%,100% { box-shadow: 0 0 0 2px rgba(74,222,128,.3); }
        50%      { box-shadow: 0 0 0 5px rgba(74,222,128,.1); }
    }
    .gta-chat__title {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.1rem;
        letter-spacing: 3px;
        color: #fff;
    }
    .gta-chat__online {
        display: flex;
        align-items: center;
        gap: .3rem;
        font-size: .72rem;
        color: rgba(255,255,255,.8);
        font-weight: 600;
    }
    .gta-chat__online-dot {
        width: 6px; height: 6px;
        background: #4ade80;
        border-radius: 50%;
    }
    .gta-chat__close {
        background: rgba(255,255,255,.2);
        border: none;
        color: #fff;
        width: 24px; height: 24px;
        border-radius: 50%;
        cursor: pointer;
        font-size: .8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .2s;
    }
    .gta-chat__close:hover { background: rgba(255,255,255,.35); }

    /* Messages area */
    .gta-chat__messages {
        flex: 1;
        overflow-y: auto;
        padding: .8rem;
        display: flex;
        flex-direction: column;
        gap: .5rem;
        min-height: 280px;
        max-height: 320px;
        background: var(--bg);
        scrollbar-width: thin;
        scrollbar-color: var(--orange-mid) transparent;
    }
    .gta-chat__messages::-webkit-scrollbar { width: 3px; }
    .gta-chat__messages::-webkit-scrollbar-thumb { background: var(--orange-mid); }

    /* Loading dots */
    .gta-chat__loading { display: flex; justify-content: center; padding: 1rem; }
    .gta-chat__dots { display: flex; gap: 4px; }
    .gta-chat__dots span {
        width: 7px; height: 7px;
        background: var(--orange-mid);
        border-radius: 50%;
        animation: chatDot .8s ease-in-out infinite;
    }
    .gta-chat__dots span:nth-child(2) { animation-delay: .15s; }
    .gta-chat__dots span:nth-child(3) { animation-delay: .3s; }
    @keyframes chatDot { 0%,80%,100%{transform:scale(.7);opacity:.4} 40%{transform:scale(1);opacity:1} }

    /* Empty state */
    .gta-chat__empty {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--text3);
        font-size: .85rem;
    }
    .gta-chat__empty-icon { font-size: 2rem; margin-bottom: .5rem; }

    /* Message bubble */
    .chat-msg {
        display: flex;
        gap: .5rem;
        align-items: flex-start;
        animation: msgIn .2s ease;
    }
    @keyframes msgIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
    .chat-msg--own { flex-direction: row-reverse; }
    .chat-msg__avatar {
        width: 28px; height: 28px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        background: var(--orange);
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; font-weight: 700; color: #fff;
    }
    .chat-msg__avatar img { width: 100%; height: 100%; object-fit: cover; }
    .chat-msg__content { max-width: 75%; }
    .chat-msg__name {
        font-size: .65rem;
        font-weight: 700;
        color: var(--text3);
        letter-spacing: .03em;
        margin-bottom: 2px;
        display: flex;
        align-items: center;
        gap: .3rem;
    }
    .chat-msg--own .chat-msg__name { flex-direction: row-reverse; }
    .chat-msg__role-admin  { color: #dc2626; font-size: .55rem; letter-spacing: 1px; text-transform: uppercase; font-weight: 700; }
    .chat-msg__role-mod    { color: #d97706; font-size: .55rem; letter-spacing: 1px; text-transform: uppercase; font-weight: 700; }
    .chat-msg__bubble {
        background: #fff;
        border: 1px solid var(--border);
        padding: .5rem .75rem;
        font-size: .86rem;
        color: var(--text);
        line-height: 1.5;
        word-break: break-word;
        position: relative;
    }
    .chat-msg--own .chat-msg__bubble {
        background: var(--orange-pale);
        border-color: var(--orange-mid);
    }
    .chat-msg__time {
        font-size: .6rem;
        color: var(--text3);
        margin-top: 2px;
        text-align: right;
    }
    .chat-msg--own .chat-msg__time { text-align: left; }
    .chat-msg__delete {
        position: absolute;
        top: 2px; right: 4px;
        background: none;
        border: none;
        color: transparent;
        font-size: .7rem;
        cursor: pointer;
        padding: 0 2px;
        line-height: 1;
        transition: color .2s;
    }
    .chat-msg__bubble:hover .chat-msg__delete { color: var(--error); }

    /* System message */
    .chat-system {
        text-align: center;
        font-size: .72rem;
        color: var(--text3);
        padding: .2rem 0;
    }

    /* Footer / input */
    .gta-chat__footer {
        padding: .7rem;
        border-top: 1px solid var(--border);
        background: #fff;
        flex-shrink: 0;
    }
    .gta-chat__input-wrap {
        display: flex;
        align-items: center;
        gap: .4rem;
        background: var(--bg);
        border: 1.5px solid var(--border);
        padding: 4px 6px;
        transition: border-color .2s;
    }
    .gta-chat__input-wrap:focus-within { border-color: var(--orange); }
    .gta-chat__user-avatar {
        width: 24px; height: 24px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
    }
    .gta-chat__user-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .gta-chat__input {
        flex: 1;
        background: none;
        border: none;
        font-size: .85rem;
        color: var(--text);
        padding: 4px 4px;
        font-family: 'DM Sans', sans-serif;
        outline: none;
        min-width: 0;
    }
    .gta-chat__input::placeholder { color: var(--text3); }
    .gta-chat__send {
        background: var(--orange);
        color: #fff;
        border: none;
        width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        font-size: .85rem;
        flex-shrink: 0;
        transition: background .2s;
    }
    .gta-chat__send:hover { background: var(--orange-h); }
    .gta-chat__send:disabled { background: var(--border-d); cursor: not-allowed; }
    .gta-chat__login-prompt {
        text-align: center;
        font-size: .82rem;
        color: var(--text3);
        padding: .3rem 0;
    }
    .gta-chat__login-prompt a { color: var(--orange); font-weight: 700; }

    /* Mobile */
    @media (max-width: 480px) {
        .gta-chat { bottom: 1rem; right: 1rem; }
        .gta-chat__window { width: calc(100vw - 2rem); right: 0; }
    }
    </style>

    <script>
    (function() {
        const chat     = document.getElementById('gtaChat');
        const toggle   = document.getElementById('chatToggle');
        const window_  = document.getElementById('chatWindow');
        const closeBtn = document.getElementById('chatClose');
        const messages = document.getElementById('chatMessages');
        const input    = document.getElementById('chatInput');
        const sendBtn  = document.getElementById('chatSend');
        const badge    = document.getElementById('chatBadge');
        const loading  = document.getElementById('chatLoading');

        const ajaxUrl  = (typeof onegtaVars !== 'undefined' && onegtaVars.ajaxUrl) ? onegtaVars.ajaxUrl : '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const nonce    = (typeof onegtaVars !== 'undefined' && onegtaVars.nonce)   ? onegtaVars.nonce   : '<?php echo esc_js(wp_create_nonce('onegta_nonce')); ?>';
        const currentUserId = <?php echo is_user_logged_in() ? get_current_user_id() : 0; ?>;
        const canMod   = <?php echo $can_mod ? 'true' : 'false'; ?>;

        let isOpen      = false;
        let lastTime    = 0;
        let pollTimer   = null;
        let unread      = 0;
        let initialized = false;

        /* ── OPEN / CLOSE ── */
        function openChat() {
            isOpen = true;
            chat.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
            unread = 0;
            badge.style.display = 'none';
            if (!initialized) { loadMessages(true); initialized = true; }
            startPolling();
            setTimeout(() => input?.focus(), 300);
        }

        function closeChat() {
            isOpen = false;
            chat.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            stopPolling();
        }

        toggle.addEventListener('click', () => isOpen ? closeChat() : openChat());
        closeBtn?.addEventListener('click', closeChat);
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && isOpen) closeChat(); });

        /* ── RENDER MESSAGE ── */
        function formatTime(ts) {
            const d = new Date(ts * 1000);
            return d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
        }

        function roleBadge(role) {
            if (role === 'administrator') return '<span class="chat-msg__role-admin">Админ</span>';
            if (role === 'onegta_moderator') return '<span class="chat-msg__role-mod">Мод</span>';
            return '';
        }

        function renderMessage(msg) {
            const isOwn = msg.user_id == currentUserId;
            const div   = document.createElement('div');
            div.className = 'chat-msg' + (isOwn ? ' chat-msg--own' : '');
            div.dataset.msgId = msg.id;
            div.innerHTML = `
                <div class="chat-msg__avatar">
                    ${msg.avatar ? `<img src="${msg.avatar}" alt="">` : msg.name[0].toUpperCase()}
                </div>
                <div class="chat-msg__content">
                    <div class="chat-msg__name">
                        ${escHtml(msg.name)}
                        ${roleBadge(msg.role)}
                    </div>
                    <div class="chat-msg__bubble">
                        ${escHtml(msg.text)}
                        ${(canMod || isOwn) ? `<button class="chat-msg__delete" data-id="${msg.id}" title="Удалить">✕</button>` : ''}
                    </div>
                    <div class="chat-msg__time">${formatTime(msg.time)}</div>
                </div>
            `;
            return div;
        }

        function renderSystem(text) {
            const div = document.createElement('div');
            div.className = 'chat-system';
            div.textContent = text;
            return div;
        }

        function escHtml(str) {
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        /* ── SCROLL ── */
        function scrollBottom(force = false) {
            const el = messages;
            const atBottom = el.scrollHeight - el.clientHeight - el.scrollTop < 80;
            if (force || atBottom) el.scrollTop = el.scrollHeight;
        }

        /* ── LOAD MESSAGES ── */
        async function loadMessages(initial = false) {
            try {
                const fd = new FormData();
                fd.append('action', 'onegta_chat_load');
                fd.append('nonce',  nonce);
                fd.append('since',  initial ? 0 : lastTime);

                const r = await fetch(ajaxUrl, { method: 'POST', body: fd });
                const d = await r.json();
                if (!d.success) return;

                const msgs = d.data.messages;
                loading?.remove();

                if (initial) {
                    messages.innerHTML = '';
                    if (!msgs.length) {
                        messages.appendChild(renderSystem('Пока нет сообщений. Будь первым! 👋'));
                    } else {
                        msgs.forEach(m => messages.appendChild(renderMessage(m)));
                        if (msgs.length) lastTime = msgs[msgs.length - 1].time;
                    }
                    scrollBottom(true);
                } else if (msgs.length) {
                    msgs.forEach(m => {
                        // Avoid duplicates
                        if (!messages.querySelector(`[data-msg-id="${m.id}"]`)) {
                            messages.appendChild(renderMessage(m));
                            if (!isOpen) { unread++; badge.textContent = unread; badge.style.display = 'flex'; }
                        }
                    });
                    lastTime = msgs[msgs.length - 1].time;
                    scrollBottom();
                }

                // Считаем онлайн по уникальным авторам за последние 5 минут
                const allMsgs = initial ? msgs : msgs;
                const recent = allMsgs.filter(m => (Date.now()/1000 - m.time) < 300);
                const uniqueUsers = new Set(recent.map(m => m.user_id)).size;
                const onlineEl = document.getElementById('onlineNum');
                if (onlineEl) onlineEl.textContent = Math.max(1, uniqueUsers);

            } catch(e) { /* silent */ }
        }

        /* ── POLLING ── */
        function startPolling() {
            stopPolling();
            pollTimer = setInterval(() => loadMessages(false), 4000);
        }
        function stopPolling() {
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
        }

        /* ── SEND MESSAGE ── */
        async function sendMessage() {
            const text = input?.value.trim();
            if (!text || !sendBtn) return;
            input.value = '';
            sendBtn.disabled = true;

            const fd = new FormData();
            fd.append('action',  'onegta_chat_send');
            fd.append('nonce',   nonce);
            fd.append('message', text);

            try {
                const r = await fetch(ajaxUrl, { method: 'POST', body: fd });
                const d = await r.json();
                if (d.success) {
                    // Remove empty state
                    messages.querySelector('.chat-system')?.remove();
                    const el = renderMessage(d.data.message);
                    messages.appendChild(el);
                    scrollBottom(true);
                    lastTime = d.data.message.time;
                } else {
                    const errText = d.data?.message || 'Ошибка. Попробуй ещё раз.';
                    const err = renderSystem('⚠️ ' + errText);
                    messages.appendChild(err);
                    scrollBottom(true);
                    setTimeout(() => err.remove(), 4000);
                    // Восстанавливаем текст в инпут чтобы не потерять
                    if (input) input.value = text;
                }
            } catch(e) {
                const err = renderSystem('⚠️ Ошибка соединения с сервером');
                messages.appendChild(err);
                scrollBottom(true);
                setTimeout(() => err.remove(), 4000);
                if (input) input.value = text;
            }

            setTimeout(() => { if(sendBtn) sendBtn.disabled = false; }, 1000);
            input?.focus();
        }

        sendBtn?.addEventListener('click', sendMessage);
        input?.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });

        /* ── DELETE MESSAGE ── */
        messages.addEventListener('click', async e => {
            const btn = e.target.closest('.chat-msg__delete');
            if (!btn) return;
            const msgId  = btn.dataset.id;
            const msgEl  = btn.closest('.chat-msg');

            msgEl.style.opacity = '0.4';
            const fd = new FormData();
            fd.append('action', 'onegta_chat_delete');
            fd.append('nonce',  nonce);
            fd.append('msg_id', msgId);
            const r = await fetch(ajaxUrl, { method: 'POST', body: fd });
            const d = await r.json();
            if (d.success) {
                msgEl.style.transition = 'all .2s';
                msgEl.style.transform  = 'translateX(20px)';
                msgEl.style.opacity    = '0';
                setTimeout(() => msgEl.remove(), 200);
            } else {
                msgEl.style.opacity = '1';
            }
        });

        /* ── VISIBILITY API — pause polling when tab hidden ── */
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stopPolling();
            else if (isOpen) startPolling();
        });

    })();
    </script>
    <?php
}
