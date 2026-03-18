<?php
/**
 * OneGTA — Поиск
 */
defined('ABSPATH') || exit;

/* ── INCLUDE ALL CPT IN SEARCH ───────────────── */
add_filter('pre_get_posts', function($q) {
    if (!$q->is_main_query()) return;
    if ($q->is_search() && !is_admin()) {
        $q->set('post_type', ['post','news','articles','files','videos']);
    }
    return $q;
});

/* ── AJAX LIVE SEARCH ────────────────────────── */
add_action('wp_ajax_onegta_live_search',        'onegta_live_search_cb');
add_action('wp_ajax_nopriv_onegta_live_search', 'onegta_live_search_cb');

function onegta_live_search_cb() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $q   = sanitize_text_field($_POST['query'] ?? '');
    $type= sanitize_key($_POST['type'] ?? '');
    if (strlen($q) < 2) wp_send_json_success(['results'=>[]]);

    $args = [
        's'              => $q,
        'posts_per_page' => 8,
        'post_status'    => 'publish',
        'post_type'      => $type ?: ['post','news','articles','files','videos'],
    ];

    $results = [];
    $posts   = get_posts($args);
    foreach ($posts as $p) {
        $cats  = get_the_terms($p->ID, ['news_category','game_title','file_category','category']);
        $cat   = $cats && !is_wp_error($cats) ? $cats[0]->name : '';
        $type_label = [
            'news'    => '📰',
            'articles'=> '📖',
            'files'   => '📦',
            'videos'  => '🎬',
            'post'    => '📝',
        ][$p->post_type] ?? '📄';

        $results[] = [
            'id'       => $p->ID,
            'title'    => get_the_title($p->ID),
            'url'      => get_permalink($p->ID),
            'type'     => $p->post_type,
            'typeIcon' => $type_label,
            'cat'      => $cat,
            'thumb'    => has_post_thumbnail($p->ID) ? get_the_post_thumbnail_url($p->ID,'onegta-thumb') : null,
        ];
    }

    wp_send_json_success(['results'=>$results, 'total'=>count($results), 'query'=>$q]);
}
