<?php
get_header();
the_post();
$pt    = get_post_type();
$tax   = ['news'=>'news_category','articles'=>'game_title','post'=>'category'][$pt] ?? 'category';
$cats  = get_the_terms(get_the_ID(), $tax) ?: [];
$cat   = $cats && !is_wp_error($cats) ? $cats[0] : null;
$tags  = get_the_terms(get_the_ID(), 'gta_tag') ?: [];
$author= get_userdata(get_the_author_meta('ID'));
?>

<div class="page-hero" style="padding-bottom:30px;">
  <div class="container page-hero__inner">
    <?php onegta_breadcrumb(); ?>
  </div>
</div>

<main class="single-layout">
  <div class="container">
    <div class="single-layout__inner">

      <article>
        <header class="single-post__header">
          <?php if ($cat) : ?>
            <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="single-post__cat"><?php echo esc_html($cat->name); ?></a>
          <?php endif; ?>
          <h1 class="single-post__title"><?php the_title(); ?></h1>
          <div class="single-post__meta">
            <a href="<?php echo esc_url(onegta_profile_url(get_the_author_meta('ID'))); ?>" class="single-post__author-link">
              <div class="post-card__author-avatar">
                <img src="<?php echo esc_url(onegta_avatar_url(get_the_author_meta('ID'))); ?>" alt="">
              </div>
              <?php the_author(); ?>
            </a>
            <span><?php echo esc_html(onegta_date()); ?></span>
            <?php if (comments_open()) : ?><span><?php comments_number('0 комментариев','1 комментарий','% комментариев'); ?></span><?php endif; ?>
            <?php if ($pt === 'articles') :
              $diff = get_post_meta(get_the_ID(), 'article_difficulty', true);
              $type = get_post_meta(get_the_ID(), 'article_type', true);
              if ($diff) echo '<span class="badge badge--pale">'.esc_html($diff).'</span>';
              if ($type) echo '<span class="badge badge--dark">'.esc_html($type).'</span>';
            endif; ?>
          </div>
        </header>

        <?php if (has_post_thumbnail()) : ?>
          <div class="single-post__featured-img">
            <?php the_post_thumbnail('onegta-featured', ['alt'=>esc_attr(get_the_title())]); ?>
          </div>
        <?php endif; ?>

        <div class="entry-content"><?php the_content(); ?></div>

        <?php if ($tags) : ?>
          <div class="entry-tags">
            <span>Теги:</span>
            <?php foreach ($tags as $tag) : ?>
              <a href="<?php echo esc_url(get_term_link($tag)); ?>"><?php echo esc_html($tag->name); ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Related -->
        <?php
        $related_args = ['numberposts'=>3,'post_type'=>$pt,'post__not_in'=>[get_the_ID()],'post_status'=>'publish'];
        if ($cat) $related_args['tax_query'] = [['taxonomy'=>$tax,'field'=>'term_id','terms'=>$cat->term_id]];
        $related = get_posts($related_args);
        if ($related) :
        ?>
          <div style="margin-top:3rem;">
            <div class="section-label">Читать ещё</div>
            <div class="posts-grid" style="margin-top:1.5rem;">
              <?php foreach ($related as $rp) :
                setup_postdata($rp);
                get_template_part('template-parts/cards/post-card');
              endforeach; wp_reset_postdata(); ?>
            </div>
          </div>
        <?php endif; ?>

        <?php onegta_comments_template(); ?>
      </article>

      <!-- SIDEBAR -->
      <aside class="sidebar">
        <div class="sidebar-widget">
          <div class="sidebar-widget__title">Поиск</div>
          <div class="sidebar-widget__body">
            <form class="sidebar-search" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get">
              <input type="search" name="s" placeholder="Найти…" value="<?php echo esc_attr(get_search_query()); ?>" aria-label="Поиск">
              <button type="submit" aria-label="Найти">→</button>
            </form>
          </div>
        </div>

        <div class="sidebar-widget">
          <div class="sidebar-widget__title">Свежее</div>
          <div class="sidebar-widget__body">
            <?php foreach (onegta_recent(5, $pt) as $rp) : ?>
              <div class="sidebar-recent-item">
                <?php if (has_post_thumbnail($rp->ID)) : ?>
                  <a href="<?php echo esc_url(get_permalink($rp)); ?>" class="sidebar-recent-item__thumb">
                    <?php echo get_the_post_thumbnail($rp->ID, 'onegta-thumb', ['alt'=>'']); ?>
                  </a>
                <?php endif; ?>
                <div>
                  <a href="<?php echo esc_url(get_permalink($rp)); ?>" class="sidebar-recent-item__title"><?php echo esc_html($rp->post_title); ?></a>
                  <div class="sidebar-recent-item__date"><?php echo esc_html(onegta_date($rp->ID)); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if ($tags) : ?>
          <div class="sidebar-widget">
            <div class="sidebar-widget__title">Теги</div>
            <div class="sidebar-widget__body">
              <div class="tag-cloud">
                <?php foreach ($tags as $t) : ?>
                  <a href="<?php echo esc_url(get_term_link($t)); ?>"><?php echo esc_html($t->name); ?></a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php
        $sidebars = ['news'=>'sidebar-news','articles'=>'sidebar-articles','post'=>'sidebar-articles'];
        $sb = $sidebars[$pt] ?? 'sidebar-news';
        if (is_active_sidebar($sb)) dynamic_sidebar($sb);
        ?>
      </aside>

    </div>
  </div>
</main>

<?php get_footer(); ?>
