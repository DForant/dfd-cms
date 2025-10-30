<?php
/**
* Plugin Name: Portfolio CMS Headless Functions
* Description: Registers Custom Post Types (Articles, Projects) for the headless CMS.
* Version: 1.0.0
* Author: Dean Forant
* Text Domain: cms-functions
*/

// Exit if accessed directly (security best practice)
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* Register Custom Post Types: Articles and Projects
*/
function portfolio_register_custom_post_types() {

    // --- 1. ARTICLES/TUTORIALS CPT ---
    $article_labels = array(
        'name'          => _x( 'Articles', 'Post Type General Name', 'cms-functions' ),
        'singular_name' => _x( 'Article', 'Post Type Singular Name', 'cms-functions' ),
        'menu_name'     => __( 'Articles', 'cms-functions' ),
        'all_items'     => __( 'All Articles', 'cms-functions' ),
    );
    $article_args = array(
        'labels'             => $article_labels,
        // Enable author support so REST can include author and UI shows Author box
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'author' ),
        'public'             => true,
        'show_in_rest'       => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => false,
        'has_archive'        => false,
        'rewrite'            => array( 'slug' => 'articles' ),
        'show_in_graphql'    => true, // REQUIRED for WPGraphQL
        'graphql_single_name' => 'article',
        'graphql_plural_name' => 'articles',
    );
    register_post_type( 'article', $article_args );


    // --- 2. PROJECTS/CASE STUDIES CPT ---
    $project_labels = array(
        'name'          => _x( 'Projects', 'Post Type General Name', 'cms-functions' ),
        'singular_name' => _x( 'Project', 'Post Type Singular Name', 'cms-functions' ),
        'menu_name'     => __( 'Projects', 'cms-functions' ),
        'all_items'     => __( 'All Projects', 'cms-functions' ),
    );
    $project_args = array(
        'labels'             => $project_labels,
        // Also enable author on projects for consistency
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'author' ),
        'public'             => true,
        'show_in_rest'       => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => false,
        'has_archive'        => false,
        'rewrite'            => array( 'slug' => 'projects' ),
        'show_in_graphql'    => true, // REQUIRED for WPGraphQL
        'graphql_single_name' => 'project',
        'graphql_plural_name' => 'projects',
    );
    register_post_type( 'project', $project_args );
}
// Hook into the 'init' action to register the CPTs
add_action( 'init', 'portfolio_register_custom_post_types' );

/**
 * Register Taxonomies: Categories (Hierarchical) and Tags (Non-Hierarchical)
 */
function portfolio_register_taxonomies() {

    // --- 1. HIERARCHICAL CATEGORY TAXONOMY (e.g., Graphic Design -> How-To) ---
    $category_labels = array(
        'name'          => _x( 'Content Categories', 'taxonomy general name', 'cms-functions' ),
        'singular_name' => _x( 'Content Category', 'taxonomy singular name', 'cms-functions' ),
        'menu_name'     => __( 'Categories', 'cms-functions' ),
    );
    $category_args = array(
        'labels'             => $category_labels,
        'hierarchical'       => true, // THIS IS KEY for parent/sub-categories
        'public'             => true,
        'show_ui'            => true,
        'show_in_rest'       => true,
        'show_admin_column'  => true,
        'query_var'          => true,
        'show_in_graphql'    => true, // Essential for WPGraphQL
        'graphql_single_name' => 'contentCategory',
        'graphql_plural_name' => 'contentCategories',
    );
    // Attach to both 'article' and 'project' CPTs
    register_taxonomy( 'content_category', array( 'article', 'project' ), $category_args );


    // --- 2. NON-HIERARCHICAL TAG TAXONOMY (e.g., WordPress, Figma, SCSS) ---
    $tag_labels = array(
        'name'          => _x( 'Content Tags', 'taxonomy general name', 'cms-functions' ),
        'singular_name' => _x( 'Content Tag', 'taxonomy singular name', 'cms-functions' ),
        'menu_name'     => __( 'Tags', 'cms-functions' ),
    );
    $tag_args = array(
        'labels'             => $tag_labels,
        'hierarchical'       => false, // THIS IS KEY for flat, keyword-style tags
        'public'             => true,
        'show_ui'            => true,
        'show_in_rest'       => true,
        'show_admin_column'  => true,
        'query_var'          => true,
        'show_in_graphql'    => true, // Essential for WPGraphQL
        'graphql_single_name' => 'contentTag',
        'graphql_plural_name' => 'contentTags',
    );
    // Attach to both 'article' and 'project' CPTs
    register_taxonomy( 'content_tag', array( 'article', 'project' ), $tag_args );
}
add_action( 'init', 'portfolio_register_taxonomies' ); // Execute the function

function portfolio_cms_ensure_acf_json_dir() {
    $dir = PORTFOLIO_CMS_PATH . '/acf-json';
    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
    }
    // Optional: keep folder in Git when empty
    if ( is_dir( $dir ) && ! file_exists( $dir . '/.gitkeep' ) ) {
        @file_put_contents( $dir . '/.gitkeep', '' );
    }
}

/**
* Activation Hook: Flushes rewrite rules to make CPT slugs available immediately.
*/
function portfolio_cms_activate() {
    // Call the registration function on activation
    portfolio_register_custom_post_types(); 
    // Ensure ACF JSON directory exists
    portfolio_cms_ensure_acf_json_dir();
    // Flush rules
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'portfolio_cms_activate' );

// Define the path to your plugin directory
define( 'PORTFOLIO_CMS_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

/**
 * Filter to set the ACF JSON save path to the plugin folder.
 * * @param string $path The default path.
 * @return string The new path.
 */
add_filter('acf/settings/save_json', 'portfolio_acf_json_save_point');
function portfolio_acf_json_save_point( $path ) {
    // Set the path to the 'acf-json' folder in your plugin
    $path = PORTFOLIO_CMS_PATH . '/acf-json';
    return $path;
}

/**
 * Filter to add the plugin folder to the ACF JSON load paths.
 * * @param array $paths An array of paths to look for ACF JSON files.
 * @return array The updated array of paths.
 */
add_filter('acf/settings/load_json', 'portfolio_acf_json_load_point');
function portfolio_acf_json_load_point( $paths ) {
    // Remove the original (theme) path if you don't want to load from themes
    unset($paths[0]); 
    
    // Append the new path to the 'acf-json' folder in your plugin
    $paths[] = PORTFOLIO_CMS_PATH . '/acf-json';
    
    return $paths;
}

/**
 * Ensure Gutenberg (block editor) is enabled for custom post types.
 * Some environments/plugins may disable it; this guarantees it's on for these CPTs.
 */
add_filter( 'use_block_editor_for_post_type', function ( $use_block_editor, $post_type ) {
    if ( in_array( $post_type, array( 'article', 'project' ), true ) ) {
        return true;
    }
    return $use_block_editor;
}, 10, 2 );

/**
 * Register ACF user meta fields for REST API.
 * Exposes author profile details to the /wp/v2/users endpoint.
 */
function portfolio_register_user_rest_fields() {
    // Define all user meta fields from the Author Profile Details ACF group
    $user_fields = array(
        'author_profile_image' => 'url',
        'linkedin_url'         => 'string',
        'twitter_url'          => 'string',
        'instagram_url'        => 'string',
        'facebook_url'         => 'string',
        'youtube_url'          => 'string',
        'web_portfolio_url'    => 'string',
        'other_url_1'          => 'string',
        'other_url_2'          => 'string',
        'other_url_3'          => 'string',
        'author_cta_hook'      => 'string',
        'author_cta_action_url' => 'string',
    );

    foreach ( $user_fields as $field_name => $field_type ) {
        register_rest_field(
            'user',
            $field_name,
            array(
                'get_callback' => function( $user ) use ( $field_name ) {
                    $value = get_field( $field_name, 'user_' . $user['id'] );
                    return $value ? $value : '';
                },
                'update_callback' => function( $value, $user ) use ( $field_name ) {
                    return update_field( $field_name, $value, 'user_' . $user->ID );
                },
                'schema' => array(
                    'description' => sprintf( 'ACF field: %s', $field_name ),
                    'type'        => $field_type,
                ),
            )
        );
    }
}
add_action( 'rest_api_init', 'portfolio_register_user_rest_fields' );

/**
 * Register author profile fields for posts in REST API.
 * Adds author_profile field to articles and projects that includes all author meta.
 */
function portfolio_register_author_profile_for_posts() {
    $post_types = array( 'article', 'project' );

    foreach ( $post_types as $post_type ) {
        register_rest_field(
            $post_type,
            'author_profile',
            array(
                'get_callback' => function( $post ) {
                    $author_id = $post['author'];
                    
                    // Get all author profile fields
                    return array(
                        'author_profile_image' => get_field( 'author_profile_image', 'user_' . $author_id ) ?: '',
                        'linkedin_url'         => get_field( 'linkedin_url', 'user_' . $author_id ) ?: '',
                        'twitter_url'          => get_field( 'twitter_url', 'user_' . $author_id ) ?: '',
                        'instagram_url'        => get_field( 'instagram_url', 'user_' . $author_id ) ?: '',
                        'facebook_url'         => get_field( 'facebook_url', 'user_' . $author_id ) ?: '',
                        'youtube_url'          => get_field( 'youtube_url', 'user_' . $author_id ) ?: '',
                        'web_portfolio_url'    => get_field( 'web_portfolio_url', 'user_' . $author_id ) ?: '',
                        'other_url_1'          => get_field( 'other_url_1', 'user_' . $author_id ) ?: '',
                        'other_url_2'          => get_field( 'other_url_2', 'user_' . $author_id ) ?: '',
                        'other_url_3'          => get_field( 'other_url_3', 'user_' . $author_id ) ?: '',
                        'author_cta_hook'      => get_field( 'author_cta_hook', 'user_' . $author_id ) ?: '',
                        'author_cta_action_url' => get_field( 'author_cta_action_url', 'user_' . $author_id ) ?: '',
                    );
                },
                'schema' => array(
                    'description' => 'Author profile details including social links and CTA information',
                    'type'        => 'object',
                ),
            )
        );
    }
}
add_action( 'rest_api_init', 'portfolio_register_author_profile_for_posts' );
add_action( 'rest_api_init', 'portfolio_register_author_profile_for_posts' );