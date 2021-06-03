<?php

add_shortcode( 'quiztag', 'quiz_main_func' );

function quiz_main_func() {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (!empty($_POST['begin-button'])) {
            $out = quiz_test_func();
        }
        elseif (!empty($_POST['end-button'])) {
            $out = quiz_end_func();
        }
        elseif (!empty($_POST['mytests-button'])) {
            $out = quiz_mytests_func();
        }
        else {
            foreach ($_POST as $param_name => $param_val) {
                if (str_contains($param_name, 'save_test_')) {
                    $test_id = str_replace('save_test_', '', $param_name);
                    save_test_func($test_id);
                }
            }
        }
    }
    else {
        $out = quiz_select_func();
    }
    return $out;
}

function quiz_end_func() {
    global $wpdb;

    $test_id = $wpdb->get_results( "select max(id) id from " . $wpdb->prefix . "qm_tests where user_id = " . get_current_user_id() )[0]->id;
    $ct = $wpdb->get_results( "select current_timestamp ct" )[0]->ct;

    $wpdb->update( $wpdb->prefix . 'qm_tests',
	    [ 'test_end' => $ct ],
	    [ 'id' => $test_id ]
    );
    
    foreach ($_POST as $param_name => $param_val) {
        if ($param_name != 'end-button' && $param_name != 'testname') {
            $qid = str_replace('q', '', $param_name);

            $dresult = array (
                'test_id' => $test_id,
                'question_id' => $qid,
                'answer' => $param_val,
            );

            $wpdb->insert($wpdb->prefix . "qm_results", $dresult);
        }
    }

    $out = '<div>Тест завершен</div>';
    return $out;
}

function quiz_test_func() {
    global $wpdb;
    $questions = quiz_get_questions($_POST['selected-test']);
    $test_name = $wpdb->get_results( "select test_id, test_name, duration from " . $wpdb->prefix . "qm_schemes_t where test_id = '" . $_POST['selected-test'] . "'" );
    
    //Добавляю начатый тест в базу
    $dtest = array (
        'user_id' => get_current_user_id(),
        'test_name' => $test_name[0]->test_name,
    );
    $wpdb->insert($wpdb->prefix . "qm_tests", $dtest);

    $out = '<section><div class="qm-mb-1 qm-fw-b"><div class="qm-d-inline">' . $test_name[0]->test_name . '</div><div class="qm-d-inline qm-right qm-fs-smaller">Осталось времени: <span id="test-timer">' . $test_name[0]->duration . '</span> мин.</div></div><div>';
    for($i = 0; $i < count($questions); ++$i) {
        $out = $out . '<input id="qm-b' . $questions[$i]->id . '" type="button" value="' . ($i + 1) . '" class="qm-square">';
	}
        
    $out = $out . '</div><div><form onsubmit="return validateQuestionList()" method="post" id="quiz-test-form"><ul id="qm-qlist" class="qm-qlist">';

    foreach ($questions as $question) {
        $out = $out . '<li id="q' . $question->id . '" class="qm-collapsed"><p>' . $question->text . '</p><textarea name="q' . $question->id . '" rows="10"></textarea></li>';
    }

    $out = $out . '</ul>
        <div>
            <input id="qm-bprev" type="button" value="Назад">
            <input id="qm-bnext" type="button" value="Вперед">
        </div>
        <div>
            <input type="submit" id="end-button" name="end-button" value="Завершить тест">
        </div>
        </form></div></section>';

    return $out;
}

function quiz_get_questions($test_type) {
    global $wpdb;
    $test_scheme = $wpdb->get_results( "select test_id, category_list, question_count from " . $wpdb->prefix . "qm_schemes_q where test_id = '" . $test_type . "'" );

    for($i = 0; $i < count($test_scheme); ++$i) {
        if ($i > 0) {
            $question_array = array_merge($question_array, $wpdb->get_results( "select * from " . $wpdb->prefix . "qm_questions where category in (" . $test_scheme[$i]->category_list . ") order by rand() limit " . $test_scheme[$i]->question_count ));
        }
        else {
            $question_array = $wpdb->get_results( "select * from " . $wpdb->prefix . "qm_questions where category in (" . $test_scheme[$i]->category_list . ") order by rand() limit " . $test_scheme[$i]->question_count );
        }        
    }

    return $question_array;
}

function quiz_select_func() {
    global $wpdb;
    $test_list = $wpdb->get_results( "select test_id, test_name, duration from " . $wpdb->prefix . "qm_schemes_t" );
    $out = '<div><form id="select-form" method="post"><input class="qm-mytests" name="mytests-button" type="submit" value="Мои тесты">';    
    foreach ($test_list as $test_item) {
        $out = $out . '<p><input type="radio" name="selected-test" id="' . $test_item->test_id . '" value="' . $test_item->test_id . '"><label style="margin-left: .5rem;" for="' . $test_item->test_id . '">' . $test_item->test_name . '</label></p>';
    }    
    $out = $out . '<input name="begin-button" type="submit" value="Начать тест"></form></div>';
    return $out;
}

function quiz_mytests_func() {
    global $wpdb;
    
    $out = '<form id="save_test" method="POST">
    <table class="widefat fixed striped">
    <thead>
    <tr>
    <th>№</th>
    <th>Имя теста</th>
    <th>Дата</th>
    <th>#</th>
    </tr>
    </thead>
    <tbody>';
    
    $tests = $wpdb->get_results( "SELECT id, test_name, test_date from " . $wpdb->prefix . "qm_tests wt where wt.user_id = " . get_current_user_id() . " and wt.test_end is not null and (select count(*) from wp_qm_results r where wt.id = r.test_id and length(answer) > 0) > 0 order by test_date desc" );
    foreach ($tests as $test) {
        $out = $out . '<tr><td>' . $test->id . '</td><td>' . $test->test_name . '</td><td>' . $test->test_date . '</td><td><input id="save_test_' . $test->id . '" name="save_test_' . $test->id . '" class="button" type="submit" value="Сохранить"></td></tr>';
    }
    
    $out = $out . '</tbody></table></form>';
    return $out;
}

?>