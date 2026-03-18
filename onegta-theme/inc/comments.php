<?php
/**
 * OneGTA — кастомный вывод комментариев
 */
defined('ABSPATH') || exit;

/* ── COMMENT WALKER ──────────────────────────── */
class OneGTA_Comment_Walker extends Walker_Comment {

    public function start_lvl(&$output, $depth=0, $args=null) {
        $output .= '<div class="comment-children" style="margin-left:2.5rem;border-left:2px solid var(--border);padding-left:1.5rem;margin-top:1rem;">';
    }
    public function end_lvl(&$output, $depth=0, $args=null) {
        $output .= '</div>';
    }

    public function start_el(&$output, $comment, $depth=0, $args=null, $id=0) {
        $user    = get_userdata($comment->user_id);
        $role    = $user ? ($user->roles[0] ?? '') : '';
        $av_url  = get_avatar_url($comment->comment_author_email, ['size'=>48]);
        $initial = strtoupper(mb_substr($comment->comment_author, 0, 1));
        $pending = $comment->comment_approved == '0';

        ob_start();
        ?>
        <div id="comment-<?php echo esc_attr($comment->comment_ID); ?>" class="comment-item <?php echo $pending ? 'comment-pending' : ''; ?>">
            <div class="comment-item__avatar">
                <img src="<?php echo esc_url($av_url); ?>" alt="" loading="lazy">
            </div>
            <div class="comment-item__body">
                <div class="comment-item__header">
                    <span class="comment-item__author"><?php echo esc_html($comment->comment_author); ?></span>
                    <?php if ($role) echo onegta_role_badge($role); ?>
                    <?php if ($pending) echo '<span class="badge badge--pale" style="font-size:.6rem;">На модерации</span>'; ?>
                    <span class="comment-item__date"><?php echo esc_html(date_i18n('d M Y, H:i', strtotime($comment->comment_date))); ?></span>
                </div>
                <?php if ($pending) : ?>
                    <p style="color:var(--text3);font-style:italic;font-size:.88rem;">Комментарий ожидает модерации</p>
                <?php else : ?>
                    <div class="comment-item__text"><?php comment_text(); ?></div>
                <?php endif; ?>
                <div class="comment-item__actions">
                    <?php comment_reply_link(array_merge($args, [
                        'reply_text' => '↩ Ответить',
                        'before'     => '',
                        'after'      => '',
                        'depth'      => $depth,
                        'max_depth'  => $args['max_depth'],
                        'add_below'  => 'comment-'.$comment->comment_ID,
                    ])); ?>
                    <?php if (current_user_can('moderate_comments')) : ?>
                        <?php if ($comment->comment_approved == '0') : ?>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('comment.php?action=approvecomment&c='.$comment->comment_ID), 'approve-comment_'.$comment->comment_ID)); ?>" class="comment-action-btn comment-action-btn--approve">✓ Одобрить</a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('comment.php?action=deletecomment&c='.$comment->comment_ID), 'delete-comment_'.$comment->comment_ID)); ?>" class="comment-action-btn comment-action-btn--delete" onclick="return confirm('Удалить комментарий?')">✕ Удалить</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        $output .= ob_get_clean();
    }

    public function end_el(&$output, $comment, $depth=0, $args=null) {
        // closed in start_el
    }
}

/* ── COMMENTS TEMPLATE FUNCTION ──────────────── */
function onegta_comments_template() {
    if (!comments_open() && !get_comments_number()) return;
    ?>
    <div class="comments-wrap" id="comments">

        <?php if (have_comments()) : ?>
        <div class="comments-list">
            <div class="section-label" style="margin-bottom:1.5rem;">
                <?php comments_number('0 комментариев', '1 комментарий', '% комментариев'); ?>
            </div>

            <div class="comments-items">
                <?php wp_list_comments([
                    'walker'      => new OneGTA_Comment_Walker(),
                    'avatar_size' => 48,
                    'style'       => 'div',
                    'max_depth'   => 3,
                ]); ?>
            </div>

            <?php the_comments_pagination(['prev_text'=>'←','next_text'=>'→']); ?>
        </div>
        <?php endif; ?>

        <?php if (comments_open()) : ?>
        <div class="comment-form-wrap" id="respond">
            <div class="section-label" style="margin-bottom:1.5rem;">
                <?php is_user_logged_in() ? print('Оставить комментарий') : print('Войдите чтобы комментировать'); ?>
            </div>

            <?php if (is_user_logged_in()) :
                $user = wp_get_current_user();
            ?>
                <form id="onegtaCommentForm" class="onegta-comment-form">
                    <?php wp_nonce_field('onegta_nonce', 'onegta_comment_nonce'); ?>
                    <input type="hidden" name="comment_post_ID" value="<?php the_ID(); ?>">
                    <input type="hidden" name="comment_parent" id="comment_parent" value="0">

                    <div style="display:flex;align-items:flex-start;gap:1rem;">
                        <div class="comment-item__avatar" style="flex-shrink:0;">
                            <img src="<?php echo esc_url(onegta_avatar_url($user->ID)); ?>" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                        </div>
                        <div style="flex:1;">
                            <div id="replyNotice" style="display:none;font-size:.78rem;color:var(--orange);margin-bottom:.4rem;"></div>
                            <textarea class="form-input" name="comment" id="commentText" rows="4" placeholder="Напиши комментарий…" required style="margin-bottom:.6rem;"></textarea>
                            <div style="display:flex;align-items:center;gap:.8rem;">
                                <button type="submit" class="btn btn--primary btn--sm">Отправить</button>
                                <button type="button" id="cancelReply" style="display:none;font-size:.82rem;color:var(--text3);background:none;border:none;cursor:pointer;">✕ Отмена</button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php else : ?>
                <div class="alert alert--info">
                    <a href="#" onclick="document.getElementById('openAuthBtn')?.click();return false;" style="font-weight:700;color:var(--orange);">Войди</a> или <a href="#" onclick="document.getElementById('openRegisterBtn')?.click();return false;" style="font-weight:700;color:var(--orange);">зарегистрируйся</a> чтобы оставить комментарий.
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
    <?php
}

/* ── AJAX COMMENT SUBMIT ─────────────────────── */
add_action('wp_ajax_onegta_comment', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message'=>'Необходима авторизация']);

    $post_id = absint($_POST['post_id'] ?? 0);
    $content = sanitize_textarea_field($_POST['comment'] ?? '');
    $parent  = absint($_POST['parent'] ?? 0);

    if (!$post_id || !$content) wp_send_json_error(['message'=>'Пустой комментарий']);
    if (!comments_open($post_id)) wp_send_json_error(['message'=>'Комментарии закрыты']);

    $user = wp_get_current_user();
    $data = [
        'comment_post_ID'      => $post_id,
        'comment_content'      => $content,
        'comment_parent'       => $parent,
        'comment_author'       => $user->display_name,
        'comment_author_email' => $user->user_email,
        'comment_author_url'   => '',
        'user_id'              => $user->ID,
        'comment_approved'     => current_user_can('moderate_comments') ? 1 : 0,
    ];

    $id = wp_insert_comment($data);
    if (!$id) wp_send_json_error(['message'=>'Ошибка при сохранении']);

    // Уведомление автору поста
    $post   = get_post($post_id);
    $author = get_userdata($post->post_author);
    if ($author && $author->ID !== $user->ID) {
        wp_mail(
            $author->user_email,
            '[OneGTA] Новый комментарий к вашему материалу',
            "Привет, {$author->display_name}!\n\nНовый комментарий к материалу \"".get_the_title($post_id)."\":\n\n{$content}\n\nАвтор: {$user->display_name}\nСсылка: ".get_permalink($post_id)."#comment-{$id}\n\n— OneGTA"
        );
    }

    // Рендерим HTML нового комментария
    $comment = get_comment($id);
    $pending = $comment->comment_approved == '0';

    $av = get_avatar_url($user->user_email, ['size'=>48]);
    $role = $user->roles[0] ?? '';
    ob_start();
    ?>
    <div id="comment-<?php echo esc_attr($id); ?>" class="comment-item" style="animation:fadeInUp .3s ease;">
        <div class="comment-item__avatar"><img src="<?php echo esc_url($av); ?>" alt="" loading="lazy"></div>
        <div class="comment-item__body">
            <div class="comment-item__header">
                <span class="comment-item__author"><?php echo esc_html($user->display_name); ?></span>
                <?php echo onegta_role_badge($role); ?>
                <span class="comment-item__date"><?php echo esc_html(date_i18n('d M Y, H:i')); ?></span>
            </div>
            <?php if ($pending) : ?>
                <p style="color:var(--text3);font-style:italic;font-size:.88rem;">Комментарий ожидает модерации</p>
            <?php else : ?>
                <div class="comment-item__text"><?php echo nl2br(esc_html($content)); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success(['html'=>$html, 'pending'=>$pending]);
});

/* ── DISABLE DEFAULT WP COMMENT FORM ─────────── */
// Мы используем кастомную форму через onegta_comments_template()
// Но оставляем wp_list_comments для рендера
