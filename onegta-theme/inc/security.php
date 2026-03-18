<?php
/**
 * OneGTA — Security (без блокировки wp-admin)
 */
defined('ABSPATH') || exit;

/* ── СКРЫТЬ ADMINBAR для обычных пользователей ── */
add_action('after_setup_theme', function() {
    $user = wp_get_current_user();
    $role = $user->roles[0] ?? '';
    if (!in_array($role, ['administrator', 'onegta_moderator', 'editor'])) {
        show_admin_bar(false);
    }
});

/* ── УБРАТЬ META GENERATOR ───────────────────── */
remove_action('wp_head', 'wp_generator');

/* ── УБРАТЬ X-PINGBACK ───────────────────────── */
add_filter('wp_headers', function($h) { unset($h['X-Pingback']); return $h; });

/* ── ОТКЛЮЧИТЬ XML-RPC ───────────────────────── */
add_filter('xmlrpc_enabled', '__return_false');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

/* ── LIMIT LOGIN ATTEMPTS ────────────────────── */
add_filter('authenticate', function($user, $username, $password) {
    if (empty($username) || empty($password)) return $user;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if ((int)get_transient('onegta_login_fail_' . md5($ip)) >= 5)
        return new WP_Error('too_many_retries', 'Слишком много попыток. Подождите 15 минут.');
    return $user;
}, 30, 3);

add_action('wp_login_failed', function() {
    $t = 'onegta_login_fail_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
    set_transient($t, (int)get_transient($t) + 1, 15 * MINUTE_IN_SECONDS);
});

add_action('wp_login', function() {
    delete_transient('onegta_login_fail_' . md5($_SERVER['REMOTE_ADDR'] ?? ''));
});
