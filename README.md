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

## 5. Standard Deployment Strategy (Local → Staging → Production)
-----------------------------------------------------------------

This project follows a repeatable workflow that uses Local by Flywheel for development, Hosting.net (cPanel) subdomains for staging/production, and Netlify for the front‑end deploys. To simplify CMS moves between environments, we use the Duplicator plugin to package the WordPress site.

High‑level flow:

1) Develop locally in Local by Flywheel
2) Create a Duplicator package of the CMS and deploy to a staging subdomain on Hosting.net (cPanel)
3) Deploy the front‑end portfolio to a Netlify staging site that points to the staging CMS API
4) After validation, repeat 2–3 to production using separate subdomain and Netlify production site

You can also use the manual cPanel process in Sections 8–9 when needed, but Duplicator is the recommended path for speed and reliability.

### A. Staging CMS (Hosting.net via cPanel using Duplicator)

Prereqs:
- Domain with a staging subdomain ready (e.g., `stg-cms.yourdomain.com`)
- cPanel access with MySQL and SSL
- Local by Flywheel site running and up to date

Steps:
1. In Local, install and activate the Duplicator plugin (free is sufficient).
2. Duplicator → Packages → Create New:
    - Include Database and Files
    - Exclusions (optional): cache, debug logs. Keep `wp-content/uploads` unless you’ll sync media another way.
    - Build the package and download both files: `installer.php` and the archive `.zip`.
3. cPanel → Domains → Subdomains → Create `stg-cms.yourdomain.com`.
    - Document Root: a clean directory for the staging site (e.g., `public_html/stg-cms`).
4. cPanel → File Manager: upload `installer.php` and the archive `.zip` into that Document Root.
5. cPanel → MySQL® Databases: create a DB and user, grant ALL PRIVILEGES.
6. In the browser, open `https://stg-cms.yourdomain.com/installer.php` and follow the Duplicator wizard:
    - Supply DB name/user/password
    - When prompted, set the new URL to `https://stg-cms.yourdomain.com`
    - Let Duplicator run search/replace for URLs
7. Log into `wp-admin`, then:
    - Settings → Permalinks → Post name → Save (writes `.htaccess`)
    - Enable SSL (cPanel AutoSSL) and confirm the site loads over HTTPS
    - Install/activate required plugins if not included in the archive (ACF, WPGraphQL, Wordfence, etc.)
    - Generate an Application Password for API usage
    - Discourage indexing on staging (Settings → Reading)

Verification:
- REST: `https://stg-cms.yourdomain.com/wp-json/wp/v2/article`
- GraphQL: `https://stg-cms.yourdomain.com/graphql`

### B. Staging Front‑end (Netlify)

1. Create a new Netlify site from the front‑end repo (`portfolio-site`).
2. Build command and publish directory (Vite):
    - Build command: `npm --workspace frontend run build`
    - Publish directory: `frontend/dist`
3. Environment variables:
    - `CMS_BASE_URL=https://stg-cms.yourdomain.com`
    - Any API keys or function URLs required by your setup
4. Redirects (SPA): ensure your `netlify.toml` includes SPA fallback and any function routes.
5. Deploy and verify that the portfolio pages pull article/project data from the staging CMS.

### C. Promote to Production

Create a separate CMS subdomain and a separate Netlify site (or a production branch on the same site):

1. CMS (cPanel with Duplicator):
    - Subdomain: `cms.yourdomain.com` (production)
    - Repeat the Duplicator package and install steps (you can package from staging if content is more current than local)
    - Ensure indexing is allowed on production (uncheck “Discourage search engines…”) and Wordfence rules are tuned
2. Front‑end (Netlify):
    - Production site or main branch deploy
    - `CMS_BASE_URL=https://cms.yourdomain.com`
    - Confirm API calls resolve to production CMS

Notes:
- Composer-managed plugins: if you rely on Composer in production, ensure your Duplicator package includes `vendor/` or run `composer install` after extraction.
- Alternative/manual path: See Sections 8–9 for cPanel deployment and database migration without Duplicator.

* * *

### Related front‑end deployment docs (Netlify)

- For the SPA (portfolio-site) staging/production steps and the exact environment variables to set, see: `portfolio-site/README.md` → “Front‑end Staging/Production Checklist (Netlify + CMS)”.
- Key front‑end env to set per environment:
    - Staging: `CMS_BASE_URL=https://stg-cms.yourdomain.com`
    - Production: `CMS_BASE_URL=https://cms.yourdomain.com`
    - Optionally `WP_API_BASE_URL` for functions if your REST root differs.

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


---

## 8. Production install on Hosting.com (cPanel) with a subdomain

The instructions below walk you through deploying this headless WordPress CMS to a Hosting.com cPanel account and serving it from a subdomain (for example, `cms.yourdomain.com`). The front‑end application can continue to be hosted separately (e.g., Netlify/Vite). The goal is to keep the CMS on its own subdomain and to point that subdomain’s Document Root directly to this repo’s `app/public` directory.

### Prerequisites

- cPanel access on Hosting.com with MySQL and SSL enabled
- A registered domain on the same account (e.g., `yourdomain.com`)
- This repository’s code available locally or on GitHub
- PHP 8.1 or 8.2 available in cPanel (recommended)

### Step 1 — Create the subdomain

1. Log into cPanel.
2. Go to Domains → Subdomains.
3. Create a subdomain, e.g., `cms.yourdomain.com`.
4. Set the Document Root to point at the WordPress public directory in this project:
     - Recommended: `/home/<cpanel-user>/dfd-cms/app/public`
     - If cPanel restricts you to `public_html`, use `public_html/cms` and place the contents of `app/public` there (see Alternative layout below).
5. Save. cPanel will create the directory if it doesn’t exist.

Notes:
- You can first upload the repository folder `dfd-cms` anywhere under your home directory (e.g., `/home/<user>/dfd-cms`) and then point the subdomain’s Document Root to `/home/<user>/dfd-cms/app/public`.

### Step 2 — Upload/deploy the code

Choose one of the following:

- Option A: Git Deploy (preferred)
    1. In cPanel, open “Git™ Version Control”.
    2. Create a new repository clone from your GitHub URL.
    3. Set the clone path to `/home/<user>/dfd-cms`.
    4. After cloning, ensure the subdomain’s Document Root is `/home/<user>/dfd-cms/app/public` (Domains → Subdomains → Edit).

- Option B: ZIP Upload
    1. Zip the local `dfd-cms` folder (including `app/`, `vendor/` if present, and `composer.*`).
    2. Use cPanel → File Manager to upload and extract it to `/home/<user>/dfd-cms`.

### Step 3 — Install PHP dependencies

This project manages plugins via Composer.

- If your cPanel has SSH or “Terminal”: navigate to `/home/<user>/dfd-cms` and run `composer install` to create the `vendor/` directory.
- If Composer isn’t available on the host: run `composer install` locally, then upload the generated `vendor/` folder to `/home/<user>/dfd-cms/`.

### Step 4 — Create the database and user

1. In cPanel, open “MySQL® Databases”.
2. Create a new database (e.g., `youruser_portfolio`).
3. Create a new MySQL user and strong password.
4. Add the user to the database with ALL PRIVILEGES.
5. Optional: If you’re migrating content, import your SQL dump via “phpMyAdmin” (left sidebar) → select the new DB → Import → choose the `.sql` file (e.g., `sql/local.sql`).

### Step 5 — Configure WordPress (`wp-config.php`)

Edit `app/public/wp-config.php` and set the following values:

```php
// Database settings
define('DB_NAME', 'youruser_portfolio');
define('DB_USER', 'youruser_dbuser');
define('DB_PASSWORD', 'your-strong-password');
define('DB_HOST', 'localhost');

// Absolute URLs for reliability on subdomain
define('WP_HOME',    'https://cms.yourdomain.com');
define('WP_SITEURL', 'https://cms.yourdomain.com');

// Composer autoload (already present in this project)
require_once __DIR__ . '/../vendor/autoload.php';

// (Recommended) Harden admin file editing
define('DISALLOW_FILE_EDIT', true);
```

Also update the Authentication Unique Keys and Salts using the WordPress secret-key service:
`https://api.wordpress.org/secret-key/1.1/salt/`

### Step 6 — PHP version and extensions

In cPanel → “Select PHP Version” (or “MultiPHP Manager”):

- PHP 8.1 or 8.2
- Enable extensions: `curl`, `dom`, `gd`, `json`, `mbstring`, `mysqli`, `openssl`, `xml`, `zip`, `intl` (if available)
- PHP Options (typical): memory_limit 256M; upload_max_filesize 64M; post_max_size 64M; max_execution_time 120

### Step 7 — SSL and HTTPS

1. Enable AutoSSL/Let’s Encrypt for `cms.yourdomain.com` in cPanel → SSL/TLS → SSL/TLS Status.
2. Force HTTPS by ensuring the standard WordPress `.htaccess` rules exist in `app/public/.htaccess` (WordPress will write them when permalinks are saved). Example minimal rules:

```apache
# WordPress standard rules
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
```

### Step 8 — Finalize WordPress

1. Visit `https://cms.yourdomain.com/wp-admin` and complete the install/login.
2. Go to Settings → Permalinks → choose “Post name” → Save.
3. Verify required plugins are installed/activated (ACF, WPGraphQL, Wordfence, etc.).
4. Confirm the APIs respond:
     - REST: `https://cms.yourdomain.com/wp-json/wp/v2/article`
     - GraphQL: `https://cms.yourdomain.com/graphql`

### Step 9 — Connect the front‑end (optional but recommended)

If your portfolio front‑end is hosted elsewhere (e.g., Netlify), point it to the new CMS subdomain:

- Set the environment variable used by the front‑end/Netlify Functions (e.g., `CMS_BASE_URL=https://cms.yourdomain.com`).
- Redeploy the front‑end so it begins fetching content from the new subdomain.

### Alternative layout (if Document Root cannot point into `app/public`)

If cPanel requires the Document Root to live under `public_html`, you can:

1. Create the subdomain with Document Root `public_html/cms`.
2. Copy the contents of this repo’s `app/public/` into `public_html/cms/`.
3. Keep the rest of the repo (including `vendor/`) in `/home/<user>/dfd-cms/` and ensure `wp-config.php` requires the Composer autoloader using a relative path, for example:

```php
require_once __DIR__ . '/../../dfd-cms/vendor/autoload.php';
```

This preserves the convention of keeping non-public code outside the web root while still satisfying shared-host constraints.

## 9. Migrate from Local by Flywheel to Hosting.net Staging (cPanel)

This section walks through moving your Local-by-Flywheel WordPress site (this headless CMS) to a staging subdomain on Hosting.net using cPanel. It covers database export/import, URL updates, file deployment, and staging safeguards.

### Overview

- Source: Local by Flywheel site (e.g., `portfolio-cms.local`)
- Destination: Staging subdomain (e.g., `cms-staging.yourdomain.com`) hosted on Hosting.net (cPanel)
- Document Root: point the subdomain to `/home/<user>/dfd-cms/app/public` (or use the Alternative layout in Section 8 if required)

### Step 1 — Prepare in Local

1. Update WordPress core, themes, and plugins.
2. In Local, right-click your site → Export… → check “Include Database”. Save the `.zip` locally; you’ll have a SQL dump and `wp-content` files.
3. Alternatively (or additionally), export the database via Adminer/Sequel Ace if you prefer a standalone `.sql` file.
4. Ensure ACF JSON is committed (this repo already stores Field Groups under `wp-content/plugins/portfolio-cms-functions/acf-json`). On staging you’ll be able to Sync these.
5. Note your Local site URL (e.g., `http://portfolio-cms.local`)—you’ll need it for search/replace.

### Step 2 — Create the staging subdomain in cPanel

1. cPanel → Domains → Subdomains → Create `cms-staging.yourdomain.com`.
2. Set Document Root to: `/home/<user>/dfd-cms/app/public`.
     - If restricted to `public_html`, create `public_html/cms-staging` and see “Alternative layout” in Section 8.
3. If DNS is managed elsewhere, add an `A` record for `cms-staging` pointing to your server’s IP.

### Step 3 — Deploy code to the server

Choose one:

- Git (preferred): cPanel → Git™ Version Control → Clone this repo to `/home/<user>/dfd-cms` (or `/home/<user>/dfd-cms-staging`).
- ZIP upload: Upload/extract the project so the path `/home/<user>/dfd-cms/app/public` exists.

Then install PHP dependencies:

- With SSH/Terminal: `composer install` in `/home/<user>/dfd-cms/`.
- Without Composer on host: run locally and upload the `vendor/` directory to the same path.

Copy uploads from Local:

- From your Local export, copy `wp-content/uploads/` into `/home/<user>/dfd-cms/app/public/wp-content/uploads/`.

### Step 4 — Create database and import

1. cPanel → MySQL® Databases → create DB (e.g., `youruser_portfolio_stg`).
2. Create a DB user and grant ALL PRIVILEGES to the new DB.
3. cPanel → phpMyAdmin → select the new DB → Import → choose your Local `.sql` file → Go.

### Step 5 — Configure `wp-config.php`

Edit `/home/<user>/dfd-cms/app/public/wp-config.php`:

```php
define('DB_NAME', 'youruser_portfolio_stg');
define('DB_USER', 'youruser_dbuser');
define('DB_PASSWORD', 'your-strong-password');
define('DB_HOST', 'localhost');

define('WP_HOME',    'https://cms-staging.yourdomain.com');
define('WP_SITEURL', 'https://cms-staging.yourdomain.com');

// Mark this instance as staging
define('WP_ENVIRONMENT_TYPE', 'staging');

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Optional hardening
define('DISALLOW_FILE_EDIT', true);
```

Update the Authentication Keys/Salts with fresh values from https://api.wordpress.org/secret-key/1.1/salt/

### Step 6 — Update URLs in the database

After importing the Local DB, it still contains references to the Local URL. Update them to the staging subdomain. Prefer a tool that preserves serialized data:

- WP‑CLI (if available on host):
    - `wp search-replace 'http://portfolio-cms.local' 'https://cms-staging.yourdomain.com' --skip-columns=guid`
- Better Search Replace plugin (GUI):
    - Search for `http://portfolio-cms.local` → Replace with `https://cms-staging.yourdomain.com` → Select all tables → Run as a dry run first.

### Step 7 — SSL, permalinks, and caches

1. cPanel → SSL/TLS Status → enable AutoSSL for `cms-staging.yourdomain.com`.
2. Visit `https://cms-staging.yourdomain.com/wp-admin`, log in, go to Settings → Permalinks → choose “Post name” → Save (this writes `.htaccess`).
3. If you use a caching plugin, clear its caches after the URL change.

### Step 8 — Staging safeguards

- Discourage indexing: Settings → Reading → “Discourage search engines…” (checked).
- robots: ensure `Disallow: /` on staging or use a plugin to set `noindex` headers.
- Optional password: cPanel → Directory Privacy to protect `/app/public/wp-admin` or entire site.
- Wordfence: ensure WAF is active; consider relaxed rate limits for your IP while testing.

### Step 9 — Verify the APIs and ACF JSON

- REST: `https://cms-staging.yourdomain.com/wp-json/wp/v2/article`
- GraphQL: `https://cms-staging.yourdomain.com/graphql`
- ACF JSON: In WP Admin → ACF → Field Groups, click “Sync” if prompted to load JSON from the repo.

### Step 10 — Front‑end and credentials

- If a front‑end is consuming this staging CMS, configure its env var: `CMS_BASE_URL=https://cms-staging.yourdomain.com` and redeploy.
- Generate a new Application Password for staging (Users → Profile → Application Passwords) and use it only for staging.

### Troubleshooting

- Mixed content: ensure `WP_HOME/WP_SITEURL` use `https://` and update any hardcoded `http://` assets.
- 404s: re-save Permalinks; confirm `.htaccess` WordPress rules exist.
- Login loop: clear browser/site caches; verify cookie domain not pinned to the Local domain.
- Missing media: confirm `wp-content/uploads/` was copied to the server with correct permissions (typically 755 dirs / 644 files).

