<?php
/**
 * OneGTA — Яндекс.Диск интеграция
 * inc/yadisk.php
 *
 * Как настроить:
 * 1. Зайди на https://oauth.yandex.ru
 * 2. Создай приложение, выдай права: cloud_api:disk.write, cloud_api:disk.read, cloud_api:disk.info
 * 3. Получи OAuth токен
 * 4. Вставь токен в настройках: Консоль → Настройки → OneGTA → Яндекс.Диск
 */
defined('ABSPATH') || exit;

/* ══════════════════════════════════════════════
   НАСТРОЙКИ В АДМИНКЕ
══════════════════════════════════════════════ */
add_action('admin_menu', function() {
    add_options_page(
        'OneGTA — Яндекс.Диск',
        'OneGTA Яндекс.Диск',
        'manage_options',
        'onegta-yadisk',
        'onegta_yadisk_settings_page'
    );
});

function onegta_yadisk_settings_page() {
    if (isset($_POST['onegta_yadisk_save'])) {
        check_admin_referer('onegta_yadisk_settings');
        update_option('onegta_yadisk_token',  sanitize_text_field($_POST['yadisk_token']  ?? ''));
        update_option('onegta_yadisk_folder', sanitize_text_field($_POST['yadisk_folder'] ?? '/OneGTA'));
        echo '<div class="notice notice-success"><p>Настройки сохранены!</p></div>';
    }
    $token  = get_option('onegta_yadisk_token',  '');
    $folder = get_option('onegta_yadisk_folder', '/OneGTA');
    ?>
    <div class="wrap">
        <h1>OneGTA — Яндекс.Диск</h1>
        <form method="post">
            <?php wp_nonce_field('onegta_yadisk_settings'); ?>
            <table class="form-table">
                <tr>
                    <th>OAuth Токен</th>
                    <td>
                        <input type="text" name="yadisk_token" value="<?php echo esc_attr($token); ?>" class="regular-text" placeholder="y0_AgAAAA...">
                        <p class="description">Получи токен на <a href="https://oauth.yandex.ru" target="_blank">oauth.yandex.ru</a>. Нужны права: cloud_api:disk.write, cloud_api:disk.read</p>
                    </td>
                </tr>
                <tr>
                    <th>Папка на Диске</th>
                    <td>
                        <input type="text" name="yadisk_folder" value="<?php echo esc_attr($folder); ?>" class="regular-text" placeholder="/OneGTA">
                        <p class="description">Папка где будут храниться файлы. Например: /OneGTA/files</p>
                    </td>
                </tr>
            </table>
            <p>
                <button type="submit" name="onegta_yadisk_save" class="button button-primary">Сохранить</button>
                <?php if ($token) : ?>
                    <button type="button" id="testYadisk" class="button">Проверить подключение</button>
                    <span id="testYadiskResult" style="margin-left:1rem;"></span>
                <?php endif; ?>
            </p>
        </form>
        <?php if ($token) : ?>
        <script>
        document.getElementById('testYadisk')?.addEventListener('click', async () => {
            const el = document.getElementById('testYadiskResult');
            el.textContent = 'Проверяем…';
            const fd = new FormData();
            fd.append('action', 'onegta_yadisk_test');
            fd.append('nonce',  '<?php echo wp_create_nonce('onegta_nonce'); ?>');
            const r = await fetch(ajaxurl, {method:'POST',body:fd});
            const d = await r.json();
            el.textContent = d.success ? '✅ ' + d.data.message : '❌ ' + d.data.message;
        });
        </script>
        <?php endif; ?>
    </div>
    <?php
}

/* ══════════════════════════════════════════════
   API КЛАСС
══════════════════════════════════════════════ */
class OneGTA_YaDisk {

    private string $token;
    private string $base_url = 'https://cloud-api.yandex.net/v1/disk';
    private string $base_folder;

    public function __construct() {
        $this->token       = get_option('onegta_yadisk_token',  '');
        $this->base_folder = rtrim(get_option('onegta_yadisk_folder', '/OneGTA'), '/');
    }

    public function is_configured(): bool {
        return !empty($this->token);
    }

    // Заголовки для запросов
    private function headers(): array {
        return [
            'Authorization' => 'OAuth ' . $this->token,
            'Content-Type'  => 'application/json',
        ];
    }

    // GET запрос
    private function get(string $endpoint, array $params = []): array {
        $url      = $this->base_url . $endpoint;
        if ($params) $url .= '?' . http_build_query($params);
        $response = wp_remote_get($url, [
            'headers' => $this->headers(),
            'timeout' => 30,
        ]);
        if (is_wp_error($response)) return ['error' => $response->get_error_message()];
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $code = wp_remote_retrieve_response_code($response);
        return ['code' => $code, 'data' => $body];
    }

    // PUT/POST запрос
    private function request(string $method, string $endpoint, array $params = [], $body = null): array {
        $url      = $this->base_url . $endpoint;
        if ($params) $url .= '?' . http_build_query($params);
        $args = [
            'method'  => strtoupper($method),
            'headers' => $this->headers(),
            'timeout' => 60,
        ];
        if ($body) $args['body'] = is_array($body) ? json_encode($body) : $body;
        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) return ['error' => $response->get_error_message()];
        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        return ['code' => $code, 'data' => $body ? json_decode($body, true) : []];
    }

    // Проверить подключение
    public function test(): array {
        $r = $this->get('/');
        if (isset($r['error'])) return ['success' => false, 'message' => $r['error']];
        if ($r['code'] !== 200) return ['success' => false, 'message' => 'Ошибка авторизации. Проверь токен.'];
        $used  = round(($r['data']['used_space'] ?? 0) / 1073741824, 2);
        $total = round(($r['data']['total_space'] ?? 0) / 1073741824, 2);
        return ['success' => true, 'message' => "Подключено! Использовано: {$used} GB из {$total} GB"];
    }

    // Создать папку (если нет)
    public function ensure_folder(string $path): bool {
        $r = $this->request('PUT', '/resources', ['path' => $path]);
        return in_array($r['code'], [200, 201, 409]); // 409 = уже существует
    }

    // Получить URL для загрузки файла
    public function get_upload_url(string $yadisk_path, bool $overwrite = false): ?string {
        $r = $this->get('/resources/upload', [
            'path'      => $yadisk_path,
            'overwrite' => $overwrite ? 'true' : 'false',
        ]);
        if ($r['code'] !== 200) return null;
        return $r['data']['href'] ?? null;
    }

    // Загрузить файл на Яндекс.Диск
    public function upload_file(string $local_path, string $yadisk_path): array {
        if (!file_exists($local_path))
            return ['success' => false, 'message' => 'Файл не найден'];

        // Убедимся что папка существует
        $dir = dirname($yadisk_path);
        $this->ensure_folder($this->base_folder);
        if ($dir !== $this->base_folder) $this->ensure_folder($dir);

        // Получаем URL для загрузки
        $upload_url = $this->get_upload_url($yadisk_path, true);
        if (!$upload_url)
            return ['success' => false, 'message' => 'Не удалось получить URL загрузки'];

        // Загружаем файл
        $file_data = file_get_contents($local_path);
        $response  = wp_remote_request($upload_url, [
            'method'  => 'PUT',
            'body'    => $file_data,
            'timeout' => 300,
            'headers' => ['Content-Type' => mime_content_type($local_path) ?: 'application/octet-stream'],
        ]);

        if (is_wp_error($response))
            return ['success' => false, 'message' => $response->get_error_message()];

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 201)
            return ['success' => false, 'message' => "Ошибка загрузки (HTTP {$code})"];

        // Публикуем файл чтобы получить публичную ссылку
        $public_url = $this->publish_file($yadisk_path);

        return [
            'success'    => true,
            'message'    => 'Файл загружен на Яндекс.Диск',
            'yadisk_path'=> $yadisk_path,
            'public_url' => $public_url,
        ];
    }

    // Опубликовать файл (получить публичную ссылку)
    public function publish_file(string $yadisk_path): ?string {
        $r = $this->request('PUT', '/resources/publish', ['path' => $yadisk_path]);
        if ($r['code'] !== 200) return null;

        // Получаем мета файла с публичной ссылкой
        $info = $this->get('/resources', ['path' => $yadisk_path, 'fields' => 'public_url,public_key']);
        return $info['data']['public_url'] ?? null;
    }

    // Удалить файл с Диска
    public function delete_file(string $yadisk_path): bool {
        $r = $this->request('DELETE', '/resources', ['path' => $yadisk_path, 'permanently' => 'true']);
        return in_array($r['code'], [200, 204]);
    }

    // Список файлов в папке
    public function list_files(string $folder = '', int $limit = 50): array {
        $path = $folder ?: $this->base_folder;
        $r    = $this->get('/resources', ['path' => $path, 'limit' => $limit, 'sort' => 'created']);
        if ($r['code'] !== 200) return [];
        return $r['data']['_embedded']['items'] ?? [];
    }

    // Скачать счётчик (прямые ссылки не хранятся, возвращаем публичную)
    public function get_download_url(string $yadisk_path): ?string {
        $r = $this->get('/resources/download', ['path' => $yadisk_path]);
        if ($r['code'] !== 200) return null;
        return $r['data']['href'] ?? null;
    }
}

/* ══════════════════════════════════════════════
   ГЛОБАЛЬНЫЙ ИНСТАНС
══════════════════════════════════════════════ */
function onegta_yadisk(): OneGTA_YaDisk {
    static $instance = null;
    if (!$instance) $instance = new OneGTA_YaDisk();
    return $instance;
}

/* ══════════════════════════════════════════════
   AJAX: ТЕСТ ПОДКЛЮЧЕНИЯ
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_yadisk_test', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Нет прав']);
    $result = onegta_yadisk()->test();
    $result['success'] ? wp_send_json_success($result) : wp_send_json_error($result);
});

/* ══════════════════════════════════════════════
   AJAX: ЗАГРУЗИТЬ ФАЙЛ НА ЯНДЕКС.ДИСК (фронтенд)
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_yadisk_upload', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Необходима авторизация']);

    $yd = onegta_yadisk();
    if (!$yd->is_configured())
        wp_send_json_error(['message' => 'Яндекс.Диск не настроен. Обратитесь к администратору.']);

    if (empty($_FILES['file_upload']['name']))
        wp_send_json_error(['message' => 'Файл не выбран']);

    $file    = $_FILES['file_upload'];
    $name    = sanitize_file_name($file['name']);
    $tmp     = $file['tmp_name'];
    $size    = $file['size'];
    $max_size= 100 * 1024 * 1024; // 100 MB

    if ($size > $max_size)
        wp_send_json_error(['message' => 'Файл слишком большой. Максимум 100 MB.']);

    // Проверка расширения
    $allowed_ext = ['zip','rar','7z','exe','dll','asi','oiv','cs','lua','xml','json','ini','cfg','txt','pdf','img','iso'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext))
        wp_send_json_error(['message' => "Тип файла .{$ext} не разрешён"]);

    // Путь на Диске: /OneGTA/2026/03/filename.zip
    $base_folder = get_option('onegta_yadisk_folder', '/OneGTA');
    $date_folder = date('Y/m');
    $uid         = get_current_user_id();
    $unique_name = $uid . '_' . time() . '_' . $name;
    $yadisk_path = "{$base_folder}/{$date_folder}/{$unique_name}";

    $result = $yd->upload_file($tmp, $yadisk_path);

    if (!$result['success'])
        wp_send_json_error(['message' => $result['message']]);

    // Возвращаем публичную ссылку и путь
    wp_send_json_success([
        'message'     => 'Файл загружен на Яндекс.Диск!',
        'public_url'  => $result['public_url'],
        'yadisk_path' => $yadisk_path,
        'file_size'   => size_format($size),
    ]);
});

/* ══════════════════════════════════════════════
   AJAX: ЗАГРУЗИТЬ ФАЙЛ И СОЗДАТЬ ПОСТ СРАЗУ
══════════════════════════════════════════════ */
add_action('wp_ajax_onegta_submit_file_yadisk', function() {
    check_ajax_referer('onegta_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Необходима авторизация']);

    $title    = sanitize_text_field($_POST['title']    ?? '');
    $content  = wp_kses_post($_POST['content']         ?? '');
    $excerpt  = sanitize_textarea_field($_POST['excerpt'] ?? '');
    $category = absint($_POST['file_category']         ?? 0);
    $game     = sanitize_text_field($_POST['file_game']?? '');
    $version  = sanitize_text_field($_POST['file_version'] ?? '');

    if (!$title) wp_send_json_error(['message' => 'Введите название файла']);

    $yd = onegta_yadisk();

    // Шаг 1: загружаем файл на ЯД если он есть
    $file_url  = '';
    $file_size = '';

    if (!empty($_FILES['file_upload']['name'])) {
        if (!$yd->is_configured())
            wp_send_json_error(['message' => 'Яндекс.Диск не настроен. Используйте внешнюю ссылку.']);

        $file    = $_FILES['file_upload'];
        $name    = sanitize_file_name($file['name']);
        $size    = $file['size'];
        $max_mb  = 100;

        if ($size > $max_mb * 1024 * 1024)
            wp_send_json_error(['message' => "Максимальный размер файла {$max_mb} MB"]);

        $base_folder = get_option('onegta_yadisk_folder', '/OneGTA');
        $uid         = get_current_user_id();
        $unique_name = $uid . '_' . time() . '_' . $name;
        $yadisk_path = "{$base_folder}/" . date('Y/m') . "/{$unique_name}";

        $result = $yd->upload_file($file['tmp_name'], $yadisk_path);
        if (!$result['success'])
            wp_send_json_error(['message' => 'Ошибка загрузки: ' . $result['message']]);

        $file_url  = $result['public_url'] ?? '';
        $file_size = size_format($size);

        // Сохраняем yadisk_path для возможного удаления
        // (добавим к мете поста)
        $yadisk_path_saved = $yadisk_path;

    } elseif (!empty($_POST['file_url'])) {
        $file_url = esc_url_raw($_POST['file_url']);
    } else {
        wp_send_json_error(['message' => 'Загрузите файл или укажите внешнюю ссылку']);
    }

    // Шаг 2: создаём пост
    $status  = current_user_can('publish_posts') ? 'publish' : 'pending';
    $post_id = wp_insert_post([
        'post_title'   => $title,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_status'  => $status,
        'post_type'    => 'files',
        'post_author'  => get_current_user_id(),
    ]);

    if (is_wp_error($post_id))
        wp_send_json_error(['message' => $post_id->get_error_message()]);

    // Мета
    update_post_meta($post_id, 'file_url',      $file_url);
    update_post_meta($post_id, 'file_size',     $file_size);
    update_post_meta($post_id, 'file_version',  $version);
    update_post_meta($post_id, 'file_game',     $game);
    update_post_meta($post_id, 'file_downloads', 0);
    update_post_meta($post_id, 'file_source',   'yadisk');
    if (!empty($yadisk_path_saved))
        update_post_meta($post_id, 'yadisk_path', $yadisk_path_saved);

    // Категория
    if ($category) wp_set_post_terms($post_id, [$category], 'file_category');

    // Превью
    if (!empty($_FILES['thumbnail']['name'])) {
        require_once ABSPATH.'wp-admin/includes/image.php';
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/media.php';
        $att = media_handle_upload('thumbnail', $post_id);
        if (!is_wp_error($att)) set_post_thumbnail($post_id, $att);
    }

    $msg = $status === 'publish' ? 'Файл добавлен!' : 'Отправлено на модерацию';
    wp_send_json_success([
        'message'  => $msg,
        'url'      => get_permalink($post_id),
        'file_url' => $file_url,
        'on_yadisk'=> $yd->is_configured() && !empty($yadisk_path_saved),
    ]);
});

/* ══════════════════════════════════════════════
   AJAX: СКАЧАТЬ ФАЙЛ (счётчик + редирект)
  Переопределяем старый onegta_download
══════════════════════════════════════════════ */
remove_action('wp_ajax_onegta_download',        'onegta_count_download');
remove_action('wp_ajax_nopriv_onegta_download', 'onegta_count_download');

add_action('wp_ajax_onegta_download',        'onegta_yadisk_download');
add_action('wp_ajax_nopriv_onegta_download', 'onegta_yadisk_download');

function onegta_yadisk_download() {
    check_ajax_referer('onegta_nonce', 'nonce');
    $pid = absint($_POST['post_id'] ?? 0);
    if (!$pid) wp_send_json_error();

    // Счётчик
    $count = (int)get_post_meta($pid, 'file_downloads', true);
    update_post_meta($pid, 'file_downloads', $count + 1);

    // Получаем URL
    $source     = get_post_meta($pid, 'file_source', true);
    $yadisk_path= get_post_meta($pid, 'yadisk_path', true);
    $file_url   = get_post_meta($pid, 'file_url', true);

    // Если файл на ЯД и есть путь — получаем свежую ссылку на скачивание
    if ($source === 'yadisk' && $yadisk_path && onegta_yadisk()->is_configured()) {
        $dl_url = onegta_yadisk()->get_download_url($yadisk_path);
        if ($dl_url) {
            wp_send_json_success(['url' => $dl_url, 'count' => $count + 1]);
            return;
        }
    }

    // Иначе отдаём сохранённую ссылку
    wp_send_json_success(['url' => esc_url($file_url), 'count' => $count + 1]);
}

/* ══════════════════════════════════════════════
   ПРИ УДАЛЕНИИ ПОСТА — УДАЛЯЕМ ФАЙЛ С ЯД
══════════════════════════════════════════════ */
add_action('before_delete_post', function($post_id) {
    if (get_post_type($post_id) !== 'files') return;
    $yadisk_path = get_post_meta($post_id, 'yadisk_path', true);
    if ($yadisk_path && onegta_yadisk()->is_configured()) {
        onegta_yadisk()->delete_file($yadisk_path);
    }
});
