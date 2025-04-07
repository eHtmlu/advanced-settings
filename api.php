<?php

// Exit direct requests
if (!defined('ABSPATH')) exit;

// Register REST API endpoints
register_rest_route('advanced-settings/v1', '/search', array(
    'methods' => 'GET',
    'callback' => 'advset_search_callback',
    'permission_callback' => function() {
        return current_user_can('manage_options');
    }
));

function advset_search_callback($request) {
    $query = sanitize_text_field($request->get_param('query'));
    
    // Hier können Sie Ihre eigene Suchlogik implementieren
    // Dies ist nur ein Beispiel
    $results = array();
    
    // Suche in Posts
    $posts = get_posts(array(
        'post_type' => 'any',
        'post_status' => 'publish',
        's' => $query,
        'posts_per_page' => 10
    ));
    
    foreach ($posts as $post) {
        $results[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'description' => wp_trim_words($post->post_content, 20),
            'type' => $post->post_type,
            'url' => get_edit_post_link($post->ID)
        );
    }
    
    return new WP_REST_Response($results, 200);
} 