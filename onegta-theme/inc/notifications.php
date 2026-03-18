<?php
/**
 * OneGTA — Email уведомления
 */
defined('ABSPATH') || exit;

/* ── EMAIL HEADERS ───────────────────────────── */
add_filter('wp_mail_content_type', fn() => 'text/html');
add_filter('wp_mail_from',         fn() => 'noreply@'.preg_replace('#^www\.#','',parse_url(home_url(),PHP_URL_HOST)));
add_filter('wp_mail_from_name',    fn() => get_bloginfo('name'));

/* ── EMAIL TEMPLATE ──────────────────────────── */
function onegta_email_template($title, $body, $btn_text='', $btn_url='') {
    ob_start(); ?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#F7F5F2;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F5F2;padding:40px 20px;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
      <!-- Header -->
      <tr><td style="background:#F55C00;padding:24px 32px;">
        <div style="font-family:Impact,sans-serif;font-size:28px;letter-spacing:4px;color:#fff;">
          ONE<span style="color:rgba(255,255,255,.7);">GTA</span>
        </div>
      </td></tr>
      <!-- Body -->
      <tr><td style="background:#fff;padding:32px;border-left:1px solid #E2DDD8;border-right:1px solid #E2DDD8;">
        <h1 style="font-size:22px;color:#1A1210;margin:0 0 16px;font-family:Impact,sans-serif;letter-spacing:2px;"><?php echo $title; ?></h1>
        <div style="font-size:15px;color:#4A3F38;line-height:1.7;"><?php echo $body; ?></div>
        <?php if ($btn_text && $btn_url) : ?>
        <div style="margin-top:28px;">
          <a href="<?php echo esc_url($btn_url); ?>" style="display:inline-block;background:#F55C00;color:#fff;padding:14px 32px;font-family:Impact,sans-serif;font-size:16px;letter-spacing:3px;text-decoration:none;"><?php echo esc_html($btn_text); ?></a>
        </div>
        <?php endif; ?>
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#F7F5F2;padding:20px 32px;border:1px solid #E2DDD8;border-top:none;">
        <p style="margin:0;font-size:12px;color:#8A7A70;">
          Это автоматическое уведомление от <a href="<?php echo esc_url(home_url('/')); ?>" style="color:#F55C00;"><?php echo esc_html(get_bloginfo('name')); ?></a>.
          Не отвечайте на это письмо.
        </p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
    <?php return ob_get_clean();
}

/* ── WELCOME EMAIL AFTER REGISTRATION ───────── */
add_action('user_register', function($user_id) {
    $user = get_userdata($user_id);
    if (!$user) return;

    $body = "
        <p>Привет, <strong>{$user->display_name}</strong>!</p>
        <p>Добро пожаловать на <strong>OneGTA</strong> — главный фан-портал серии Grand Theft Auto.</p>
        <p>Теперь ты можешь:</p>
        <ul style='padding-left:20px;'>
          <li>Добавлять новости, статьи и файлы</li>
          <li>Комментировать материалы</li>
          <li>Участвовать в жизни сообщества</li>
        </ul>
        <p>Твой логин: <strong>{$user->user_login}</strong></p>
    ";

    wp_mail(
        $user->user_email,
        '🎮 Добро пожаловать на OneGTA!',
        onegta_email_template('Добро пожаловать!', $body, 'Перейти на сайт', home_url('/'))
    );
}, 20);

/* ── NOTIFY ON NEW COMMENT ───────────────────── */
add_action('wp_insert_comment', function($id, $comment) {
    if ($comment->comment_approved != 1) return;
    $post   = get_post($comment->comment_post_ID);
    if (!$post) return;
    $author = get_userdata($post->post_author);
    // Не уведомляем если автор поста сам оставил комментарий
    if (!$author || $author->user_email == $comment->comment_author_email) return;

    $commenter = $comment->comment_author;
    $post_url  = get_permalink($post->ID) . '#comment-' . $id;
    $body = "
        <p>Привет, <strong>{$author->display_name}</strong>!</p>
        <p><strong>{$commenter}</strong> оставил комментарий к вашему материалу <strong>«{$post->post_title}»</strong>:</p>
        <blockquote style='border-left:3px solid #F55C00;padding:12px 16px;background:#FFF0E6;margin:16px 0;font-style:italic;color:#1A1210;'>
            " . nl2br(esc_html(wp_trim_words($comment->comment_content, 40))) . "
        </blockquote>
    ";

    wp_mail(
        $author->user_email,
        '💬 Новый комментарий на OneGTA',
        onegta_email_template('Новый комментарий', $body, 'Читать комментарий', $post_url)
    );
}, 10, 2);

/* ── NOTIFY ON COMMENT REPLY ─────────────────── */
add_action('wp_insert_comment', function($id, $comment) {
    if (!$comment->comment_parent || $comment->comment_approved != 1) return;
    $parent  = get_comment($comment->comment_parent);
    if (!$parent || !$parent->user_id) return;
    // Не уведомляем если тот же пользователь
    if ($parent->user_id == $comment->user_id) return;

    $parent_user = get_userdata($parent->user_id);
    if (!$parent_user) return;

    $post     = get_post($comment->comment_post_ID);
    $post_url = get_permalink($post->ID) . '#comment-' . $id;
    $body = "
        <p>Привет, <strong>{$parent_user->display_name}</strong>!</p>
        <p><strong>{$comment->comment_author}</strong> ответил на твой комментарий в материале <strong>«{$post->post_title}»</strong>:</p>
        <blockquote style='border-left:3px solid #F55C00;padding:12px 16px;background:#FFF0E6;margin:16px 0;font-style:italic;color:#1A1210;'>
            " . nl2br(esc_html(wp_trim_words($comment->comment_content, 40))) . "
        </blockquote>
    ";

    wp_mail(
        $parent_user->user_email,
        '↩ Ответ на ваш комментарий — OneGTA',
        onegta_email_template('Ответ на комментарий', $body, 'Посмотреть ответ', $post_url)
    );
}, 10, 2);

/* ── NOTIFY WHEN POST APPROVED ───────────────── */
add_action('transition_post_status', function($new, $old, $post) {
    if ($new !== 'publish' || $old !== 'pending') return;
    $cpts = ['news','articles','files','videos'];
    if (!in_array($post->post_type, $cpts)) return;

    $author = get_userdata($post->post_author);
    if (!$author) return;

    $type_labels = ['news'=>'новость','articles'=>'статья','files'=>'файл','videos'=>'видео'];
    $type_label  = $type_labels[$post->post_type] ?? 'материал';
    $post_url    = get_permalink($post->ID);

    $body = "
        <p>Привет, <strong>{$author->display_name}</strong>!</p>
        <p>Твой {$type_label} <strong>«{$post->post_title}»</strong> был проверен и опубликован на сайте.</p>
        <p>Спасибо за вклад в развитие OneGTA! 🔥</p>
    ";

    wp_mail(
        $author->user_email,
        '✅ Материал опубликован на OneGTA',
        onegta_email_template('Материал опубликован!', $body, 'Смотреть', $post_url)
    );
}, 10, 3);

/* ── NOTIFY WHEN POST REJECTED (trashed) ────── */
add_action('transition_post_status', function($new, $old, $post) {
    if ($new !== 'trash' || $old !== 'pending') return;
    $author = get_userdata($post->post_author);
    if (!$author) return;

    $body = "
        <p>Привет, <strong>{$author->display_name}</strong>!</p>
        <p>К сожалению, твой материал <strong>«{$post->post_title}»</strong> был отклонён модератором.</p>
        <p>Если у тебя есть вопросы — напиши нам.</p>
    ";

    wp_mail(
        $author->user_email,
        '❌ Материал отклонён — OneGTA',
        onegta_email_template('Материал отклонён', $body, 'Добавить новый', home_url('/submit/'))
    );
}, 10, 3);
