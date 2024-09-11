<?php
/*
Plugin Name: Job Search Plugin
Plugin URI: https://example.com
Description: A simple job search form with filtering options for category, location, and job type.
Version: 1.0
Author: Erick Ruo
Author URI: https://example.com
License: GPL2
*/

// Enqueue styles
function job_search_plugin_enqueue_styles() {
    wp_enqueue_style( 'job-search-style', plugin_dir_url( __FILE__ ) . 'style.css' );
}
add_action( 'wp_enqueue_scripts', 'job_search_plugin_enqueue_styles' );

// Shortcode for Job Search Form
function job_search_form_shortcode() {
    ob_start(); ?>
    <form method="get" id="job-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="job-search-form">
        <label for="category">Category:</label>
        <select name="category" id="category">
            <option value="">Select Category</option>
            <?php
                $categories = get_categories();
                foreach($categories as $category) {
                    $selected = (get_query_var('category_name') == $category->slug) ? 'selected' : '';
                    echo '<option value="' . $category->slug . '" ' . $selected . '>' . $category->name . '</option>';
                }
            ?>
        </select>

        <label for="search">Search:</label>
        <input type="text" name="s" id="search" placeholder="Search jobs..." value="<?php echo get_search_query(); ?>" />

        <label for="location">Location:</label>
        <select name="location" id="location">
            <option value="">Select Location</option>
            <option value="nairobi">Nairobi</option>
            <option value="bungoma">Bungoma</option>
            <option value="eldoret">Eldoret</option>
            <option value="nakuru">Nakuru</option>
            <option value="thika">Thika</option>
            <option value="all-other-locations">All Other Locations</option>
        </select>

        <label for="job_type">Job Type:</label>
        <select name="job_type" id="job_type">
            <option value="">Select Job Type</option>
            <option value="contract">Contract</option>
            <option value="internship">Internship</option>
            <option value="remote">Remote</option>
            <option value="hybrid">Hybrid</option>
            <option value="full-time">Full Time</option>
            <option value="volunteer">Volunteer</option>
        </select>

        <button type="submit">Search</button>
        <button type="reset" class="clear-filters">Clear Filters</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode( 'job_search_form', 'job_search_form_shortcode' );

// Apply filters to the query
function job_search_filter_query($query) {
    if ( !is_admin() && $query->is_main_query() && (is_search() || is_archive()) ) {
        // Category filter
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $query->set('category_name', sanitize_text_field($_GET['category']));
        }

        // Location filter (using tags or custom taxonomy for location)
        if (isset($_GET['location']) && !empty($_GET['location'])) {
            $query->set('tag', sanitize_text_field($_GET['location']));
        }

        // Job Type filter (using tags or custom taxonomy for job type)
        if (isset($_GET['job_type']) && !empty($_GET['job_type'])) {
            $query->set('tag', sanitize_text_field($_GET['job_type']));
        }
    }
}
add_action('pre_get_posts', 'job_search_filter_query');

// Archive Title Shortcode
function custom_archive_title_shortcode() {
    $title = '';
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    $search = get_search_query();
    $location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
    $job_type = isset($_GET['job_type']) ? sanitize_text_field($_GET['job_type']) : '';

    // Construct the title
    if ($category || $search || $location || $job_type) {
        if ($category) {
            $title .= ucfirst($category) . ' jobs';
        }
        if ($search) {
            $title .= ($title ? ' for ' : '') . '"' . $search . '"';
        }
        if ($location) {
            $title .= ($title ? ' in ' : '') . ucfirst($location);
        }
        if ($job_type) {
            $title .= ($title ? ' with job type ' : '') . ucfirst($job_type);
        }
    } else {
        $title = 'Search results';
    }

    return '<h1 class="custom-archive-title">' . esc_html($title) . '</h1>';
}
add_shortcode( 'custom_archive_title', 'custom_archive_title_shortcode' );
