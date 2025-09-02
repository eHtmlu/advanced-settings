<?php

if (!defined('ABSPATH')) exit;


class Advset__Feature__Auto_Thumbs {

    private static $instance = null;

    private function __construct() {}
    
    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Transition the post status.
     * 
     * @param string $new_status The new status of the post.
     * @param string $old_status The old status of the post.
     * @param object $post The post object.
     * @return bool True if the post thumbnail was generated, false otherwise.
     */
    public function transition_post_status( $new_status, $old_status, $post ) {
        global $wpdb;
        
        // If the post is not published, return
        if ($new_status !== 'publish') {
            return false;
        }

        // First check whether Post Thumbnail is already set for this post.
        if (get_post_meta($post->ID, '_thumbnail_id', true) || get_post_meta($post->ID, 'skip_post_thumb', true))
            return false;
    
        // Initialize variable used to store the thumbnail id
        $thumb_id = null;
    
        // Initialize variable used to store list of matched images as per provided regular expression
        $images = array();
    
        // Get all images from post's body
        preg_match_all('/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*)[^\>]*/i', empty($post->post_content) ? '' : $post->post_content, $images, PREG_SET_ORDER);
    
        // If no images are found, return
        if (empty($images)) {
            return false;
        }
    
        // Loop through all images
        foreach ($images as $image) {
            $img_html = $image[0];
            $image_url = $image[1];
    
            // If the image is from wordpress's own media gallery, then it appends the thumbmail id to a css class.
            preg_match('/\s+class\s*=\s*[\""\']?([^\""\'>]*)/i', $img_html, $matches_class);
            preg_match('/wp-image-([\d]*)/i', empty($matches_class[1]) ? '' : $matches_class[1], $matches_thumb_id);
            if (!empty($matches_thumb_id[1]) && $matches_thumb_id[1] > 0) {
                $thumb_id = $matches_thumb_id[1];
                break;
            }
    
            // Get the image URL
            if (!wp_http_validate_url($image_url)) {
                continue;
            }
    
            // If thumb id is not found, try to look for the image in DB. Thanks to "Erwin Vrolijk" for providing this code.
            $attachment = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid = %s", $image_url));
            if (!empty($attachment[0]->ID) && $attachment[0]->ID > 0) {
                $thumb_id = $attachment[0]->ID;
                break;
            }
    
            // Ok. Still no id found. Some other way used to insert the image in post. Now we must fetch the image from URL and do the needful.
            preg_match('/\s+alt\s*=\s*[\""\']?([^\""\'>]*)/i', $img_html, $matches_alt);
            preg_match('/\s+title\s*=\s*[\""\']?([^\""\'>]*)/i', $img_html, $matches_title);
            $imported_thumb_id = $this->import_image_from_url($image_url, $post->ID, [
                'alt' => empty($matches_alt[1]) ? '' : $matches_alt[1],
                //'desc' => $image_title,
                'title' => empty($matches_title[1]) ? '' : $matches_title[1],
            ]);
            if (!is_wp_error($imported_thumb_id) && $imported_thumb_id > 0) {
                $thumb_id = $imported_thumb_id;
                break;
            }
        }
    
        // If we succeed in generating thumg, let's update post meta
        if ($thumb_id) {
            update_post_meta( $post->ID, '_thumbnail_id', $thumb_id );
            return true;
        }

        return false;
    }
    
    /**
     * Imports an image from an external URL into the media library.
     * 
     * @param string $url The URL of the image to import.
     * @param int $post_id The ID of the post to import the image to.
     * @param array $opts Optional arguments for the import.
     * @return int|\WP_Error Attachment-ID oder Fehler
     */
    private function import_image_from_url( string $url, int $post_id = 0, array $opts = [] ) {
        // Validiere die URL
        if (!wp_http_validate_url($url)) {
            return new WP_Error('invalid_url', __('Invalid image URL.'));
        }

        $opts = wp_parse_args($opts, [
            'alt'     => '',
            'desc'    => '',
            'title'   => '',
            'author'  => get_current_user_id(),
            'dedupe'  => true, // do not import the same source twice
        ]);

        if ( $opts['dedupe'] ) {
            $existing = get_posts([
                'post_type'      => 'attachment',
                'post_status'    => 'inherit',
                'meta_key'       => '_source_url',
                'meta_value'     => esc_url_raw($url),
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]);
            if ($existing) return (int) $existing[0];
        }

        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/image.php';
        require_once ABSPATH.'wp-admin/includes/media.php';

        $tmp = download_url($url);
        if ( is_wp_error($tmp) ) return $tmp;

        $name = wp_basename(parse_url($url, PHP_URL_PATH) ?: 'image');
        $mime = function_exists('wp_get_image_mime') ? wp_get_image_mime($tmp) : false;

        $file = [
            'name'     => $name,
            'tmp_name' => $tmp,
        ];
        if ($mime) $file['type'] = $mime;

        $attachment_id = media_handle_sideload($file, $post_id, $opts['desc'], [
            'post_title'  => $opts['title'] ?: preg_replace('/\.[^.]+$/', '', $name),
            'post_author' => (int) $opts['author'],
        ]);

        if ( is_wp_error($attachment_id) ) {
            @unlink($tmp);
            return $attachment_id;
        }

        if ($opts['alt'] !== '') {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $opts['alt']);
        }
        update_post_meta($attachment_id, '_source_url', esc_url_raw($url));

        return (int) $attachment_id;
    }
}
