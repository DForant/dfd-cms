✍️ Portfolio Content Management System (Headless CMS)
=====================================================

A decoupled, content management system built on WordPress, designed to securely deliver content to my public-facing portfolio website.

## 1. Executive Summary & Architecture

This project establishes the backend for a portfolio website using a headless architecture. The core principle is to use WordPress for content management only, while a separate, front-end application handles the presentation.

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

## 5. Deployment Strategy
----------------------

The project utilizes a decoupled two-stage (Staging and Production) deployment process using hosting.net.

### A. WordPress Backend (Content & CMS)

*   The WordPress backend is deployed using the host's cPanel/hPanel Staging tool for easy staging environment creation.
    
*   **Database Synchronization** is performed from Staging to Production only when content structure, plugins, or security settings are verified, preventing direct changes to the live environment.
    
*   The `vendor/autoload.php` file is included in `wp-config.php` to ensure all Composer-managed dependencies are loaded efficiently.
    

### B. Frontend Application

*   The front-end code (in a separate public GitHub repository) is deployed to the hosting environment using either Direct Integration (if available) or a manual process (FTP).
    
*   The front-end is responsible for making authenticated GraphQL queries to the production WordPress API.
    

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

*   Log into the WordPress admin and verify all required plugins are installed and activated.
    
*   Verify the GraphQL endpoint is accessible at `http://yoursite.local/graphql`.