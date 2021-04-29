<?php
/*
Plugin Name: Quiz Master
Description: Модуль онлайн тестирования персонала
Version: 1.1.0
Author: Андрей Намашко
*/

register_activation_hook(__FILE__, 'jal_install');

function jal_install () {
    global $wpdb;

    $table_name = $wpdb->prefix . "qm_categories";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name text NOT NULL,
            UNIQUE KEY id (id)
          );";
      
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "qm_questions";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
	        category mediumint(9) DEFAULT '0' NOT NULL,
	        text text NOT NULL,
	        UNIQUE KEY id (id)
          );";
      
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "qm_tests";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) DEFAULT '0' NOT NULL,
            test_name text NOT NULL,
            test_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY id (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "qm_results";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
            test_id mediumint(9) DEFAULT '0' NOT NULL,
            question_id mediumint(9) DEFAULT '0' NOT NULL,
            answer text
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "qm_schemes_t";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
            test_id text NOT NULL,
            test_name text NOT NULL,
            duration mediumint(9) DEFAULT '60' NOT NULL
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "qm_schemes_q";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
            test_id text NOT NULL,
            category_list text NOT NULL,
            question_count mediumint(9) DEFAULT '0' NOT NULL
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_style( 'quiz-master-admin', plugin_dir_url( __FILE__ ) . 'css/quiz-master-admin.css' );
});

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'quiz-master-main', plugin_dir_url( __FILE__ ) . 'css/quiz-master-main.css' );
    wp_enqueue_script( 'quiz-master-main', plugin_dir_url( __FILE__ ) . 'js/quiz-master-main.js', '', '', true );
});

include('quiz-master-admin.php');
include('quiz-master-main.php');

?>