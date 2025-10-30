<?php
/**
 * REST API Test Cases for Author Profile Fields
 * 
 * This file contains test cases to validate the REST API implementation
 * for user meta fields. Run these tests in your local WordPress environment.
 * 
 * Usage:
 * 1. Place this file in your plugin directory
 * 2. Access it via: http://yoursite.local/wp-content/plugins/portfolio-cms-functions/test-rest-api.php
 * 3. Or run from command line: php test-rest-api.php
 * 
 * Note: This is for local testing only. Do NOT deploy to production.
 */

// Prevent direct access in production
if ( ! defined( 'ABSPATH' ) && php_sapi_name() !== 'cli' ) {
    // Load WordPress for CLI testing
    $wp_load_path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
    if ( file_exists( $wp_load_path ) ) {
        require_once $wp_load_path;
    } else {
        die( 'WordPress not found. Please run this from your WordPress installation.' );
    }
}

/**
 * Test Case 1: Verify REST fields are registered for users
 */
function test_user_rest_fields_registered() {
    echo "\n=== TEST 1: User REST Fields Registration ===\n";
    
    $expected_fields = array(
        'author_profile_image',
        'linkedin_url',
        'twitter_url',
        'instagram_url',
        'facebook_url',
        'youtube_url',
        'web_portfolio_url',
        'other_url_1',
        'other_url_2',
        'other_url_3',
        'author_cta_hook',
        'author_cta_action_url',
    );
    
    global $wp_rest_additional_fields;
    
    if ( ! isset( $wp_rest_additional_fields['user'] ) ) {
        echo "❌ FAIL: No REST fields registered for users\n";
        return false;
    }
    
    $registered_fields = array_keys( $wp_rest_additional_fields['user'] );
    $missing_fields = array_diff( $expected_fields, $registered_fields );
    
    if ( empty( $missing_fields ) ) {
        echo "✅ PASS: All 12 user fields are registered\n";
        echo "   Registered fields: " . implode( ', ', $registered_fields ) . "\n";
        return true;
    } else {
        echo "❌ FAIL: Missing fields: " . implode( ', ', $missing_fields ) . "\n";
        return false;
    }
}

/**
 * Test Case 2: Verify author_profile field is registered for posts
 */
function test_post_author_profile_registered() {
    echo "\n=== TEST 2: Post Author Profile Registration ===\n";
    
    global $wp_rest_additional_fields;
    $post_types = array( 'article', 'project' );
    $all_pass = true;
    
    foreach ( $post_types as $post_type ) {
        if ( ! isset( $wp_rest_additional_fields[ $post_type ]['author_profile'] ) ) {
            echo "❌ FAIL: author_profile not registered for {$post_type}\n";
            $all_pass = false;
        } else {
            echo "✅ PASS: author_profile registered for {$post_type}\n";
        }
    }
    
    return $all_pass;
}

/**
 * Test Case 3: Test user field retrieval with mock data
 */
function test_user_field_retrieval() {
    echo "\n=== TEST 3: User Field Retrieval ===\n";
    
    // Get the first admin user
    $users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
    
    if ( empty( $users ) ) {
        echo "⚠️  SKIP: No admin users found for testing\n";
        return null;
    }
    
    $user = $users[0];
    $user_id = $user->ID;
    
    echo "Testing with User ID: {$user_id} ({$user->user_login})\n";
    
    // Test setting a field value
    $test_url = 'https://linkedin.com/in/testuser';
    update_field( 'linkedin_url', $test_url, 'user_' . $user_id );
    
    // Test retrieving the field value
    $retrieved_value = get_field( 'linkedin_url', 'user_' . $user_id );
    
    if ( $retrieved_value === $test_url ) {
        echo "✅ PASS: Field value set and retrieved correctly\n";
        echo "   Set: {$test_url}\n";
        echo "   Got: {$retrieved_value}\n";
        
        // Clean up
        delete_field( 'linkedin_url', 'user_' . $user_id );
        return true;
    } else {
        echo "❌ FAIL: Field value mismatch\n";
        echo "   Expected: {$test_url}\n";
        echo "   Got: " . var_export( $retrieved_value, true ) . "\n";
        return false;
    }
}

/**
 * Test Case 4: Simulate REST API request for user data
 */
function test_rest_api_user_request() {
    echo "\n=== TEST 4: REST API User Request Simulation ===\n";
    
    $users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
    
    if ( empty( $users ) ) {
        echo "⚠️  SKIP: No admin users found for testing\n";
        return null;
    }
    
    $user = $users[0];
    $user_id = $user->ID;
    
    // Set test data
    update_field( 'linkedin_url', 'https://linkedin.com/in/testuser', 'user_' . $user_id );
    update_field( 'twitter_url', 'https://twitter.com/testuser', 'user_' . $user_id );
    
    // Simulate REST request
    $request = new WP_REST_Request( 'GET', '/wp/v2/users/' . $user_id );
    $response = rest_do_request( $request );
    $data = $response->get_data();
    
    // Check if custom fields are present
    $has_linkedin = isset( $data['linkedin_url'] );
    $has_twitter = isset( $data['twitter_url'] );
    
    if ( $has_linkedin && $has_twitter ) {
        echo "✅ PASS: Custom fields present in REST response\n";
        echo "   linkedin_url: {$data['linkedin_url']}\n";
        echo "   twitter_url: {$data['twitter_url']}\n";
        
        // Clean up
        delete_field( 'linkedin_url', 'user_' . $user_id );
        delete_field( 'twitter_url', 'user_' . $user_id );
        return true;
    } else {
        echo "❌ FAIL: Custom fields missing from REST response\n";
        if ( ! $has_linkedin ) echo "   Missing: linkedin_url\n";
        if ( ! $has_twitter ) echo "   Missing: twitter_url\n";
        return false;
    }
}

/**
 * Test Case 5: Simulate REST API request for post with author profile
 */
function test_rest_api_post_author_profile() {
    echo "\n=== TEST 5: REST API Post Author Profile ===\n";
    
    // Get or create a test article
    $articles = get_posts( array(
        'post_type' => 'article',
        'posts_per_page' => 1,
    ) );
    
    if ( empty( $articles ) ) {
        echo "⚠️  SKIP: No articles found for testing\n";
        echo "   Create an article first, then run this test\n";
        return null;
    }
    
    $article = $articles[0];
    $author_id = $article->post_author;
    
    // Set test author data
    update_field( 'linkedin_url', 'https://linkedin.com/in/author', 'user_' . $author_id );
    update_field( 'author_cta_hook', 'Follow me for more content!', 'user_' . $author_id );
    
    // Simulate REST request
    $request = new WP_REST_Request( 'GET', '/wp/v2/article/' . $article->ID );
    $response = rest_do_request( $request );
    $data = $response->get_data();
    
    // Check if author_profile is present
    if ( isset( $data['author_profile'] ) ) {
        $profile = $data['author_profile'];
        
        echo "✅ PASS: author_profile object present in article response\n";
        echo "   Article ID: {$article->ID}\n";
        echo "   Author ID: {$author_id}\n";
        echo "   linkedin_url: {$profile['linkedin_url']}\n";
        echo "   author_cta_hook: {$profile['author_cta_hook']}\n";
        
        // Clean up
        delete_field( 'linkedin_url', 'user_' . $author_id );
        delete_field( 'author_cta_hook', 'user_' . $author_id );
        return true;
    } else {
        echo "❌ FAIL: author_profile object missing from article response\n";
        echo "   Available fields: " . implode( ', ', array_keys( $data ) ) . "\n";
        return false;
    }
}

/**
 * Run all tests
 */
function run_all_tests() {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║   REST API Test Suite for Author Profile Fields             ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    
    $results = array();
    
    // Ensure REST API is initialized
    do_action( 'rest_api_init' );
    
    $results['test1'] = test_user_rest_fields_registered();
    $results['test2'] = test_post_author_profile_registered();
    $results['test3'] = test_user_field_retrieval();
    $results['test4'] = test_rest_api_user_request();
    $results['test5'] = test_rest_api_post_author_profile();
    
    // Summary
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║   Test Summary                                               ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    
    $passed = count( array_filter( $results, function( $r ) { return $r === true; } ) );
    $failed = count( array_filter( $results, function( $r ) { return $r === false; } ) );
    $skipped = count( array_filter( $results, function( $r ) { return $r === null; } ) );
    $total = count( $results );
    
    echo "Total Tests: {$total}\n";
    echo "✅ Passed: {$passed}\n";
    echo "❌ Failed: {$failed}\n";
    echo "⚠️  Skipped: {$skipped}\n";
    
    if ( $failed === 0 && $passed > 0 ) {
        echo "\n🎉 All tests passed!\n";
    } elseif ( $failed > 0 ) {
        echo "\n⚠️  Some tests failed. Please review the output above.\n";
    }
    
    echo "\n";
}

// Run tests if accessed directly
if ( php_sapi_name() === 'cli' || isset( $_GET['run_tests'] ) ) {
    run_all_tests();
} else {
    echo "<h1>REST API Test Suite</h1>";
    echo "<p>Add <code>?run_tests=1</code> to the URL to run tests.</p>";
    echo "<p>Example: <code>http://yoursite.local/wp-content/plugins/portfolio-cms-functions/test-rest-api.php?run_tests=1</code></p>";
}
