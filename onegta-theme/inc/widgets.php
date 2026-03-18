<?php
/**
 * OneGTA — Виджеты сайдбара
 */
defined('ABSPATH') || exit;

/* ── WIDGET: ТОП СКАЧИВАНИЙ ──────────────────── */
class OneGTA_TopDownloads_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct('onegta_top_downloads', 'OneGTA: Топ скачиваний', ['description'=>'Топ самых скачиваемых файлов']);
    }

    public function widget($args, $instance) {
        $count = absint($instance['count'] ?? 5);
        $files = get_posts([
            'post_type'      => 'files',
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'meta_key'       => 'file_downloads',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ]);
        if (!$files) return;

        echo $args['before_widget'];
        echo $args['before_title'] . 'Топ скачиваний' . $args['after_title'];
        echo '<div class="widget-top-downloads">';
        foreach ($files as $i => $file) :
            $dl   = (int)get_post_meta($file->ID, 'file_downloads', true);
            $game = get_post_meta($file->ID, 'file_game', true);
            ?>
            <div class="sidebar-recent-item" style="position:relative;">
                <div style="width:28px;height:28px;background:<?php echo $i===0?'var(--orange)':'var(--bg)'; ?>;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue',sans-serif;font-size:1rem;color:<?php echo $i===0?'#fff':'var(--text3)'; ?>;flex-shrink:0;">
                    <?php echo $i+1; ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <a href="<?php echo esc_url(get_permalink($file)); ?>" class="sidebar-recent-item__title"><?php echo esc_html($file->post_title); ?></a>
                    <div class="sidebar-recent-item__date">
                        <?php if ($game) echo esc_html($game).' · '; ?>
                        <?php echo esc_html(number_format($dl)); ?> скач.
                    </div>
                </div>
            </div>
        <?php endforeach;
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($instance) {
        $count = absint($instance['count'] ?? 5);
        echo '<p><label>Кол-во файлов: <input type="number" name="'.$this->get_field_name('count').'" value="'.esc_attr($count).'" min="1" max="20" style="width:60px;"></label></p>';
    }

    public function update($new, $old) {
        return ['count' => absint($new['count'] ?? 5)];
    }
}

/* ── WIDGET: ПОСЛЕДНИЕ ФАЙЛЫ ─────────────────── */
class OneGTA_RecentFiles_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct('onegta_recent_files', 'OneGTA: Последние файлы', ['description'=>'Свежие файлы в архиве']);
    }

    public function widget($args, $instance) {
        $count    = absint($instance['count'] ?? 5);
        $category = sanitize_text_field($instance['category'] ?? '');
        $query    = ['post_type'=>'files','posts_per_page'=>$count,'post_status'=>'publish'];
        if ($category) $query['tax_query'] = [['taxonomy'=>'file_category','field'=>'slug','terms'=>$category]];
        $files = get_posts($query);
        if (!$files) return;

        echo $args['before_widget'];
        echo $args['before_title'] . 'Последние файлы' . $args['after_title'];
        echo '<div class="widget-recent-files">';
        foreach ($files as $file) :
            $size = get_post_meta($file->ID, 'file_size', true) ?: '';
            $cats = get_the_terms($file->ID, 'file_category');
            $cat  = $cats && !is_wp_error($cats) ? $cats[0]->name : '';
            ?>
            <div class="sidebar-recent-item">
                <div style="width:36px;height:36px;background:var(--orange-pale);border:1px solid var(--orange-mid);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">📦</div>
                <div style="flex:1;min-width:0;">
                    <a href="<?php echo esc_url(get_permalink($file)); ?>" class="sidebar-recent-item__title"><?php echo esc_html($file->post_title); ?></a>
                    <div class="sidebar-recent-item__date">
                        <?php echo $cat ? esc_html($cat) : ''; ?>
                        <?php echo $size ? ' · '.esc_html($size) : ''; ?>
                    </div>
                </div>
            </div>
        <?php endforeach;
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($instance) {
        $count = absint($instance['count'] ?? 5);
        $cat   = sanitize_text_field($instance['category'] ?? '');
        echo '<p><label>Кол-во: <input type="number" name="'.$this->get_field_name('count').'" value="'.esc_attr($count).'" min="1" max="20" style="width:60px;"></label></p>';
        echo '<p><label>Категория (slug):<br><input type="text" name="'.$this->get_field_name('category').'" value="'.esc_attr($cat).'" class="widefat" placeholder="Все категории"></label></p>';
    }

    public function update($n, $o) { return ['count'=>absint($n['count']??5),'category'=>sanitize_text_field($n['category']??'')]; }
}

/* ── WIDGET: ПОСЛЕДНИЕ НОВОСТИ ───────────────── */
class OneGTA_RecentNews_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct('onegta_recent_news', 'OneGTA: Последние новости', ['description'=>'Свежие новости с превью']);
    }

    public function widget($args, $instance) {
        $count = absint($instance['count'] ?? 4);
        $posts = get_posts(['post_type'=>'news','posts_per_page'=>$count,'post_status'=>'publish']);
        if (!$posts) $posts = get_posts(['posts_per_page'=>$count,'post_status'=>'publish']);
        if (!$posts) return;

        echo $args['before_widget'];
        echo $args['before_title'] . 'Последние новости' . $args['after_title'];
        foreach ($posts as $p) : ?>
            <div class="sidebar-recent-item">
                <?php if (has_post_thumbnail($p->ID)) : ?>
                    <a href="<?php echo esc_url(get_permalink($p)); ?>" class="sidebar-recent-item__thumb">
                        <?php echo get_the_post_thumbnail($p->ID, 'onegta-thumb', ['alt'=>'']); ?>
                    </a>
                <?php endif; ?>
                <div>
                    <a href="<?php echo esc_url(get_permalink($p)); ?>" class="sidebar-recent-item__title"><?php echo esc_html($p->post_title); ?></a>
                    <div class="sidebar-recent-item__date"><?php echo esc_html(onegta_date($p->ID)); ?></div>
                </div>
            </div>
        <?php endforeach;
        echo $args['after_widget'];
    }

    public function form($instance) {
        $count = absint($instance['count'] ?? 4);
        echo '<p><label>Кол-во: <input type="number" name="'.$this->get_field_name('count').'" value="'.esc_attr($count).'" min="1" max="10" style="width:60px;"></label></p>';
    }

    public function update($n,$o) { return ['count'=>absint($n['count']??4)]; }
}

/* ── WIDGET: ИГРЫ GTA ────────────────────────── */
class OneGTA_GamesWidget extends WP_Widget {

    public function __construct() {
        parent::__construct('onegta_games_widget', 'OneGTA: Игры серии', ['description'=>'Список игр серии GTA со ссылками']);
    }

    public function widget($args, $instance) {
        $games = get_terms(['taxonomy'=>'game_title','hide_empty'=>false,'orderby'=>'term_order']);
        if (!$games || is_wp_error($games)) return;

        echo $args['before_widget'];
        echo $args['before_title'] . 'Игры серии GTA' . $args['after_title'];
        echo '<ul class="footer-col__links" style="padding:0;">';
        foreach ($games as $g) {
            echo '<li><a href="'.esc_url(get_term_link($g)).'">'.esc_html($g->name).'</a></li>';
        }
        echo '</ul>';
        echo $args['after_widget'];
    }

    public function form($i) { echo '<p>Список формируется автоматически из таксономии "Игры GTA".</p>'; }
    public function update($n,$o) { return []; }
}

/* ── WIDGET: СТАТИСТИКА ──────────────────────── */
class OneGTA_Stats_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct('onegta_stats', 'OneGTA: Статистика', ['description'=>'Статистика сайта']);
    }

    public function widget($args, $instance) {
        $news_count    = wp_count_posts('news')->publish;
        $articles_count= wp_count_posts('articles')->publish;
        $files_count   = wp_count_posts('files')->publish;
        $videos_count  = wp_count_posts('videos')->publish;
        $users_count   = count_users()['total_users'];

        echo $args['before_widget'];
        echo $args['before_title'] . 'Статистика' . $args['after_title'];
        $stats = [
            ['📰', 'Новостей',  $news_count],
            ['📖', 'Статей',    $articles_count],
            ['📦', 'Файлов',    $files_count],
            ['🎬', 'Видео',     $videos_count],
            ['👥', 'Участников',$users_count],
        ];
        echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:3px;">';
        foreach ($stats as [$icon, $label, $count]) {
            echo '<div style="background:var(--bg);padding:.8rem;text-align:center;">';
            echo '<div style="font-size:1.3rem;">'.$icon.'</div>';
            echo '<div style="font-family:\'Bebas Neue\',sans-serif;font-size:1.5rem;color:var(--orange);line-height:1;">'.number_format($count).'</div>';
            echo '<div style="font-size:.65rem;color:var(--text3);letter-spacing:1px;text-transform:uppercase;">'.esc_html($label).'</div>';
            echo '</div>';
        }
        echo '</div>';
        echo $args['after_widget'];
    }

    public function form($i) { echo '<p>Статистика формируется автоматически.</p>'; }
    public function update($n,$o) { return []; }
}

/* ── REGISTER ALL WIDGETS ────────────────────── */
add_action('widgets_init', function() {
    register_widget('OneGTA_TopDownloads_Widget');
    register_widget('OneGTA_RecentFiles_Widget');
    register_widget('OneGTA_RecentNews_Widget');
    register_widget('OneGTA_GamesWidget');
    register_widget('OneGTA_Stats_Widget');
});
