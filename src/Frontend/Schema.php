<?php

namespace STSSearch\Frontend;

if (!defined('ABSPATH')) exit;

class Schema
{
    public function __construct()
    {
        add_action('wp_head', [$this, 'output_schema'], 20);
    }

    public function output_schema()
    {
        return; // DESATIVADO: Usando Schema do Tema (sts-recipe-2)
        $schemas = [];

        // 1. Breadcrumbs (Para quase todas as páginas)
        $breadcrumbs = $this->get_breadcrumb_schema();
        if ($breadcrumbs) {
            $schemas[] = $breadcrumbs;
        }

        // 2. Conteúdo Específico (Post, Receita ou Página)
        if (is_singular('post')) {
            $schemas[] = $this->get_post_schema();
        } elseif (is_front_page()) {
            $schemas[] = $this->get_website_schema();
        }

        if (empty($schemas)) {
            return;
        }

        echo "\n" . '<!-- SEO Engine Pro: Structured Data -->' . "\n";
        echo '<script type="application/ld+json">' . "\n";
        echo json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }

    private function get_website_schema()
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }

    private function get_post_schema()
    {
        global $post;
        $post_id = $post->ID;
        
        $title = get_post_meta($post_id, '_sts_seo_title', true) ?: get_the_title();
        $desc = get_post_meta($post_id, '_sts_seo_desc', true) ?: wp_trim_words(get_the_excerpt(), 25);
        $thumb = get_the_post_thumbnail_url($post_id, 'full');
        
        // Dados do Autor (E-E-A-T)
        $author_id = $post->post_author;
        $author_name = get_the_author_meta('display_name', $author_id);
        $author_url = get_author_posts_url($author_id);
        $expertise = get_the_author_meta('expertise', $author_id);
        $job_title = get_the_author_meta('job_title', $author_id) ?: 'Chef';

        // Avaliação (Rating)
        $rating_avg = get_post_meta($post_id, '_rating_avg', true);
        $rating_count = get_post_meta($post_id, '_rating_count', true);

        // Se tiver ingredientes e estiver em uma categoria de receitas, tentamos tratar como Recipe
        $is_recipe = false;
        $categories = get_the_category($post_id);
        foreach ($categories as $cat) {
            if (stripos($cat->name, 'receita') !== false || stripos($cat->slug, 'receita') !== false) {
                $is_recipe = true;
                break;
            }
        }

        $type = $is_recipe ? 'Recipe' : 'Article';

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'headline' => $title,
            'description' => $desc,
            'image' => $thumb ? [$thumb] : [],
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'author' => [
                '@type' => 'Person',
                'name' => $author_name,
                'url' => $author_url,
                'jobTitle' => $job_title,
                'knowsAbout' => $expertise ? explode(',', $expertise) : []
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $this->get_logo_url()
                ]
            ]
        ];

        // Adiciona Rating se existir
        if ($rating_avg && $rating_count) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $rating_avg,
                'reviewCount' => $rating_count,
                'bestRating' => '5',
                'worstRating' => '1'
            ];
        }

        // Se for Recipe, adicionamos campos extras (Ingredientes simplificados do conteúdo)
        if ($is_recipe) {
            $schema['recipeCategory'] = isset($categories[0]) ? $categories[0]->name : '';
            $schema['keywords'] = get_post_meta($post_id, '_sts_seo_keyword', true);
            
            // Tentamos herdar os ingredientes se eles estiverem limpos no meta
            $ingredientes = get_post_meta($post_id, '_ingredientes_user', true);
            if ($ingredientes) {
                $schema['recipeIngredient'] = array_map('trim', explode("\n", strip_tags($ingredientes)));
            }
        }

        return $schema;
    }

    private function get_breadcrumb_schema()
    {
        $items = [];
        $items[] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => home_url('/')
        ];

        if (is_category()) {
            $cat = get_queried_object();
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $cat->name,
                'item' => get_category_link($cat->term_id)
            ];
        } elseif (is_singular('post')) {
            $cats = get_the_category();
            if ($cats) {
                $items[] = [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $cats[0]->name,
                    'item' => get_category_link($cats[0]->term_id)
                ];
            }
            $items[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => get_the_title(),
                'item' => get_permalink()
            ];
        }

        if (count($items) <= 1) return null;

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];
    }

    private function get_logo_url()
    {
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
        return $logo ? $logo[0] : '';
    }
}
