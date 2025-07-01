<?php
/**
 * Template for the WordPress.com Reader Connections page
 * 
 * This template renders the main connections interface.
 * Variables available: $data_provider, $current_user_profile
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$data_provider = new \WordPressCom\Reader\Connections\CreatorDataProvider();
$available_topics = $data_provider->get_available_topics();
?>

<div class="mobile-header">
    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
    <div class="mobile-title"><?php _e('Connections', 'wpcom-reader'); ?></div>
    <div></div>
</div>

<div class="sidebar-overlay" onclick="closeMobileMenu()"></div>

<div class="container">
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-title"><?php _e('Reader', 'wpcom-reader'); ?></div>
            <div class="sidebar-subtitle"><?php _e('Keep up with your interests.', 'wpcom-reader'); ?></div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="/read" class="nav-item">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="1 4 1 10 7 10"/>
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                </svg>
                <?php _e('Recent', 'wpcom-reader'); ?>
            </a>
            <a href="/read/discover" class="nav-item">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="8"/>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    <path d="M2 12h20"/>
                </svg>
                <?php _e('Discover', 'wpcom-reader'); ?>
            </a>
            <a href="/read/likes" class="nav-item">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <?php _e('Likes', 'wpcom-reader'); ?>
            </a>
            <a href="/read/connections" class="nav-item active">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <?php _e('Connections', 'wpcom-reader'); ?>
            </a>
            <a href="/read/conversations" class="nav-item">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <?php _e('Conversations', 'wpcom-reader'); ?>
            </a>
            <a href="/read/lists" class="nav-item">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/>
                    <line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/>
                    <line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                <?php _e('Lists', 'wpcom-reader'); ?>
            </a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="content-header">
            <h1 class="content-title"><?php _e('Connections', 'wpcom-reader'); ?></h1>
            <p class="content-subtitle">
                <?php _e('Discover creators you might want to collaborate with. Like or comment on their blog to get started.', 'wpcom-reader'); ?>
            </p>
        </div>
        
        <div class="tabs">
            <a href="#" class="tab active" data-tab="suggested">
                <?php _e('Suggested', 'wpcom-reader'); ?>
            </a>
            <a href="#" class="tab" data-tab="trending">
                <?php _e('Trending', 'wpcom-reader'); ?>
            </a>
            <a href="#" class="tab" data-tab="local">
                <?php _e('Local', 'wpcom-reader'); ?>
            </a>
        </div>
        
        <div class="filters">
            <select id="topicFilter">
                <option value=""><?php _e('All Topics', 'wpcom-reader'); ?></option>
                <?php foreach ($available_topics as $topic): ?>
                    <option value="<?php echo esc_attr($topic); ?>">
                        <?php echo esc_html($topic); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="sizeFilter">
                <option value=""><?php _e('All Sizes', 'wpcom-reader'); ?></option>
                <option value="small"><?php _e('Small (< 2k followers)', 'wpcom-reader'); ?></option>
                <option value="medium"><?php _e('Medium (2k - 5k followers)', 'wpcom-reader'); ?></option>
                <option value="large"><?php _e('Large (> 5k followers)', 'wpcom-reader'); ?></option>
            </select>
        </div>
        
        <div class="connections-list" id="connectionsList">
            <div class="loading-spinner">
                <?php _e('Loading creators...', 'wpcom-reader'); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="creator-item-template">
    <div class="connection-item">
        <div class="connection-avatar" style="background-color: {{avatar.color}}">
            {{avatar.initials}}
        </div>
        <div class="connection-info">
            <div class="connection-name">{{name}}</div>
            <div class="connection-details">
                <a href="https://{{url}}" target="_blank">{{url}}</a> • {{followers}} <?php _e('followers', 'wpcom-reader'); ?>
            </div>
            <div class="connection-topics">{{topics}}</div>
            <div class="match-reason">{{match_reason}}</div>
        </div>
        <div class="connection-actions">
            <button class="btn" onclick="viewProfile('{{username}}')">
                <?php _e('View profile', 'wpcom-reader'); ?>
            </button>
            <button class="btn btn-primary" onclick="viewBlog('{{url}}')">
                <?php _e('View blog', 'wpcom-reader'); ?>
            </button>
        </div>
    </div>
</script>