<?php
/**
 * OneGTA — Роли пользователей
 * Пользователь → Модератор → Администратор
 */
defined('ABSPATH') || exit;

/* ── REGISTER ROLES ──────────────────────────── */
function onegta_register_roles() {

    // Удаляем и пересоздаём чтоб capabilities были актуальные
    remove_role('onegta_user');
    remove_role('onegta_moderator');

    /* ── ПОЛЬЗОВАТЕЛЬ ── */
    add_role('onegta_user', 'Пользователь OneGTA', [
        'read'                   => true,
        // Посты (уходят на модерацию)
        'edit_posts'             => true,
        'delete_posts'           => false,
        'publish_posts'          => false, // только pending
        // CPT: news
        'edit_news'              => true,
        'delete_news'            => false,
        'publish_news'           => false,
        // CPT: articles
        'edit_articles'          => true,
        'delete_articles'        => false,
        'publish_articles'       => false,
        // CPT: files
        'edit_files'             => true,
        'delete_files'           => false,
        'publish_files'          => false,
        // CPT: videos
        'edit_videos'            => true,
        'delete_videos'          => false,
        'publish_videos'         => false,
        // Комментарии
        'moderate_comments'      => false,
        // Загрузка файлов
        'upload_files'           => true,
        // Профиль
        'edit_profile'           => true,
        // Читать личные записи
        'read_private_posts'     => false,
        // Кастомные
        'onegta_submit_content'  => true,
        'onegta_download_files'  => true,
    ]);

    /* ── МОДЕРАТОР ── */
    add_role('onegta_moderator', 'Модератор OneGTA', [
        'read'                        => true,
        'upload_files'                => true,
        'edit_profile'                => true,
        // Посты
        'edit_posts'                  => true,
        'edit_others_posts'           => true,
        'edit_published_posts'        => true,
        'publish_posts'               => true,
        'delete_posts'                => true,
        'delete_others_posts'         => true,
        'delete_published_posts'      => true,
        'read_private_posts'          => true,
        // CPT: news
        'edit_news'                   => true,
        'edit_others_news'            => true,
        'edit_published_news'         => true,
        'publish_news'                => true,
        'delete_news'                 => true,
        'delete_others_news'          => true,
        'delete_published_news'       => true,
        'read_private_news'           => true,
        // CPT: articles
        'edit_articles'               => true,
        'edit_others_articles'        => true,
        'edit_published_articles'     => true,
        'publish_articles'            => true,
        'delete_articles'             => true,
        'delete_others_articles'      => true,
        'delete_published_articles'   => true,
        // CPT: files
        'edit_files'                  => true,
        'edit_others_files'           => true,
        'edit_published_files'        => true,
        'publish_files'               => true,
        'delete_files'                => true,
        'delete_others_files'         => true,
        'delete_published_files'      => true,
        // CPT: videos
        'edit_videos'                 => true,
        'edit_others_videos'          => true,
        'edit_published_videos'       => true,
        'publish_videos'              => true,
        'delete_videos'               => true,
        'delete_others_videos'        => true,
        'delete_published_videos'     => true,
        // Комментарии
        'moderate_comments'           => true,
        'edit_comment'                => true,
        // Таксономии
        'manage_categories'           => true,
        'edit_categories'             => true,
        // Кастомные
        'onegta_submit_content'       => true,
        'onegta_download_files'       => true,
        'onegta_moderate_content'     => true,
        'onegta_approve_posts'        => true,
    ]);
}

/* ── GIVE ADMIN ALL ONEGTA CAPS ──────────────── */
function onegta_admin_caps($caps, $cap, $user_id) {
    if (!isset($caps[$cap])) return $caps;
    $user = get_userdata($user_id);
    if ($user && in_array('administrator', (array)$user->roles)) {
        $caps[$cap] = true;
    }
    return $caps;
}
add_filter('user_has_cap', function($caps, $cap_requested, $args, $user) {
    if (!$user) return $caps;
    if (in_array('administrator', (array)$user->roles)) {
        // Admin gets everything
        $onegta_caps = [
            'onegta_submit_content','onegta_download_files',
            'onegta_moderate_content','onegta_approve_posts',
            'onegta_manage_roles',
        ];
        foreach ($onegta_caps as $c) $caps[$c] = true;
    }
    return $caps;
}, 10, 4);

/* ── AUTO-ASSIGN ROLE ON REGISTER ────────────── */
add_action('user_register', function($user_id) {
    $user = new WP_User($user_id);
    // Если роль не установлена или subscriber — ставим onegta_user
    if (in_array('subscriber', $user->roles) || empty($user->roles)) {
        $user->set_role('onegta_user');
    }
});

/* ── POST STATUS BY ROLE ─────────────────────── */
// Пользователи → pending, модераторы/админы → publish
function onegta_get_submit_status() {
    if (current_user_can('onegta_approve_posts') || current_user_can('publish_posts')) {
        return 'publish';
    }
    return 'pending';
}

/* ── CAPS FOR CPT ────────────────────────────── */
// Регистрируем capabilities для каждого CPT
function onegta_map_cpt_caps() {
    $cpts = ['news','articles','files','videos'];
    foreach ($cpts as $cpt) {
        $obj = get_post_type_object($cpt);
        if (!$obj) continue;
        // Даём модераторам доступ к чужим постам
        $roles = ['onegta_moderator', 'administrator'];
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if (!$role) continue;
            $role->add_cap("edit_{$cpt}");
            $role->add_cap("edit_others_{$cpt}");
            $role->add_cap("edit_published_{$cpt}");
            $role->add_cap("publish_{$cpt}");
            $role->add_cap("delete_{$cpt}");
            $role->add_cap("delete_others_{$cpt}");
            $role->add_cap("delete_published_{$cpt}");
            $role->add_cap("read_private_{$cpt}");
        }
        // Пользователи только свои
        $user_role = get_role('onegta_user');
        if ($user_role) {
            $user_role->add_cap("edit_{$cpt}");
            $user_role->add_cap("delete_{$cpt}");
        }
    }
}
add_action('init', 'onegta_map_cpt_caps', 20);

/* ── PENDING POSTS MODERATION NOTIFY ─────────── */
add_action('transition_post_status', function($new, $old, $post) {
    if ($new !== 'pending') return;
    $cpts = ['post','news','articles','files','videos'];
    if (!in_array($post->post_type, $cpts)) return;

    // Уведомляем всех модераторов и администраторов
    $moderators = get_users(['role__in' => ['onegta_moderator','administrator']]);
    $subject    = '[OneGTA] Новый материал на модерации: ' . get_the_title($post->ID);
    $admin_url  = admin_url('post.php?post='.$post->ID.'&action=edit');
    $message    = "Привет!\n\nНовый материал ожидает модерации:\n\n";
    $message   .= "Заголовок: " . get_the_title($post->ID) . "\n";
    $message   .= "Тип: " . $post->post_type . "\n";
    $message   .= "Автор: " . get_the_author_meta('display_name', $post->post_author) . "\n";
    $message   .= "Ссылка для модерации: " . $admin_url . "\n\n";
    $message   .= "— OneGTA";

    foreach ($moderators as $mod) {
        wp_mail($mod->user_email, $subject, $message);
    }
}, 10, 3);

/* ── AJAX: GET USER ROLE INFO ────────────────── */
add_action('wp_ajax_onegta_get_role', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $user = wp_get_current_user();
    wp_send_json_success([
        'role'       => $user->roles[0] ?? 'guest',
        'roleLabel'  => onegta_role_label($user->roles[0] ?? ''),
        'canPublish' => current_user_can('publish_posts'),
        'canModerate'=> current_user_can('onegta_moderate_content'),
    ]);
});

/* ── AJAX: MODERATE POST (approve/reject) ────── */
add_action('wp_ajax_onegta_moderate_post', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!current_user_can('onegta_moderate_content')) wp_send_json_error(['message'=>'Нет прав']);

    $post_id = absint($_POST['post_id'] ?? 0);
    $action  = sanitize_key($_POST['moderate_action'] ?? '');

    if (!$post_id || !in_array($action, ['approve','reject'])) wp_send_json_error(['message'=>'Неверные данные']);

    $post = get_post($post_id);
    if (!$post) wp_send_json_error(['message'=>'Пост не найден']);

    if ($action === 'approve') {
        wp_update_post(['ID'=>$post_id, 'post_status'=>'publish']);
        // Уведомляем автора
        $author = get_userdata($post->post_author);
        if ($author) {
            wp_mail(
                $author->user_email,
                '[OneGTA] Ваш материал опубликован',
                "Ваш материал \"".get_the_title($post_id)."\" был одобрен и опубликован!\n\nСсылка: ".get_permalink($post_id)."\n\n— OneGTA"
            );
        }
        wp_send_json_success(['message'=>'Опубликовано', 'url'=>get_permalink($post_id)]);
    } else {
        wp_update_post(['ID'=>$post_id, 'post_status'=>'trash']);
        // Уведомляем автора
        $author = get_userdata($post->post_author);
        if ($author) {
            wp_mail(
                $author->user_email,
                '[OneGTA] Материал отклонён',
                "Ваш материал \"".get_the_title($post_id)."\" был отклонён модератором.\n\nЕсли у вас есть вопросы, свяжитесь с администрацией.\n\n— OneGTA"
            );
        }
        wp_send_json_success(['message'=>'Отклонено']);
    }
});

/* ── AJAX: CHANGE USER ROLE (admin only) ─────── */
add_action('wp_ajax_onegta_change_role', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!current_user_can('administrator')) wp_send_json_error(['message'=>'Нет прав']);

    $target_id = absint($_POST['user_id'] ?? 0);
    $new_role  = sanitize_key($_POST['new_role'] ?? '');
    $allowed   = ['onegta_user','onegta_moderator','administrator'];

    if (!$target_id || !in_array($new_role, $allowed)) wp_send_json_error(['message'=>'Неверные данные']);
    if ($target_id === get_current_user_id()) wp_send_json_error(['message'=>'Нельзя изменить свою роль']);

    $user = new WP_User($target_id);
    $user->set_role($new_role);
    wp_send_json_success(['message'=>'Роль изменена на '.onegta_role_label($new_role)]);
});

/* ── HELPERS ─────────────────────────────────── */
function onegta_role_label($role) {
    $labels = [
        'onegta_user'      => 'Пользователь',
        'onegta_moderator' => 'Модератор',
        'administrator'    => 'Администратор',
        'subscriber'       => 'Пользователь',
        'author'           => 'Автор',
        'editor'           => 'Редактор',
    ];
    return $labels[$role] ?? ucfirst($role);
}

function onegta_role_badge($role) {
    $badges = [
        'onegta_user'      => ['Пользователь', '#6b7280'],
        'onegta_moderator' => ['Модератор',    '#d97706'],
        'administrator'    => ['Администратор','#dc2626'],
        'subscriber'       => ['Пользователь', '#6b7280'],
    ];
    [$label, $color] = $badges[$role] ?? ['Участник','#6b7280'];
    return "<span style='background:{$color};color:#fff;font-size:.6rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:2px 8px;'>{$label}</span>";
}

function onegta_current_role() {
    if (!is_user_logged_in()) return 'guest';
    $user = wp_get_current_user();
    return $user->roles[0] ?? 'guest';
}

function onegta_is_moderator() {
    return current_user_can('onegta_moderate_content') || current_user_can('administrator');
}
