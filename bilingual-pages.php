<?php
/**
 * Bilingual Pages System
 * 
 * Simple bilingual system for WordPress pages without plugins.
 * Creates separate post type for second language with automatic lang and hreflang management.
 * 
 * @author      Torwald45
 * @link        https://github.com/Torwald45/wp-snippet-bilingual-pages
 * @license     GPL-2.0-or-later
 * @version     1.0.2
 */

// Configuration: Set your second language code
define('TORWALD45_BL_SECOND_LANG', 'en-GB'); // Change to 'de-DE' or other language code

/**
 * Get first language from WordPress settings
 */
function torwald45_bl_get_first_language() {
    return get_locale();
}

/**
 * Get language short code (e.g., 'en', 'de')
 */
function torwald45_bl_get_short_code($locale) {
    $parts = explode('-', $locale);
    return strtolower($parts[0]);
}

/**
 * Get language name for display (e.g., 'EN', 'DE')
 */
function torwald45_bl_get_language_name($locale) {
    $parts = explode('-', $locale);
    return strtoupper($parts[0]);
}

/**
 * Convert WordPress locale to hreflang format
 * Example: pl_PL -> pl-PL
 */
function torwald45_bl_locale_to_hreflang($locale) {
    return str_replace('_', '-', $locale);
}

/**
 * Register custom post type for second language pages
 */
add_action('init', function() {
    $second_lang_short = torwald45_bl_get_short_code(TORWALD45_BL_SECOND_LANG);
    $second_lang_name = torwald45_bl_get_language_name(TORWALD45_BL_SECOND_LANG);
    
    $labels = [
        'name' => 'Pages ' . $second_lang_name,
        'singular_name' => 'Page ' . $second_lang_name,
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Page ' . $second_lang_name,
        'edit_item' => 'Edit Page ' . $second_lang_name,
        'new_item' => 'New Page ' . $second_lang_name,
        'view_item' => 'View Page ' . $second_lang_name,
        'search_items' => 'Search Pages ' . $second_lang_name,
        'not_found' => 'No pages found',
        'not_found_in_trash' => 'No pages found in Trash',
        'parent_item_colon' => 'Parent Page:',
        'all_items' => 'All Pages ' . $second_lang_name,
        'menu_name' => 'Pages ' . $second_lang_name,
    ];
    
    $args = [
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => ['slug' => $second_lang_short, 'with_front' => false],
        'capability_type' => 'page',
        'has_archive' => false,
        'hierarchical' => true,
        'menu_position' => 21,
        'menu_icon' => 'dashicons-admin-page',
        'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields', 'revisions'],
        'show_in_rest' => true,
    ];
    
    register_post_type('torwald45_bl_' . $second_lang_short, $args);
});

/**
 * Add meta box for translation selection
 */
add_action('add_meta_boxes', function() {
    $second_lang_short = torwald45_bl_get_short_code(TORWALD45_BL_SECOND_LANG);
    
    // Meta box for standard pages (first language)
    add_meta_box(
        'torwald45_bl_translation',
        'Translation',
        'torwald45_bl_translation_metabox',
        'page',
        'side',
        'default'
    );
    
    // Meta box for second language pages
    add_meta_box(
        'torwald45_bl_translation',
        'Translation',
        'torwald45_bl_translation_metabox',
        'torwald45_bl_' . $second_lang_short,
        'side',
        'default'
    );
});

function torwald45_bl_translation_metabox($post) {
    $second_lang_short = torwald45_bl_get_short_code(TORWALD45_BL_SECOND_LANG);
    $second_lang_name = torwald45_bl_get_language_name(TORWALD45_BL_SECOND_LANG);
    $first_lang_name = torwald45_bl_get_language_name(torwald45_bl_get_first_language());
    
    $translation_id = get_post_meta($post->ID, '_torwald45_bl_translation_id', true);
    
    // Determine current and opposite post type
    $current_post_type = $post->post_type;
    if ($current_post_type === 'page') {
        $opposite_post_type = 'torwald45_bl_' . $second_lang_short;
        $current_lang = $first_lang_name;
        $opposite_lang = $second_lang_name;
    } else {
        $opposite_post_type = 'page';
        $current_lang = $second_lang_name;
        $opposite_lang = $first_lang_name;
    }
    
    // Get pages in opposite language
    $pages = get_posts([
        'post_type' => $opposite_post_type,
        'posts_per_page' => -1,
        'post_status' => 'any',
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    
    wp_nonce_field('torwald45_bl_translation_nonce', 'torwald45_bl_translation_nonce');
    
    echo '<p><strong>Current language:</strong> ' . esc_html($current_lang) . '</p>';
    echo '<label for="torwald45_bl_translation_id">Select ' . esc_html($opposite_lang) . ' translation:</label><br>';
    echo '<select name="torwald45_bl_translation_id" id="torwald45_bl_translation_id" style="width: 90%;">';
    echo '<option value="">-- None --</option>';
    
    foreach ($pages as $page) {
        $selected = ($translation_id == $page->ID) ? 'selected' : '';
        echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>';
        echo esc_html($page->post_title) . ' (ID: ' . $page->ID . ')';
        echo '</option>';
    }
    
    echo '</select>';
}

/**
 * Save translation relationship (bidirectional)
 */
add_action('save_post', function($post_id, $post) {
    $second_lang_short = torwald45_bl_get_short_code(TORWALD45_BL_SECOND_LANG);
    
    // Only for our post types
    if (!in_array($post->post_type, ['page', 'torwald45_bl_' . $second_lang_short])) {
        return;
    }
    
    // Check nonce and autosave
    if (!isset($_POST['torwald45_bl_translation_nonce']) || 
        !wp_verify_nonce($_POST['torwald45_bl_translation_nonce'], 'torwald45_bl_translation_nonce') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $old_translation_id = get_post_meta($post_id, '_torwald45_bl_translation_id', true);
    $new_translation_id = isset($_POST['torwald45_bl_translation_id']) ? intval($_POST['torwald45_bl_translation_id']) : 0;
    
    // Remove old bidirectional link
    if ($old_translation_id && $old_translation_id != $new_translation_id) {
        delete_post_meta($old_translation_id, '_torwald45_bl_translation_id');
    }
    
    // Set new translation
    if ($new_translation_id) {
        update_post_meta($post_id, '_torwald45_bl_translation_id', $new_translation_id);
        // Set bidirectional link
        update_post_meta($new_translation_id, '_torwald45_bl_translation_id', $post_id);
    } else {
        delete_post_meta($post_id, '_torwald45_bl_translation_id');
    }
}, 10, 2);

/**
 * Change lang attribute in HTML tag
 */
add_filter('language_attributes', function($output) {
    $second_lang_short = torwald45_bl_get_short_code(TORWALD45_BL_SECOND_LANG);
    
    if (is_page()) {
        $first_lang = torwald45_bl_locale_to_hreflang(torwald45_bl_get_first_language());
        return 'lang="' . esc_attr($first_lang) . '"';
    }
    
    if (is_singular('torwald45_bl_' . $second_lang_short)) {
        return 'lang="' . esc_attr(TORWALD45_BL_SECOND_LANG) . '"';
    }
    
    return $output;
});

/**
 * Add hreflang links to head
 */
add_action('wp_head', function() {
    $second_lang_short = torwald45_bl_get_short_code(TORWALD45_BL_SECOND_LANG);
    
    // Check if we're on a page or second language page
    if (!is_page() && !is_singular('torwald45_bl_' . $second_lang_short)) {
        return;
    }
    
    $current_id = get_the_ID();
    $translation_id = get_post_meta($current_id, '_torwald45_bl_translation_id', true);
    
    if (!$translation_id) {
        return;
    }
    
    // Determine languages (convert to hreflang format)
    $current_post_type = get_post_type($current_id);
    if ($current_post_type === 'page') {
        $current_language = torwald45_bl_locale_to_hreflang(torwald45_bl_get_first_language());
        $translation_language = TORWALD45_BL_SECOND_LANG;
    } else {
        $current_language = TORWALD45_BL_SECOND_LANG;
        $translation_language = torwald45_bl_locale_to_hreflang(torwald45_bl_get_first_language());
    }
    
    $current_url = get_permalink($current_id);
    $translation_url = get_permalink($translation_id);
    
    echo '<link rel="alternate" hreflang="' . esc_attr($current_language) . '" href="' . esc_url($current_url) . '" />' . "\n";
    echo '<link rel="alternate" hreflang="' . esc_attr($translation_language) . '" href="' . esc_url($translation_url) . '" />' . "\n";
    // x-default always points to first language (default)
    $default_url = ($current_post_type === 'page') ? $current_url : $translation_url;
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($default_url) . '" />' . "\n";
}, 1);

/**
 * Enable page templates for second language pages
 */
add_filter('theme_page_templates', function($templates, $theme, $post) {
    $second_lang_short = torwald45_bl_get_short_code(TORWALD45_BL_SECOND_LANG);
    
    if ($post && $post->post_type === 'torwald45_bl_' . $second_lang_short) {
        return $templates;
    }
    
    return $templates;
}, 10, 3);

add_filter('template_include', function($template) {
    $second_lang_short = torwald45_bl_get_short_code(TORWALD45_BL_SECOND_LANG);
    
    if (is_singular('torwald45_bl_' . $second_lang_short)) {
        $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);
        
        if ($page_template && $page_template != 'default') {
            $new_template = locate_template([$page_template]);
            if ($new_template) {
                return $new_template;
            }
        }
    }
    
    return $template;
});
