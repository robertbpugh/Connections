/**
 * WordPress.com Reader Connections JavaScript
 * 
 * Handles AJAX interactions and UI behavior for the connections interface
 */

(function($) {
    'use strict';
    
    let currentTab = 'suggested';
    
    /**
     * Initialize the connections interface
     */
    function init() {
        bindEvents();
        loadCreators('suggested');
    }
    
    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Tab switching
        $('.tab').on('click', function(e) {
            e.preventDefault();
            const tab = $(this).data('tab');
            switchTab(tab);
        });
        
        // Filter changes
        $('#topicFilter, #sizeFilter').on('change', function() {
            applyFilters();
        });
        
        // Mobile menu toggle
        window.toggleMobileMenu = toggleMobileMenu;
        window.closeMobileMenu = closeMobileMenu;
        window.viewProfile = viewProfile;
        window.viewBlog = viewBlog;
        
        // Close mobile menu on window resize
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                closeMobileMenu();
            }
        });
    }
    
    /**
     * Switch between tabs
     */
    function switchTab(tab) {
        currentTab = tab;
        
        // Update tab styling
        $('.tab').removeClass('active');
        $(`.tab[data-tab="${tab}"]`).addClass('active');
        
        // Reset filters
        $('#topicFilter, #sizeFilter').val('');
        
        // Load creators for the selected tab
        loadCreators(tab);
    }
    
    /**
     * Apply current filters
     */
    function applyFilters() {
        const filters = {
            topic: $('#topicFilter').val(),
            size: $('#sizeFilter').val()
        };
        
        loadCreators(currentTab, filters);
    }
    
    /**
     * Load creators via AJAX
     */
    function loadCreators(tab, filters = {}) {
        const $list = $('#connectionsList');
        
        // Show loading state
        $list.html('<div class="loading-spinner">Loading creators...</div>');
        
        let action;
        switch(tab) {
            case 'trending':
                action = 'get_trending_creators';
                break;
            case 'local':
                action = 'get_local_creators';
                break;
            case 'suggested':
            default:
                action = 'get_creator_suggestions';
                break;
        }
        
        $.ajax({
            url: connectionsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: action,
                nonce: connectionsAjax.nonce,
                topic: filters.topic || '',
                size: filters.size || ''
            },
            success: function(response) {
                if (response.success) {
                    renderCreators(response.data.creators);
                } else {
                    showError('Failed to load creators. Please try again.');
                }
            },
            error: function() {
                showError('Network error. Please check your connection and try again.');
            }
        });
    }
    
    /**
     * Render creators in the list
     */
    function renderCreators(creators) {
        const $list = $('#connectionsList');
        const template = $('#creator-item-template').html();
        
        if (!creators || creators.length === 0) {
            $list.html('<div class="no-results">No creators found matching your criteria.</div>');
            return;
        }
        
        let html = '';
        creators.forEach(function(creator) {
            let itemHtml = template
                .replace(/{{name}}/g, escapeHtml(creator.name))
                .replace(/{{url}}/g, escapeHtml(creator.url))
                .replace(/{{username}}/g, escapeHtml(creator.username))
                .replace(/{{followers}}/g, escapeHtml(creator.followers))
                .replace(/{{topics}}/g, escapeHtml(creator.topics.join(' • ')))
                .replace(/{{match_reason}}/g, escapeHtml(creator.match_reason))
                .replace(/{{avatar\.color}}/g, creator.avatar.color)
                .replace(/{{avatar\.initials}}/g, escapeHtml(creator.avatar.initials));
            
            html += itemHtml;
        });
        
        $list.html(html);
    }
    
    /**
     * Show error message
     */
    function showError(message) {
        const $list = $('#connectionsList');
        $list.html(`<div class="error-message">${escapeHtml(message)}</div>`);
    }
    
    /**
     * Mobile menu functions
     */
    function toggleMobileMenu() {
        const $sidebar = $('#sidebar');
        const $overlay = $('.sidebar-overlay');
        
        $sidebar.toggleClass('open');
        $overlay.toggleClass('show');
    }
    
    function closeMobileMenu() {
        const $sidebar = $('#sidebar');
        const $overlay = $('.sidebar-overlay');
        
        $sidebar.removeClass('open');
        $overlay.removeClass('show');
    }
    
    /**
     * Button actions
     */
    function viewProfile(username) {
        // In development, show alert. In production, navigate to profile
        if (typeof console !== 'undefined') {
            console.log('Would navigate to:', `https://wordpress.com/reader/users/${username}`);
        }
        alert(`Would open: https://wordpress.com/reader/users/${username}`);
    }
    
    function viewBlog(url) {
        // In development, show alert. In production, navigate to blog
        if (typeof console !== 'undefined') {
            console.log('Would navigate to:', `https://${url}`);
        }
        alert(`Would open: https://${url}`);
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize when document is ready
    $(document).ready(init);
    
})(jQuery);