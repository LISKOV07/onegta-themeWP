<?php
$pt    = get_post_type();
$tax   = ['news'=>'news_category','articles'=>'game_title','post'=>'category'][$pt] ?? 'category';
$cats  = get_the_terms(get_the_ID(), $tax) ?: get_the_category();
$cat   = $cats && !is_wp_error($cats) ? $cats[0] : null;
?>
<article <?php post_class('post-card'); ?>>
  <div class="post-card__stripe"></div>
  <?php if (has_post_thumbnail()) : ?>
    <a href="<?php the_permalink(); ?>" class="post-card__thumb" tabindex="-1">
      <?php the_post_thumbnail('onegta-card', ['alt'=>esc_attr(get_the_title())]); ?>
    </a>
  <?php else : ?>
    <div class="post-card__thumb"><div class="post-card__thumb-placeholder">GTA</div></div>
  <?php endif; ?>
  <div class="post-card__body">
    <div class="post-card__meta-top">
      <?php if ($cat) : ?>
        <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="post-card__cat"><?php echo esc_html($cat->name); ?></a>
      <?php endif; ?>
      <span class="post-card__date"><?php echo esc_html(onegta_date()); ?></span>
    </div>
    <h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <p class="post-card__excerpt"><?php the_excerpt(); ?></p>
    <div class="post-card__footer">
      <div class="post-card__author">
        <a href="<?php echo esc_url(onegta_profile_url(get_the_author_meta('ID'))); ?>" style="display:flex;align-items:center;gap:.4rem;color:var(--text3);font-size:.75rem;font-weight:600;text-decoration:none;">
          <div class="post-card__author-avatar">
            <img src="<?php echo esc_url(onegta_avatar_url(get_the_author_meta('ID'))); ?>" alt="">
          </div>
          <?php the_author(); ?>
        </a>
      </div>
    </div>
  </div>
</article>
