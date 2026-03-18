<?php
/**
 * Template Name: Новая тема форума
 */
get_header();

if (!is_user_logged_in()) {
    wp_redirect(home_url('/forum/'));
    exit;
}

$sections       = get_terms(['taxonomy' => 'forum_section', 'hide_empty' => false]);
$preset_section = absint($_GET['section'] ?? 0);
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <?php onegta_breadcrumb(); ?>
    <div class="section-label">Форум</div>
    <h1 class="page-hero__title">Создать тему</h1>
  </div>
</section>

<div style="background:var(--bg);padding:2rem 0 4rem;">
<div class="container--narrow">
  <div id="newTopicAlert" style="margin-bottom:1rem;"></div>
  <div class="submit-form-wrap">
    <form id="newTopicForm">
      <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('onegta_nonce'); ?>">

      <div class="form-group">
        <label class="form-label" for="topicTitle">Заголовок темы *</label>
        <input class="form-input" type="text" id="topicTitle" name="title" required maxlength="200"
               placeholder="Кратко опиши о чём тема">
      </div>

      <div class="form-group">
        <label class="form-label" for="topicSection">Раздел *</label>
        <select class="form-input" id="topicSection" name="section" required>
          <option value="">— Выбери раздел —</option>
          <?php foreach ((array)$sections as $s) : ?>
            <option value="<?php echo esc_attr($s->term_id); ?>"
              <?php selected($preset_section, $s->term_id); ?>>
              <?php echo esc_html($s->name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="topicContent">Текст темы *</label>
        <textarea class="form-input" id="topicContent" name="content" rows="12" required
                  placeholder="Подробно опиши свою тему, вопрос или идею…"></textarea>
        <div class="form-hint">Поддерживается базовое форматирование</div>
      </div>

      <div class="form-group">
        <label class="form-label" for="topicTags">Теги (через запятую)</label>
        <input class="form-input" type="text" id="topicTags" name="tags"
               placeholder="gta v, совет, помощь…">
      </div>

      <div style="display:flex;align-items:center;gap:1rem;">
        <button type="submit" class="btn btn--primary">Создать тему</button>
        <a href="<?php echo esc_url(home_url('/forum/')); ?>" class="btn btn--ghost">Отмена</a>
      </div>
    </form>
  </div>
</div>
</div>

<?php get_footer(); ?>
