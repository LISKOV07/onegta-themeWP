<?php
/**
 * Template Name: Добавить материал
 */
get_header();
if (!is_user_logged_in()) {
    wp_redirect(home_url('/'));
    exit;
}
$active_type = sanitize_key($_GET['type'] ?? 'news');
$news_cats   = get_terms(['taxonomy'=>'news_category','hide_empty'=>false]);
$game_titles = get_terms(['taxonomy'=>'game_title','hide_empty'=>false]);
$file_cats   = get_terms(['taxonomy'=>'file_category','hide_empty'=>false]);
$vid_cats    = get_terms(['taxonomy'=>'video_category','hide_empty'=>false]);
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="section-label">Контент</div>
    <h1 class="page-hero__title">Добавить материал</h1>
    <p style="max-width:500px;margin-top:.5rem;">Поделись с сообществом — добавь новость, статью, файл или видео.</p>
  </div>
</section>

<div class="submit-page">
  <div class="container--narrow">

    <!-- Type selector -->
    <div class="filter-bar__inner" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:2rem;">
      <?php
      $types = ['news'=>'📰 Новость','articles'=>'📖 Статья','files'=>'📦 Файл','videos'=>'🎬 Видео'];
      foreach ($types as $type_key => $type_label) :
      ?>
        <a href="?type=<?php echo esc_attr($type_key); ?>"
           class="filter-btn <?php echo $active_type === $type_key ? 'active' : ''; ?>">
          <?php echo esc_html($type_label); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div id="submitAlert"></div>

    <?php if ($active_type === 'files') :
      $yd_configured = onegta_yadisk()->is_configured();
    ?>
    <!-- FILE FORM -->
    <div class="submit-form-wrap">
      <h2>Загрузить файл</h2>

      <?php if ($yd_configured) : ?>
        <div class="alert alert--success" style="margin-bottom:1.5rem;">
          ☁️ Файлы загружаются на <strong>Яндекс.Диск</strong> — место на хостинге не расходуется
        </div>
      <?php else : ?>
        <div class="alert alert--info" style="margin-bottom:1.5rem;">
          ℹ️ Яндекс.Диск не настроен. Используй внешнюю ссылку или <a href="<?php echo esc_url(admin_url('options-general.php?page=onegta-yadisk')); ?>">настрой интеграцию</a>.
        </div>
      <?php endif; ?>

      <form id="submitFileForm" enctype="multipart/form-data">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('onegta_nonce'); ?>">
        <input type="hidden" name="_yadisk_mode" value="<?php echo $yd_configured ? '1' : '0'; ?>">

        <div class="submit-section">
          <div class="submit-section__title">Основная информация</div>
          <div class="form-group">
            <label class="form-label" for="fileTitle">Название файла *</label>
            <input class="form-input" type="text" id="fileTitle" name="title" required placeholder="Например: NaturalVision Remastered v3.0">
          </div>
          <div class="form-group">
            <label class="form-label" for="fileCategory">Категория *</label>
            <select class="form-input" id="fileCategory" name="file_category" required>
              <option value="">— Выберите категорию —</option>
              <?php foreach ((array)$file_cats as $fc) :
                $pad = str_repeat('&nbsp;&nbsp;', $fc->parent ? 1 : 0);
              ?>
                <option value="<?php echo esc_attr($fc->term_id); ?>"><?php echo $pad.esc_html($fc->name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
              <label class="form-label" for="fileGame">Для игры</label>
              <select class="form-input" id="fileGame" name="file_game">
                <option value="">— Для любой —</option>
                <?php foreach (['GTA VI','GTA V','GTA IV','San Andreas','Vice City','GTA III'] as $gn) :
                  echo '<option value="'.esc_attr($gn).'">'.esc_html($gn).'</option>';
                endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="fileVersion">Версия</label>
              <input class="form-input" type="text" id="fileVersion" name="file_version" placeholder="1.0.0">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="fileDesc">Описание</label>
            <textarea class="form-input" id="fileDesc" name="content" rows="5" placeholder="Что делает этот файл? Как установить?"></textarea>
          </div>
        </div>

        <div class="submit-section">
          <div class="submit-section__title">
            <?php echo $yd_configured ? '☁️ Загрузка на Яндекс.Диск' : 'Файл'; ?>
          </div>

          <?php if ($yd_configured) : ?>
            <!-- ЯД загрузка -->
            <div class="form-group">
              <div class="file-upload-area" id="fileDropArea">
                <div class="file-upload-area__icon">☁️</div>
                <div class="file-upload-area__text">Перетащи файл или нажми для выбора</div>
                <div class="file-upload-area__hint">Макс. 100 MB · ZIP, RAR, 7Z, EXE, DLL, ASI, OIV</div>
              </div>
              <input type="file" id="fileInput" name="file_upload" style="display:none;"
                     accept=".zip,.rar,.7z,.exe,.dll,.asi,.oiv,.cs,.lua,.xml,.cfg,.ini,.txt">
              <div id="filePreview"></div>
              <!-- Прогресс загрузки -->
              <div id="yadiskProgress" style="display:none;margin-top:.8rem;">
                <div style="background:var(--bg);border:1px solid var(--border);height:6px;overflow:hidden;">
                  <div id="yadiskProgressBar" style="height:100%;background:var(--orange);width:0%;transition:width .3s;"></div>
                </div>
                <div id="yadiskProgressText" style="font-size:.78rem;color:var(--text3);margin-top:.3rem;text-align:center;">Загрузка на Яндекс.Диск…</div>
              </div>
            </div>
            <div class="form-hint" style="margin-top:-.6rem;">— или укажи внешнюю ссылку —</div>
          <?php else : ?>
            <div class="form-group">
              <div class="file-upload-area" id="fileDropArea">
                <div class="file-upload-area__icon">📦</div>
                <div class="file-upload-area__text">Перетащи файл сюда или нажми для выбора</div>
                <div class="file-upload-area__hint">Макс. 64MB · ZIP, RAR, 7Z, EXE, DLL</div>
              </div>
              <input type="file" id="fileInput" name="file_upload" style="display:none;"
                     accept=".zip,.rar,.7z,.exe,.dll,.asi,.oiv">
              <div id="filePreview"></div>
            </div>
          <?php endif; ?>

          <div class="form-group" style="margin-top:.8rem;">
            <label class="form-label" for="fileUrlAlt">Внешняя ссылка на файл</label>
            <input class="form-input" type="url" id="fileUrlAlt" name="file_url"
                   placeholder="https://... (если не загружаешь файл выше)">
          </div>
        </div>

        <div class="submit-section">
          <div class="submit-section__title">Скриншот / превью</div>
          <div class="form-group">
            <div class="file-upload-area" id="thumbDropArea">
              <div class="file-upload-area__icon">🖼️</div>
              <div class="file-upload-area__text">Загрузи превью-изображение</div>
              <div class="file-upload-area__hint">JPG, PNG · Рекомендуется 600×380</div>
            </div>
            <input type="file" id="thumbInput" name="thumbnail" style="display:none;" accept="image/*">
            <div id="thumbPreview"></div>
          </div>
        </div>

        <button type="submit" class="btn btn--primary btn--full" id="submitFileBtn">
          <span><?php echo $yd_configured ? '☁️ Загрузить на Яндекс.Диск' : 'Загрузить файл'; ?></span>
        </button>
      </form>
    </div>

    <?php elseif ($active_type === 'videos') : ?>
    <!-- VIDEO FORM -->
    <div class="submit-form-wrap">
      <h2>Добавить видео</h2>
      <form id="submitPostForm" data-post-type="videos">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('onegta_nonce'); ?>">
        <input type="hidden" name="post_type" value="videos">

        <div class="submit-section">
          <div class="submit-section__title">Основное</div>
          <div class="form-group">
            <label class="form-label" for="vidTitle">Заголовок *</label>
            <input class="form-input" type="text" id="vidTitle" name="title" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="vidUrl">YouTube / Rutube ссылка *</label>
            <input class="form-input" type="url" id="vidUrl" name="video_url" required placeholder="https://youtube.com/watch?v=...">
          </div>
          <div class="form-group">
            <label class="form-label" for="vidCat">Категория</label>
            <select class="form-input" id="vidCat" name="category">
              <option value="">— Выберите —</option>
              <?php foreach ((array)$vid_cats as $vc) echo '<option value="'.esc_attr($vc->term_id).'">'.esc_html($vc->name).'</option>'; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="vidDesc">Описание</label>
            <textarea class="form-input" id="vidDesc" name="content" rows="4"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="vidTags">Теги (через запятую)</label>
            <input class="form-input" type="text" id="vidTags" name="tags" placeholder="GTA V, геймплей, моды">
          </div>
        </div>

        <div class="submit-section">
          <div class="submit-section__title">Превью</div>
          <div class="form-group">
            <div class="file-upload-area" id="vidThumbArea">
              <div class="file-upload-area__icon">🎬</div>
              <div class="file-upload-area__text">Загрузи превью (необязательно)</div>
            </div>
            <input type="file" id="vidThumbInput" name="thumbnail" style="display:none;" accept="image/*">
            <div id="vidThumbPreview"></div>
          </div>
        </div>

        <button type="submit" class="btn btn--primary btn--full">Добавить видео</button>
      </form>
    </div>

    <?php else : ?>
   <!-- NEWS / ARTICLES FORM -->
    <div class="submit-form-wrap">
      <h2><?php echo $active_type === 'articles' ? 'Написать статью' : 'Добавить новость'; ?></h2>
      <form id="submitPostForm" data-post-type="<?php echo esc_attr($active_type); ?>" enctype="multipart/form-data">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('onegta_nonce'); ?>">
        <input type="hidden" name="post_type" value="<?php echo esc_attr($active_type); ?>">

        <div class="submit-section">
          <div class="submit-section__title">Заголовок и категория</div>
          <div class="form-group">
            <label class="form-label" for="postTitle">Заголовок *</label>
            <input class="form-input" type="text" id="postTitle" name="title" required placeholder="Заголовок материала">
          </div>
          <div style="display:grid;grid-template-columns:<?php echo $active_type==='articles' ? '1fr 1fr 1fr':'1fr'; ?>;gap:1rem;">
            <div class="form-group">
              <label class="form-label" for="postCat">Раздел</label>
              <select class="form-input" id="postCat" name="category">
                <option value="">— Без раздела —</option>
                <?php
                $cats_list = $active_type === 'articles' ? (array)$game_titles : (array)$news_cats;
                foreach ($cats_list as $c) echo '<option value="'.esc_attr($c->term_id).'">'.esc_html($c->name).'</option>';
                ?>
              </select>
            </div>
            <?php if ($active_type === 'articles') : ?>
              <div class="form-group">
                <label class="form-label" for="artType">Тип</label>
                <select class="form-input" id="artType" name="article_type">
                  <?php foreach (['Гайд','Прохождение','Читы','Обзор','Список', 'Туториалы'] as $o) echo '<option>'.esc_html($o).'</option>'; ?>
                </select>
              </div>
              <div class="form-group" id="artDiffGroup">
  <label class="form-label" for="artDiff">Сложность</label>
  <select class="form-input" id="artDiff" name="article_difficulty">
    <?php foreach (['Любой','Новичок','Средний','Эксперт'] as $o) echo '<option value="'.esc_attr($o).'">'.esc_html($o).'</option>'; ?>
  </select>
</div>

<?php if ($active_type === 'articles') : ?>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const artType = document.getElementById('artType');
    const artDiffGroup = document.getElementById('artDiffGroup');

    if (artType && artDiffGroup) {
        function checkType() {
            const val = artType.value;
            // Показываем только если Гайд или Прохождение
            if (val === 'Гайд' || val === 'Прохождение') {
                artDiffGroup.style.visibility = 'visible';
                artDiffGroup.style.opacity = '1';
                artDiffGroup.style.pointerEvents = 'auto';
            } else {
                // Вместо display:none используем visibility, чтобы сетка 1fr 1fr 1fr не схлопнулась
                artDiffGroup.style.visibility = 'hidden';
                artDiffGroup.style.opacity = '0';
                artDiffGroup.style.pointerEvents = 'none';
            }
        }
        artType.addEventListener('change', checkType);
        checkType(); // Запуск при загрузке
    }
});
</script>
<?php endif; ?>
            <?php endif; ?>
          </div>
        </div>


        <div class="submit-section">
          <div class="submit-section__title">Превью изображение</div>
          <div class="form-group">
            <div class="file-upload-area" id="postThumbArea">
              <div class="file-upload-area__icon">🖼️</div>
              <div class="file-upload-area__text">Загрузи обложку материала</div>
              <div class="file-upload-area__hint">JPG, PNG · Рекомендуется 1200×600</div>
            </div>
            <input type="file" id="postThumbInput" name="thumbnail" style="display:none;" accept="image/*">
            <div id="postThumbPreview"></div>
          </div>
        </div>

        <div class="submit-section">
          <div class="submit-section__title">Краткое описание</div>
          <div class="form-group">
            <textarea class="form-input" name="excerpt" rows="3" placeholder="Краткое описание (отображается в превью)"></textarea>
          </div>
        </div>

        <div class="submit-section">
          <div class="submit-section__title">Содержание</div>
          <div class="form-group">
            <textarea class="form-input" id="postContent" name="content" rows="15" placeholder="Основной текст материала..."></textarea>
            <div class="form-hint">Поддерживается базовая разметка: **жирный**, *курсив*, ## Заголовок</div>
          </div>
        </div>

        <div class="submit-section">
          <div class="submit-section__title">Теги</div>
          <div class="form-group">
            <input class="form-input" type="text" name="tags" placeholder="GTA V, гайд, оружие... (через запятую)">
          </div>
        </div>

        <button type="submit" class="btn btn--primary btn--full" id="submitPostBtn">
          <?php echo $active_type === 'articles' ? 'Опубликовать статью' : 'Опубликовать новость'; ?>
        </button>
        <p style="text-align:center;font-size:.78rem;color:var(--text3);margin-top:.8rem;">
          Материал <?php echo current_user_can('publish_posts') ? 'будет опубликован сразу' : 'пройдёт модерацию перед публикацией'; ?>
        </p>
      </form>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php get_footer(); ?>
