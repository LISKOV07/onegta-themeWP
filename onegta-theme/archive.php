<?php
get_header();
$post_type = get_query_var('post_type') ?: 'post';
$is_tax    = is_tax();
$queried   = get_queried_object();

// Determine taxonomy for filters
$tax_map   = ['news'=>'news_category','articles'=>'game_title','files'=>'file_category','videos'=>'video_category','post'=>'category'];
$tax       = $tax_map[$post_type] ?? 'category';
$terms     = get_terms(['taxonomy'=>$tax,'hide_empty'=>false,'number'=>20]);
$cur_term  = $is_tax ? $queried->term_id : 0;

// Labels
$labels = ['news'=>'Новости','articles'=>'Статьи','files'=>'Файловый архив','videos'=>'Видео','post'=>'Блог'];
$archive_title = $is_tax ? $queried->name : ($labels[$post_type] ?? 'Материалы');
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <?php onegta_breadcrumb(); ?>
    <div class="section-label"><?php echo esc_html($labels[$post_type] ?? 'Архив'); ?></div>
    <h1 class="page-hero__title"><?php echo esc_html($archive_title); ?></h1>
    <?php if ($is_tax && $queried->description) : ?>
      <p style="max-width:600px;margin-top:.5rem;"><?php echo esc_html($queried->description); ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- Filter bar -->
<?php if ($terms && !is_wp_error($terms)) : ?>
<div class="filter-bar">
  <div class="container filter-bar__inner">
    <a href="<?php echo esc_url(get_post_type_archive_link($post_type)); ?>"
       class="filter-btn <?php echo !$is_tax ? 'active' : ''; ?>">Все</a>
    <?php foreach ($terms as $t) : ?>
      <a href="<?php echo esc_url(get_term_link($t)); ?>"
         class="filter-btn <?php echo $cur_term === $t->term_id ? 'active' : ''; ?>">
        <?php echo esc_html($t->name); ?>
        <small style="opacity:.6;font-size:.7em;">(<?php echo esc_html($t->count); ?>)</small>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="posts-section">
  <div class="container">
    <div id="postsGrid" class="<?php echo $post_type === 'files' ? 'files-grid' : 'posts-grid'; ?>">
      <?php if (have_posts()) :
        while (have_posts()) : the_post();
          if ($post_type === 'files') get_template_part('template-parts/cards/file-card');
          elseif ($post_type === 'videos') get_template_part('template-parts/cards/video-card');
          else get_template_part('template-parts/cards/post-card');
        endwhile;
      else : ?>
        <p style="color:var(--text3);padding:2rem 0;grid-column:1/-1;">Материалов пока нет.</p>
      <?php endif; ?>
    </div>

    <?php the_posts_pagination(['prev_text'=>'←','next_text'=>'→','mid_size'=>2]); ?>
  </div>
</div>

<?php get_footer(); ?>
