# WP Snippet: Bilingual Pages

Simple bilingual system for WordPress pages without plugins. Creates separate post type for second language with automatic lang and hreflang management.

## Features

- Two separate post types for bilingual **pages**
- Automatic lang attribute management (`<html lang="...">`)
- Automatic hreflang tags for SEO
- Bidirectional translation linking
- Configurable second language (en-GB, de-DE, etc.)
- Page templates support for second language pages
- **Designed for simple sites using only Pages**
- No plugins required

## Current Limitations

- Works only with **Pages** (not Posts)
- Blog functionality should be disabled - you can use these snippets:
  - [Disable Posts](https://github.com/Torwald45/wp-snippet-disable-posts-menu) - removes posts from admin
  - [Disable Comments](https://github.com/Torwald45/wp-snippet-disable-comments) - removes comments functionality
- Designed for simple, page-based websites

## Future Improvements

See [Issues](https://github.com/Torwald45/wp-snippet-bilingual-pages/issues) for planned features:
- Add support for Posts
- Add support for Custom Post Types

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Site using only Pages (Posts disabled)

## Installation

### Method 1: functions.php

1. Open your theme's `functions.php` file
2. Copy the entire content from `bilingual-pages.php`
3. Paste at the end of your `functions.php`
4. Save the file

### Method 2: Code Snippets Plugin (Recommended)

1. Install and activate the [Code Snippets](https://wordpress.org/plugins/code-snippets/) plugin
2. Go to Snippets → Add New
3. Copy content from `bilingual-pages.php` **WITHOUT the opening `<?php` tag**
4. Paste into the Code field
5. Activate the snippet

## Configuration

### First Language (Default)
The **first language** is automatically taken from WordPress settings:
- Go to **Settings → General → Site Language**
- Select your primary language (e.g., "Polski" for Polish)
- This will be used for standard WordPress Pages

### Second Language
Edit the language code at the top of the snippet:
```php
define('TORWALD45_BL_SECOND_LANG', 'en-GB'); // Change to your second language
```

Available language codes examples:
- `en-GB` - English (UK)
- `en-US` - English (US)
- `de-DE` - German
- `fr-FR` - French
- `es-ES` - Spanish
- `it-IT` - Italian

**Example:** If your WordPress is set to Polish (`pl-PL`) and you configure `en-GB`:
- Standard Pages = Polish pages
- Custom "Pages EN" = English pages

## Usage

### 1. Flush Rewrite Rules

After activation, flush rewrite rules:
- Go to Settings → Permalinks
- Click "Save Changes" (no need to change anything)

Or via WP-CLI:
```bash
wp rewrite flush --hard --allow-root
```

### 2. Create Pages

1. **First language pages**: Create as normal WordPress Pages
2. **Second language pages**: Use the new "Pages EN" menu (or your configured language)

### 3. Link Translations

In the page editor sidebar, you'll see a "Translation" metabox:
1. Select the corresponding page in the other language
2. Save the page
3. The link is bidirectional (both pages will be linked automatically)

### 4. Verify

Check your page source code:
- Lang attribute: `<html lang="pl-PL">` or `<html lang="en-GB">`
- Hreflang tags in `<head>`:
```html
  <link rel="alternate" hreflang="pl-PL" href="..." />
  <link rel="alternate" hreflang="en-GB" href="..." />
  <link rel="alternate" hreflang="x-default" href="..." />
```

## Technical Details

### Database
- Meta key: `_torwald45_bl_translation_id` (stored in wp_postmeta)

### Custom Post Type
- Name: `torwald45_bl_[lang]` (e.g., `torwald45_bl_en`)
- Max length: 15 characters (safe under WordPress 20-char limit)
- URL pattern: `/en/page-slug/`

### HTML Elements
- Metabox ID: `torwald45_bl_translation`
- Select field ID: `torwald45_bl_translation_id`

### Hooks Used
- `init` - register custom post type
- `add_meta_boxes` - add translation metabox
- `save_post` - save bidirectional translation links
- `language_attributes` - modify lang attribute
- `wp_head` - add hreflang tags (priority 1)
- `theme_page_templates` - enable page templates
- `template_include` - apply page templates

### Functions
All functions use prefix `torwald45_bl_` to prevent conflicts:
- `torwald45_bl_get_first_language()`
- `torwald45_bl_get_short_code()`
- `torwald45_bl_get_language_name()`
- `torwald45_bl_translation_metabox()`

## Troubleshooting

### Second language pages return 404
**Solution:** Flush rewrite rules (Settings → Permalinks → Save)

### Hreflang tags not appearing
**Possible causes:**
1. Pages are not linked (check Translation metabox)
2. Cache (clear browser cache and any WordPress caching plugins)

### Lang attribute not changing
**Possible cause:** Theme overrides `language_attributes` filter. Check your theme's functions.php.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## License

GPL v2 or later

## Author

[Torwald45](https://github.com/Torwald45)
