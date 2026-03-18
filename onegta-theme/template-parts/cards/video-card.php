<?php
$vid_url = get_post_meta(get_the_ID(), 'video_url', true);
$cats    = get_the_terms(get_the_ID(), 'video_category');
$cat     = $cats && !is_wp_error($cats) ? $cats[0]->name : 'Видео';
?>
<article <?php post_class('post-card'); ?>>
  <div class="post-card__stripe"></div>
  <a href="<?php the_permalink(); ?>" class="post-card__thumb" tabindex="-1">
    <?php if (has_post_thumbnail()) : ?>
      <?php the_post_thumbnail('onegta-card', ['alt'=>esc_attr(get_the_title())]); ?>
    <?php else : ?>
      <div class="post-card__thumb-placeholder" style="background:var(--dark);color:rgba(255,255,255,.2);">▶</div>
    <?php endif; ?>
  </a>
  <div class="post-card__body">
    <div class="post-card__meta-top">
      <span class="post-card__cat"><?php echo esc_html($cat); ?></span>
      <span class="post-card__date"><?php echo esc_html(onegta_date()); ?></span>
    </div>
    <h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <p class="post-card__excerpt"><?php the_excerpt(); ?></p>
  </div>
</article>
