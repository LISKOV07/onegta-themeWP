<?php
get_header(); the_post();
$file_url  = get_post_meta(get_the_ID(), 'file_url',  true);
$file_size = get_post_meta(get_the_ID(), 'file_size', true) ?: '—';
$version   = get_post_meta(get_the_ID(), 'file_version', true);
$game      = get_post_meta(get_the_ID(), 'file_game',  true);
$downloads = (int)get_post_meta(get_the_ID(), 'file_downloads', true);
$cats      = get_the_terms(get_the_ID(), 'file_category') ?: [];
$cat       = $cats && !is_wp_error($cats) ? $cats[0] : null;
$tags      = get_the_terms(get_the_ID(), 'gta_tag') ?: [];
?>
<div class="page-hero" style="padding-bottom:30px;">
  <div class="container page-hero__inner"><?php onegta_breadcrumb(); ?></div>
</div>
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
            <span><?php echo esc_html($downloads); ?> скачиваний</span>
          </div>
        </header>

        <!-- Download card -->
        <div style="background:var(--orange-pale);border:1px solid var(--orange-mid);padding:2rem;margin-bottom:2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
          <div>
            <div style="font-family:'Orbitron',monospace;font-size:.6rem;letter-spacing:4px;color:var(--orange);margin-bottom:.5rem;">ФАЙЛ</div>
            <div style="font-family:'Bebas Neue',sans-serif;font-size:1.8rem;letter-spacing:2px;color:var(--text);"><?php the_title(); ?></div>
            <div style="display:flex;gap:1rem;margin-top:.5rem;flex-wrap:wrap;">
              <span class="badge badge--pale"><?php echo esc_html($file_size); ?></span>
              <?php if ($version) echo '<span class="badge badge--pale">v'.esc_html($version).'</span>'; ?>
              <?php if ($game) echo '<span class="badge badge--orange">'.esc_html($game).'</span>'; ?>
            </div>
          </div>
          <?php if ($file_url) : ?>
            <button class="btn btn--primary" style="font-size:1.2rem;padding:16px 40px;" data-post-id="<?php the_ID(); ?>" id="mainDownloadBtn">
              ⬇ Скачать
            </button>
          <?php endif; ?>
        </div>

        <?php if (has_post_thumbnail()) : ?>
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
        <div class="sidebar-widget"><div class="sidebar-widget__title">Информация</div><div class="sidebar-widget__body">
          <table style="width:100%">
            <tr style="border-bottom:1px solid var(--border)"><td style="padding:.5rem .3rem;font-size:.75rem;color:var(--text3);font-weight:700;">РАЗМЕР</td><td style="padding:.5rem .3rem;font-size:.85rem;color:var(--text);font-weight:600;"><?php echo esc_html($file_size); ?></td></tr>
            <?php if ($version) : ?><tr style="border-bottom:1px solid var(--border)"><td style="padding:.5rem .3rem;font-size:.75rem;color:var(--text3);font-weight:700;">ВЕРСИЯ</td><td style="padding:.5rem .3rem;font-size:.85rem;color:var(--text);font-weight:600;"><?php echo esc_html($version); ?></td></tr><?php endif; ?>
            <?php if ($game) : ?><tr style="border-bottom:1px solid var(--border)"><td style="padding:.5rem .3rem;font-size:.75rem;color:var(--text3);font-weight:700;">ИГРА</td><td style="padding:.5rem .3rem;font-size:.85rem;color:var(--text);font-weight:600;"><?php echo esc_html($game); ?></td></tr><?php endif; ?>
            <tr><td style="padding:.5rem .3rem;font-size:.75rem;color:var(--text3);font-weight:700;">СКАЧАНО</td><td style="padding:.5rem .3rem;font-size:.85rem;color:var(--orange);font-weight:700;"><?php echo esc_html($downloads); ?></td></tr>
          </table>
        </div></div>
        <div class="sidebar-widget"><div class="sidebar-widget__title">Похожие файлы</div><div class="sidebar-widget__body">
          <?php foreach (onegta_recent(5,'files') as $f) : ?>
            <?php if ($f->ID === get_the_ID()) continue; ?>
            <div class="sidebar-recent-item">
              <div><a href="<?php echo esc_url(get_permalink($f)); ?>" class="sidebar-recent-item__title"><?php echo esc_html($f->post_title); ?></a><div class="sidebar-recent-item__date"><?php echo esc_html(onegta_date($f->ID)); ?></div></div>
            </div>
          <?php endforeach; ?>
        </div></div>
      </aside>
    </div>
  </div>
</main>
<?php get_footer(); ?>
