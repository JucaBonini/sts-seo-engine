<?php
/**
 * Plugin Name: SEO Engine Pro
 * Plugin URI: https://descomplicandoreceitas.com.br
 * Description: Motor de SEO nativo de alta performance focado em Compliance GEO, Schema dinâmico e Sitemaps inteligentes.
 * Version: 2.2.0
 * Author: Juca Souza Bonini
 * License: GPL2
 */

defined('ABSPATH') || exit;

define('STS_SEO_PATH', plugin_dir_path(__FILE__));
define('STS_SEO_URL', plugin_dir_url(__FILE__));

/**
 * Autoloader PSR-4
 */
spl_autoload_register(function ($class) {
    $prefix = 'STSSearch\\';
    $base_dir = STS_SEO_PATH . 'src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

/**
 * Initialize Components
 */
add_action('plugins_loaded', function() {
    // new \STSSearch\Core\Sitemap();
    // new \STSSearch\Frontend\Schema();
    // new \STSSearch\Frontend\Head();
    new \STSSearch\Core\ImageSEO();
    
    if (is_admin()) {
        new \STSSearch\Admin\MetaBox();
        // Monitor de updates via GitHub
        new \STSSearch\Core\Updater('sts-seo-engine', '2.0.0', 'JucaBonini/sts-seo-engine');
    }
});

/**
 * Activation/Deactivation
 */
register_activation_hook(__FILE__, function() {
    // $sitemap = new \STSSearch\Core\Sitemap();
    // $sitemap->add_rewrite_rules();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
