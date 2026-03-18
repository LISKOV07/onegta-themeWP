<?php
// single-videos.php
get_header(); the_post();
$vid_url = get_post_meta(get_the_ID(), 'video_url', true);
$embed   = onegta_video_embed($vid_url);
$cats    = get_the_terms(get_the_ID(), 'video_category') ?: [];
$cat     = $cats && !is_wp_error($cats) ? $cats[0] : null;
$tags    = get_the_terms(get_the_ID(), 'gta_tag') ?: [];
?>
<main class="single-layout">
  <div class="container">
    <div class="single-layout__inner">
      <article>
        <header class="single-post__header">
          <?php if ($cat) : ?><a href="<?php echo esc_url(get_term_link($cat)); ?>" class="single-post__cat"><?php echo esc_html($cat->name); ?></a><?php endif; ?>
          <h1 class="single-post__title"><?php the_title(); ?></h1>
          <div class="single-post__meta">
            <span><?php the_author(); ?></span>
            <span><?php echo esc_html(onegta_date()); ?></span>
          </div>
        </header>
        <?php if ($embed) : ?>
          <div style="margin-bottom:2rem;"><?php echo $embed; ?></div>
        <?php elseif (has_post_thumbnail()) : ?>
          <div class="single-post__featured-img"><?php the_post_thumbnail('onegta-featured'); ?></div>
        <?php endif; ?>
        <div class="entry-content"><?php the_content(); ?></div>
        <?php if ($tags) : ?>
          <div class="entry-tags"><span>Теги:</span>
            <?php foreach ($tags as $t) : ?><a href="<?php echo esc_url(get_term_link($t)); ?>"><?php echo esc_html($t->name); ?></a><?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if (comments_open() || get_comments_number()) comments_template(); ?>
      </article>
      <aside class="sidebar">
        <div class="sidebar-widget"><div class="sidebar-widget__title">Похожие видео</div><div class="sidebar-widget__body">
          <?php foreach (onegta_recent(5,'videos') as $v) : ?>
            <div class="sidebar-recent-item">
              <?php if (has_post_thumbnail($v->ID)) : ?>
                <a href="<?php echo esc_url(get_permalink($v)); ?>" class="sidebar-recent-item__thumb"><?php echo get_the_post_thumbnail($v->ID,'onegta-thumb'); ?></a>
              <?php endif; ?>
              <div><a href="<?php echo esc_url(get_permalink($v)); ?>" class="sidebar-recent-item__title"><?php echo esc_html($v->post_title); ?></a><div class="sidebar-recent-item__date"><?php echo esc_html(onegta_date($v->ID)); ?></div></div>
            </div>
          <?php endforeach; ?>
        </div></div>
      </aside>
    </div>
  </div>
</main>
<?php get_footer(); ?>
