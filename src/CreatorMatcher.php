<?php
/**
 * Creator Matching Algorithm for WordPress.com Reader Connections
 * 
 * Handles intelligent matching of creators based on topics, engagement, 
 * location, and language compatibility.
 */

namespace WordPressCom\Reader\Connections;

class CreatorMatcher {
    
    /**
     * Scoring weights for the matching algorithm
     */
    const TOPIC_WEIGHT = 40;
    const LANGUAGE_WEIGHT = 30;
    const ENGAGEMENT_WEIGHT = 20;
    const SIZE_BONUS = 10;
    const MIN_MATCH_SCORE = 30;
    const MAX_RESULTS = 5;
    
    /**
     * Find matching creators for a given user
     *
     * @param array $user_profile Current user's profile data
     * @param array $creators Available creators to match against
     * @return array Sorted array of matched creators with scores
     */
    public function find_matches($user_profile, $creators) {
        $matches = [];
        
        foreach ($creators as $creator) {
            $score = $this->calculate_match_score($user_profile, $creator);
            
            if ($score >= self::MIN_MATCH_SCORE) {
                $creator['match_score'] = $score;
                $creator['common_topics'] = $this->find_common_topics(
                    $user_profile['topics'], 
                    $creator['topics']
                );
                $matches[] = $creator;
            }
        }
        
        // Sort by match score (highest first)
        usort($matches, function($a, $b) {
            return $b['match_score'] - $a['match_score'];
        });
        
        return array_slice($matches, 0, self::MAX_RESULTS);
    }
    
    /**
     * Calculate compatibility score between user and creator
     *
     * @param array $user User profile
     * @param array $creator Creator profile
     * @return int Match score (0-100)
     */
    private function calculate_match_score($user, $creator) {
        $score = 0;
        
        // Topic similarity (most important factor)
        $common_topics = $this->find_common_topics($user['topics'], $creator['topics']);
        $score += count($common_topics) * self::TOPIC_WEIGHT;
        
        // Language compatibility (essential)
        if ($creator['language'] === $user['language']) {
            $score += self::LANGUAGE_WEIGHT;
        }
        
        // Engagement compatibility
        $engagement_diff = abs($creator['engagement_score'] - $user['engagement_score']);
        $engagement_points = max(0, self::ENGAGEMENT_WEIGHT - ($engagement_diff * 0.5));
        $score += $engagement_points;
        
        // Size bonus for mid-size creators (easier collaboration)
        if ($creator['followers'] >= 1000 && $creator['followers'] <= 5000) {
            $score += self::SIZE_BONUS;
        }
        
        return round($score);
    }
    
    /**
     * Find topics that both user and creator share
     *
     * @param array $user_topics User's topics
     * @param array $creator_topics Creator's topics  
     * @return array Common topics
     */
    private function find_common_topics($user_topics, $creator_topics) {
        $common = [];
        
        foreach ($user_topics as $user_topic) {
            foreach ($creator_topics as $creator_topic) {
                if (stripos($creator_topic, $user_topic) !== false || 
                    stripos($user_topic, $creator_topic) !== false) {
                    $common[] = $user_topic;
                    break;
                }
            }
        }
        
        return array_unique($common);
    }
    
    /**
     * Get trending creators based on growth rate
     *
     * @param array $creators All creators
     * @return array Trending creators sorted by growth
     */
    public function get_trending_creators($creators) {
        $trending = array_filter($creators, function($creator) {
            return isset($creator['trending']) && $creator['trending'] === true;
        });
        
        usort($trending, function($a, $b) {
            return ($b['growth_rate'] ?? 0) - ($a['growth_rate'] ?? 0);
        });
        
        return array_slice($trending, 0, self::MAX_RESULTS);
    }
    
    /**
     * Get local creators based on location proximity
     *
     * @param array $user_location User's location data
     * @param array $creators All creators
     * @return array Local creators sorted by proximity
     */
    public function get_local_creators($user_location, $creators) {
        $same_region = [];
        $same_country = [];
        
        foreach ($creators as $creator) {
            if (!isset($creator['location'])) continue;
            
            $creator_location = $creator['location'];
            
            // Same region (highest priority)
            if ($creator_location['region'] === $user_location['region'] && 
                $creator_location['country'] === $user_location['country']) {
                $same_region[] = $creator;
            }
            // Same country, different region
            elseif ($creator_location['country'] === $user_location['country'] &&
                     $creator_location['region'] !== $user_location['region']) {
                $same_country[] = $creator;
            }
        }
        
        // Sort by follower count
        $sort_by_followers = function($a, $b) {
            return ($b['followers'] ?? 0) - ($a['followers'] ?? 0);
        };
        
        usort($same_region, $sort_by_followers);
        usort($same_country, $sort_by_followers);
        
        // Combine and limit results
        $local_creators = array_merge($same_region, $same_country);
        return array_slice($local_creators, 0, self::MAX_RESULTS);
    }
    
    /**
     * Apply filters to creator list
     *
     * @param array $creators Creators to filter
     * @param array $filters Filter criteria
     * @return array Filtered creators
     */
    public function apply_filters($creators, $filters) {
        $filtered = $creators;
        
        // Filter by topic
        if (!empty($filters['topic'])) {
            $filtered = array_filter($filtered, function($creator) use ($filters) {
                foreach ($creator['topics'] as $topic) {
                    if (stripos($topic, $filters['topic']) !== false) {
                        return true;
                    }
                }
                return false;
            });
        }
        
        // Filter by creator size
        if (!empty($filters['size'])) {
            $filtered = array_filter($filtered, function($creator) use ($filters) {
                $followers = $creator['followers'] ?? 0;
                
                switch ($filters['size']) {
                    case 'small':
                        return $followers < 2000;
                    case 'medium':
                        return $followers >= 2000 && $followers <= 5000;
                    case 'large':
                        return $followers > 5000;
                    default:
                        return true;
                }
            });
        }
        
        return array_values($filtered); // Re-index array
    }
    
    /**
     * Format follower count for display
     *
     * @param int $count Follower count
     * @return string Formatted count (e.g., "2.3k")
     */
    public function format_follower_count($count) {
        if ($count >= 1000) {
            return number_format($count / 1000, 1) . 'k';
        }
        return (string) $count;
    }
}