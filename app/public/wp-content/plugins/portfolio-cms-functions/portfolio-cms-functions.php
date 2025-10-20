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
        'show-in-rest'       => true,
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
        'show-in-rest'       => true,
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