<?php
/**
 * OneGTA — functions.php
 */
defined('ABSPATH') || exit;
define('ONEGTA_VER', '2.0.0');
define('ONEGTA_DIR', get_template_directory());
define('ONEGTA_URL', get_template_directory_uri());

/* ── SETUP ───────────────────────────────────── */
function onegta_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','script','style']);
    add_theme_support('wp-block-styles');
    add_theme_support('responsive-embeds');
    add_image_size('onegta-card',     600, 380, true);
    add_image_size('onegta-featured', 1200, 600, true);
    add_image_size('onegta-thumb',    120, 120, true);
    add_image_size('onegta-game',     500, 700, true);
    register_nav_menus([
        'primary' => 'Основное меню',
        'footer'  => 'Футер меню',
    ]);
}
add_action('after_setup_theme', 'onegta_setup');

/* ── ENQUEUE ─────────────────────────────────── */
function onegta_scripts() {
    wp_enqueue_style('onegta-fonts',
        'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=Orbitron:wght@500;700&display=swap',
        [], null);
    wp_enqueue_style('onegta-style', get_stylesheet_uri(), ['onegta-fonts'], ONEGTA_VER);
    wp_enqueue_script('onegta-main', ONEGTA_URL.'/js/main.js', [], ONEGTA_VER, true);
    wp_localize_script('onegta-main', 'onegtaVars', [
        'ajaxUrl'    => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('onegta_nonce'),
        'isLoggedIn' => is_user_logged_in() ? 1 : 0,
        'loginUrl'   => wp_login_url(),
        'profileUrl' => is_user_logged_in() ? onegta_profile_url() : home_url('/profile/'),
    ]);
    if (is_singular() && comments_open()) wp_enqueue_script('comment-reply');
}
add_action('wp_enqueue_scripts', 'onegta_scripts');

/* ── CPT: NEWS ───────────────────────────────── */
function onegta_cpt_news() {
    register_post_type('news', [
        'labels' => [
            'name'          => 'Новости',
            'singular_name' => 'Новость',
            'add_new_item'  => 'Добавить новость',
            'edit_item'     => 'Редактировать новость',
            'menu_name'     => 'Новости',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'news'],
        'menu_icon'    => 'dashicons-megaphone',
        'menu_position'=> 5,
        'supports'     => ['title','editor','thumbnail','excerpt','author','comments','revisions'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'onegta_cpt_news');

/* ── CPT: ARTICLES (per game) ────────────────── */
function onegta_cpt_articles() {
    register_post_type('articles', [
        'labels' => [
            'name'          => 'Статьи',
            'singular_name' => 'Статья',
            'add_new_item'  => 'Добавить статью',
            'menu_name'     => 'Статьи',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'articles'],
        'menu_icon'    => 'dashicons-book-alt',
        'menu_position'=> 6,
        'supports'     => ['title','editor','thumbnail','excerpt','author','comments','revisions'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'onegta_cpt_articles');

/* ── CPT: FILES ──────────────────────────────── */
function onegta_cpt_files() {
    register_post_type('files', [
        'labels' => [
            'name'          => 'Файлы',
            'singular_name' => 'Файл',
            'add_new_item'  => 'Добавить файл',
            'menu_name'     => 'Файловый архив',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'files'],
        'menu_icon'    => 'dashicons-media-archive',
        'menu_position'=> 7,
        'supports'     => ['title','editor','thumbnail','excerpt','author','comments','revisions'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'onegta_cpt_files');

/* ── CPT: VIDEOS ─────────────────────────────── */
function onegta_cpt_videos() {
    register_post_type('videos', [
        'labels' => [
            'name'          => 'Видео',
            'singular_name' => 'Видео',
            'add_new_item'  => 'Добавить видео',
            'menu_name'     => 'Видео',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'videos'],
        'menu_icon'    => 'dashicons-video-alt3',
        'menu_position'=> 8,
        'supports'     => ['title','editor','thumbnail','excerpt','author','comments','revisions'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'onegta_cpt_videos');

/* ── TAXONOMIES ──────────────────────────────── */
function onegta_taxonomies() {

    // Новости — категории
    register_taxonomy('news_category', 'news', [
        'labels'       => ['name'=>'Разделы новостей','singular_name'=>'Раздел','add_new_item'=>'Добавить раздел'],
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'news-cat'],
        'show_in_rest' => true,
        'public'       => true,
    ]);

    // Статьи — по игре
    register_taxonomy('game_title', 'articles', [
        'labels'       => ['name'=>'Игры GTA','singular_name'=>'Игра','add_new_item'=>'Добавить игру'],
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'game'],
        'show_in_rest' => true,
        'public'       => true,
    ]);

    // Файлы — категории
    register_taxonomy('file_category', 'files', [
        'labels'       => ['name'=>'Категории файлов','singular_name'=>'Категория','add_new_item'=>'Добавить категорию'],
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'file-cat'],
        'show_in_rest' => true,
        'public'       => true,
    ]);

    // Общий тег для всех CPT + posts
    register_taxonomy('gta_tag', ['post','news','articles','files','videos'], [
        'labels'       => ['name'=>'GTA Теги','singular_name'=>'Тег','add_new_item'=>'Добавить тег'],
        'hierarchical' => false,
        'rewrite'      => ['slug' => 'tag'],
        'show_in_rest' => true,
        'public'       => true,
    ]);

    // Видео — категории
    register_taxonomy('video_category', 'videos', [
        'labels'       => ['name'=>'Категории видео','singular_name'=>'Категория'],
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'video-cat'],
        'show_in_rest' => true,
        'public'       => true,
    ]);
}
add_action('init', 'onegta_taxonomies');

/* ── DEFAULT TERMS ON ACTIVATION ────────────── */
function onegta_default_terms() {
    // Разделы новостей
    $news_cats = ['Rockstar Games', 'GTA V', 'GRAND THEFT AUTO', 'Игры', 'Прочее'];
    foreach ($news_cats as $cat) {
        if (!term_exists($cat, 'news_category'))
            wp_insert_term($cat, 'news_category');
    }
    // Игры GTA (статьи)
    $games = [
        'GTA I'          => 'gta-1',
        'GTA II'         => 'gta-2',
        'GTA III'        => 'gta-3',
        'GTA: Vice City' => 'gta-vice-city',
        'GTA: San Andreas'=> 'gta-san-andreas',
        'GTA IV'         => 'gta-4',
        'GTA V'          => 'gta-5',
        'GTA VI'         => 'gta-6',
        'GTA Online'     => 'gta-online',
    ];
    foreach ($games as $name => $slug) {
        if (!term_exists($name, 'game_title'))
            wp_insert_term($name, 'game_title', ['slug' => $slug]);
    }
    // Категории файлов
    $file_cats = [
        'Моды'       => ['Скины','Транспорт','Карты','Геймплей','Графика'],
        'Патчи'      => [],
        'Трейнеры'   => [],
        'Инструменты'=> ['Редакторы','Конвертеры'],
        'Сохранения' => [],
        'Звук'       => ['Музыка','SFX'],
    ];
    foreach ($file_cats as $parent => $children) {
        $p = term_exists($parent, 'file_category');
        if (!$p) $p = wp_insert_term($parent, 'file_category');
        $pid = is_array($p) ? $p['term_id'] : $p;
        foreach ($children as $child) {
            if (!term_exists($child, 'file_category'))
                wp_insert_term($child, 'file_category', ['parent' => $pid]);
        }
    }
    // Видео
    $vid_cats = ['Трейлеры','Геймплей','Обзоры','Приколы','Туториалы'];
    foreach ($vid_cats as $vc) {
        if (!term_exists($vc, 'video_category'))
            wp_insert_term($vc, 'video_category');
    }
}
register_activation_hook(__FILE__, 'onegta_default_terms');
// Also run on after_switch_theme
add_action('after_switch_theme', 'onegta_default_terms');

/* ── META BOXES ──────────────────────────────── */
add_action('add_meta_boxes', function() {
    // Files meta
    add_meta_box('onegta_file_meta', 'Информация о файле', 'onegta_file_meta_cb', 'files', 'normal', 'high');
    // Videos meta
    add_meta_box('onegta_video_meta', 'Информация о видео', 'onegta_video_meta_cb', 'videos', 'normal', 'high');
    // Game meta for articles
    add_meta_box('onegta_article_meta', 'Дополнительно', 'onegta_article_meta_cb', 'articles', 'side');
});

function onegta_file_meta_cb($post) {
    wp_nonce_field('onegta_file_meta', 'onegta_file_nonce');
    $fields = ['file_url'=>'Прямая ссылка на файл','file_size'=>'Размер файла','file_version'=>'Версия','file_game'=>'Для игры','file_downloads'=>'Скачиваний'];
    echo '<table class="form-table">';
    foreach ($fields as $k => $l) {
        $v = get_post_meta($post->ID, $k, true);
        echo "<tr><th><label for='$k'>$l</label></th><td><input type='text' id='$k' name='$k' value='".esc_attr($v)."' class='regular-text'></td></tr>";
    }
    echo '</table>';
}

function onegta_video_meta_cb($post) {
    wp_nonce_field('onegta_video_meta', 'onegta_video_nonce');
    $url = get_post_meta($post->ID, 'video_url', true);
    echo '<table class="form-table"><tr><th><label for="video_url">YouTube / Rutube URL</label></th><td><input type="url" id="video_url" name="video_url" value="'.esc_attr($url).'" class="regular-text"></td></tr></table>';
}

function onegta_article_meta_cb($post) {
    wp_nonce_field('onegta_article_meta', 'onegta_article_nonce');
    
    $difficulty = get_post_meta($post->ID, 'article_difficulty', true);
    $type       = get_post_meta($post->ID, 'article_type', true);

    // Поле: Тип статьи
    echo '<p><label>Тип статьи</label><br>';
    echo '<select name="article_type" id="onegta_article_type" style="width:100%">';
    foreach (['Гайд','Прохождение','Читы','Обзор','Список','Туториалы'] as $opt) {
        echo '<option value="'.esc_attr($opt).'"'.selected($type, $opt, false).'>'.esc_html($opt).'</option>';
    }
    echo '</select></p>';

    // Поле: Сложность (оборачиваем в контейнер с ID, чтобы легко скрывать)
    // Добавляем инлайновый стиль для начального скрытия, если это не Гайд или Прохождение
    $display_style = ($type === 'Гайд' || $type === 'Прохождение') ? 'display:block' : 'display:none';
    
    echo '<div id="difficulty_wrapper" style="'.$display_style.'">';
    echo '<p><label>Сложность</label><br>';
    echo '<select name="article_difficulty" style="width:100%">';
    foreach (['Любой','Новичок','Средний','Эксперт'] as $opt) {
        echo '<option value="'.esc_attr($opt).'"'.selected($difficulty, $opt, false).'>'.esc_html($opt).'</option>';
    }
    echo '</select></p></div>';

    // JS-скрипт, который будет следить за изменениями прямо в админке
    ?>
    <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                var typeSelect = $('#onegta_article_type');
                var diffWrapper = $('#difficulty_wrapper');

                typeSelect.on('change', function() {
                    var currentVal = $(this).val();
                    // Проверяем: если Гайд или Прохождение — показываем сложность
                    if (currentVal === 'Гайд' || currentVal === 'Прохождение') {
                        diffWrapper.slideDown(200);
                    } else {
                        diffWrapper.slideUp(200);
                    }
                });
            });
        })(jQuery);
    </script>
    <?php
}

add_action('save_post', function($id) {
    // File meta
    if (isset($_POST['onegta_file_nonce']) && wp_verify_nonce($_POST['onegta_file_nonce'], 'onegta_file_meta')) {
        foreach (['file_url','file_size','file_version','file_game','file_downloads'] as $k)
            if (isset($_POST[$k])) update_post_meta($id, $k, sanitize_text_field($_POST[$k]));
    }
    // Video meta
    if (isset($_POST['onegta_video_nonce']) && wp_verify_nonce($_POST['onegta_video_nonce'], 'onegta_video_meta')) {
        if (isset($_POST['video_url'])) update_post_meta($id, 'video_url', esc_url_raw($_POST['video_url']));
    }
    // Article meta
    if (isset($_POST['onegta_article_nonce']) && wp_verify_nonce($_POST['onegta_article_nonce'], 'onegta_article_meta')) {
        if (isset($_POST['article_type']))       update_post_meta($id, 'article_type', sanitize_text_field($_POST['article_type']));
        if (isset($_POST['article_difficulty'])) update_post_meta($id, 'article_difficulty', sanitize_text_field($_POST['article_difficulty']));
    }
});

/* ── WIDGETS ─────────────────────────────────── */
function onegta_widgets() {
    $args = ['before_widget'=>'<div class="sidebar-widget">','after_widget'=>'</div><div class="sidebar-widget__body-end"></div>','before_title'=>'<div class="sidebar-widget__title">','after_title'=>'</div><div class="sidebar-widget__body">'];
    register_sidebar(array_merge($args, ['name'=>'Сайдбар — Статьи','id'=>'sidebar-articles']));
    register_sidebar(array_merge($args, ['name'=>'Сайдбар — Новости','id'=>'sidebar-news']));
    register_sidebar(array_merge($args, ['name'=>'Сайдбар — Файлы','id'=>'sidebar-files']));
}
add_action('widgets_init', 'onegta_widgets');

/* ── HELPERS ─────────────────────────────────── */
function onegta_date($id=null, $fmt='d M Y') { return get_the_date($fmt, $id); }
function onegta_recent($n=5, $type='post') {
    return get_posts(['numberposts'=>$n,'post_type'=>$type,'post_status'=>'publish','suppress_filters'=>false]);
}
function onegta_ticker() {
    $posts = get_posts(['numberposts'=>8,'post_type'=>['post','news'],'post_status'=>'publish']);
    if (!$posts) return ['GTA VI — официальная дата выхода','GTA Online — новое обновление','Топ читов GTA V','Лучшие моды San Andreas'];
    return wp_list_pluck($posts,'post_title');
}
function onegta_avatar_url($user_id) {
    return get_avatar_url($user_id, ['size'=>80]);
}
function onegta_user_initial($user) {
    return strtoupper(mb_substr($user->display_name, 0, 1));
}

/* ── ADMIN BAR BODY OFFSET ───────────────────── */
add_action('wp_head', function() { ?>
<style>
/* Push page content below fixed header + adminbar */
body { padding-top: var(--header-h); }
.admin-bar body { padding-top: 0; }
html.admin-bar { margin-top: 0 !important; }
.admin-bar #wpadminbar { position: fixed; }
</style>
<?php });

add_filter('excerpt_length', fn()=>22);
add_filter('excerpt_more',   fn()=>'…');

/* ── BODY CLASSES ────────────────────────────── */
add_filter('body_class', function($c) {
    if (is_singular('news'))     $c[] = 'single-news';
    if (is_singular('articles')) $c[] = 'single-articles';
    if (is_singular('files'))    $c[] = 'single-files';
    if (is_singular('videos'))   $c[] = 'single-videos';
    return $c;
});

/* ── FRONTEND AUTH ───────────────────────────── */

// Login AJAX
add_action('wp_ajax_nopriv_onegta_login', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $user = wp_signon([
        'user_login'    => sanitize_user($_POST['login'] ?? ''),
        'user_password' => $_POST['password'] ?? '',
        'remember'      => !empty($_POST['remember']),
    ], false);
    if (is_wp_error($user)) {
        wp_send_json_error(['message' => 'Неверный логин или пароль']);
    }
    wp_send_json_success(['redirect' => onegta_profile_url($user)]);
});

// Register AJAX
add_action('wp_ajax_nopriv_onegta_register', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $username = sanitize_user($_POST['username'] ?? '');
    $email    = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$username || !$email || !$password)
        wp_send_json_error(['message' => 'Заполните все поля']);
    if (!is_email($email))
        wp_send_json_error(['message' => 'Неверный email']);
    if ($password !== $confirm)
        wp_send_json_error(['message' => 'Пароли не совпадают']);
    if (strlen($password) < 6)
        wp_send_json_error(['message' => 'Пароль минимум 6 символов']);
    if (username_exists($username))
        wp_send_json_error(['message' => 'Такой логин уже занят']);
    if (email_exists($email))
        wp_send_json_error(['message' => 'Email уже зарегистрирован']);

    $id = wp_create_user($username, $password, $email);
    if (is_wp_error($id)) wp_send_json_error(['message' => $id->get_error_message()]);

    wp_update_user(['ID'=>$id, 'display_name'=>$username]);
    wp_set_current_user($id);
    wp_set_auth_cookie($id, true);
    wp_send_json_success(['redirect' => onegta_profile_url($id)]);
});

// Logout AJAX
add_action('wp_ajax_onegta_logout', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    wp_logout();
    wp_send_json_success(['redirect' => home_url('/')]);
});

// Update profile AJAX
add_action('wp_ajax_onegta_update_profile', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Не авторизован']);
    $uid  = get_current_user_id();
    $data = ['ID' => $uid];
    if (!empty($_POST['display_name'])) $data['display_name'] = sanitize_text_field($_POST['display_name']);
    if (!empty($_POST['email']) && is_email($_POST['email'])) $data['user_email'] = sanitize_email($_POST['email']);
    if (!empty($_POST['new_password']) && $_POST['new_password'] === ($_POST['confirm_password'] ?? ''))
        $data['user_pass'] = $_POST['new_password'];
    if (!empty($_POST['bio'])) update_user_meta($uid, 'description', sanitize_textarea_field($_POST['bio']));
    $r = wp_update_user($data);
    if (is_wp_error($r)) wp_send_json_error(['message' => $r->get_error_message()]);
    wp_send_json_success(['message' => 'Профиль обновлён']);
});

/* ── FRONTEND SUBMIT ─────────────────────────── */

// Submit news/article
add_action('wp_ajax_onegta_submit_post', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Войдите, чтобы добавить материал']);

    $type    = sanitize_key($_POST['post_type'] ?? 'news');
    $allowed = ['news','articles','videos'];
    if (!in_array($type, $allowed)) wp_send_json_error(['message' => 'Неверный тип']);

    $title   = sanitize_text_field($_POST['title'] ?? '');
    $content = wp_kses_post($_POST['content'] ?? '');
    $excerpt = sanitize_textarea_field($_POST['excerpt'] ?? '');
    if (!$title) wp_send_json_error(['message' => 'Введите заголовок']);

    $status = current_user_can('publish_posts') ? 'publish' : 'pending';
    $pid = wp_insert_post([
        'post_title'   => $title,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_status'  => $status,
        'post_type'    => $type,
        'post_author'  => get_current_user_id(),
    ]);
    if (is_wp_error($pid)) wp_send_json_error(['message' => $pid->get_error_message()]);

    // Categories / terms
    if (!empty($_POST['category'])) {
        $tax_map = ['news'=>'news_category','articles'=>'game_title','videos'=>'video_category'];
        $tax = $tax_map[$type] ?? 'category';
        wp_set_post_terms($pid, [(int)$_POST['category']], $tax);
    }
    if (!empty($_POST['tags'])) {
        $tags = array_map('trim', explode(',', sanitize_text_field($_POST['tags'])));
        wp_set_post_terms($pid, $tags, 'gta_tag');
    }
    // Video URL
    if ($type === 'videos' && !empty($_POST['video_url']))
        update_post_meta($pid, 'video_url', esc_url_raw($_POST['video_url']));
    // Article extras
    if ($type === 'articles') {
        if (!empty($_POST['article_type']))       update_post_meta($pid, 'article_type', sanitize_text_field($_POST['article_type']));
        if (!empty($_POST['article_difficulty'])) update_post_meta($pid, 'article_difficulty', sanitize_text_field($_POST['article_difficulty']));
    }

    // Thumbnail upload
    if (!empty($_FILES['thumbnail']['name'])) {
        require_once ABSPATH.'wp-admin/includes/image.php';
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/media.php';
        $att = media_handle_upload('thumbnail', $pid);
        if (!is_wp_error($att)) set_post_thumbnail($pid, $att);
    }

    $msg = $status === 'publish' ? 'Материал опубликован!' : 'Отправлено на модерацию';
    wp_send_json_success(['message' => $msg, 'url' => get_permalink($pid)]);
});

// Submit file
add_action('wp_ajax_onegta_submit_file', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Войдите, чтобы загрузить файл']);

    $title = sanitize_text_field($_POST['title'] ?? '');
    if (!$title) wp_send_json_error(['message' => 'Введите название']);

    $status = current_user_can('publish_posts') ? 'publish' : 'pending';
    $pid = wp_insert_post([
        'post_title'   => $title,
        'post_content' => wp_kses_post($_POST['content'] ?? ''),
        'post_excerpt' => sanitize_textarea_field($_POST['excerpt'] ?? ''),
        'post_status'  => $status,
        'post_type'    => 'files',
        'post_author'  => get_current_user_id(),
    ]);
    if (is_wp_error($pid)) wp_send_json_error(['message' => $pid->get_error_message()]);

    if (!empty($_POST['file_category'])) wp_set_post_terms($pid, [(int)$_POST['file_category']], 'file_category');
    if (!empty($_POST['file_url']))     update_post_meta($pid, 'file_url', esc_url_raw($_POST['file_url']));
    if (!empty($_POST['file_size']))    update_post_meta($pid, 'file_size', sanitize_text_field($_POST['file_size']));
    if (!empty($_POST['file_version'])) update_post_meta($pid, 'file_version', sanitize_text_field($_POST['file_version']));
    if (!empty($_POST['file_game']))    update_post_meta($pid, 'file_game', sanitize_text_field($_POST['file_game']));

    // Actual file upload
    if (!empty($_FILES['file_upload']['name'])) {
        require_once ABSPATH.'wp-admin/includes/file.php';
        $uploaded = wp_handle_upload($_FILES['file_upload'], ['test_form'=>false]);
        if (!isset($uploaded['error'])) {
            update_post_meta($pid, 'file_url', $uploaded['url']);
            update_post_meta($pid, 'file_size', size_format(filesize($uploaded['file'])));
        }
    }
    // Thumbnail
    if (!empty($_FILES['thumbnail']['name'])) {
        require_once ABSPATH.'wp-admin/includes/image.php';
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/media.php';
        $att = media_handle_upload('thumbnail', $pid);
        if (!is_wp_error($att)) set_post_thumbnail($pid, $att);
    }

    $msg = $status === 'publish' ? 'Файл добавлен!' : 'Отправлено на модерацию';
    wp_send_json_success(['message' => $msg, 'url' => get_permalink($pid)]);
});

// Download counter
add_action('wp_ajax_onegta_download', 'onegta_count_download');
add_action('wp_ajax_nopriv_onegta_download', 'onegta_count_download');
function onegta_count_download() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $pid = absint($_POST['post_id'] ?? 0);
    if (!$pid) wp_send_json_error();
    $count = (int)get_post_meta($pid, 'file_downloads', true);
    update_post_meta($pid, 'file_downloads', $count + 1);
    $url = get_post_meta($pid, 'file_url', true);
    wp_send_json_success(['url' => esc_url($url), 'count' => $count + 1]);
}

// Load more AJAX
add_action('wp_ajax_onegta_load_more',        'onegta_load_more_cb');
add_action('wp_ajax_nopriv_onegta_load_more', 'onegta_load_more_cb');
function onegta_load_more_cb() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $page = absint($_POST['page'] ?? 2);
    $type = sanitize_key($_POST['post_type'] ?? 'news');
    $term = absint($_POST['term'] ?? 0);
    $args = ['post_type'=>$type,'posts_per_page'=>9,'paged'=>$page,'post_status'=>'publish'];
    if ($term) {
        $tax_map = ['news'=>'news_category','articles'=>'game_title','files'=>'file_category','videos'=>'video_category'];
        $args['tax_query'] = [['taxonomy'=>$tax_map[$type]??'category','field'=>'term_id','terms'=>$term]];
    }
    $q = new WP_Query($args);
    ob_start();
    if ($q->have_posts()) while ($q->have_posts()) { $q->the_post(); get_template_part('template-parts/cards/post-card'); }
    wp_reset_postdata();
    wp_send_json_success(['html'=>ob_get_clean(),'hasMore'=>$page<$q->max_num_pages]);
}

/* ── PROFILE URL REWRITE ─────────────────────── */
// /profile/username → страница профиля с параметром
add_action('init', function() {
    add_rewrite_rule(
        '^profile/([^/]+)/?$',
        'index.php?pagename=profile&profile_user=$matches[1]',
        'top'
    );
});

add_filter('query_vars', function($vars) {
    $vars[] = 'profile_user';
    return $vars;
});

// Передаём profile_user в $_GET['user'] для page-profile.php
add_action('template_redirect', function() {
    $profile_user = get_query_var('profile_user');
    if ($profile_user) {
        $_GET['user'] = sanitize_user($profile_user);
    }
});

/* ── PROFILE URL HELPER ──────────────────────── */
function onegta_profile_url($user = null) {
    if (!$user) {
        if (!is_user_logged_in()) return home_url('/profile/');
        $user = wp_get_current_user();
    }
    if (is_int($user)) $user = get_userdata($user);
    if (!$user) return home_url('/profile/');
    return home_url('/profile/' . $user->user_login . '/');
}

/* ── FLUSH REWRITE ON THEME SWITCH ──────────── */
add_action('after_switch_theme', function() {
    onegta_cpt_news(); onegta_cpt_articles(); onegta_cpt_files(); onegta_cpt_videos();
    onegta_taxonomies();
    flush_rewrite_rules();
});

/* ── ALLOW PENDING POST AUTHORS TO SUBMIT ────── */
add_filter('user_has_cap', function($caps) {
    if (is_user_logged_in()) {
        $caps['read'] = true;
    }
    return $caps;
});

/* ── NAV WALKER ──────────────────────────────── */
class OneGTA_Walker extends Walker_Nav_Menu {
    public function start_el(&$out, $item, $d=0, $args=null, $id=0) {
        $classes = implode(' ', array_filter((array)$item->classes));
        $is_cta  = in_array('menu-item-cta', (array)$item->classes);
        $href    = esc_url($item->url);
        $title   = apply_filters('the_title', $item->title, $item->ID);
        $link_class = $is_cta ? ' class="nav-cta"' : '';
        $out .= '<li class="'.esc_attr($classes).'">';
        $out .= "<a href=\"{$href}\"{$link_class}>{$title}</a>";
    }
}

/* ── COMMENT FORM DEFAULTS ───────────────────── */
add_filter('comment_form_defaults', function($d) {
    $d['title_reply'] = '<span class="section-label">Комментарии</span>';
    return $d;
});

// Include all modules
require_once ONEGTA_DIR . '/inc/helpers.php';
require_once ONEGTA_DIR . '/inc/roles.php';
require_once ONEGTA_DIR . '/inc/seo.php';
require_once ONEGTA_DIR . '/inc/comments.php';
require_once ONEGTA_DIR . '/inc/widgets.php';
require_once ONEGTA_DIR . '/inc/notifications.php';
require_once ONEGTA_DIR . '/inc/search.php';
require_once ONEGTA_DIR . '/inc/chat.php';
require_once ONEGTA_DIR . '/inc/security.php';
require_once ONEGTA_DIR . '/inc/forum.php';
require_once ONEGTA_DIR . '/inc/yadisk.php';

// Создаём разделы форума если их нет (запускается один раз)
add_action('init', function() {
    if (!get_option('onegta_forum_sections_created')) {
        onegta_forum_default_sections();
        onegta_default_terms();
        update_option('onegta_forum_sections_created', 1);
        flush_rewrite_rules();
    }
}, 99);

// Создаём разделы форума если их нет
add_action('init', function() {
    $terms = get_terms(['taxonomy'=>'forum_section','hide_empty'=>false,'number'=>1]);
    if (empty($terms) || is_wp_error($terms)) {
        onegta_forum_default_sections();
    }
}, 99);

// Register roles on theme switch
add_action('after_switch_theme', 'onegta_register_roles');
// Also on init in case roles were removed
add_action('init', function() {
    if (!get_role('onegta_user') || !get_role('onegta_moderator')) {
        onegta_register_roles();
    }
    // Создаём разделы форума если их нет
    if (!get_terms(['taxonomy' => 'forum_section', 'hide_empty' => false, 'number' => 1])) {
        onegta_forum_default_sections();
    }
}, 1);
