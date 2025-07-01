<?php
/**
 * WordPress.com Reader Connections Controller
 * 
 * Handles the main integration with WordPress.com Reader,
 * including AJAX endpoints and page rendering.
 */

namespace WordPressCom\Reader\Connections;

class ConnectionsController {
    
    private $matcher;
    private $data_provider;
    
    public function __construct() {
        $this->matcher = new CreatorMatcher();
        $this->data_provider = new CreatorDataProvider();
        
        // WordPress hooks
        add_action('wp_ajax_get_creator_suggestions', [$this, 'ajax_get_suggestions']);
        add_action('wp_ajax_get_trending_creators', [$this, 'ajax_get_trending']);
        add_action('wp_ajax_get_local_creators', [$this, 'ajax_get_local']);
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Add to Reader navigation
        add_filter('wpcom_reader_nav_items', [$this, 'add_connections_nav']);
    }
    
    /**
     * Enqueue CSS and JavaScript assets
     */
    public function enqueue_assets() {
        if (!$this->is_reader_page()) {
            return;
        }
        
        wp_enqueue_style(
            'reader-connections-css',
            plugin_dir_url(__FILE__) . '../assets/connections.css',
            [],
            '1.0.0'
        );
        
        wp_enqueue_script(
            'reader-connections-js',
            plugin_dir_url(__FILE__) . '../assets/connections.js',
            ['jquery'],
            '1.0.0',
            true
        );
        
        // Localize script with AJAX URL and nonce
        wp_localize_script('reader-connections-js', 'connectionsAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('connections_nonce'),
            'currentUser' => $this->get_current_user_profile()
        ]);
    }
    
    /**
     * Add Connections tab to Reader navigation
     */
    public function add_connections_nav($nav_items) {
        $nav_items['connections'] = [
            'label' => __('Connections', 'wpcom-reader'),
            'url' => '/read/connections',
            'icon' => 'users'
        ];
        return $nav_items;
    }
    
    /**
     * AJAX handler for getting suggested creators
     */
    public function ajax_get_suggestions() {
        check_ajax_referer('connections_nonce', 'nonce');
        
        $user_profile = $this->get_current_user_profile();
        $creators = $this->data_provider->get_all_creators();
        
        // Apply filters if provided
        $filters = [
            'topic' => sanitize_text_field($_POST['topic'] ?? ''),
            'size' => sanitize_text_field($_POST['size'] ?? '')
        ];
        
        if (!empty($filters['topic']) || !empty($filters['size'])) {
            $creators = $this->matcher->apply_filters($creators, $filters);
        }
        
        $suggestions = $this->matcher->find_matches($user_profile, $creators);
        
        wp_send_json_success([
            'creators' => $this->format_creators_for_response($suggestions, 'suggested')
        ]);
    }
    
    /**
     * AJAX handler for getting trending creators
     */
    public function ajax_get_trending() {
        check_ajax_referer('connections_nonce', 'nonce');
        
        $creators = $this->data_provider->get_all_creators();
        
        // Apply filters
        $filters = [
            'topic' => sanitize_text_field($_POST['topic'] ?? ''),
            'size' => sanitize_text_field($_POST['size'] ?? '')
        ];
        
        if (!empty($filters['topic']) || !empty($filters['size'])) {
            $creators = $this->matcher->apply_filters($creators, $filters);
        }
        
        $trending = $this->matcher->get_trending_creators($creators);
        
        wp_send_json_success([
            'creators' => $this->format_creators_for_response($trending, 'trending')
        ]);
    }
    
    /**
     * AJAX handler for getting local creators
     */
    public function ajax_get_local() {
        check_ajax_referer('connections_nonce', 'nonce');
        
        $user_profile = $this->get_current_user_profile();
        $creators = $this->data_provider->get_all_creators();
        
        // Apply filters
        $filters = [
            'topic' => sanitize_text_field($_POST['topic'] ?? ''),
            'size' => sanitize_text_field($_POST['size'] ?? '')
        ];
        
        if (!empty($filters['topic']) || !empty($filters['size'])) {
            $creators = $this->matcher->apply_filters($creators, $filters);
        }
        
        $local = $this->matcher->get_local_creators($user_profile['location'], $creators);
        
        wp_send_json_success([
            'creators' => $this->format_creators_for_response($local, 'local')
        ]);
    }
    
    /**
     * Get current user's profile for matching
     */
    private function get_current_user_profile() {
        $user_id = get_current_user_id();
        
        // In real implementation, this would fetch from user meta or API
        return [
            'topics' => get_user_meta($user_id, 'creator_topics', true) ?: ['Food', 'Cooking', 'Recipes'],
            'language' => get_user_locale() ?: 'en',
            'engagement_score' => get_user_meta($user_id, 'engagement_score', true) ?: 75,
            'location' => [
                'region' => get_user_meta($user_id, 'location_region', true) ?: 'California',
                'country' => get_user_meta($user_id, 'location_country', true) ?: 'United States'
            ]
        ];
    }
    
    /**
     * Format creators for JSON response
     */
    private function format_creators_for_response($creators, $context) {
        $formatted = [];
        
        foreach ($creators as $creator) {
            $formatted[] = [
                'name' => $creator['name'],
                'url' => $creator['url'],
                'username' => $creator['username'],
                'followers' => $this->matcher->format_follower_count($creator['followers']),
                'topics' => $creator['topics'],
                'avatar' => $creator['avatar'],
                'match_reason' => $this->get_match_reason($creator, $context)
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Generate match reason text based on context
     */
    private function get_match_reason($creator, $context) {
        switch ($context) {
            case 'trending':
                return sprintf(
                    __('Trending: %d%% growth this month', 'wpcom-reader'),
                    $creator['growth_rate'] ?? 0
                );
                
            case 'local':
                $user_location = $this->get_current_user_profile()['location'];
                $is_same_region = ($creator['location']['region'] === $user_location['region']);
                
                return $is_same_region 
                    ? sprintf(__('Local: %s', 'wpcom-reader'), $creator['location']['region'])
                    : sprintf(__('Local: %s, %s', 'wpcom-reader'), 
                        $creator['location']['region'], 
                        $creator['location']['country']
                    );
                    
            case 'suggested':
            default:
                $common_topics = $creator['common_topics'] ?? [];
                if (!empty($common_topics)) {
                    return sprintf(__('Match: %s', 'wpcom-reader'), implode(', ', $common_topics));
                } else {
                    $topics = array_slice($creator['topics'], 0, 2);
                    return sprintf(__('Match: %s', 'wpcom-reader'), implode(', ', $topics));
                }
        }
    }
    
    /**
     * Check if we're on a Reader page
     */
    private function is_reader_page() {
        // WordPress.com specific check - adjust for your environment
        return (strpos($_SERVER['REQUEST_URI'], '/read') === 0);
    }
    
    /**
     * Render the main Connections page
     */
    public function render_connections_page() {
        $template_path = plugin_dir_path(__FILE__) . '../templates/connections-page.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
}