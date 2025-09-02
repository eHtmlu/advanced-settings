<?php

if (!defined('ABSPATH')) exit;


/**
 * Auto Thumbs feature class.
 */
class Advset__Feature__Auto_Thumbs {

    private static $instance = null;

    private function __construct() {}
    
    /**
     * Initialize the feature.
     * 
     * @return Advset__Feature__Auto_Thumbs The instance of the feature.
     */
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
    public function transition_post_status( string $new_status, string $old_status, object $post ) {
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
        $images = [];
    
        // Get all images from post's body
        preg_match_all('/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*)[^\>]*/i', $post->post_content ?? '', $images, PREG_SET_ORDER);
    
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
            preg_match('/(?:^|\s+)wp-image-([\d]*)(?:\s+|$)/i', $matches_class[1] ?? '', $matches_thumb_id);
            if (!empty($matches_thumb_id[1]) && (int) $matches_thumb_id[1] > 0) {
                $thumb_id = (int) $matches_thumb_id[1];
                break;
            }
    
            // Get the image URL
            if (!wp_http_validate_url($image_url)) {
                continue;
            }
    
            // If thumb id is not found, try to look for the image in DB. Thanks to "Erwin Vrolijk" for providing this code.
            $attachment = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid = %s", $image_url));
            if (!empty($attachment[0]->ID) && (int) $attachment[0]->ID > 0) {
                $thumb_id = (int) $attachment[0]->ID;
                break;
            }
    
            // Ok. Still no id found. Some other way used to insert the image in post. Now we must fetch the image from URL and do the needful.
            preg_match('/\s+alt\s*=\s*[\""\']?([^\""\'>]*)/i', $img_html, $matches_alt);
            preg_match('/\s+title\s*=\s*[\""\']?([^\""\'>]*)/i', $img_html, $matches_title);
            $imported_thumb_id = $this->import_image_from_url($image_url, $post->ID, [
                'alt' => $matches_alt[1] ?? '',
                'title' => $matches_title[1] ?? '',
            ]);
            if (!is_wp_error($imported_thumb_id) && (int) $imported_thumb_id > 0) {
                $thumb_id = (int) $imported_thumb_id;
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
        
        // Validate the URL
        if (!wp_http_validate_url($url)) {
            return new WP_Error('invalid_url', __('Invalid image URL.'));
        }

        // Parse the options
        $opts = wp_parse_args($opts, [
            'alt'     => '',
            'title'   => '',
        ]);

        // Require the necessary files
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/image.php';
        require_once ABSPATH.'wp-admin/includes/media.php';

        // Download the image
        $tmp = download_url($url);
        if ( is_wp_error($tmp) ) {
            return $tmp;
        }

        // Get the name and mime type of the image
        $name = wp_basename(parse_url($url, PHP_URL_PATH) ?: 'image');
        $wp_filetype = wp_check_filetype_and_ext( $tmp, $name );

        // Check if the image is a valid image
		if (empty($wp_filetype['type']) || !wp_match_mime_types('image', $wp_filetype['type'])) {
			return new WP_Error('invalid_image', __('The uploaded file is not a valid image. Please try again.'));
		}

        // Create the file array
        $file_array = [
            'name'     => $name,
            'tmp_name' => $tmp,
            'type'     => $wp_filetype['type'],
        ];

        // Create the post data
        $post_data = [];
        if (!empty($opts['title'])) {
            $post_data['post_title'] = $opts['title'];
        }

        // Create the description
        $desc = null;

        // Sideload the image
        $attachment_id = media_handle_sideload($file_array, $post_id, $desc, $post_data);

        // Unlink the temporary file if it exists
        @unlink($tmp);

        // If there is an error, return the error
        if ( is_wp_error($attachment_id) ) {
            return $attachment_id;
        }

        // Update the attachment meta if the alt text is provided
        if (!empty($opts['alt'])) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $opts['alt']);
        }

        // Return the attachment ID (as integer)
        return (int) $attachment_id;
    }
}
