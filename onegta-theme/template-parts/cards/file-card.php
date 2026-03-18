<?php
$size = get_post_meta(get_the_ID(), 'file_size', true) ?: '—';
$game = get_post_meta(get_the_ID(), 'file_game', true) ?: '';
$dl   = (int)get_post_meta(get_the_ID(), 'file_downloads', true);
$cats = get_the_terms(get_the_ID(), 'file_category');
$cat  = $cats && !is_wp_error($cats) ? $cats[0]->name : 'Файл';
?>
<div class="file-card">
  <div class="file-card__icon">📦</div>
  <div class="file-card__info">
    <a href="<?php the_permalink(); ?>" class="file-card__name"><?php the_title(); ?></a>
    <div class="file-card__meta"><?php echo esc_html($cat); ?><?php if ($game) echo ' · '.esc_html($game); ?> · <?php echo esc_html(onegta_date()); ?></div>
    <div class="file-card__tags">
      <?php if ($game) echo '<span class="badge badge--pale">'.esc_html($game).'</span>'; ?>
    </div>
  </div>
  <div class="file-card__actions">
    <span class="file-card__size"><?php echo esc_html($size); ?></span>
    <button class="download-btn" data-post-id="<?php the_ID(); ?>">Скачать</button>
    <span style="font-size:.65rem;color:var(--text3);"><?php echo esc_html($dl); ?> скач.</span>
  </div>
</div>
