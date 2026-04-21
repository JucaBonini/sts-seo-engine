<?php

namespace STSSearch\Core;

if (!defined('ABSPATH')) exit;

class Sitemap
{
    public function __construct()
    {
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'render_sitemap']);
    }

    public function add_rewrite_rules()
    {
        add_rewrite_rule('sitemap\.xml$', 'index.php?sts_sitemap=index', 'top');
        add_rewrite_rule('sitemap-([a-z0-9_-]+)\.xml$', 'index.php?sts_sitemap=$matches[1]', 'top');
    }

    public function add_query_vars($vars)
    {
        $vars[] = 'sts_sitemap';
        return $vars;
    }

    public function render_sitemap()
    {
        $type = get_query_var('sts_sitemap');
        if (!$type) return;

        // Clear any previous output
        if (ob_get_length()) ob_clean();

        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow', true);

        if ($type === 'index') {
            $this->render_index();
        } else {
            $this->render_type($type);
        }
        exit;
    }

    private function render_index()
    {
        $post_types = get_post_types(['public' => true]);
        $taxonomies = get_taxonomies(['public' => true]);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?xml-stylesheet type="text/xsl" href="' . STS_SEO_URL . 'assets/sitemap.xsl' . '"?>';
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Post Types
        foreach ($post_types as $t) {
            if (in_array($t, ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset'])) continue;
            
            $lastmod = $this->get_last_modified($t);
            echo '<sitemap>';
            echo '<loc>' . home_url("/sitemap-{$t}.xml") . '</loc>';
            echo '<lastmod>' . $lastmod . '</lastmod>';
            echo '</sitemap>';
        }

        // Taxonomies
        foreach ($taxonomies as $tax) {
            if (in_array($tax, ['post_tag', 'nav_menu', 'link_category', 'post_format'])) continue;
            
            echo '<sitemap>';
            echo '<loc>' . home_url("/sitemap-tax-{$tax}.xml") . '</loc>';
            echo '<lastmod>' . date('c') . '</lastmod>';
            echo '</sitemap>';
        }
        
        echo '</sitemapindex>';
    }

    private function render_type($type)
    {
        // Check if it's a taxonomy sitemap
        if (strpos($type, 'tax-') === 0) {
            $tax_name = str_replace('tax-', '', $type);
            $this->render_taxonomy($tax_name);
            return;
        }

        $query = new \WP_Query([
            'post_type' => $type,
            'post_status' => 'publish',
            'posts_per_page' => 1000,
            'orderby' => 'modified',
            'order' => 'DESC'
        ]);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?xml-stylesheet type="text/xsl" href="' . STS_SEO_URL . 'assets/sitemap.xsl' . '"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
        
        while ($query->have_posts()) {
            $query->the_post();
            echo '<url>';
            echo '<loc>' . get_permalink() . '</loc>';
            echo '<lastmod>' . get_the_modified_date('c') . '</lastmod>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.8</priority>';
            
            if (has_post_thumbnail()) {
                $img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                echo '<image:image><image:loc>' . esc_url($img_url) . '</image:loc></image:image>';
            }
            
            echo '</url>';
        }
        
        wp_reset_postdata();
        echo '</urlset>';
    }

    private function render_taxonomy($tax_name)
    {
        $terms = get_terms([
            'taxonomy' => $tax_name,
            'hide_empty' => true,
        ]);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?xml-stylesheet type="text/xsl" href="' . STS_SEO_URL . 'assets/sitemap.xsl' . '"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($terms as $term) {
            echo '<url>';
            echo '<loc>' . get_term_link($term) . '</loc>';
            echo '<lastmod>' . date('c') . '</lastmod>'; // No date modified for terms in standard WP
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.5</priority>';
            echo '</url>';
        }
        
        echo '</urlset>';
    }

    private function get_last_modified($post_type)
    {
        global $wpdb;
        $date = $wpdb->get_var($wpdb->prepare(
            "SELECT post_modified_gmt FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1",
            $post_type
        ));
        return $date ? date('c', strtotime($date)) : date('c');
    }
}
