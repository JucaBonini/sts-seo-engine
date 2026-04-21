<?php

namespace STSSearch\Frontend;

if (!defined('ABSPATH')) exit;

class Head
{
    public function __construct()
    {
        add_filter('document_title_parts', [$this, 'modify_title'], 99);
        add_action('wp_head', [$this, 'output_meta_description'], 1);
        add_action('wp_head', [$this, 'output_social_meta'], 5);
    }

    public function modify_title($parts)
    {
        if (is_singular()) {
            $custom_title = get_post_meta(get_the_ID(), '_sts_seo_title', true);
            if (!empty($custom_title)) {
                $parts['title'] = $custom_title;
            }
        }
        return $parts;
    }

    public function output_meta_description()
    {
        // Silenciado em favor do tema para evitar duplicidade
        return;
    }

    public function output_social_meta()
    {
        // Robots Logic - Mantido pois é vital
        if (is_author() || is_date() || is_search() || is_404()) {
            echo '<meta name="robots" content="noindex, follow" />' . "\n";
        } else {
            echo '<meta name="robots" content="index, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large" />' . "\n";
        }

        // Códigos de Verificação - Mantido (Configuração Global)
        echo '<!-- SEO Engine Pro Tools -->' . "\n";
        echo '<meta name="google-site-verification" content="rZSdMspnsnOU9x8ZxwyvLIWfPDVoGsae1mWBZgti1PY" />' . "\n";
        echo '<meta name="msvalidate.01" content="E3BEF536136496E86D4C035E2C36E401" />' . "\n";
        echo '<meta name="pinnable" content="645852f757b84ed974209acf2794c0cd" />' . "\n";
    }
}
