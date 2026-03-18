<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="cursor" id="cursor" aria-hidden="true"></div>
<div class="cursor-trail" id="cursorTrail" aria-hidden="true"></div>

<header class="site-header" id="siteHeader">
  <div class="container">
    <div class="site-header__inner">

      <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo">ONE<span>GTA</span></a>

      <nav class="main-nav" id="mainNav" aria-label="Основное меню">
        <?php wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'menu_class'     => 'main-nav__list',
          'walker'         => new OneGTA_Walker(),
          'fallback_cb'    => function() { ?>
            <ul class="main-nav__list">
              <li class="has-dropdown">
                <a href="<?php echo esc_url(home_url('/news/')); ?>">Новости</a>
                <div class="dropdown">
                  <?php
                  $ncats = get_terms(['taxonomy'=>'news_category','hide_empty'=>false,'number'=>8]);
                  foreach ((array)$ncats as $t) echo '<a href="'.esc_url(get_term_link($t)).'">'.esc_html($t->name).'</a>';
                  ?>
                </div>
              </li>
              <li class="has-dropdown">
                <a href="<?php echo esc_url(home_url('/articles/')); ?>">Статьи</a>
                <div class="dropdown">
                  <?php
                  $games = get_terms(['taxonomy'=>'game_title','hide_empty'=>false]);
                  foreach ((array)$games as $g) echo '<a href="'.esc_url(get_term_link($g)).'">'.esc_html($g->name).'</a>';
                  ?>
                </div>
              </li>
              <li><a href="<?php echo esc_url(home_url('/files/')); ?>">Файлы</a></li>
              <li><a href="<?php echo esc_url(home_url('/videos/')); ?>">Видео</a></li>
              <li><a href="<?php echo esc_url(home_url('/cheats/')); ?>">Читы</a></li>
              <li><a href="<?php echo esc_url(home_url('/forum/')); ?>" class="nav-cta">GTA VI</a></li>
            </ul>
          <?php }
        ]); ?>
      </nav>

      <div class="header-actions">
        <!-- Live Search -->
        <div class="search-wrap" id="headerSearchWrap">
          <form role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get" id="headerSearchForm">
            <div style="display:flex;border:1px solid var(--border);background:var(--bg);transition:border-color .2s;" id="headerSearchBox">
              <input type="search" name="s" id="headerSearchInput" placeholder="Поиск…" value="<?php echo esc_attr(get_search_query()); ?>"
                style="background:none;border:none;padding:8px 12px;font-size:.85rem;color:var(--text);width:160px;font-family:'DM Sans',sans-serif;" autocomplete="off">
              <button type="submit" style="background:none;border:none;padding:8px 10px;cursor:pointer;color:var(--text3);font-size:.9rem;">🔍</button>
            </div>
          </form>
          <div class="search-results-dropdown" id="searchDropdown" style="display:none;"></div>
        </div>
        <?php if (is_user_logged_in()) :
          $user    = wp_get_current_user();
          $av_url  = onegta_avatar_url($user->ID);
          $initial = onegta_user_initial($user);
        ?>
          <div class="user-menu" id="userMenu">
            <div class="user-menu__trigger" id="userMenuTrigger" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
              <div class="user-avatar">
                <img src="<?php echo esc_url($av_url); ?>" alt="<?php echo esc_attr($user->display_name); ?>">
              </div>
              <span class="user-name"><?php echo esc_html($user->display_name); ?></span>
              <span aria-hidden="true">▾</span>
            </div>
            <div class="user-dropdown" id="userDropdown" role="menu">
              <div class="user-dropdown__header">
                <div class="user-dropdown__name"><?php echo esc_html($user->display_name); ?></div>
                <div class="user-dropdown__email"><?php echo esc_html($user->user_email); ?></div>
              </div>
              <a href="<?php echo esc_url(onegta_profile_url()); ?>" role="menuitem">👤 Мой профиль</a>
              <a href="<?php echo esc_url(home_url('/submit/')); ?>" role="menuitem">➕ Добавить материал</a>
              <a href="<?php echo esc_url(onegta_profile_url()); ?>?tab=posts" role="menuitem">📝 Мои материалы</a>
              <?php if (current_user_can('edit_posts')) : ?>
                <a href="<?php echo esc_url(admin_url()); ?>" role="menuitem">⚙️ Админ-панель</a>
              <?php endif; ?>
              <button class="logout-btn" id="logoutBtn" role="menuitem">🚪 Выйти</button>
            </div>
          </div>

        <?php else : ?>
          <button class="btn btn--ghost btn--sm" id="openAuthBtn" aria-haspopup="dialog">Войти</button>
          <button class="btn btn--primary btn--sm" id="openRegisterBtn" aria-haspopup="dialog">Регистрация</button>
        <?php endif; ?>
      </div>

      <button class="nav-toggle" id="navToggle" aria-controls="mainNav" aria-expanded="false" aria-label="Меню">
        <span></span><span></span><span></span>
      </button>

    </div>
  </div>
</header>

<!-- ── AUTH MODAL ────────────────────────────── -->
<?php if (!is_user_logged_in()) : ?>
<div class="auth-overlay" id="authOverlay" role="dialog" aria-modal="true" aria-label="Вход и регистрация">
  <div class="auth-modal" id="authModal">
    <div class="auth-modal__header">
      <div class="auth-modal__title">OneGTA</div>
      <div class="auth-modal__subtitle">Вход в аккаунт</div>
      <button class="auth-modal__close" id="authClose" aria-label="Закрыть">✕</button>
    </div>
    <div class="auth-modal__body">
      <div id="authAlert"></div>

      <div class="auth-tabs" role="tablist">
        <button class="auth-tab active" data-tab="login" role="tab" aria-selected="true">Вход</button>
        <button class="auth-tab" data-tab="register" role="tab" aria-selected="false">Регистрация</button>
      </div>

      <!-- LOGIN -->
      <form class="auth-form active" id="loginForm" data-tab="login" novalidate>
        <div class="form-group">
          <label class="form-label" for="loginUser">Логин или Email</label>
          <input class="form-input" type="text" id="loginUser" name="login" autocomplete="username" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="loginPass">Пароль</label>
          <input class="form-input" type="password" id="loginPass" name="password" autocomplete="current-password" required>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
          <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer;">
            <input type="checkbox" name="remember"> Запомнить меня
          </label>
          <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" style="font-size:.82rem;color:var(--orange);">Забыл пароль</a>
        </div>
        <button type="submit" class="btn btn--primary btn--full">Войти</button>
        <div class="auth-footer">Нет аккаунта? <a href="#" data-switch-tab="register">Зарегистрироваться</a></div>
      </form>

      <!-- REGISTER -->
      <form class="auth-form" id="registerForm" data-tab="register" novalidate>
        <div class="form-group">
          <label class="form-label" for="regUser">Логин</label>
          <input class="form-input" type="text" id="regUser" name="username" autocomplete="username" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="regEmail">Email</label>
          <input class="form-input" type="email" id="regEmail" name="email" autocomplete="email" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="regPass">Пароль</label>
          <input class="form-input" type="password" id="regPass" name="password" autocomplete="new-password" required>
          <div class="form-hint">Минимум 6 символов</div>
        </div>
        <div class="form-group">
          <label class="form-label" for="regConfirm">Подтвердите пароль</label>
          <input class="form-input" type="password" id="regConfirm" name="confirm" autocomplete="new-password" required>
        </div>
        <div style="margin-bottom:1rem;font-size:.78rem;color:var(--text3);">
          Регистрируясь, вы соглашаетесь с <a href="#" style="color:var(--orange);">правилами сайта</a>
        </div>
        <button type="submit" class="btn btn--primary btn--full">Создать аккаунт</button>
        <div class="auth-footer">Уже есть аккаунт? <a href="#" data-switch-tab="login">Войти</a></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
