<?php
/**
 * Template Name: Форум
 */
get_header();

$sections = get_terms(['taxonomy' => 'forum_section', 'hide_empty' => false, 'orderby' => 'term_order']);
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="section-label">Сообщество</div>
    <h1 class="page-hero__title">Форум OneGTA</h1>
    <p style="margin-top:.4rem;max-width:500px;">Обсуждай игры, делись советами, задавай вопросы — живое сообщество фанатов GTA</p>
  </div>
</section>

<div style="background:var(--bg);padding:2rem 0 4rem;">
<div class="container">

  <!-- Топ-бар форума -->
  <div class="forum-topbar">
    <div class="forum-search-wrap">
      <input type="text" id="forumSearchInput" class="form-input" placeholder="🔍 Поиск по форуму…" style="max-width:320px;" autocomplete="off">
      <div class="search-results-dropdown" id="forumSearchDropdown" style="display:none;max-width:320px;"></div>
    </div>
    <div style="display:flex;align-items:center;gap:1rem;">
      <div class="forum-online-wrap">
        <span class="forum-online-dot"></span>
        <span id="forumOnlineCount">0</span> онлайн
      </div>
      <?php if (is_user_logged_in()) : ?>
        <a href="<?php echo esc_url(home_url('/forum-new-topic/')); ?>" class="btn btn--primary btn--sm">+ Создать тему</a>
      <?php else : ?>
        <button class="btn btn--primary btn--sm" id="forumLoginBtn">Войти для участия</button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Разделы форума -->
  <?php if ($sections && !is_wp_error($sections)) : ?>
    <div class="forum-sections">
      <?php foreach ($sections as $section) :
        // Кол-во тем в разделе
        $topic_count = $section->count;

        // Последняя тема
        $last_topics = get_posts([
          'post_type'      => 'forum_topic',
          'posts_per_page' => 1,
          'tax_query'      => [['taxonomy'=>'forum_section','field'=>'term_id','terms'=>$section->term_id]],
          'orderby'        => 'modified',
          'order'          => 'DESC',
          'post_status'    => 'publish',
        ]);
        $last_topic  = $last_topics ? $last_topics[0] : null;
        $last_reply  = $last_topic  ? onegta_topic_last_reply($last_topic->ID) : null;

        // Суммарно ответов в разделе
        $total_replies = 0;
        if ($topic_count > 0) {
          $all_topics = get_posts(['post_type'=>'forum_topic','posts_per_page'=>-1,'fields'=>'ids','tax_query'=>[['taxonomy'=>'forum_section','field'=>'term_id','terms'=>$section->term_id]],'post_status'=>'publish']);
          foreach ($all_topics as $tid) $total_replies += onegta_topic_reply_count($tid);
        }
      ?>
        <div class="forum-section-card">
          <div class="forum-section-card__icon"><?php echo mb_substr($section->name, 0, 2); ?></div>
          <div class="forum-section-card__info">
            <a href="<?php echo esc_url(get_term_link($section)); ?>" class="forum-section-card__name">
              <?php echo esc_html($section->name); ?>
            </a>
            <?php if ($section->description) : ?>
              <div class="forum-section-card__desc"><?php echo esc_html($section->description); ?></div>
            <?php endif; ?>
          </div>
          <div class="forum-section-card__stats">
            <div class="forum-section-stat">
              <span class="forum-section-stat__num"><?php echo esc_html($topic_count); ?></span>
              <span class="forum-section-stat__lbl">тем</span>
            </div>
            <div class="forum-section-stat">
              <span class="forum-section-stat__num"><?php echo esc_html($total_replies); ?></span>
              <span class="forum-section-stat__lbl">ответов</span>
            </div>
          </div>
          <div class="forum-section-card__last">
            <?php if ($last_topic) :
              $last_author_id = $last_reply ? (int)$last_reply->post_author : (int)$last_topic->post_author;
              $last_author    = get_userdata($last_author_id);
              $last_time      = $last_reply ? $last_reply->post_date : $last_topic->post_date;
            ?>
              <div class="forum-section-card__last-avatar">
                <img src="<?php echo esc_url(onegta_avatar_url($last_author_id)); ?>" alt="">
              </div>
              <div class="forum-section-card__last-info">
                <a href="<?php echo esc_url(get_permalink($last_topic->ID)); ?>" class="forum-section-card__last-title">
                  <?php echo esc_html(wp_trim_words($last_topic->post_title, 6)); ?>
                </a>
                <div class="forum-section-card__last-meta">
                  <?php echo $last_author ? esc_html($last_author->display_name) : '—'; ?>
                  · <?php echo esc_html(date_i18n('d M, H:i', strtotime($last_time))); ?>
                </div>
              </div>
            <?php else : ?>
              <div style="font-size:.8rem;color:var(--text3);">Нет тем</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Статистика форума -->
  <div class="forum-stats-bar">
    <?php
    $total_topics  = wp_count_posts('forum_topic')->publish;
    $total_replies = (int)(new WP_Query(['post_type'=>'forum_reply','post_status'=>'publish','posts_per_page'=>1]))->found_posts;
    $total_users   = count_users()['total_users'];
    $online_users  = get_users(['meta_key'=>'last_activity','meta_value'=>time()-300,'meta_compare'=>'>=']);
    ?>
    <div class="forum-stat-item">📋 Тем: <strong><?php echo number_format($total_topics); ?></strong></div>
    <div class="forum-stat-item">💬 Сообщений: <strong><?php echo number_format($total_replies); ?></strong></div>
    <div class="forum-stat-item">👥 Участников: <strong><?php echo number_format($total_users); ?></strong></div>
    <div class="forum-stat-item">🟢 Онлайн: <strong id="statsOnline"><?php echo count($online_users); ?></strong></div>
  </div>

</div>
</div>

<?php get_footer(); ?>
