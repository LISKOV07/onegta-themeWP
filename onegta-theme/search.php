<?php
get_header();
$query   = get_search_query();
$type    = sanitize_key($_GET['post_type'] ?? '');
$total   = $GLOBALS['wp_query']->found_posts;
$types   = [''=>'Всё','news'=>'📰 Новости','articles'=>'📖 Статьи','files'=>'📦 Файлы','videos'=>'🎬 Видео','post'=>'📝 Посты'];
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="section-label">Поиск</div>
    <h1 class="page-hero__title">«<?php echo esc_html($query); ?>»</h1>
    <p style="margin-top:.4rem;"><?php echo esc_html($total); ?> результатов</p>
  </div>
</section>

<div class="filter-bar">
  <div class="container filter-bar__inner">
    <?php foreach ($types as $key => $label) :
      $active = ($type === $key);
      $url    = add_query_arg(['s'=>$query,'post_type'=>$key], home_url('/'));
    ?>
      <a href="<?php echo esc_url($url); ?>" class="filter-btn <?php echo $active?'active':''; ?>"><?php echo esc_html($label); ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="posts-section">
  <div class="container">
    <?php if (have_posts()) : ?>
      <div class="posts-grid">
        <?php while (have_posts()) : the_post(); ?>
          <?php
          $pt = get_post_type();
          if ($pt === 'files')  get_template_part('template-parts/cards/file-card');
          elseif ($pt === 'videos') get_template_part('template-parts/cards/video-card');
          else get_template_part('template-parts/cards/post-card');
          ?>
        <?php endwhile; ?>
      </div>
      <?php the_posts_pagination(['prev_text'=>'←','next_text'=>'→','mid_size'=>2]); ?>
    <?php else : ?>
      <div style="padding:4rem 0;text-align:center;">
        <div style="font-family:'Bebas Neue',sans-serif;font-size:5rem;color:var(--border-d);letter-spacing:4px;margin-bottom:1rem;">🔍</div>
        <h2 style="margin-bottom:.8rem;">Ничего не найдено</h2>
        <p style="max-width:400px;margin:0 auto 2rem;">По запросу «<?php echo esc_html($query); ?>» ничего не нашлось. Попробуй другие слова.</p>
        <form role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get" style="display:flex;max-width:400px;margin:0 auto;">
          <input type="search" name="s" value="<?php echo esc_attr($query); ?>" class="form-input" placeholder="Поиск…">
          <button type="submit" class="btn btn--primary" style="white-space:nowrap;">Найти</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php get_footer(); ?>
