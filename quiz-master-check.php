<?php

add_action( 'init', 'check_test_begin' );

function check_test_begin() {
    if (is_user_logged_in()) {

        global $wpdb;
        $last_test = $wpdb->get_results( "select * from " . $wpdb->prefix . "qm_tests where user_id = " . get_current_user_id() . " and test_end is null order by test_date desc limit 1" )[0];

        if ($last_test) {
            
            $current_url = home_url($_SERVER['REQUEST_URI']);

            if (strcmp($current_url, "http://ed.sheriff.md/check/") !== 0 && strcmp($current_url, "http://ed.sheriff.md/testy/") !== 0) {
                wp_redirect( "http://ed.sheriff.md/check/" );
            }
        }
    }
}

add_shortcode( 'quizcheck', 'quiz_check_func' );

function quiz_check_func() {
    global $wpdb;

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (!empty($_POST['test'])) {

            $test_id = $_POST['test'];

            $test_end = $wpdb->get_results( "select test_end, user_id from " . $wpdb->prefix . "qm_tests where id = " . $test_id );

            if (count($test_end) == 0 || !is_null($test_end[0]->test_end) || $test_end[0]->user_id != get_current_user_id()) {
                $out = '<div>Тест завершен!</div>';
            }
            else {
                $wpdb->query( "DELETE FROM " . $wpdb->prefix . "qm_tests WHERE id = " . $test_id );

                $out = '<div>Тест удален!</div>';
            }
        }
    }
    else {
        $last_test = $wpdb->get_results( "select * from " . $wpdb->prefix . "qm_tests where user_id = " . get_current_user_id() . " and test_end is null order by test_date desc limit 1" )[0];

        $out = '<section><form method="post">
        <h3>Идет тестирование!</h3>
        <p>Название: ' . $last_test->test_name . '</p>
        <p>Время начала: ' . $last_test->test_date . '</p>
        <p>Если вы больше не выполнаете тест нажмите <q>Удалить тест</q>, иначе вернитесь к выполнению теста.</p>
        <input style="display: none;" type="text" id="test" name="test" value="' . $last_test->id . '">
        <input type="submit" value="Удалить тест">
        </form></section>';
    }

    return $out;
}

?>