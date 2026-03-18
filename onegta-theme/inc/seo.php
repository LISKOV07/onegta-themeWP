<?php
/**
 * OneGTA — SEO мета-теги
 */
defined('ABSPATH') || exit;

add_action('wp_head', 'onegta_seo_meta', 1);

function onegta_seo_meta() {
    global $post;

    $site_name  = get_bloginfo('name');
    $site_url   = home_url('/');
    $site_desc  = get_bloginfo('description') ?: 'Фан-портал серии Grand Theft Auto — новости, гайды, файлы, читы';
    $default_img= ONEGTA_URL . '/images/og-default.jpg';

    // Defaults
    $title    = get_bloginfo('name');
    $desc     = $site_desc;
    $url      = get_permalink() ?: $site_url;
    $img      = $default_img;
    $type     = 'website';
    $robots   = 'index, follow';
    $schema   = [];

    if (is_singular()) {
        $title = get_the_title() . ' — ' . $site_name;
        $desc  = get_the_excerpt() ?: wp_trim_words(get_the_content(), 30);
        $url   = get_permalink();
        $type  = 'article';

        if (has_post_thumbnail()) {
            $img_data = wp_get_attachment_image_src(get_post_thumbnail_id(), 'onegta-featured');
            if ($img_data) $img = $img_data[0];
        }

        // Article schema
        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'Article',
            'headline'        => get_the_title(),
            'description'     => $desc,
            'url'             => $url,
            'datePublished'   => get_the_date('c'),
            'dateModified'    => get_the_modified_date('c'),
            'author'          => [
                '@type' => 'Person',
                'name'  => get_the_author(),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name'  => $site_name,
                'url'   => $site_url,
            ],
            'image' => $img,
        ];

        // File schema
        if (get_post_type() === 'files') {
            $schema['@type'] = 'SoftwareApplication';
            $schema['applicationCategory'] = 'GameApplication';
            $schema['operatingSystem']     = 'Windows';
            $dl = (int)get_post_meta(get_the_ID(), 'file_downloads', true);
            if ($dl) $schema['downloadCount'] = $dl;
        }

    } elseif (is_archive() || is_tax()) {
        $term  = get_queried_object();
        $title = ($term->name ?? 'Архив') . ' — ' . $site_name;
        $desc  = $term->description ?? $site_desc;
        $url   = get_term_link($term) ?? $site_url;

        $schema = [
            '@context'  => 'https://schema.org',
            '@type'     => 'CollectionPage',
            'name'      => $term->name ?? 'Архив',
            'url'       => $url,
            'description' => $desc,
        ];

    } elseif (is_home() || is_front_page()) {
        $title = $site_name . ' — ' . $site_desc;
        $desc  = $site_desc;
        $url   = $site_url;

        $schema = [
            '@context'  => 'https://schema.org',
            '@type'     => 'WebSite',
            'name'      => $site_name,
            'url'       => $site_url,
            'description' => $desc,
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => $site_url . '?s={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];

    } elseif (is_search()) {
        $title   = 'Поиск: ' . get_search_query() . ' — ' . $site_name;
        $desc    = 'Результаты поиска по запросу: ' . get_search_query();
        $robots  = 'noindex, follow';

    } elseif (is_404()) {
        $title   = '404 — Страница не найдена — ' . $site_name;
        $robots  = 'noindex, follow';
    }

    // Clean desc
    $desc = wp_strip_all_tags($desc);
    $desc = str_replace(["\n","\r","\t"], ' ', $desc);
    $desc = substr($desc, 0, 160);

    ?>
<!-- OneGTA SEO -->
<meta name="description" content="<?php echo esc_attr($desc); ?>">
<meta name="robots" content="<?php echo esc_attr($robots); ?>">
<link rel="canonical" href="<?php echo esc_url($url); ?>">

<!-- Open Graph -->
<meta property="og:type" content="<?php echo esc_attr($type); ?>">
<meta property="og:title" content="<?php echo esc_attr($title); ?>">
<meta property="og:description" content="<?php echo esc_attr($desc); ?>">
<meta property="og:url" content="<?php echo esc_url($url); ?>">
<meta property="og:image" content="<?php echo esc_url($img); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
<meta property="og:locale" content="ru_RU">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr($title); ?>">
<meta name="twitter:description" content="<?php echo esc_attr($desc); ?>">
<meta name="twitter:image" content="<?php echo esc_url($img); ?>">

<?php if ($schema) : ?>
<!-- Schema.org -->
<script type="application/ld+json"><?php echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<?php endif; ?>

<!-- Misc -->
<meta name="theme-color" content="#F55C00">
<meta name="msapplication-TileColor" content="#F55C00">
<?php
}

/* ── TITLE TAG ───────────────────────────────── */
add_filter('document_title_separator', fn() => '—');
add_filter('document_title_parts', function($parts) {
    $parts['tagline'] = '';
    return array_filter($parts);
});
