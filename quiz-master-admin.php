<?php

function str_contains(string $haystack, string $needle): bool {
    return '' === $needle || false !== strpos($haystack, $needle);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

	if (!empty($_POST['save_tests_button'])) {
		save_tests_func();
	}
	elseif (!empty($_POST['schemes_t_add_button'])) {
		$data = array(
			'test_id' => $_POST['schemes_t_add_id'],
			'test_name' => $_POST['schemes_t_add_name'],
			'duration' => $_POST['schemes_t_add_duration'],
		);
		$wpdb->insert($wpdb->prefix . "qm_schemes_t", $data);
	}
	elseif (!empty($_POST['schemes_q_add_button'])) {
		$data = array(
			'test_id' => $_POST['schemes_q_add_id'],
			'category_list' => $_POST['schemes_q_add_category_list'],
			'question_count' => $_POST['schemes_q_add_question_count'],
		);
		$wpdb->insert($wpdb->prefix . "qm_schemes_q", $data);
	}
	elseif (!empty($_POST['question_add_button'])) {
		$data = array(
			'category' => $_POST['question_add_category'],
			'text' => $_POST['question_add_text'],
		);
		$wpdb->insert($wpdb->prefix . "qm_questions", $data);
	}
	elseif (!empty($_POST['category_add_button'])) {
		$data = array(
			'name' => $_POST['category_add_name'],
		);
		$wpdb->insert($wpdb->prefix . "qm_categories", $data);
	}
	elseif (!empty($_POST['schemes_t_del_button'])) {
		$wpdb->query( "delete from " . $wpdb->prefix . "qm_schemes_t where id = " . $_POST['schemes_t_del_id'] );
		$wpdb->query( "delete from " . $wpdb->prefix . "qm_schemes_q where id = " . $_POST['schemes_t_del_id'] );
	}
	elseif (!empty($_POST['question_del_button'])) {
		$wpdb->query( "delete from " . $wpdb->prefix . "qm_questions where id = " . $_POST['question_del_id'] );
	}
	elseif (!empty($_POST['category_del_button'])) {
		$wpdb->query( "delete from " . $wpdb->prefix . "qm_categories where id = " . $_POST['category_del_id'] );
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

add_action('admin_menu', 'quiz_master_menu' );
function quiz_master_menu() {

    add_menu_page(
		'Результаты тестирования персонала',
		'Тестирование',
		'publish_pages',
		'site-options',
		'add_main_settings',
		plugins_url( 'quiz-master/images/icon.png' )
	);

	add_submenu_page(
		'site-options',
		'Настройки: Категории тестирования персонала',
		'Категории',
		'publish_pages',
		'site-options-categories',
		'add_category_settings'
	);

	add_submenu_page(
		'site-options',
		'Настройки: Вопросы для тестирования персонала',
		'Вопросы',
		'publish_pages',
		'site-options-questions',
		'add_question_settings'
	);

	add_submenu_page( 
		'site-options', 
		'Настройки: Схемы тестирования персонала', 
		'Тесты', 
		'publish_pages', 
		'site-options-tests', 
		'add_tests_settings'
	);

}

function add_main_settings() {
	global $wpdb;
	?>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>

		<div>

		<form style="display: none;" id="save_tests" method="POST">
			<fieldset class="qm-qstyle">
				<legend>Выгрузка тестов</legend>					
				<p>
				<input id="save_tests_button" name="save_tests_button" class="button" type="submit" value="Выгрузить тесты">
				</p>
			</fieldset>			
		</form>

		<form id="save_test" method="POST">
		<table class="widefat fixed striped">
		<thead>
		<tr>
		<th>№</th>
		<th>ФИО</th>
		<th>Магазин</th>
		<th>Имя теста</th>
		<th>Дата</th>
		<th>#</th>
		</tr>
		</thead>
		<tbody>		
		<?php
			$tests = $wpdb->get_results( "SELECT id, user_id, test_name, test_date, (select meta_value from " . $wpdb->prefix . "usermeta wm where wm.user_id = wt.user_id and meta_key = 'shop') shop, (select display_name from " . $wpdb->prefix . "users where " . $wpdb->prefix . "users.ID = wt.user_id) display_name from " . $wpdb->prefix . "qm_tests wt where wt.test_end is not null and (select count(*) from wp_qm_results r where wt.id = r.test_id and length(answer) > 0) > 0 order by test_date desc" );
			foreach ($tests as $test) {
				echo '<tr><td>' . $test->id . '</td><td>' . $test->display_name . '</td><td>' . $test->shop . '</td><td>' . $test->test_name . '</td><td>' . $test->test_date . '</td><td><input id="save_test_' . $test->id . '" name="save_test_' . $test->id . '" class="button" type="submit" value="Сохранить"></td></tr>';
			}
		?>
		</tbody>
		</table>
		</form>

		</div>
	</div>
	<?php

}

function add_category_settings() {
	global $wpdb;
	?>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>

		<div>

        <form id="category_add" method="POST">
			<p>
				<input id="category_add_name" name="category_add_name" type="text" placeholder="Наименование категрии" class="qm-w400">
				<input id="category_add_button" name="category_add_button" class="button" type="submit" value="Добавить категорию">
			</p>
		</form>
		
		<table class="widefat fixed striped">
		<thead>
        <tr>
        <th style="width: 10%;">№</th>
        <th style="width: 90%;">Название категории</th>
        </tr>
		</thead>
		<tbody>
        <?php 
            $categories = $wpdb->get_results( "SELECT id, name FROM " . $wpdb->prefix . "qm_categories" );
            foreach ($categories as $category) {
                echo '<tr><td>' . $category->id . '</td><td>' . $category->name . '</td></tr>';
            }
        ?>
		</tbody>
        </table>

		<form id="category_del" method="POST">
			<p>
				<input id="category_del_id" name="category_del_id" type="number" placeholder="№ категории">
				<input id="category_del_button" name="category_del_button" class="button" type="submit" value="Удалить">
			</p>
		</form>

		</div>		
	</div>
	<?php
}

function add_question_settings() {
	global $wpdb;
	$categories = $wpdb->get_results( "SELECT id, name FROM " . $wpdb->prefix . "qm_categories" );
	?>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>

		<div>

		<form id="question_add" method="POST">			
				<fieldset class="qm-qstyle">
					<legend>Добавление вопроса</legend>
					<p>
					<select id="question_add_category" name="question_add_category" class="qm-w400 qm-dblock">
						<?php
						foreach ($categories as $category) {
                			echo '<option value="' . $category->id . '">' . $category->name . '</option>';
            			}
						?>
					</select>
					</p>
					<p>
					<textarea name="question_add_text" id="question_add_text" rows="5" placeholder="Текст вопроса" class="qm-dblock qm-w100"></textarea>
					</p>
					<p>
					<input id="question_add_button" name="question_add_button" class="button" type="submit" value="Добавить вопрос">
					</p>
				</fieldset>			
		</form>

		<form id="question_filter" method="post">
			<p>
				<select id="question_filter_category" name="question_filter_category" class="qm-w400">
					<?php
					foreach ($categories as $category) {
                		echo '<option value="' . $category->id . '">' . $category->name . '</option>';
            		}
					?>
				</select>
				<input id="question_filter_button" name="question_filter_button" class="button" type="submit" value="Фильтровать">
			</p>
		</form>

		<table class="widefat fixed striped">
		<thead>
		<tr>
		<th style="width: 10%;">№</th>
		<th style="width: 35%;">Категория</th>
		<th style="width: 55%;">Вопрос</th>
		</tr>
		</thead>
		<tbody>
		<?php 
			if ($_SERVER['REQUEST_METHOD'] == "POST") {
				if (!empty($_POST['question_filter_button'])) {
					$filter = ' where category = ' . $_POST['question_filter_category'];
				}
			}

            $questions = $wpdb->get_results( "select id, (select name from " . $wpdb->prefix . "qm_categories where " . $wpdb->prefix . "qm_categories.id = category) category, text from " . $wpdb->prefix . "qm_questions" . $filter );
            foreach ($questions as $question) {
                echo '<tr><td>' . $question->id . '</td><td>' . $question->category . '</td><td>' . $question->text . '</td></tr>';
            }
        ?>
		</tbody>
		</table>

		<form id="question_del" method="POST">
			<p>
				<input id="question_del_id" name="question_del_id" type="number" placeholder="№ вопроса">
				<input id="question_del_button" name="question_del_button" class="button" type="submit" value="Удалить">
			</p>
		</form>

		</div>
	</div>
	<?php
}

function add_tests_settings() {
	global $wpdb;
	?>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>

		<div>

        <form id="schemes_t_add" method="POST">
			<p>
				<input id="schemes_t_add_id" name="schemes_t_add_id" type="text" placeholder="ID теста" class="qm-w400">
				<input id="schemes_t_add_name" name="schemes_t_add_name" type="text" placeholder="Имя теста" class="qm-w400">
				<input type="number" name="schemes_t_add_duration" id="schemes_t_add_duration" placeholder="Длительность (мин)">
				<input id="schemes_t_add_button" name="schemes_t_add_button" class="button" type="submit" value="Добавить тест">
			</p>
		</form>
		
		<table class="widefat fixed striped">
		<thead>
        <tr>
        <th style="width: 30%;">ID теста</th>
		<th style="width: 40%;">Имя теста</th>
		<th style="width: 30%;">Длительность (мин)</th>
        </tr>
		</thead>
		<tbody>
        <?php 
            $schemes_t = $wpdb->get_results( "SELECT test_id, test_name, duration FROM " . $wpdb->prefix . "qm_schemes_t" );
            foreach ($schemes_t as $scheme_t) {
                echo '<tr><td>' . $scheme_t->test_id . '</td><td>' . $scheme_t->test_name . '</td><td>' . $scheme_t->duration . '</td></tr>';
            }
        ?>
		</tbody>
        </table>

		<form id="schemes_t_del" method="POST">
			<p>
				<input id="schemes_t_del_id" name="schemes_t_del_id" type="text" placeholder="ID теста">
				<input id="schemes_t_del_button" name="schemes_t_del_button" class="button" type="submit" value="Удалить">
			</p>
		</form>

		</div>

		<div>

        <form id="schemes_q_add" method="POST">
			<p>
				<input id="schemes_q_add_id" name="schemes_q_add_id" type="text" placeholder="ID теста" class="qm-w400">
				<input id="schemes_q_add_category_list" name="schemes_q_add_category_list" type="text" placeholder="Список категорий" class="qm-w400">
				<input type="number" name="schemes_q_add_question_count" id="schemes_q_add_question_count" placeholder="Кол-во вопросов">
				<input id="schemes_q_add_button" name="schemes_q_add_button" class="button" type="submit" value="Добавить тест">
			</p>
		</form>
		
		<table class="widefat fixed striped">
		<thead>
        <tr>
        <th style="width: 30%;">ID Теста</th>
		<th style="width: 50%;">Список категорий</th>
		<th style="width: 20%;">Кол-во вопросов</th>
        </tr>
		</thead>
		<tbody>
        <?php 
            $schemes_q = $wpdb->get_results( "SELECT test_id, if(length(category_list) > 3, category_list,(select name from " . $wpdb->prefix . "qm_categories where id = category_list)) category_list, question_count FROM " . $wpdb->prefix . "qm_schemes_q" );
            foreach ($schemes_q as $scheme_q) {
                echo '<tr><td>' . $scheme_q->test_id . '</td><td>' . $scheme_q->category_list . '</td><td>' . $scheme_q->question_count . '</td></tr>';
            }
        ?>
		</tbody>
        </table>

		</div>
	</div>
	<?php
}

function save_tests_func() {
	?>
	<div>Тесты выгружены</div>
<?php
}

function save_test_func($test_id) {
	global $wpdb;

	$results = $wpdb->get_results('select (select qq.text from ' . $wpdb->prefix . 'qm_questions qq where qq.id = qr.question_id) question, qr.answer from ' . $wpdb->prefix . 'qm_results qr where qr.test_id = ' . $test_id);
	$userinfo = $wpdb->get_results("select display_name, user_email, (select meta_value from " . $wpdb->prefix . "usermeta where user_id = " . $wpdb->prefix . "users.ID and meta_key = 'shop') shop from " . $wpdb->prefix . "users where ID = (select user_id from " . $wpdb->prefix . "qm_tests where id = " . $test_id . ")");
	$test_name = $wpdb->get_results('select test_name from ' . $wpdb->prefix . 'qm_tests where id = ' . $test_id);

	require_once('PHPWord/autoload.php');
	$phpWord = new \PhpOffice\PhpWord\PhpWord();
	$section = $phpWord->addSection();

	$section->addText('Тест №' . $test_id . '. ' . $userinfo[0]->display_name . ' (email: ' . $userinfo[0]->user_email . ', магазин: ' . $userinfo[0]->shop . ', ' . $test_name[0]->test_name . ')', array('name' => 'Arial', 'size' => 12));

	for($i = 0; $i < count($results); ++$i) {
		$section->addText(($i + 1) . '. ' . $results[$i]->question, array('name' => 'Arial', 'size' => 14, 'bold' => true));
		$section->addText($results[$i]->answer, array('name' => 'Arial', 'size' => 12));
	}

	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment;filename="' . $test_id . '. ' . $userinfo[0]->display_name . ' (email - ' . $userinfo[0]->user_email . ', магазин - ' . $userinfo[0]->shop . ', ' . $test_name[0]->test_name . ')' . '.docx"');

	$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
	$objWriter->save('php://output');
}

?>