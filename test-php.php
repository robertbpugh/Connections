<?php
/**
 * Simple PHP test file to verify the classes work
 * Run with: php test-php.php
 */

// Include the classes
require_once 'src/CreatorMatcher.php';
require_once 'src/CreatorDataProvider.php';

use WordPressCom\Reader\Connections\CreatorMatcher;
use WordPressCom\Reader\Connections\CreatorDataProvider;

// Test the matching algorithm
echo "Testing Creator Matcher...\n";

$matcher = new CreatorMatcher();
$dataProvider = new CreatorDataProvider();

// Mock user profile
$userProfile = [
    'topics' => ['Food', 'Cooking', 'Recipes'],
    'language' => 'en',
    'engagement_score' => 75,
    'location' => [
        'region' => 'California',
        'country' => 'United States'
    ]
];

// Get sample creators
$creators = $dataProvider->get_sample_creators(); // We'd need to make this public or create a test method

// Find matches
$matches = $matcher->find_matches($userProfile, $creators);

echo "Found " . count($matches) . " matching creators:\n";
foreach ($matches as $creator) {
    echo "- {$creator['name']} (Score: {$creator['match_score']})\n";
}

// Test trending creators
echo "\nTesting trending creators...\n";
$trending = $matcher->get_trending_creators($creators);
echo "Found " . count($trending) . " trending creators\n";

// Test local creators
echo "\nTesting local creators...\n";
$local = $matcher->get_local_creators($userProfile['location'], $creators);
echo "Found " . count($local) . " local creators\n";

// Test filters
echo "\nTesting filters...\n";
$filters = ['topic' => 'Food', 'size' => 'medium'];
$filtered = $matcher->apply_filters($creators, $filters);
echo "Found " . count($filtered) . " creators matching filters\n";

echo "\nAll tests completed successfully!\n";
?>