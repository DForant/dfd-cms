✍️ Portfolio Content Management System (Headless CMS)
=====================================================

A decoupled, content management system built on WordPress, designed to securely deliver content to my public-facing portfolio website.



## 1. Executive Summary & Architecture

This project establishes the backend for my portfolio website using a headless architecture. The core principle is to use WordPress for content management only, while a separate, front-end application handles the presentation.

### Key Architectural Decisions:

*   **API Layer:** WPGraphQL is used instead of the traditional REST API to allow for efficient, specific data querying, reducing over-fetching and improving front-end performance.
    
*   **Data Modeling:** Custom Post Types (CPTs) and Advanced Custom Fields (ACF) were implemented to create structured data models for Articles and Projects/Case Studies.
    
*   **Security:** A security-first approach was taken by implementing a Web Application Firewall (Wordfence) and securing API connections using Application Passwords.
    
*   **Dependency Management:** All PHP dependencies (plugins) are managed and version-controlled via Composer and WPackagist, ensuring a consistent and reproducible environment across all stages.
    

* * *

## 2. Technical Stack and Dependencies
-----------------------------------

| **Component** | **Technology** | **Purpose** |
| --- | --- | --- |
| **CMS Core** | WordPress (PHP) | Content management and authentication layer. |
| **API Layer** | WPGraphQL, WPGraphQL for ACF | Efficient, structured data querying. |
| **Data Modeling** | Advanced Custom Fields (ACF) | Defining custom fields for content types. |
| **Security** | Wordfence WAF, HTTPS/SSL | Web application firewall and traffic encryption. |
| **Development Tools** | Composer, Local by Flywheel, Git | Dependency management and local environment setup. |
| **Deployment** | Duplicator | Migration and deployment from local to staging/production environments. |

### Composer Configuration Summary

The following files are tracked in source control to manage the environment:
*   `composer.json` and `composer.lock`.
    
*   The `repositories` block is configured with **WPackagist** to allow Composer to manage all WordPress plugins.
    

* * *

## 3. Data Modeling (Custom Post Types and API Schema)
---------------------------------------------------

### A. Custom Post Types (CPTs)

| **CPT Name** | **Slug** | **Purpose** |
| --- | --- | --- |
| **Articles** | `article` | Tutorials, guides, and long-form content. |
| **Projects/Case Studies** | `project` | Showcasing portfolio work and client solutions. |

### B. ACF Fields and GraphQL Schema Verification

ACF Field Groups were created for each CPT and verified to be exposed to the GraphQL schema.
| **CPT** | **ACF Field Group Name** | **Example Custom Fields** | **GraphQL Query Example** |
| --- | --- | --- | --- |
| **Articles** | `articleData` | `reading_time`, `tutorial_level` | **Query:** `query GetArticles { articles { nodes { title, articleData { readingTime } } } }` |
| **Projects** | `projectData` | `client_name`, `project_url`, `project_services` | **Query:** `query GetProjects { projects { nodes { title, projectData { clientName } } } }` |


* * *

## 4. Security Implementation and Hardening


Security was implemented before deployment to Production to protect the content and the CMS backend.

### A. Backend Hardening (Wordfence)

The **Wordfence** plugin was configured for maximum protection:
*   **Web Application Firewall (WAF):** Set to Extended Protection to run before the WordPress core loads.
    
*   **Brute Force & 2FA:** Brute force protection is configured, and Two-Factor Authentication (2FA) is enabled for all administrative users.
    
*   **Updates:** A strict policy of immediately applying core, theme, and plugin updates is followed.
    

### B. API and Frontend Connection Security

*   **Application Passwords:** API access is secured using Application Passwords instead of primary user passwords. These are unique, revocable credentials that prevent exposing high-privilege user details.
    
*   **Restrict Endpoint Access:** WPGraphQL is configured to Restrict Endpoint to Authenticated Users, disabling anonymous access and defending against potential Denial-of-Service (DoS) attacks.
    
*   **Secure Credential Storage:** API credentials are never hardcoded in the front-end code; they are stored in Environment Variables (`.env` files) on the server, which are excluded from source control (`.gitignore`).
    
*   **SSL/HTTPS:** All traffic, including API requests, is served over HTTPS to ensure data encryption.
    

* * *

## 5. Deployment Strategy
----------------------

The project utilizes a decoupled two-stage (Staging and Production) deployment process with the following environments:

*   **Staging Environment:** `stg-cms.deanforant.com` (hosted on hosting.com)
*   **Production Environment:** `cms.deanforant.com` (hosted on hosting.com)

### A. WordPress Backend Deployment (Content & CMS)

The WordPress backend is deployed from Local by Flywheel to the staging environment using the **Duplicator** plugin.

#### Deploying from Local to Staging with Duplicator

1.  **Install Duplicator on Local:**
    *   In your Local by Flywheel WordPress site, navigate to **Plugins → Add New**.
    *   Search for "Duplicator" and install the free version by Snap Creek.
    *   Activate the plugin.

2.  **Create a Package:**
    *   Go to **Duplicator → Packages** in your WordPress admin.
    *   Click **Create New**.
    *   Name your package (e.g., "staging-deploy-2024-11-02").
    *   Under **Archive** settings, ensure all necessary files are included (wp-content, plugins, themes).
    *   Click **Next** to scan your site for issues.
    *   Once the scan passes, click **Build** to create the package.
    *   Download both the **Installer** file (`installer.php`) and the **Archive** file (`.zip` package).

3.  **Prepare Staging Environment on hosting.com:**
    *   Log into your hosting.com account and access **cPanel**.
    *   Navigate to **Domains** and ensure `stg-cms.deanforant.com` is set up and pointing to a directory (e.g., `public_html/stg-cms`).
    *   **Note:** Subdomain configuration steps may vary by hosting provider. Refer to hosting.com's documentation if needed.
    *   Create a new MySQL database and user via **MySQL Database Wizard** in cPanel.
    *   Note the database name, username, password, and host (usually `localhost`).

4.  **Upload Package to Staging:**
    *   In cPanel, use **File Manager** or an FTP client to navigate to the staging directory.
    *   Upload both the `installer.php` and the `.zip` archive file to the root of `stg-cms.deanforant.com`.

5.  **Run the Duplicator Installer:**
    *   In your browser, navigate to `https://stg-cms.deanforant.com/installer.php`.
    *   Accept the terms and conditions, then click **Next**.
    *   Enter your database credentials (name, user, password, host).
    *   Click **Test Database** to verify the connection, then click **Next**.
    *   Review the settings and click **Next** to extract and install the site.
    *   Once complete, click **Admin Login** to log into your newly deployed staging site.
    
    **⚠️ CRITICAL SECURITY STEP:**
    *   Immediately delete the `installer.php` and archive `.zip` files from the server. These files pose a significant security risk if left on the server and could allow unauthorized access or reinstallation.

6.  **Post-Deployment Configuration:**
    *   Verify all plugins are active, especially Wordfence, WPGraphQL, and ACF.
    *   Update permalinks: Go to **Settings → Permalinks** and click **Save Changes** to flush rewrite rules.
    *   Configure environment-specific settings (e.g., API endpoints, Application Passwords for staging).
    *   The `vendor/autoload.php` file should already be included in `wp-config.php` to ensure all Composer-managed dependencies are loaded efficiently.

7.  **Deploying to Production:**
    *   Follow the same Duplicator process to deploy from staging to `cms.deanforant.com`.
    *   **Database Synchronization** should be performed from Staging to Production only when content structure, plugins, or security settings are verified, preventing direct changes to the live environment.
    *   Always test thoroughly on staging before deploying to production.

### B. Frontend Application

*   The front-end application is hosted separately on **Netlify** (in a separate public GitHub repository).
    
*   The front-end is responsible for making authenticated GraphQL queries to the WordPress API endpoints:
    *   Staging frontend connects to: `https://stg-cms.deanforant.com/graphql`
    *   Production frontend connects to: `https://cms.deanforant.com/graphql`
    

* * *

## 6. Local Development Setup Guide
--------------------------------

This section details the steps to replicate the local CMS environment.

### 1. Setting Up Your Local Environment

1.  **Install Local by Flywheel:** Download and install Local.
    
2.  **Create a New WordPress Site:** Use the default settings to quickly spin up a new WordPress instance.
    

### 2. Version Control with Git and Composer

1.  **Initialize Composer:** In the root WordPress directory, run `composer init` and add the WPackagist repository block to `composer.json`.
    JSON
    
        "repositories": [
            {
                "type": "composer",
                "url": "https://wpackagist.org"
            }
        ]
    
2.  **Install Plugins:** Install all required plugins via Composer:
    Bash
    
        composer require wpackagist-plugin/wp-graphql
        composer require wpackagist-plugin/advanced-custom-fields
        composer require wpackagist-plugin/wordfence
        # ... and others like wpackagist-plugin/wp-graphql-acf
    
3.  **Enable Autoloading:** Add the following line to your `wp-config.php` file to enable the autoloader:
    PHP
    
        require_once __DIR__ . '/vendor/autoload.php';
    

### 3. Verification

-   Log into the WordPress admin and verify all required plugins are installed and activated.
    
-   Verify the GraphQL endpoint is accessible at `http://yoursite.local/graphql`.
    - You should see a message similar to this:

```
    {
  "errors": [
    {
      "message": "GraphQL Request must include at least one of those two parameters: \"query\" or \"queryId\""
    }
  ],
  "extensions": {
    "debug": [
      {
        "type": "DEBUG_LOGS_INACTIVE",
        "message": "GraphQL Debug logging is not active. To see debug logs, GRAPHQL_DEBUG must be enabled."
      }
    ]
  }
}
```   

## 7 - Setting up the articles/tutorials and projects/case studies post types

### Prerequisites

- The Advanced Custom Fields (ACF) plugin is installed and activated.

- Your two Custom Post Types (article and project) are registered in WordPress. To do this we will create a standard custom plugin to register these post types

    **1 Create The Plugin Files**

    - Navigate to your WordPress installation's plugins directory: `wp-content/plugins/`.
    - Create a new folder for your custom code: `wp-content/plugins/portfolio-cms-functions/`
    - Inside that folder, create the main PHP file: `portfolio-cms-functions.php`

    **2 The Custom Plugin Code**

    Open portfolio-cms-functions.php and paste the following complete code block. This code handles three things:

    - Plugin Header: Identifies the code as a plugin to WordPress.

    - CPT Registration: Defines the slugs and arguments for article and project.

    - Activation Hook: Flushes rewrite rules upon activation to ensure the CPTs work immediately.

    PHP
    ```
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
    * Activation Hook: Flushes rewrite rules to make CPT slugs available immediately.
    */
    function portfolio_cms_activate() {
        // Call the registration function on activation
        portfolio_register_custom_post_types(); 
        // Flush rules
        flush_rewrite_rules();
    }
    register_activation_hook( __FILE__, 'portfolio_cms_activate' );
    ```
    **3 Activation and Git Tracking**
    - **Activate:** Go to Plugins in your WordPress admin. Find the new plugin named "Portfolio CMS Headless Functions" and click Activate.
    - **Verify:** The new menu items "Articles" and "Projects" will now appear in your admin sidebar.
    - **Git Tracking:** Since this is a standard plugin directory within `wp-content/plugins/`, you must commit the entire `portfolio-cms-functions/` folder to your GitHub repository. This ensures your custom code is version-controlled.

- The WPGraphQL for ACF plugin is installed and activated.

### 1. Field Group: Articles/Tutorials

This field group will hold all the UX, SEO, and tutorial-specific fields for your articles.

**A. Create the Field Group**

   1. Go to ACF → Field Groups → Add New.
   
   2. Set the Title to Article Fields.

**B. Set the Location Rule** 
- In the Location box, set the rule: Show this field group if Post Type is equal to Article.

**C. Add Custom Fields**

|Field Label|Field Name (Key)|Field Type|Instructions/Details|
|-----------|----------------|----------|--------------------|
|Reading Time(min)|reading_time|Number|Estimated time to read the article in minutes.|
|Tutorial Level|tutorial_level|Select|Choices: Beginner, Intermediate, Advanced.|
|Live Demo URL|live_demo_url|URL|Link to a deployed version of the code.|
|Source Code URL|source_code_url|URL|Link to the GitHub repository.|
|SEO Meta Description|seo_meta_description|Text Area|Custom description for the <meta name="description"> tag.|
|Open Graph Image|og_image|Image|Specifically for social media sharing cards.

**D. Configure GraphQL Settings**

1. In the sidebar (or top section) of the Field Group settings, find the GraphQL box.

2. Set Show in GraphQL to Yes.

3. Set the GraphQL Field Name to articleData. This is the wrapper name we used in our queries (articleData { readingTime }).

4. Click Save Changes.

### 2. Field Group: Projects/Case Studies

This field group will hold all the context, results, and visual fields for your project showcases.

**A. Create the Field Group**
1. Go to ACF → Field Groups → Add New.
2. Set the Title to Project Fields.

**B. Set the Location Rule**
- In the Location box, set the rule: Show this field group if Post Type is equal to Project.

**C. Add Custom Fields**

|Field Label|Field Name (Key)|Field Type|Instructions/Details|
|-----------|----------------|----------|--------------------|
|Client Name|client_name|Text|Name of the client/company.|
|Live Project URL|live_project_url|URL|Link to the deployed project.|
|Project Date|project_date|Date Picker|Date the project was completed.|
|Client Testimonial|Textarea|Client Testimonial
|Key Deliverables|key_deliverables|Checkbox|Options: Design, Development, Branding, SEO.|
|Source Repository URL|source_repository_url|URL|Link to the souce control repository (if availabile)|

**D. Configure GraphQL Settings**

1. In the sidebar (or top section) of the Field Group settings, find the GraphQL box.

2. Set Show in GraphQL to Yes.

3. Set the GraphQL Field Name to projectData. This will be your wrapper name in queries (projectData { clientName }).

4. Click Save Changes.

## Create an export of the custom fields and groups in JSON format. 

We want to be able to track the custom fields and groups in source control. In order to do this we will create an export of our fields and groups and place the file in the acf-json folder under the portfolio-cms-functions plugin folder.

1. In the portfolio-cms-functions plugin folder create a folder callled acf-json. This will be the folder where we export our exported json file to.

2. Inside this folder create a index.php file and add the following

```
<?php 
    // Silence is Golden
?>
```

2.  In the Wordpress admin page, navigate to ACF>Tools 

3. In the Export panel, check the Toggle All checkbox to include both Article and Project fields

4. Click the Export As JSON button

5. When the file Save as dialog pops up navigate to portfolio-cms-functions/acf-json folder and save the json file as portfolio-fields.json

