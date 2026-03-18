<?php
/**
 * Страница темы форума
 */
get_header();
the_post();

$topic_id     = get_the_ID();
$topic_author = (int) get_the_author_meta('ID');
$status       = onegta_topic_status($topic_id);
$is_pinned    = onegta_topic_is_pinned($topic_id);
$reply_count  = onegta_topic_reply_count($topic_id);
$view_count   = onegta_topic_views($topic_id);
$sections     = get_the_terms($topic_id, 'forum_section');
$section      = $sections && !is_wp_error($sections) ? $sections[0] : null;
$tags         = get_the_terms($topic_id, 'forum_tag');
$is_closed    = $status === 'closed';
$can_mod      = current_user_can('onegta_moderate_content') || current_user_can('administrator');
$paged        = max(1, (int)get_query_var('paged'));
$per_page     = 15;

// Увеличиваем просмотры (не для автора)
if (!is_user_logged_in() || get_current_user_id() !== $topic_author) {
    onegta_topic_increment_views($topic_id);
}

// Получаем ответы
$replies_query = new WP_Query([
    'post_type'      => 'forum_reply',
    'post_parent'    => $topic_id,
    'post_status'    => 'publish',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'ASC',
]);
?>

<section class="page-hero" style="padding-bottom:1.5rem;">
  <div class="container page-hero__inner">
    <?php onegta_breadcrumb(); ?>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-top:.5rem;">
      <div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;flex-wrap:wrap;">
          <?php if ($is_pinned) echo '<span class="badge badge--orange">📌 Прикреплена</span>'; ?>
          <?php if ($is_closed) echo '<span class="badge" style="background:#6b7280;color:#fff;">🔒 Закрыта</span>'; ?>
          <?php if ($section)   echo '<a href="'.esc_url(get_term_link($section)).'" class="badge badge--pale">'.esc_html($section->name).'</a>'; ?>
        </div>
        <h1 class="page-hero__title" style="font-size:clamp(1.8rem,4vw,3rem);"><?php the_title(); ?></h1>
        <div style="display:flex;gap:1.5rem;margin-top:.5rem;flex-wrap:wrap;font-size:.75rem;color:var(--text3);font-weight:600;letter-spacing:1px;">
          <span>👤 <?php the_author(); ?></span>
          <span>📅 <?php echo esc_html(get_the_date('d M Y')); ?></span>
          <span>💬 <?php echo esc_html($reply_count); ?> ответов</span>
          <span>👁 <?php echo esc_html($view_count); ?> просмотров</span>
        </div>
      </div>

      <!-- Модерация -->
      <?php if ($can_mod) : ?>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
          <button class="btn btn--ghost btn--sm forum-mod-btn" data-topic-id="<?php echo esc_attr($topic_id); ?>" data-action="pin">
            <?php echo $is_pinned ? '📌 Открепить' : '📌 Прикрепить'; ?>
          </button>
          <button class="btn btn--ghost btn--sm forum-mod-btn" data-topic-id="<?php echo esc_attr($topic_id); ?>" data-action="close">
            <?php echo $is_closed ? '🔓 Открыть' : '🔒 Закрыть'; ?>
          </button>
          <button class="btn btn--sm forum-mod-btn" style="background:#fee2e2;color:var(--error);border:1px solid #fecaca;" data-topic-id="<?php echo esc_attr($topic_id); ?>" data-action="delete">
            🗑 Удалить тему
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<div style="background:var(--bg);padding:2rem 0 4rem;">
<div class="container">
  <div id="forumAlert" style="margin-bottom:1rem;"></div>

  <!-- Первое сообщение (сама тема) -->
  <div class="forum-replies" id="forumReplies">
    <?php
    // Рендерим первый пост как reply
    $fake_first = (object)[
        'ID'          => $topic_id,
        'post_author' => $topic_author,
        'post_content'=> get_the_content(),
        'post_date'   => get_the_date('Y-m-d H:i:s'),
    ];
    onegta_render_reply($fake_first, $topic_author);
    ?>

    <!-- Остальные ответы -->
    <?php if ($replies_query->have_posts()) :
      while ($replies_query->have_posts()) : $replies_query->the_post();
        onegta_render_reply(get_post(), $topic_author);
      endwhile;
      wp_reset_postdata();
    endif; ?>
  </div>

  <!-- Пагинация -->
  <?php if ($replies_query->max_num_pages > 1) : ?>
    <div class="pagination" style="margin-bottom:2rem;">
      <?php echo paginate_links(['total'=>$replies_query->max_num_pages,'current'=>$paged,'prev_text'=>'←','next_text'=>'→','mid_size'=>2]); ?>
    </div>
  <?php endif; ?>

  <!-- Форма ответа -->
  <?php if ($is_closed) : ?>
    <div class="alert alert--info">🔒 Тема закрыта. Новые ответы не принимаются.</div>
  <?php elseif (is_user_logged_in()) : ?>
    <div class="forum-reply-form" id="replyFormWrap">
      <div class="submit-section-title" style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;letter-spacing:3px;color:var(--orange);margin-bottom:1rem;">Ваш ответ</div>
      <div id="quotePreview" style="display:none;background:var(--orange-pale);border-left:3px solid var(--orange);padding:.8rem 1rem;margin-bottom:.8rem;font-size:.88rem;color:var(--text2);"></div>
      <form id="forumReplyForm">
        <input type="hidden" name="topic_id" value="<?php echo esc_attr($topic_id); ?>">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('onegta_nonce'); ?>">
        <textarea class="form-input" name="content" id="replyContent" rows="6" placeholder="Напиши ответ…" required style="margin-bottom:.8rem;"></textarea>
        <div style="display:flex;align-items:center;gap:1rem;">
          <button type="submit" class="btn btn--primary">Отправить ответ</button>
          <button type="button" id="clearQuote" style="display:none;font-size:.82rem;color:var(--text3);background:none;border:none;cursor:pointer;">✕ Убрать цитату</button>
        </div>
      </form>
    </div>
  <?php else : ?>
    <div class="alert alert--info">
      <a href="#" onclick="document.getElementById('openAuthBtn')?.click();return false;" style="font-weight:700;color:var(--orange);">Войди</a> или
      <a href="#" onclick="document.getElementById('openRegisterBtn')?.click();return false;" style="font-weight:700;color:var(--orange);">зарегистрируйся</a> чтобы ответить.
    </div>
  <?php endif; ?>

</div>
</div>

<?php get_footer(); ?>
