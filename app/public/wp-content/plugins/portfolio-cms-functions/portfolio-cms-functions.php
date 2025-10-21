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
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
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
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
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