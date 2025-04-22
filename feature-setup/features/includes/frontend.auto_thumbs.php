<?php

if( !defined('ABSPATH') ) exit;


function advset__feature__auto_thumbs( $new_status, $old_status, $post ) {
    if ('publish' == $new_status) {
        advset__feature__auto_thumbs__publish_post($post);
    }
}

//
function advset__feature__auto_thumbs__publish_post( $post ) {
    global $wpdb;

    // First check whether Post Thumbnail is already set for this post.
    if (get_post_meta($post->ID, '_thumbnail_id', true) || get_post_meta($post->ID, 'skip_post_thumb', true))
        return;

    // Initialize variable used to store list of matched images as per provided regular expression
    $matches = array();

    // Get all images from post's body
    preg_match_all('/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*)[^\>]*/i', empty($post->post_content) ? '' : $post->post_content, $matches);

    if (count($matches)) {
        foreach ($matches[0] as $key => $image) {
            /**
             * If the image is from wordpress's own media gallery, then it appends the thumbmail id to a css class.
             * Look for this id in the IMG tag.
             */
            preg_match('/wp-image-([\d]*)/i', $image, $thumb_id);
            $thumb_id = empty($thumb_id[1]) ? null : $thumb_id[1];

            // If thumb id is not found, try to look for the image in DB. Thanks to "Erwin Vrolijk" for providing this code.
            if (!$thumb_id) {
                $image = $matches[1][$key];
                $result = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid = %s", $image));
                $thumb_id = empty($result[0]->ID) ? null : $result[0]->ID;
            }

            // Ok. Still no id found. Some other way used to insert the image in post. Now we must fetch the image from URL and do the needful.
            if (!$thumb_id) {
                $thumb_id = advset__feature__auto_thumbs__generate_post_thumbnail($matches, $key, $post);
            }

            // If we succeed in generating thumg, let's update post meta
            if ($thumb_id) {
                update_post_meta( $post->ID, '_thumbnail_id', $thumb_id );
                break;
            }
        }
    }
}


function advset__feature__auto_thumbs__generate_post_thumbnail( $matches, $key, $post ) {
    // Make sure to assign correct title to the image. Extract it from img tag
    $imageTitle = '';
    preg_match_all('/<\s*img [^\>]*title\s*=\s*[\""\']?([^\""\'>]*)/i', empty($post->post_content) ? '' : $post->post_content, $matchesTitle);

    if (count($matchesTitle) && isset($matchesTitle[1])) {
        $imageTitle = empty($matchesTitle[1][$key]) ? '' : $matchesTitle[1][$key];
    }

    // Get the URL now for further processing
    $imageUrl = $matches[1][$key];

    // Get the file name
    $filename = substr($imageUrl, (strrpos($imageUrl, '/'))+1);

    if ( !(($uploads = wp_upload_dir(current_time('mysql')) ) && false === $uploads['error']) )
        return null;

    // Generate unique file name
    $filename = wp_unique_filename( $uploads['path'], $filename );

    // Move the file to the uploads dir
    $new_file = $uploads['path'] . "/$filename";

    if (!ini_get('allow_url_fopen'))
        $file_data = advset__feature__auto_thumbs__curl_get_file_contents($imageUrl);
    else
        $file_data = @file_get_contents($imageUrl);

    if (!$file_data) {
        return null;
    }

    file_put_contents($new_file, $file_data);

    // Set correct file permissions
    $stat = stat( dirname( $new_file ));
    $perms = $stat['mode'] & 0000666;
    @ chmod( $new_file, $perms );

    // Get the file type. Must to use it as a post thumbnail.
    $wp_filetype = wp_check_filetype( $filename );

    extract( $wp_filetype );

    // No file type! No point to proceed further
    if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) ) {
        return null;
    }

    // Compute the URL
    $url = $uploads['url'] . "/$filename";

    // Construct the attachment array
    $attachment = array(
        'post_mime_type' => $type,
        'guid' => $url,
        'post_parent' => null,
        'post_title' => $imageTitle,
        'post_content' => '',
    );

    $thumb_id = wp_insert_attachment($attachment, false, $post->ID);
    if ( !is_wp_error($thumb_id) ) {
        require_once(ABSPATH . '/wp-admin/includes/image.php');

        // Added fix by misthero as suggested
        wp_update_attachment_metadata( $thumb_id, wp_generate_attachment_metadata( $thumb_id, $new_file ) );
        update_attached_file( $thumb_id, $new_file );

        return $thumb_id;
    }

    return null;
}

function advset__feature__auto_thumbs__curl_get_file_contents($URL) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $URL);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) {
        return $contents;
    }

    return FALSE;
}
