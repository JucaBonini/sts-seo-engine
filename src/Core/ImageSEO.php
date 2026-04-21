<?php
namespace STSSearch\Core;

defined('ABSPATH') || exit;

/**
 * Handle automatic Image SEO attributes (Alt and Title)
 */
class ImageSEO {
    public function __construct() {
        add_filter('wp_get_attachment_image_attributes', [$this, 'add_image_attributes'], 10, 2);
    }

    /**
     * Add Alt and Title attributes if they are missing
     */
    public function add_image_attributes($attr, $attachment) {
        // If Alt is empty, try to get post title or filename
        if (empty($attr['alt'])) {
            $post = get_post($attachment->post_parent);
            if ($post) {
                $attr['alt'] = $post->post_title;
            } else {
                // Fallback to attachment title (usually the filename)
                $attr['alt'] = $attachment->post_title;
            }
        }

        // If Title is empty, use Alt or Post title
        if (empty($attr['title'])) {
            $attr['title'] = $attr['alt'];
        }

        return $attr;
    }
}
