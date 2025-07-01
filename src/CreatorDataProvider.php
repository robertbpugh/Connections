<?php
/**
 * Creator Data Provider for WordPress.com Reader Connections
 * 
 * Handles fetching and caching creator data from various sources.
 * In production, this would integrate with WordPress.com APIs.
 */

namespace WordPressCom\Reader\Connections;

class CreatorDataProvider {
    
    private $cache_key = 'wpcom_creator_connections_data';
    private $cache_duration = 3600; // 1 hour
    
    /**
     * Get all available creators for matching
     *
     * @return array Array of creator profiles
     */
    public function get_all_creators() {
        // Check cache first
        $cached_data = wp_cache_get($this->cache_key);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // In production, this would fetch from WordPress.com API
        $creators = $this->get_sample_creators();
        
        // Add dynamic data (trending status, location, etc.)
        $creators = $this->enhance_creator_data($creators);
        
        // Cache the results
        wp_cache_set($this->cache_key, $creators, '', $this->cache_duration);
        
        return $creators;
    }
    
    /**
     * Get creator data from WordPress.com API
     * 
     * @return array Creator data from API
     */
    private function fetch_from_api() {
        // Production implementation would look like:
        /*
        $api_url = 'https://public-api.wordpress.com/rest/v1.1/read/creators';
        $response = wp_remote_get($api_url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_api_token(),
                'User-Agent' => 'WordPress.com Reader Connections'
            ],
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            return [];
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return $data['creators'] ?? [];
        */
        
        return $this->get_sample_creators();
    }
    
    /**
     * Sample creator data for development/testing
     * In production, this would be replaced by API calls
     */
    private function get_sample_creators() {
        return [
            [
                'id' => 1,
                'name' => "Sarah's Kitchen Adventures",
                'url' => 'foodblog.wordpress.com',
                'username' => 'sarahskitchen',
                'followers' => 2300,
                'topics' => ['Food', 'Recipes', 'Healthy Cooking'],
                'language' => 'en',
                'engagement_score' => 85,
                'avatar' => ['color' => '#ff6b6b', 'initials' => 'SK']
            ],
            [
                'id' => 2,
                'name' => 'Digital Nomad Diaries',
                'url' => 'nomadlife.wordpress.com',
                'username' => 'digitalnomaddiaries',
                'followers' => 1800,
                'topics' => ['Travel', 'Remote Work', 'Photography'],
                'language' => 'en',
                'engagement_score' => 92,
                'avatar' => ['color' => '#4ecdc4', 'initials' => 'DN']
            ],
            [
                'id' => 3,
                'name' => 'Code & Coffee',
                'url' => 'devblog.wordpress.com',
                'username' => 'codecoffee',
                'followers' => 4100,
                'topics' => ['JavaScript', 'Web Development', 'Tutorials'],
                'language' => 'en',
                'engagement_score' => 78,
                'avatar' => ['color' => '#45b7d1', 'initials' => 'CC']
            ],
            [
                'id' => 4,
                'name' => 'Le Chef Parisien',
                'url' => 'cuisineparis.wordpress.com',
                'username' => 'lechefparisien',
                'followers' => 3200,
                'topics' => ['Food', 'French Cuisine', 'Wine'],
                'language' => 'fr',
                'engagement_score' => 67,
                'avatar' => ['color' => '#f9ca24', 'initials' => 'CP']
            ],
            [
                'id' => 5,
                'name' => 'Mindful Moments',
                'url' => 'mindfulness.wordpress.com',
                'username' => 'mindfulmoments',
                'followers' => 1500,
                'topics' => ['Wellness', 'Meditation', 'Mental Health'],
                'language' => 'en',
                'engagement_score' => 95,
                'avatar' => ['color' => '#6c5ce7', 'initials' => 'MM']
            ],
            [
                'id' => 6,
                'name' => 'Tokyo Street Food',
                'url' => 'tokyoeats.wordpress.com',
                'username' => 'tokyostreetfood',
                'followers' => 2800,
                'topics' => ['Food', 'Japanese Cuisine', 'Street Food'],
                'language' => 'en',
                'engagement_score' => 88,
                'avatar' => ['color' => '#fd79a8', 'initials' => 'TS']
            ],
            [
                'id' => 7,
                'name' => 'Backpack Europe',
                'url' => 'europetravel.wordpress.com',
                'username' => 'backpackeurope',
                'followers' => 2100,
                'topics' => ['Travel', 'Budget Travel', 'Europe'],
                'language' => 'en',
                'engagement_score' => 71,
                'avatar' => ['color' => '#00b894', 'initials' => 'BE']
            ],
            [
                'id' => 8,
                'name' => 'Python for Beginners',
                'url' => 'pythonbasics.wordpress.com',
                'username' => 'pythonforbeginners',
                'followers' => 3600,
                'topics' => ['Python', 'Programming', 'Tutorials'],
                'language' => 'en',
                'engagement_score' => 82,
                'avatar' => ['color' => '#fdcb6e', 'initials' => 'PB']
            ]
        ];
    }
    
    /**
     * Add dynamic data to creators (trending status, location, etc.)
     */
    private function enhance_creator_data($creators) {
        $locations = [
            ['region' => 'California', 'country' => 'United States'],
            ['region' => 'New York', 'country' => 'United States'],
            ['region' => 'Texas', 'country' => 'United States'],
            ['region' => 'Ontario', 'country' => 'Canada'],
            ['region' => 'British Columbia', 'country' => 'Canada'],
            ['region' => 'London', 'country' => 'United Kingdom'],
            ['region' => 'Scotland', 'country' => 'United Kingdom'],
            ['region' => 'New South Wales', 'country' => 'Australia'],
            ['region' => 'Victoria', 'country' => 'Australia'],
            ['region' => 'Bavaria', 'country' => 'Germany'],
            ['region' => 'Berlin', 'country' => 'Germany'],
            ['region' => 'Tokyo', 'country' => 'Japan'],
            ['region' => 'São Paulo', 'country' => 'Brazil'],
            ['region' => 'Mumbai', 'country' => 'India'],
            ['region' => 'Île-de-France', 'country' => 'France']
        ];
        
        foreach ($creators as &$creator) {
            // For demo purposes, ensure first 5 are trending
            if ($creator['id'] <= 5) {
                $creator['trending'] = true;
            } else {
                $creator['trending'] = (rand(1, 100) <= 15); // 15% chance
            }
            
            $creator['growth_rate'] = rand(15, 55);
            
            // Assign locations - first 3 in California, next 2 in New York
            if ($creator['id'] <= 3) {
                $creator['location'] = ['region' => 'California', 'country' => 'United States'];
            } elseif ($creator['id'] <= 5) {
                $creator['location'] = ['region' => 'New York', 'country' => 'United States'];
            } else {
                $creator['location'] = $locations[array_rand($locations)];
            }
        }
        
        return $creators;
    }
    
    /**
     * Get creator by ID
     *
     * @param int $creator_id Creator ID
     * @return array|null Creator data or null if not found
     */
    public function get_creator_by_id($creator_id) {
        $creators = $this->get_all_creators();
        
        foreach ($creators as $creator) {
            if ($creator['id'] == $creator_id) {
                return $creator;
            }
        }
        
        return null;
    }
    
    /**
     * Get creator by username
     *
     * @param string $username Creator username
     * @return array|null Creator data or null if not found
     */
    public function get_creator_by_username($username) {
        $creators = $this->get_all_creators();
        
        foreach ($creators as $creator) {
            if ($creator['username'] === $username) {
                return $creator;
            }
        }
        
        return null;
    }
    
    /**
     * Clear the creator data cache
     */
    public function clear_cache() {
        wp_cache_delete($this->cache_key);
    }
    
    /**
     * Get available topic options for filtering
     */
    public function get_available_topics() {
        $creators = $this->get_all_creators();
        $topics = [];
        
        foreach ($creators as $creator) {
            $topics = array_merge($topics, $creator['topics']);
        }
        
        return array_unique($topics);
    }
}