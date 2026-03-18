<?php
/**
 * OneGTA helpers
 */

function onegta_breadcrumb() {
    $sep  = '<span aria-hidden="true"> › </span>';
    $home = '<a href="'.esc_url(home_url('/')).'">Главная</a>';
    $crumbs = [$home];

    if (is_singular()) {
        $pt  = get_post_type();
        $obj = get_post_type_object($pt);
        if ($pt !== 'post' && $obj) {
            $archive = get_post_type_archive_link($pt);
            $crumbs[] = $archive ? '<a href="'.esc_url($archive).'">'.esc_html($obj->labels->name).'</a>' : esc_html($obj->labels->name);
        } elseif ($pt === 'post') {
            $crumbs[] = '<a href="'.esc_url(home_url('/blog/')).'">Блог</a>';
        }
        $crumbs[] = '<span>'.esc_html(get_the_title()).'</span>';
    } elseif (is_archive()) {
        $pt  = get_query_var('post_type') ?: get_post_type();
        $obj = get_post_type_object($pt);
        if (is_tax()) {
            $term = get_queried_object();
            if ($obj) {
                $archive = get_post_type_archive_link($pt);
                $crumbs[] = $archive ? '<a href="'.esc_url($archive).'">'.esc_html($obj->labels->name).'</a>' : esc_html($obj->labels->name);
            }
            if ($term->parent) {
                $parent = get_term($term->parent, $term->taxonomy);
                if (!is_wp_error($parent)) $crumbs[] = '<a href="'.esc_url(get_term_link($parent)).'">'.esc_html($parent->name).'</a>';
            }
            $crumbs[] = '<span>'.esc_html($term->name).'</span>';
        } else {
            $crumbs[] = '<span>'.esc_html($obj ? $obj->labels->name : 'Архив').'</span>';
        }
    } elseif (is_search()) {
        $crumbs[] = '<span>Поиск: '.esc_html(get_search_query()).'</span>';
    } elseif (is_page()) {
        $crumbs[] = '<span>'.esc_html(get_the_title()).'</span>';
    }

    echo '<nav class="page-hero__breadcrumb" aria-label="Хлебные крошки">';
    echo implode($sep, $crumbs);
    echo '</nav>';
}

function onegta_video_embed($url) {
    if (!$url) return '';
    // YouTube
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
        return '<div style="position:relative;padding-top:56.25%;"><iframe src="https://www.youtube.com/embed/'.esc_attr($m[1]).'" style="position:absolute;inset:0;width:100%;height:100%;" frameborder="0" allowfullscreen loading="lazy"></iframe></div>';
    }
    // Rutube
    if (preg_match('/rutube\.ru\/video\/([a-f0-9]+)/', $url, $m)) {
        return '<div style="position:relative;padding-top:56.25%;"><iframe src="https://rutube.ru/play/embed/'.esc_attr($m[1]).'" style="position:absolute;inset:0;width:100%;height:100%;" frameborder="0" allowfullscreen loading="lazy"></iframe></div>';
    }
    return '';
}

/* ── FORUM: RENDER TOPIC ROW ─────────────────── */
function onegta_render_topic_row($topic, $pinned = false) {
    $topic_id    = $topic->ID;
    $author_id   = (int)$topic->post_author;
    $author      = get_userdata($author_id);
    $replies     = onegta_topic_reply_count($topic_id);
    $views       = onegta_topic_views($topic_id);
    $status      = onegta_topic_status($topic_id);
    $is_closed   = $status === 'closed';
    $last_reply  = onegta_topic_last_reply($topic_id);
    $last_uid    = $last_reply ? (int)$last_reply->post_author : $author_id;
    $last_user   = get_userdata($last_uid);
    $last_time   = $last_reply ? $last_reply->post_date : $topic->post_date;
    $tags        = get_the_terms($topic_id, 'forum_tag');
    ?>
    <div class="forum-topic-row <?php echo $pinned ? 'forum-topic-row--pinned' : ''; ?>">
        <div class="forum-topic-row__title-wrap">
            <div class="forum-topic-row__avatar">
                <?php if ($author) : ?>
                    <a href="<?php echo esc_url(home_url('/profile/' . $author->user_login . '/')); ?>">
                        <img src="<?php echo esc_url(onegta_avatar_url($author_id)); ?>" alt="">
                    </a>
                <?php endif; ?>
            </div>
            <div class="forum-topic-row__info">
                <div class="forum-topic-row__badges">
                    <?php if ($pinned)    echo '<span class="badge badge--orange" style="font-size:.55rem;">📌 Прикреп.</span>'; ?>
                    <?php if ($is_closed) echo '<span class="badge" style="background:#6b7280;color:#fff;font-size:.55rem;">🔒</span>'; ?>
                    <?php if ($tags && !is_wp_error($tags)) :
                        foreach (array_slice($tags, 0, 2) as $tag) :
                            echo '<span class="badge badge--pale" style="font-size:.55rem;">'.esc_html($tag->name).'</span>';
                        endforeach;
                    endif; ?>
                </div>
                <a href="<?php echo esc_url(get_permalink($topic_id)); ?>" class="forum-topic-row__title">
                    <?php echo esc_html($topic->post_title); ?>
                </a>
                <div class="forum-topic-row__meta">
                    <?php echo $author ? esc_html($author->display_name) : '—'; ?>
                    · <?php echo esc_html(date_i18n('d M Y', strtotime($topic->post_date))); ?>
                </div>
            </div>
        </div>
        <div class="forum-col-stats">
            <div class="forum-col-stat">
                <div class="forum-col-stat__num"><?php echo esc_html($replies); ?></div>
                <div class="forum-col-stat__lbl">ответов</div>
            </div>
            <div class="forum-col-stat" style="margin-top:.3rem;">
                <div class="forum-col-stat__num" style="font-size:.9rem;color:var(--text3);"><?php echo esc_html($views); ?></div>
                <div class="forum-col-stat__lbl">просм.</div>
            </div>
        </div>
        <div class="forum-topic-row__last">
            <?php if ($last_user) : ?>
                <div class="forum-topic-row__last-avatar">
                    <img src="<?php echo esc_url(onegta_avatar_url($last_uid)); ?>" alt="">
                </div>
                <div class="forum-topic-row__last-info">
                    <a href="<?php echo esc_url(home_url('/profile/' . $last_user->user_login . '/')); ?>" class="forum-topic-row__last-name">
                        <?php echo esc_html($last_user->display_name); ?>
                    </a>
                    <div class="forum-topic-row__last-date"><?php echo esc_html(date_i18n('d M, H:i', strtotime($last_time))); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
