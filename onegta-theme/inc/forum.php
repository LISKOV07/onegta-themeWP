<?php
/**
 * OneGTA — Форум
 * inc/forum.php
 */
defined('ABSPATH') || exit;

/* ══════════════════════════════════════════════
   CPT: ТЕМЫ ФОРУМА
══════════════════════════════════════════════ */
add_action('init', function() {

    // Темы
    register_post_type('forum_topic', [
        'labels' => [
            'name'          => 'Темы форума',
            'singular_name' => 'Тема форума',
            'add_new_item'  => 'Создать тему',
            'menu_name'     => 'Форум — Темы',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'forum/topic'],
        'menu_icon'     => 'dashicons-format-chat',
        'menu_position' => 9,
        'supports'      => ['title','editor','author','revisions'],
        'show_in_rest'  => true,
    ]);

    // Ответы
    register_post_type('forum_reply', [
        'labels' => [
            'name'          => 'Ответы форума',
            'singular_name' => 'Ответ',
            'menu_name'     => 'Форум — Ответы',
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'rewrite'       => false,
        'menu_icon'     => 'dashicons-admin-comments',
        'menu_position' => 10,
        'supports'      => ['editor','author'],
        'show_in_rest'  => false,
    ]);

    // Таксономия разделов
    register_taxonomy('forum_section', 'forum_topic', [
        'labels' => [
            'name'          => 'Разделы форума',
            'singular_name' => 'Раздел',
            'add_new_item'  => 'Добавить раздел',
        ],
        'hierarchical'  => true,
        'rewrite'       => ['slug' => 'forum'],
        'show_in_rest'  => true,
        'public'        => true,
    ]);

    // Теги форума
    register_taxonomy('forum_tag', 'forum_topic', [
        'labels'        => ['name'=>'Теги форума','singular_name'=>'Тег'],
        'hierarchical'  => false,
        'rewrite'       => ['slug' => 'forum-tag'],
        'show_in_rest'  => true,
        'public'        => true,
    ]);
});

/* ══════════════════════════════════════════════
   ДЕФОЛТНЫЕ РАЗДЕЛЫ
══════════════════════════════════════════════ */
add_action('after_switch_theme', 'onegta_forum_default_sections');

// Запускаем с приоритетом 100 — после регистрации таксономии (приоритет 10)
add_action('init', function() {
    if (!taxonomy_exists('forum_section')) return;
    $existing = get_terms(['taxonomy'=>'forum_section','hide_empty'=>false,'number'=>1]);
    if (empty($existing) || is_wp_error($existing)) {
        onegta_forum_default_sections();
    }
}, 100);
function onegta_forum_default_sections() {
    if (!taxonomy_exists('forum_section')) return;
    $sections = [
        ['name' => '💬 Общение',        'slug' => 'general',      'desc' => 'Общение на любые темы'],
        ['name' => '🎮 GTA VI',          'slug' => 'gta-vi',       'desc' => 'Обсуждение GTA VI'],
        ['name' => '🎮 GTA V / Online',  'slug' => 'gta-v',        'desc' => 'GTA V и GTA Online'],
        ['name' => '🎮 GTA IV',          'slug' => 'gta-iv',       'desc' => 'GTA IV и Episodes'],
        ['name' => '🎮 San Andreas',     'slug' => 'san-andreas',  'desc' => 'GTA San Andreas'],
        ['name' => '🎮 Vice City',       'slug' => 'vice-city',    'desc' => 'GTA Vice City'],
        ['name' => '🎮 GTA III и ранее', 'slug' => 'gta-classic',  'desc' => 'GTA I, II, III'],
        ['name' => '🔧 Моды',            'slug' => 'mods',         'desc' => 'Моды, патчи, инструменты'],
        ['name' => '❓ Помощь',          'slug' => 'help',         'desc' => 'Вопросы и ответы'],
        ['name' => '📢 Объявления',      'slug' => 'announcements','desc' => 'Новости сайта'],
    ];
    foreach ($sections as $s) {
        if (!term_exists($s['slug'], 'forum_section')) {
            wp_insert_term($s['name'], 'forum_section', [
                'slug'        => $s['slug'],
                'description' => $s['desc'],
            ]);
        }
    }
    // Помечаем что разделы созданы
    update_option('onegta_forum_sections_created', 1);
}

/* ══════════════════════════════════════════════
   ХЕЛПЕРЫ
══════════════════════════════════════════════ */

// Кол-во ответов в теме
function onegta_topic_reply_count($topic_id) {
    return (int) get_post_meta($topic_id, '_reply_count', true);
}

// Кол-во просмотров
function onegta_topic_views($topic_id) {
    return (int) get_post_meta($topic_id, '_view_count', true);
}

// Увеличить просмотры
function onegta_topic_increment_views($topic_id) {
    $count = onegta_topic_views($topic_id);
    update_post_meta($topic_id, '_view_count', $count + 1);
}

// Статус темы
function onegta_topic_status($topic_id) {
    return get_post_meta($topic_id, '_topic_status', true) ?: 'open';
}

// Прикреплена ли тема
function onegta_topic_is_pinned($topic_id) {
    return (bool) get_post_meta($topic_id, '_topic_pinned', true);
}

// Последний ответ
function onegta_topic_last_reply($topic_id) {
    $replies = get_posts([
        'post_type'      => 'forum_reply',
        'post_parent'    => $topic_id,
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ]);
    return $replies ? $replies[0] : null;
}

// Лайков у ответа
function onegta_reply_likes($reply_id) {
    $likes = get_post_meta($reply_id, '_likes', true);
    return is_array($likes) ? $likes : [];
}

// Лайкнул ли текущий юзер
function onegta_reply_user_liked($reply_id, $user_id = 0) {
    if (!$user_id) $user_id = get_current_user_id();
    if (!$user_id) return false;
    return in_array($user_id, onegta_reply_likes($reply_id));
}

// Репутация юзера
function onegta_user_reputation($user_id) {
    return (int) get_user_meta($user_id, 'forum_reputation', true);
}

// Прибавить репутацию
function onegta_add_reputation($user_id, $amount = 1) {
    $rep = onegta_user_reputation($user_id);
    update_user_meta($user_id, 'forum_reputation', $rep + $amount);
}

// Ранг пользователя по репутации
function onegta_user_rank($user_id) {
    $rep = onegta_user_reputation($user_id);
    if ($rep >= 500) return ['Легенда',       '#F55C00'];
    if ($rep >= 200) return ['Ветеран',        '#d97706'];
    if ($rep >= 100) return ['Завсегдатай',    '#2563eb'];
    if ($rep >= 50)  return ['Участник',       '#16a34a'];
    if ($rep >= 10)  return ['Новичок+',       '#6b7280'];
    return                   ['Новичок',        '#9ca3af'];
}

// Онлайн метка
function onegta_set_user_online($user_id) {
    update_user_meta($user_id, 'last_activity', time());
}
function onegta_is_user_online($user_id) {
    $last = (int) get_user_meta($user_id, 'last_activity', true);
    return (time() - $last) < 300; // 5 минут
}

// Обновляем онлайн при каждом запросе залогиненного
add_action('init', function() {
    if (is_user_logged_in()) {
        onegta_set_user_online(get_current_user_id());
    }
});

/* ══════════════════════════════════════════════
   REWRITE RULES
══════════════════════════════════════════════ */
add_action('init', function() {
    add_rewrite_rule(
        '^forum/topic/([^/]+)/page/([0-9]+)/?$',
        'index.php?forum_topic=$matches[1]&paged=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        '^forum/?$',
        'index.php?pagename=forum',
        'top'
    );
});

/* ══════════════════════════════════════════════
   AJAX: СОЗДАТЬ ТЕМУ
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_forum_create_topic', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Необходима авторизация']);

    $title   = sanitize_text_field($_POST['title'] ?? '');
    $content = wp_kses_post($_POST['content'] ?? '');
    $section = absint($_POST['section'] ?? 0);
    $tags    = sanitize_text_field($_POST['tags'] ?? '');

    if (!$title)   wp_send_json_error(['message' => 'Введите заголовок темы']);
    if (!$content) wp_send_json_error(['message' => 'Введите текст темы']);
    if (!$section) wp_send_json_error(['message' => 'Выберите раздел']);

    // Флуд-контроль — 1 тема в 60 секунд
    $uid = get_current_user_id();
    if (get_transient('forum_flood_topic_' . $uid))
        wp_send_json_error(['message' => 'Подождите минуту перед созданием новой темы']);
    set_transient('forum_flood_topic_' . $uid, 1, 60);

    $topic_id = wp_insert_post([
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'forum_topic',
        'post_author'  => $uid,
    ]);

    if (is_wp_error($topic_id)) wp_send_json_error(['message' => $topic_id->get_error_message()]);

    // Раздел
    wp_set_post_terms($topic_id, [$section], 'forum_section');

    // Теги
    if ($tags) {
        $tag_arr = array_map('trim', explode(',', $tags));
        wp_set_post_terms($topic_id, $tag_arr, 'forum_tag');
    }

    // Инициализируем мета
    update_post_meta($topic_id, '_reply_count', 0);
    update_post_meta($topic_id, '_view_count',  0);
    update_post_meta($topic_id, '_topic_status','open');

    // Репутация за создание темы
    onegta_add_reputation($uid, 2);

    wp_send_json_success([
        'message' => 'Тема создана!',
        'url'     => get_permalink($topic_id),
    ]);
});

/* ══════════════════════════════════════════════
   AJAX: ОТВЕТИТЬ В ТЕМУ
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_forum_reply', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Необходима авторизация']);

    $topic_id = absint($_POST['topic_id'] ?? 0);
    $content  = wp_kses_post($_POST['content'] ?? '');

    if (!$topic_id) wp_send_json_error(['message' => 'Тема не найдена']);
    if (!$content)  wp_send_json_error(['message' => 'Введите текст ответа']);

    $topic = get_post($topic_id);
    if (!$topic || $topic->post_type !== 'forum_topic')
        wp_send_json_error(['message' => 'Тема не найдена']);

    if (onegta_topic_status($topic_id) === 'closed')
        wp_send_json_error(['message' => 'Тема закрыта']);

    // Флуд-контроль — 1 ответ в 30 секунд
    $uid = get_current_user_id();
    if (get_transient('forum_flood_reply_' . $uid))
        wp_send_json_error(['message' => 'Не так быстро! Подождите 30 секунд']);
    set_transient('forum_flood_reply_' . $uid, 1, 30);

    $reply_id = wp_insert_post([
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'forum_reply',
        'post_parent'  => $topic_id,
        'post_author'  => $uid,
        'post_title'   => 'Ответ в теме #' . $topic_id,
    ]);

    if (is_wp_error($reply_id)) wp_send_json_error(['message' => $reply_id->get_error_message()]);

    // Обновляем счётчик ответов и дату темы
    $count = onegta_topic_reply_count($topic_id);
    update_post_meta($topic_id, '_reply_count', $count + 1);
    update_post_meta($topic_id, '_last_reply_id', $reply_id);
    update_post_meta($topic_id, '_last_reply_time', time());
    update_post_meta($topic_id, '_last_reply_author', $uid);

    // Обновляем дату темы чтобы она поднялась в списке
    wp_update_post(['ID' => $topic_id, 'post_modified' => current_time('mysql'), 'post_modified_gmt' => current_time('mysql', 1)]);

    // Репутация за ответ
    onegta_add_reputation($uid, 1);

    // Уведомление автору темы
    $topic_author = get_userdata($topic->post_author);
    $replier      = wp_get_current_user();
    if ($topic_author && $topic_author->ID !== $uid) {
        wp_mail(
            $topic_author->user_email,
            '[OneGTA Форум] Новый ответ в теме: ' . $topic->post_title,
            "Привет, {$topic_author->display_name}!\n\n{$replier->display_name} ответил в вашей теме «{$topic->post_title}».\n\nПрочитать: " . get_permalink($topic_id) . "\n\n— OneGTA Форум"
        );
    }

    // Рендерим HTML ответа
    $user    = wp_get_current_user();
    $av_url  = onegta_avatar_url($uid);
    [$rank_label, $rank_color] = onegta_user_rank($uid);
    $rep     = onegta_user_reputation($uid);
    $role    = $user->roles[0] ?? '';

    ob_start();
    onegta_render_reply(get_post($reply_id));
    $html = ob_get_clean();

    wp_send_json_success(['html' => $html, 'count' => $count + 1]);
});

/* ══════════════════════════════════════════════
   AJAX: ЛАЙК ОТВЕТА
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_forum_like', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Войдите чтобы ставить лайки']);

    $reply_id = absint($_POST['reply_id'] ?? 0);
    $uid      = get_current_user_id();
    if (!$reply_id) wp_send_json_error();

    $likes = onegta_reply_likes($reply_id);

    if (in_array($uid, $likes)) {
        // Убираем лайк
        $likes = array_values(array_diff($likes, [$uid]));
        $liked = false;
        // Убираем репутацию у автора
        $author_id = get_post_field('post_author', $reply_id);
        onegta_add_reputation($author_id, -1);
    } else {
        // Ставим лайк
        $likes[] = $uid;
        $liked   = true;
        // Добавляем репутацию автору
        $author_id = get_post_field('post_author', $reply_id);
        onegta_add_reputation($author_id, 1);
    }

    update_post_meta($reply_id, '_likes', $likes);
    wp_send_json_success(['liked' => $liked, 'count' => count($likes)]);
});

/* ══════════════════════════════════════════════
   AJAX: МОДЕРАЦИЯ ТЕМЫ (pin/close/delete)
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_forum_moderate_topic', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!current_user_can('onegta_moderate_content') && !current_user_can('administrator'))
        wp_send_json_error(['message' => 'Нет прав']);

    $topic_id = absint($_POST['topic_id'] ?? 0);
    $action   = sanitize_key($_POST['mod_action'] ?? '');
    if (!$topic_id) wp_send_json_error();

    switch ($action) {
        case 'pin':
            $pinned = onegta_topic_is_pinned($topic_id);
            update_post_meta($topic_id, '_topic_pinned', !$pinned);
            wp_send_json_success(['pinned' => !$pinned, 'message' => !$pinned ? 'Тема прикреплена' : 'Тема откреплена']);
            break;

        case 'close':
            $status = onegta_topic_status($topic_id);
            $new    = $status === 'open' ? 'closed' : 'open';
            update_post_meta($topic_id, '_topic_status', $new);
            wp_send_json_success(['status' => $new, 'message' => $new === 'closed' ? 'Тема закрыта' : 'Тема открыта']);
            break;

        case 'delete':
            // Удаляем все ответы
            $replies = get_posts(['post_type'=>'forum_reply','post_parent'=>$topic_id,'posts_per_page'=>-1,'fields'=>'ids']);
            foreach ($replies as $rid) wp_delete_post($rid, true);
            wp_delete_post($topic_id, true);
            wp_send_json_success(['message' => 'Тема удалена', 'redirect' => home_url('/forum/')]);
            break;

        default:
            wp_send_json_error(['message' => 'Неизвестное действие']);
    }
});

/* ══════════════════════════════════════════════
   AJAX: УДАЛИТЬ ОТВЕТ (модератор или автор)
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_forum_delete_reply', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error();

    $reply_id = absint($_POST['reply_id'] ?? 0);
    $reply    = get_post($reply_id);
    if (!$reply) wp_send_json_error(['message' => 'Ответ не найден']);

    $can_delete = current_user_can('onegta_moderate_content')
               || current_user_can('administrator')
               || (int)$reply->post_author === get_current_user_id();

    if (!$can_delete) wp_send_json_error(['message' => 'Нет прав']);

    $topic_id = $reply->post_parent;
    wp_delete_post($reply_id, true);

    // Обновляем счётчик
    $count = max(0, onegta_topic_reply_count($topic_id) - 1);
    update_post_meta($topic_id, '_reply_count', $count);

    wp_send_json_success(['message' => 'Ответ удалён']);
});

/* ══════════════════════════════════════════════
   AJAX: ОНЛАЙН ПОЛЬЗОВАТЕЛИ НА ФОРУМЕ
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_forum_online',        'onegta_forum_online_cb');
add_action('wp_ajax_nopriv_onegta_forum_online', 'onegta_forum_online_cb');
function onegta_forum_online_cb() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $users = get_users(['meta_key' => 'last_activity', 'meta_value' => time() - 300, 'meta_compare' => '>=', 'number' => 20]);
    $list  = array_map(fn($u) => [
        'name'   => $u->display_name,
        'url'    => home_url('/profile/' . $u->user_login . '/'),
        'avatar' => get_avatar_url($u->ID, ['size' => 24]),
        'role'   => $u->roles[0] ?? '',
    ], $users);
    wp_send_json_success(['users' => $list, 'count' => count($list)]);
}

/* ══════════════════════════════════════════════
   AJAX: ПОИСК ПО ФОРУМУ
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_forum_search',        'onegta_forum_search_cb');
add_action('wp_ajax_nopriv_onegta_forum_search', 'onegta_forum_search_cb');
function onegta_forum_search_cb() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $q = sanitize_text_field($_POST['query'] ?? '');
    if (strlen($q) < 2) wp_send_json_success(['results' => []]);

    $topics = get_posts([
        's'              => $q,
        'post_type'      => 'forum_topic',
        'posts_per_page' => 10,
        'post_status'    => 'publish',
    ]);

    $results = array_map(fn($t) => [
        'id'      => $t->ID,
        'title'   => $t->post_title,
        'url'     => get_permalink($t->ID),
        'replies' => onegta_topic_reply_count($t->ID),
        'date'    => get_the_date('d M Y', $t->ID),
    ], $topics);

    wp_send_json_success(['results' => $results]);
}

/* ══════════════════════════════════════════════
   RENDER HELPERS
══════════════════════════════════════════════ */
function onegta_render_reply($reply, $topic_author_id = 0) {
    if (!$reply) return;
    $uid    = (int)$reply->post_author;
    $user   = get_userdata($uid);
    if (!$user) return;

    $av_url  = onegta_avatar_url($uid);
    [$rank_label, $rank_color] = onegta_user_rank($uid);
    $rep     = onegta_user_reputation($uid);
    $likes   = onegta_reply_likes($reply->ID);
    $liked   = onegta_reply_user_liked($reply->ID);
    $role    = $user->roles[0] ?? '';
    $is_op   = ($uid === $topic_author_id);
    $can_mod = current_user_can('onegta_moderate_content') || current_user_can('administrator');
    $is_own  = is_user_logged_in() && get_current_user_id() === $uid;
    $profile = home_url('/profile/' . $user->user_login . '/');
    ?>
    <div class="forum-reply" id="reply-<?php echo esc_attr($reply->ID); ?>" data-reply-id="<?php echo esc_attr($reply->ID); ?>">

        <!-- Sidebar автора -->
        <div class="forum-reply__author">
            <a href="<?php echo esc_url($profile); ?>" class="forum-reply__avatar">
                <img src="<?php echo esc_url($av_url); ?>" alt="<?php echo esc_attr($user->display_name); ?>">
            </a>
            <a href="<?php echo esc_url($profile); ?>" class="forum-reply__name"><?php echo esc_html($user->display_name); ?></a>
            <span class="forum-reply__rank" style="background:<?php echo esc_attr($rank_color); ?>;"><?php echo esc_html($rank_label); ?></span>
            <?php echo onegta_role_badge($role); ?>
            <?php if ($is_op) : ?><span class="forum-reply__op">ТС</span><?php endif; ?>
            <div class="forum-reply__rep" title="Репутация">⭐ <?php echo esc_html($rep); ?></div>
            <div class="forum-reply__posts-count">
                <?php
                $post_count = count_user_posts($uid, 'forum_reply') + count_user_posts($uid, 'forum_topic');
                echo esc_html($post_count) . ' сообщ.';
                ?>
            </div>
        </div>

        <!-- Тело ответа -->
        <div class="forum-reply__body">
            <div class="forum-reply__meta">
                <span class="forum-reply__date"><?php echo esc_html(date_i18n('d M Y, H:i', strtotime($reply->post_date))); ?></span>
                <span class="forum-reply__num">#<?php echo esc_html($reply->ID); ?></span>
            </div>
            <div class="forum-reply__content entry-content" style="color:var(--text);">
                <?php echo wp_kses_post(apply_filters('the_content', $reply->post_content)); ?>
            </div>
            <div class="forum-reply__actions">
                <!-- Лайк -->
                <?php if (is_user_logged_in()) : ?>
                    <button class="forum-like-btn <?php echo $liked ? 'liked' : ''; ?>"
                            data-reply-id="<?php echo esc_attr($reply->ID); ?>"
                            title="<?php echo $liked ? 'Убрать лайк' : 'Полезный ответ'; ?>">
                        <span class="forum-like-icon"><?php echo $liked ? '👍' : '👍'; ?></span>
                        <span class="forum-like-count"><?php echo count($likes); ?></span>
                    </button>
                <?php else : ?>
                    <span class="forum-like-btn forum-like-btn--static">
                        👍 <span><?php echo count($likes); ?></span>
                    </span>
                <?php endif; ?>

                <!-- Цитата -->
                <?php if (is_user_logged_in()) : ?>
                    <button class="forum-quote-btn" data-reply-id="<?php echo esc_attr($reply->ID); ?>"
                            data-author="<?php echo esc_attr($user->display_name); ?>"
                            data-text="<?php echo esc_attr(wp_trim_words(strip_tags($reply->post_content), 20)); ?>">
                        💬 Цитировать
                    </button>
                <?php endif; ?>

                <!-- Удалить -->
                <?php if ($can_mod || $is_own) : ?>
                    <button class="forum-delete-reply-btn" data-reply-id="<?php echo esc_attr($reply->ID); ?>">
                        🗑 Удалить
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/* ══════════════════════════════════════════════
   FLUSH REWRITE
══════════════════════════════════════════════ */
add_action('after_switch_theme', function() {
    flush_rewrite_rules();
});
