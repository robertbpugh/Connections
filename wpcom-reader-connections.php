<?php
/**
 * Plugin Name: WordPress.com Reader Connections
 * Plugin URI: https://github.com/automattic/wpcom-reader-connections
 * Description: Cross-promotion matching tool for WordPress.com that helps creators discover and collaborate with other bloggers.
 * Version: 1.0.0
 * Author: WordPress.com Creators Maison Team
 * Author URI: https://wordpress.com/
 * License: GPL v2 or later
 * Text Domain: wpcom-reader-connections
 * Domain Path: /languages
 * 
 * @package WordPressCom\Reader\Connections
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPCOM_READER_CONNECTIONS_VERSION', '1.0.0');
define('WPCOM_READER_CONNECTIONS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPCOM_READER_CONNECTIONS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WPCom_Reader_Connections {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the plugin
     */
    private function init() {
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize hooks
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'initialize_components']);
        
        // Admin hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // Activation/deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once WPCOM_READER_CONNECTIONS_PLUGIN_DIR . 'src/CreatorMatcher.php';
        require_once WPCOM_READER_CONNECTIONS_PLUGIN_DIR . 'src/CreatorDataProvider.php';
        require_once WPCOM_READER_CONNECTIONS_PLUGIN_DIR . 'src/ConnectionsController.php';
    }
    
    /**
     * Load plugin textdomain for internationalization
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wpcom-reader-connections',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    /**
     * Initialize plugin components
     */
    public function initialize_components() {
        // Only initialize on WordPress.com or in development
        if ($this->is_wpcom_environment() || $this->is_development_mode()) {
            new \WordPressCom\Reader\Connections\ConnectionsController();
        }
    }
    
    /**
     * Add admin menu for plugin configuration
     */
    public function add_admin_menu() {
        add_options_page(
            __('Reader Connections Settings', 'wpcom-reader-connections'),
            __('Reader Connections', 'wpcom-reader-connections'),
            'manage_options',
            'wpcom-reader-connections',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            'wpcom_reader_connections_settings',
            'wpcom_reader_connections_options',
            [$this, 'sanitize_settings']
        );
        
        add_settings_section(
            'wpcom_reader_connections_main',
            __('Main Settings', 'wpcom-reader-connections'),
            [$this, 'settings_section_callback'],
            'wpcom-reader-connections'
        );
        
        add_settings_field(
            'enable_connections',
            __('Enable Connections', 'wpcom-reader-connections'),
            [$this, 'enable_connections_callback'],
            'wpcom-reader-connections',
            'wpcom_reader_connections_main'
        );
        
        add_settings_field(
            'api_cache_duration',
            __('Cache Duration (seconds)', 'wpcom-reader-connections'),
            [$this, 'cache_duration_callback'],
            'wpcom-reader-connections',
            'wpcom_reader_connections_main'
        );
    }
    
    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Reader Connections Settings', 'wpcom-reader-connections'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wpcom_reader_connections_settings');
                do_settings_sections('wpcom-reader-connections');
                submit_button();
                ?>
            </form>
            
            <div class="card">
                <h2><?php _e('About Reader Connections', 'wpcom-reader-connections'); ?></h2>
                <p><?php _e('The Reader Connections feature helps WordPress.com creators discover and collaborate with other bloggers who share similar interests and engagement patterns.', 'wpcom-reader-connections'); ?></p>
                
                <h3><?php _e('Features', 'wpcom-reader-connections'); ?></h3>
                <ul>
                    <li><?php _e('Smart matching algorithm based on topics and engagement', 'wpcom-reader-connections'); ?></li>
                    <li><?php _e('Trending creators discovery', 'wpcom-reader-connections'); ?></li>
                    <li><?php _e('Local creator recommendations', 'wpcom-reader-connections'); ?></li>
                    <li><?php _e('Mobile responsive design', 'wpcom-reader-connections'); ?></li>
                    <li><?php _e('Privacy-first approach with organic engagement', 'wpcom-reader-connections'); ?></li>
                </ul>
                
                <h3><?php _e('Cache Management', 'wpcom-reader-connections'); ?></h3>
                <p>
                    <button type="button" class="button" onclick="clearConnectionsCache()">
                        <?php _e('Clear Creator Cache', 'wpcom-reader-connections'); ?>
                    </button>
                    <span class="description">
                        <?php _e('Clear the cached creator data to force a refresh from the API.', 'wpcom-reader-connections'); ?>
                    </span>
                </p>
            </div>
        </div>
        
        <script>
        function clearConnectionsCache() {
            if (confirm('<?php _e('Are you sure you want to clear the creator cache?', 'wpcom-reader-connections'); ?>')) {
                jQuery.post(ajaxurl, {
                    action: 'clear_connections_cache',
                    nonce: '<?php echo wp_create_nonce('clear_cache_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('<?php _e('Cache cleared successfully!', 'wpcom-reader-connections'); ?>');
                    } else {
                        alert('<?php _e('Error clearing cache. Please try again.', 'wpcom-reader-connections'); ?>');
                    }
                });
            }
        }
        </script>
        <?php
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure the Reader Connections feature settings.', 'wpcom-reader-connections') . '</p>';
    }
    
    /**
     * Enable connections field callback
     */
    public function enable_connections_callback() {
        $options = get_option('wpcom_reader_connections_options', []);
        $enabled = isset($options['enable_connections']) ? $options['enable_connections'] : true;
        ?>
        <input type="checkbox" id="enable_connections" name="wpcom_reader_connections_options[enable_connections]" value="1" <?php checked($enabled, true); ?> />
        <label for="enable_connections"><?php _e('Enable the Connections feature in the Reader', 'wpcom-reader-connections'); ?></label>
        <?php
    }
    
    /**
     * Cache duration field callback
     */
    public function cache_duration_callback() {
        $options = get_option('wpcom_reader_connections_options', []);
        $duration = isset($options['api_cache_duration']) ? $options['api_cache_duration'] : 3600;
        ?>
        <input type="number" id="api_cache_duration" name="wpcom_reader_connections_options[api_cache_duration]" value="<?php echo esc_attr($duration); ?>" min="300" max="86400" />
        <p class="description"><?php _e('How long to cache creator data (300-86400 seconds)', 'wpcom-reader-connections'); ?></p>
        <?php
    }
    
    /**
     * Sanitize settings input
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        $sanitized['enable_connections'] = isset($input['enable_connections']) ? true : false;
        
        if (isset($input['api_cache_duration'])) {
            $duration = intval($input['api_cache_duration']);
            $sanitized['api_cache_duration'] = max(300, min(86400, $duration));
        }
        
        return $sanitized;
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_options = [
            'enable_connections' => true,
            'api_cache_duration' => 3600
        ];
        
        if (!get_option('wpcom_reader_connections_options')) {
            add_option('wpcom_reader_connections_options', $default_options);
        }
        
        // Create any necessary database tables
        $this->create_database_tables();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear caches
        wp_cache_delete('wpcom_creator_connections_data');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables if needed
     */
    private function create_database_tables() {
        global $wpdb;
        
        // Example: Create table for storing creator collaboration history
        $table_name = $wpdb->prefix . 'reader_connections_history';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            creator_id bigint(20) NOT NULL,
            interaction_type varchar(50) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY creator_id (creator_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Check if running on WordPress.com
     */
    private function is_wpcom_environment() {
        return defined('IS_WPCOM') && IS_WPCOM;
    }
    
    /**
     * Check if in development mode
     */
    private function is_development_mode() {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
}

// Initialize the plugin
WPCom_Reader_Connections::get_instance();

// Add AJAX handler for cache clearing
add_action('wp_ajax_clear_connections_cache', function() {
    check_ajax_referer('clear_cache_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $data_provider = new \WordPressCom\Reader\Connections\CreatorDataProvider();
    $data_provider->clear_cache();
    
    wp_send_json_success('Cache cleared successfully');
});