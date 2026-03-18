<?php get_header(); ?>

<!-- TICKER -->
<div class="ticker"><div class="ticker__inner">
<?php $items = array_merge(onegta_ticker(), onegta_ticker());
foreach ($items as $t) echo '<span class="ticker__item">'.esc_html($t).'</span>'; ?>
</div></div>

<!-- HERO -->
<section class="hero" aria-labelledby="heroTitle">
  <div class="hero__inner">
    <div class="hero__content">
      <div class="hero__eyebrow">Фан-портал №1 по Grand Theft Auto</div>
      <h1 class="hero__title" id="heroTitle">Grand <span class="accent">Theft</span> Auto</h1>
      <p class="hero__desc">Всё о вселенной GTA — гайды, читы, новости, файлы и живое сообщество тысяч игроков</p>
      <div class="hero__cta">
        <a href="<?php echo esc_url(home_url('/news/')); ?>" class="btn btn--primary">Новости</a>
        <a href="<?php echo esc_url(home_url('/files/')); ?>" class="btn btn--ghost">Файлы</a>
      </div>
    </div>
    <div class="hero__right">
      <div class="hero__stats">
        <div class="hero__stat"><div class="hero__stat-num">8</div><div class="hero__stat-lbl">Частей серии</div></div>
        <div class="hero__stat"><div class="hero__stat-num"><?php echo esc_html(number_format(wp_count_posts('news')->publish + wp_count_posts()->publish)); ?></div><div class="hero__stat-lbl">Материалов</div></div>
        <div class="hero__stat"><div class="hero__stat-num">2025</div><div class="hero__stat-lbl">GTA VI</div></div>
      </div>
      <div class="hero__visual">
        <div class="hero__visual-num" aria-hidden="true">VI</div>
        <div class="hero__visual-label">
          <div class="hero__visual-label-top">Выходит в 2025</div>
          <div class="hero__visual-label-title">Grand Theft Auto VI</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- GAMES ACCORDION -->
<section class="games-section" aria-labelledby="gamesTitle">
  <div class="container">
    <div class="section-label">Серия</div>
    <h2 class="section-title" id="gamesTitle">Все части GTA</h2>
    <div class="games-grid">
      <?php
      $games_data = [
        ['roman'=>'VI', 'year'=>'2025','name'=>'GTA VI',         'art'=>'vi', 'badge'=>'Скоро',    'desc'=>'Возвращение в Вайс-Сити. Два протагониста, новый масштаб.','slug'=>'gta-6'],
        ['roman'=>'V',  'year'=>'2013','name'=>'GTA V',          'art'=>'v',  'badge'=>'Культ',    'desc'=>'Лос-Сантос, три героя, один из самых продаваемых тайтлов.','slug'=>'gta-5'],
        ['roman'=>'IV', 'year'=>'2008','name'=>'GTA IV',         'art'=>'iv', 'badge'=>'Классика', 'desc'=>'Либерти-Сити, Нико Беллик и история про американскую мечту.','slug'=>'gta-4'],
        ['roman'=>'SA', 'year'=>'2004','name'=>'San Andreas',    'art'=>'sa', 'badge'=>'Легенда',  'desc'=>'Сиджей, Грув-стрит и культовый саундтрек 90-х.','slug'=>'gta-san-andreas'],
        ['roman'=>'VC', 'year'=>'2002','name'=>'Vice City',      'art'=>'vc', 'badge'=>'Иконика',  'desc'=>'Томми Версетти и неоновый Майами 80-х.','slug'=>'gta-vice-city'],
        ['roman'=>'III','year'=>'2001','name'=>'GTA III',        'art'=>'iii','badge'=>'Революция','desc'=>'Первый 3D GTA. Клод и Либерти-Сити навсегда изменили жанр.','slug'=>'gta-3'],
      ];
      foreach ($games_data as $g) :
        $term = get_term_by('slug', $g['slug'], 'game_title');
        $link = $term ? get_term_link($term) : home_url('/articles/?game='.$g['slug']);
      ?>
        <div class="game-card">
          <div class="game-card__art-bg art-<?php echo esc_attr($g['art']); ?>"></div>
          <div class="game-card__roman" aria-hidden="true"><?php echo esc_html($g['roman']); ?></div>
          <div class="game-card__vert"><?php echo esc_html($g['name']); ?></div>
          <div class="game-card__info">
            <div class="game-card__year"><?php echo esc_html($g['year']); ?></div>
            <div class="game-card__name"><?php echo nl2br(esc_html(str_replace(' ',"\n",$g['name']))); ?></div>
            <p class="game-card__desc"><?php echo esc_html($g['desc']); ?></p>
            <span class="game-card__badge-el"><?php echo esc_html($g['badge']); ?></span>
            <a href="<?php echo esc_url($link); ?>" class="game-card__cta-link">Статьи</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- NEWS -->
<section class="news-section" aria-labelledby="newsTitle">
  <div class="container">
    <div class="news-header">
      <div><div class="section-label">Последнее</div><h2 class="section-title" id="newsTitle">Новости</h2></div>
      <a href="<?php echo esc_url(home_url('/news/')); ?>" class="btn btn--ghost btn--sm">Все новости</a>
    </div>
    <div class="news-grid">
      <?php
      $news_posts = get_posts(['numberposts'=>3,'post_type'=>'news','post_status'=>'publish']);
      if (!$news_posts) $news_posts = get_posts(['numberposts'=>3,'post_status'=>'publish']);
      foreach ($news_posts as $i => $np) :
        setup_postdata($np);
        $cats   = get_the_terms($np->ID, 'news_category') ?: get_the_category($np->ID);
        $cat_nm = $cats && !is_wp_error($cats) ? $cats[0]->name : 'Новость';
      ?>
        <article class="news-card <?php echo $i===0 ? 'news-card--featured' : ''; ?>">
          <div class="news-card__stripe"></div>
          <a href="<?php echo esc_url(get_permalink($np)); ?>" tabindex="-1" aria-hidden="true">
            <div class="news-card__img">
              <?php if (has_post_thumbnail($np->ID)) : ?>
                <?php echo get_the_post_thumbnail($np->ID, 'onegta-card', ['alt'=>esc_attr(get_the_title($np))]); ?>
              <?php else : ?>
                <div class="news-card__img-placeholder">GTA</div>
              <?php endif; ?>
              <span class="news-card__img-tag"><?php echo esc_html($cat_nm); ?></span>
            </div>
          </a>
          <div class="news-card__body">
            <div class="news-card__cat"><?php echo esc_html($cat_nm); ?></div>
            <h3 class="news-card__title"><a href="<?php echo esc_url(get_permalink($np)); ?>"><?php echo esc_html(get_the_title($np)); ?></a></h3>
            <?php if ($i===0) echo '<p class="news-card__excerpt">'.esc_html(get_the_excerpt($np)).'</p>'; ?>
            <div class="news-card__meta"><?php echo esc_html(onegta_date($np->ID)); ?></div>
          </div>
        </article>
      <?php endforeach; wp_reset_postdata(); ?>
    </div>
  </div>
</section>

<!-- FEATURES -->
<div class="features-strip" aria-label="Разделы портала">
  <?php
  $feats = [
    ['icon'=>'📰','title'=>'Новости',    'desc'=>'Свежие новости о GTA VI, Online и всей серии'],
    ['icon'=>'📖','title'=>'Статьи',     'desc'=>'Гайды, прохождения и читы по каждой игре'],
    ['icon'=>'📦','title'=>'Файловый архив','desc'=>'Моды, патчи, трейнеры и инструменты'],
    ['icon'=>'🎬','title'=>'Видео',      'desc'=>'Трейлеры, геймплей и обзоры от комьюнити'],
  ];
  foreach ($feats as $f) :
  ?>
    <div class="feature-item">
      <div class="feature-item__icon"><?php echo $f['icon']; ?></div>
      <div class="feature-item__line"></div>
      <div class="feature-item__title"><?php echo esc_html($f['title']); ?></div>
      <p class="feature-item__desc"><?php echo esc_html($f['desc']); ?></p>
    </div>
  <?php endforeach; ?>
</div>

<!-- FILES PREVIEW -->
<section class="files-section" aria-labelledby="filesTitle">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
      <div><div class="section-label">Архив</div><h2 class="section-title" id="filesTitle">Свежие файлы</h2></div>
      <a href="<?php echo esc_url(home_url('/files/')); ?>" class="btn btn--ghost btn--sm">Все файлы</a>
    </div>
    <div class="files-grid">
      <?php
      $files = get_posts(['numberposts'=>6,'post_type'=>'files','post_status'=>'publish']);
      if ($files) :
        foreach ($files as $file) :
          $size = get_post_meta($file->ID, 'file_size', true) ?: '—';
          $game = get_post_meta($file->ID, 'file_game', true) ?: '';
          $dl   = (int)get_post_meta($file->ID, 'file_downloads', true);
          $cats = get_the_terms($file->ID, 'file_category');
          $cat  = $cats && !is_wp_error($cats) ? $cats[0]->name : 'Файл';
      ?>
          <div class="file-card">
            <div class="file-card__icon">📦</div>
            <div class="file-card__info">
              <a href="<?php echo esc_url(get_permalink($file)); ?>" class="file-card__name"><?php echo esc_html($file->post_title); ?></a>
              <div class="file-card__meta"><?php echo esc_html($cat); ?><?php if ($game) echo ' · '.esc_html($game); ?> · <?php echo esc_html(onegta_date($file->ID)); ?></div>
              <div class="file-card__tags">
                <?php if ($game) echo '<span class="badge badge--pale">'.esc_html($game).'</span>'; ?>
              </div>
            </div>
            <div class="file-card__actions">
              <span class="file-card__size"><?php echo esc_html($size); ?></span>
              <button class="download-btn" data-post-id="<?php echo esc_attr($file->ID); ?>">Скачать</button>
              <span style="font-size:.65rem;color:var(--text3);"><?php echo esc_html($dl); ?> скач.</span>
            </div>
          </div>
        <?php endforeach;
      else :
        echo '<p style="color:var(--text3);padding:1rem 0;">Файлы ещё не добавлены.</p>';
      endif;
      ?>
    </div>
  </div>
</section>

<!-- COMMUNITY -->
<section class="community-section" aria-labelledby="communityTitle">
  <div class="container">
    <div class="community-section__inner">
      <div>
        <div class="section-label">Комьюнити</div>
        <h2 class="section-title" id="communityTitle">Люди говорят</h2>
        <p style="margin:1rem 0 2rem;max-width:400px;line-height:1.8;">Тысячи игроков уже нашли тут всё что нужно. Присоединяйся.</p>
        <?php if (is_user_logged_in()) : ?>
          <a href="<?php echo esc_url(home_url('/submit/')); ?>" class="btn btn--primary">Добавить материал</a>
        <?php else : ?>
          <button class="btn btn--primary" id="communityJoinBtn">Зарегистрироваться</button>
        <?php endif; ?>
      </div>
      <div class="quote-stack" aria-label="Отзывы участников">
        <?php
        $quotes=[
          ['user'=>'ARTEM_GTA','text'=>'Лучший сайт по GTA — нашёл всё что нужно за 5 минут 🔥'],
          ['user'=>'KILLER228','text'=>'Гайд по ограблениям мастхэв, прошёл Cayo Perico с первого раза'],
          ['user'=>'SANCHECK', 'text'=>'San Andreas — всё ещё топ. Гайд тут самый полный что я находил'],
          ['user'=>'VICEBOY',  'text'=>'Жду GTA VI вместе с этими ребятами — лучшие новости первыми 🙌'],
        ];
        foreach ($quotes as $q) :
        ?>
          <div class="quote-card">
            <div class="quote-card__user"><?php echo esc_html($q['user']); ?></div>
            <p class="quote-card__text"><?php echo esc_html($q['text']); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
