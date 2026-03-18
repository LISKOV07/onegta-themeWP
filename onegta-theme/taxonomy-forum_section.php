<?php
/**
 * Список тем в разделе форума
 */
get_header();
$section    = get_queried_object();
$paged      = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Прикреплённые темы
$pinned = get_posts([
    'post_type'  => 'forum_topic',
    'post_status'=> 'publish',
    'tax_query'  => [['taxonomy'=>'forum_section','field'=>'term_id','terms'=>$section->term_id]],
    'meta_query' => [['key'=>'_topic_pinned','value'=>'1','compare'=>'=']],
    'orderby'    => 'modified', 'order' => 'DESC',
    'posts_per_page' => -1,
]);

// Обычные темы
$topics_query = new WP_Query([
    'post_type'      => 'forum_topic',
    'post_status'    => 'publish',
    'tax_query'      => [['taxonomy'=>'forum_section','field'=>'term_id','terms'=>$section->term_id]],
    'meta_query'     => [['key'=>'_topic_pinned','value'=>'1','compare'=>'!=','type'=>'CHAR'],['key'=>'_topic_pinned','compare'=>'NOT EXISTS'],'relation'=>'OR'],
    'orderby'        => 'modified',
    'order'          => 'DESC',
    'paged'          => $paged,
    'posts_per_page' => 20,
]);
$pinned_ids = array_column($pinned, 'ID');
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <?php onegta_breadcrumb(); ?>
    <div class="section-label">Форум</div>
    <h1 class="page-hero__title"><?php echo esc_html($section->name); ?></h1>
    <?php if ($section->description) : ?>
      <p style="margin-top:.4rem;"><?php echo esc_html($section->description); ?></p>
    <?php endif; ?>
  </div>
</section>

<div style="background:var(--bg);padding:2rem 0 4rem;">
<div class="container">

  <!-- Actions bar -->
  <div class="forum-topbar">
    <a href="<?php echo esc_url(home_url('/forum/')); ?>" style="font-size:.85rem;color:var(--text3);display:flex;align-items:center;gap:.3rem;">← К разделам</a>
    <?php if (is_user_logged_in()) : ?>
      <a href="<?php echo esc_url(home_url('/forum-new-topic/?section=' . $section->term_id)); ?>" class="btn btn--primary btn--sm">+ Создать тему</a>
    <?php else : ?>
      <button class="btn btn--primary btn--sm" id="forumLoginBtn">Войти для участия</button>
    <?php endif; ?>
  </div>

  <!-- Topics table -->
  <div class="forum-topics-table">

    <!-- Header -->
    <div class="forum-topics-head">
      <div class="forum-col-title">Тема</div>
      <div class="forum-col-stats">Ответы / Просмотры</div>
      <div class="forum-col-last">Последнее сообщение</div>
    </div>

    <!-- Pinned topics -->
    <?php foreach ($pinned as $topic) :
      onegta_render_topic_row($topic, true);
    endforeach; ?>

    <!-- Regular topics -->
    <?php if ($topics_query->have_posts()) :
      while ($topics_query->have_posts()) : $topics_query->the_post();
        onegta_render_topic_row(get_post());
      endwhile;
      wp_reset_postdata();
    else : ?>
      <div class="forum-empty">
        <div style="font-size:2.5rem;margin-bottom:1rem;">💬</div>
        <p>В этом разделе ещё нет тем.</p>
        <?php if (is_user_logged_in()) : ?>
          <a href="<?php echo esc_url(home_url('/forum-new-topic/?section=' . $section->term_id)); ?>" class="btn btn--primary btn--sm">Создать первую тему</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>

  <!-- Pagination -->
  <?php if ($topics_query->max_num_pages > 1) : ?>
    <div class="pagination">
      <?php echo paginate_links(['total'=>$topics_query->max_num_pages,'current'=>$paged,'prev_text'=>'←','next_text'=>'→','mid_size'=>2]); ?>
    </div>
  <?php endif; ?>

</div>
</div>

<?php get_footer(); ?>
