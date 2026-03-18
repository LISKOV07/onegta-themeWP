<?php
/**
 * Template Name: Профиль пользователя
 */
get_header();

// Поддержка URL формата /profile/username
// Получаем ник из пути URL или GET параметра
$request_uri   = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$profile_base  = trim(parse_url(home_url('/profile/'), PHP_URL_PATH), '/');
$uri_username  = '';

// Извлекаем username из /profile/username
if (strpos($request_uri, $profile_base . '/') === 0) {
    $uri_username = sanitize_user(substr($request_uri, strlen($profile_base) + 1));
    $uri_username = trim($uri_username, '/');
}

// Приоритет: URL path > GET param > текущий юзер
$viewed_user_login = $uri_username
    ?: sanitize_text_field($_GET['user'] ?? '')
    ?: (is_user_logged_in() ? wp_get_current_user()->user_login : '');

if ($viewed_user_login) {
    $viewed_user = get_user_by('login', $viewed_user_login);
} else {
    $viewed_user = null;
}

if (!$viewed_user) {
    // Не залогинен и нет username — редирект на главную
    wp_redirect(home_url('/'));
    exit;
}

$is_own   = is_user_logged_in() && get_current_user_id() === $viewed_user->ID;
$tab      = sanitize_key($_GET['tab'] ?? 'posts');
$bio      = get_user_meta($viewed_user->ID, 'description', true);
$av_url   = onegta_avatar_url($viewed_user->ID);
$initial  = onegta_user_initial($viewed_user);

// Count user posts
$counts = [];
foreach (['post','news','articles','files','videos'] as $pt) {
    $q = new WP_Query(['post_type'=>$pt,'author'=>$viewed_user->ID,'post_status'=>'publish','posts_per_page'=>1]);
    $counts[$pt] = $q->found_posts;
}
$total = array_sum($counts);
?>

<!-- Profile header (dark) -->
<div class="profile-header" style="padding-top:3rem;">
  <div class="container">
    <div class="profile-header__inner">

      <div class="profile-avatar-wrap">
        <div class="profile-avatar">
          <img src="<?php echo esc_url($av_url); ?>" alt="<?php echo esc_attr($viewed_user->display_name); ?>">
        </div>
        <?php if ($is_own) : ?>
          <label class="profile-avatar-edit" title="Сменить аватар" for="avatarUploadInput">✎</label>
        <?php endif; ?>
      </div>

      <div class="profile-info">
        <div class="profile-info__role"><?php echo $is_own ? 'Мой профиль' : 'Участник'; ?></div>
        <div class="profile-info__name"><?php echo esc_html($viewed_user->display_name); ?></div>
        <div class="profile-info__meta">
          <span>📅 На сайте с <?php echo esc_html(date('M Y', strtotime($viewed_user->user_registered))); ?></span>
          <?php if ($bio) : ?><span>📝 <?php echo esc_html(wp_trim_words($bio, 10)); ?></span><?php endif; ?>
        </div>
      </div>

      <div class="profile-stats">
        <div class="profile-stat"><div class="profile-stat__num"><?php echo esc_html($total); ?></div><div class="profile-stat__lbl">Материалов</div></div>
        <div class="profile-stat"><div class="profile-stat__num"><?php echo esc_html($counts['news']); ?></div><div class="profile-stat__lbl">Новостей</div></div>
        <div class="profile-stat"><div class="profile-stat__num"><?php echo esc_html($counts['articles']); ?></div><div class="profile-stat__lbl">Статей</div></div>
        <div class="profile-stat"><div class="profile-stat__num"><?php echo esc_html($counts['files']); ?></div><div class="profile-stat__lbl">Файлов</div></div>
      </div>

    </div>
  </div>
</div>

<div class="profile-layout">
  <div class="container profile-content">

    <!-- Tabs -->
    <div class="profile-tabs" role="tablist">
      <button class="profile-tab-btn <?php echo $tab==='posts' ? 'active':'' ?>" data-tab="posts">Материалы</button>
      <?php if ($is_own) : ?>
        <button class="profile-tab-btn <?php echo $tab==='settings' ? 'active':'' ?>" data-tab="settings">Настройки</button>
        <button class="profile-tab-btn <?php echo $tab==='pending' ? 'active':'' ?>" data-tab="pending">Мои черновики</button>
      <?php endif; ?>
      <?php if (onegta_is_moderator()) : ?>
        <button class="profile-tab-btn <?php echo $tab==='moderate' ? 'active':'' ?>" data-tab="moderate">
          Проверить материалы
          <?php $pending_count = (new WP_Query(['post_status'=>'pending','post_type'=>['news','articles','files','videos'],'posts_per_page'=>1]))->found_posts;
          if ($pending_count) echo '<span style="background:var(--error);color:#fff;border-radius:50%;width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;margin-left:.4rem;">'.$pending_count.'</span>'; ?>
        </button>
      <?php endif; ?>
      <?php if (current_user_can('administrator')) : ?>
        <button class="profile-tab-btn <?php echo $tab==='users' ? 'active':'' ?>" data-tab="users">Пользователи</button>
      <?php endif; ?>
    </div>

    <!-- Posts tab -->
    <div class="profile-tab-panel <?php echo $tab==='posts' ? 'active':'' ?>" id="tab-posts">
      <?php
      // Находим ID страниц которые надо исключить (все pages автора)
      $exclude_pages = get_posts([
        'author'      => $viewed_user->ID,
        'post_type'   => 'page',
        'post_status' => ['publish','draft','pending'],
        'numberposts' => -1,
        'fields'      => 'ids',
      ]);

      $profile_posts = new WP_Query([
        'author'         => $viewed_user->ID,
        'post_status'    => 'publish',
        'posts_per_page' => 24,
        'post_type'      => ['post','news','articles','files','videos'],
        'post__not_in'   => $exclude_pages ?: [0],
        'orderby'        => 'date',
        'order'          => 'DESC',
      ]);

      if ($profile_posts->have_posts()) :
        $type_labels = ['news'=>'📰 Новость','articles'=>'📖 Статья','files'=>'📦 Файл','videos'=>'🎬 Видео','post'=>'📝 Пост'];
      ?>
        <div class="profile-posts-grid">
          <?php while ($profile_posts->have_posts()) : $profile_posts->the_post();
            $pt = get_post_type();
            if ($pt === 'files')       get_template_part('template-parts/cards/file-card');
            elseif ($pt === 'videos')  get_template_part('template-parts/cards/video-card');
            else                       get_template_part('template-parts/cards/post-card');
          endwhile; wp_reset_postdata(); ?>
        </div>
      <?php else : ?>
        <div class="alert alert--info">
          Пока нет опубликованных материалов.
          <?php if ($is_own) : ?>
            <a href="<?php echo esc_url(home_url('/submit/')); ?>">Добавить первый</a>!
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($is_own) : ?>

    <!-- Settings tab -->
    <div class="profile-tab-panel <?php echo $tab==='settings' ? 'active':'' ?>" id="tab-settings">
      <div class="submit-form-wrap" style="max-width:600px;">
        <h2>Настройки профиля</h2>
        <div id="profileAlert"></div>
        <form id="profileSettingsForm" enctype="multipart/form-data">
          <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('onegta_nonce'); ?>">
          <input type="file" id="avatarUploadInput" name="avatar" accept="image/*" style="display:none;">

          <div class="form-group">
            <label class="form-label" for="profileName">Отображаемое имя</label>
            <input class="form-input" type="text" id="profileName" name="display_name" value="<?php echo esc_attr($viewed_user->display_name); ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="profileEmail">Email</label>
            <input class="form-input" type="email" id="profileEmail" name="email" value="<?php echo esc_attr($viewed_user->user_email); ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="profileBio">О себе</label>
            <textarea class="form-input" id="profileBio" name="bio" rows="4"><?php echo esc_textarea($bio); ?></textarea>
          </div>
          <div class="submit-section">
            <div class="submit-section__title">Смена пароля</div>
            <div class="form-group">
              <label class="form-label" for="newPass">Новый пароль</label>
              <input class="form-input" type="password" id="newPass" name="new_password" placeholder="Оставьте пустым чтобы не менять">
            </div>
            <div class="form-group">
              <label class="form-label" for="confirmPass">Подтвердите пароль</label>
              <input class="form-input" type="password" id="confirmPass" name="confirm_password">
            </div>
          </div>
          <button type="submit" class="btn btn--primary">Сохранить изменения</button>
        </form>
      </div>
    </div>

    <!-- Pending tab -->
    <div class="profile-tab-panel <?php echo $tab==='pending' ? 'active':'' ?>" id="tab-pending">
      <?php
      $pending = get_posts(['author'=>get_current_user_id(),'post_status'=>'pending','numberposts'=>20,'post_type'=>['news','articles','files','videos']]);
      if ($pending) :
      ?>
        <div class="posts-grid">
          <?php foreach ($pending as $pp) : ?>
            <div class="post-card">
              <div class="post-card__stripe"></div>
              <div class="post-card__body">
                <div class="post-card__meta-top">
                  <span class="badge badge--pale"><?php echo esc_html(get_post_type_object($pp->post_type)->labels->singular_name); ?></span>
                  <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">На модерации</span>
                </div>
                <div class="post-card__title"><?php echo esc_html($pp->post_title); ?></div>
                <div class="post-card__meta"><?php echo esc_html(onegta_date($pp->ID)); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <div class="alert alert--info">Материалов на модерации нет.</div>
      <?php endif; ?>
    </div>

    <?php endif; // is_own ?>

    <?php if (onegta_is_moderator()) : ?>
    <!-- Moderation tab -->
    <div class="profile-tab-panel <?php echo $tab==='moderate' ? 'active':'' ?>" id="tab-moderate">
      <?php
      $pending_posts = get_posts(['post_status'=>'pending','post_type'=>['news','articles','files','videos'],'numberposts'=>30,'orderby'=>'date','order'=>'DESC']);
      ?>
      <?php if ($pending_posts) : ?>
        <div id="moderationList">
          <?php foreach ($pending_posts as $pp) :
            $type_icons = ['news'=>'📰','articles'=>'📖','files'=>'📦','videos'=>'🎬'];
            $icon = $type_icons[$pp->post_type] ?? '📄';
            $post_author = get_userdata($pp->post_author);
          ?>
            <div class="moderation-item" id="moditem-<?php echo esc_attr($pp->ID); ?>">
              <div style="font-size:1.5rem;"><?php echo $icon; ?></div>
              <div class="moderation-item__info">
                <div class="moderation-item__title"><?php echo esc_html($pp->post_title); ?></div>
                <div class="moderation-item__meta">
                  <?php echo esc_html(get_post_type_object($pp->post_type)->labels->singular_name); ?>
                  · <?php echo esc_html($post_author ? $post_author->display_name : ''); ?>
                  · <?php echo esc_html(onegta_date($pp->ID)); ?>
                </div>
              </div>
              <div class="moderation-item__actions">
                <a href="<?php echo esc_url(get_permalink($pp->ID)); ?>" target="_blank" class="btn btn--ghost btn--sm">Просмотр</a>
                <button class="btn btn--primary btn--sm mod-approve-btn" data-id="<?php echo esc_attr($pp->ID); ?>">✓ Одобрить</button>
                <button class="btn btn--sm mod-reject-btn" style="background:#fee2e2;color:var(--error);border:1px solid #fecaca;" data-id="<?php echo esc_attr($pp->ID); ?>">✕ Отклонить</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div id="moderationDone" style="display:none;" class="alert alert--success">Все материалы проверены 🎉</div>
      <?php else : ?>
        <div class="alert alert--info">Материалов на модерации нет.</div>
      <?php endif; ?>
    </div>
    <?php endif; // is_moderator ?>

    <?php if (current_user_can('administrator')) : ?>
    <!-- Users tab -->
    <div class="profile-tab-panel <?php echo $tab==='users' ? 'active':'' ?>" id="tab-users">
      <?php
      $users = get_users(['number'=>50,'orderby'=>'registered','order'=>'DESC']);
      $role_options = ['onegta_user'=>'Пользователь','onegta_moderator'=>'Модератор','administrator'=>'Администратор'];
      ?>
      <div id="usersAlert" style="margin-bottom:1rem;"></div>
      <div style="overflow-x:auto;">
        <table class="users-table">
          <thead>
            <tr>
              <th>Пользователь</th>
              <th>Email</th>
              <th>Роль</th>
              <th>Дата регистрации</th>
              <th>Действие</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u) :
              $role = $u->roles[0] ?? 'subscriber';
            ?>
              <tr id="userrow-<?php echo esc_attr($u->ID); ?>">
                <td>
                  <div style="display:flex;align-items:center;gap:.6rem;">
                    <div class="post-card__author-avatar" style="width:30px;height:30px;">
                      <img src="<?php echo esc_url(onegta_avatar_url($u->ID)); ?>" alt="">
                    </div>
                    <strong><?php echo esc_html($u->display_name); ?></strong>
                  </div>
                </td>
                <td style="color:var(--text3);"><?php echo esc_html($u->user_email); ?></td>
                <td><?php echo onegta_role_badge($role); ?></td>
                <td style="color:var(--text3);font-size:.78rem;"><?php echo esc_html(date_i18n('d M Y', strtotime($u->user_registered))); ?></td>
                <td>
                  <?php if ($u->ID !== get_current_user_id()) : ?>
                    <select class="role-select user-role-select" data-user-id="<?php echo esc_attr($u->ID); ?>">
                      <?php foreach ($role_options as $rk => $rl) : ?>
                        <option value="<?php echo esc_attr($rk); ?>" <?php selected($role, $rk); ?>><?php echo esc_html($rl); ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php else : ?>
                    <span style="font-size:.78rem;color:var(--text3);">Это вы</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; // administrator ?>

  </div>
</div>

<?php get_footer(); ?>
