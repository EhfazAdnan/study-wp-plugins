<?php
/*
 * Plugin Name: Adnan Slider
 * Plugin URI: https://www.google.com
 * Description: This is a test plugin
 * Version: 1.0
 */

function adnan_slider_custom_post(){
    $labels = [
        'name' => 'Adnan Slider',
    ];

    $args = [
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ];

    register_post_type('adnan-slider', $args);
}

// add custom post type
add_action('init', 'adnan_slider_custom_post');

/**
 * Activate the plugin (create required table).
 */
function adnan_slider_activate() {
    adnan_slider_custom_post();
    create_tables();
    flush_rewrite_rules();
}

// register activation hook
register_activation_hook( __FILE__, 'adnan_slider_activate');

/**
 * create table function
 * @return void
 */
function create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $test_table = $wpdb->prefix . 'adnanslider';

    // adnanslider Table Created
    $sql = "CREATE TABLE $test_table (
          `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `object_id` bigint(20) NOT NULL DEFAULT 0,
          `object_type` varchar(60) NOT NULL DEFAULT 'post',
          `cat_id` int(11) NOT NULL DEFAULT 0,
          `user_id` bigint(20) NOT NULL DEFAULT 0,
          `created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `modyfied_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`id`)) $charset_collate;";

    require_once( ABSPATH . "wp-admin/includes/upgrade.php");
    dbDelta($sql);
}

/**
 * Deactivation hook (drop plugin tables).
 */
function adnan_slider_deactivate() {
    unregister_post_type( 'adnan-slider');
    delete_table();
    flush_rewrite_rules();
}

// register deactivation hook
register_deactivation_hook( __FILE__, 'adnan_slider_deactivate' );

/**
 * drop table function
 * @return void
 */
function delete_table(){
    global $wpdb;
    $test_table = $wpdb->prefix . 'adnanslider';

    $table_names                   = [];
    $table_names['Test Table']     = $test_table;

    $sql          = "DROP TABLE IF EXISTS " . implode( ', ', array_values( $table_names ) );
    $query_result = $wpdb->query( $sql );
}
?>

<?php

// add action example for javascript
function hook_javascript() {
    ?>
        <script>
            console.log('hook javascript runs perfectly');
        </script>
    <?php
}
add_action('wp_head', 'hook_javascript');

// add action example
function test_hook(){
    echo 'test hook';
}
add_action('wp_head', 'test_hook');


/*
 * WordPress shortcode example with adnan-slide
 */
function adnan_slider_shortcode(){
   $args = [
       'post_type'     => 'adnan-slider',
       'posts_per_page' => -1
   ];
   $query = new WP_Query($args);

   $html = '<div class="adnan-slider">';

      while($query->have_posts()) : $query->the_post();
         $html .= '<h2>'.get_the_title().'</h2>';
      endwhile; wp_reset_query();

   $html .= '</div>';

   return $html;
}
add_shortcode('adnan-slider', 'adnan_slider_shortcode');


function google_link(){
    return '<div>Search on <a href="https://google.com/">GooGle</a></div>';
}
add_shortcode('google-button', 'google_link');

function adnan_slider_plugin_assets(){
    $plugin_dir_url = plugin_dir_url(__FILE__);

    wp_enqueue_style('adnan-slider-slick', $plugin_dir_url.'assets/css/slick.css');
    wp_enqueue_script('adnan-slider-slick', $plugin_dir_url.'assets/js/slick.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('adnan-slider-main', $plugin_dir_url.'assets/js/index.js');
}

add_action('wp_enqueue_scripts', 'adnan_slider_plugin_assets');

?>

