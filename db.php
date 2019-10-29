<?php

$error = false;

/**
 * connection to BD
 * @return mysqli
 */
function connectToDb()
{
	$host = 'localhost';
	$user = 'root';
	$password = 'root';
	$db = 'test_locales';
	$port = 8888;

	$mysqli = new mysqli($host, $user, $password, $db, $port);

	if($mysqli->connect_errno) {
		showDieMsg("Database connect error ", $mysqli->connect_error);
	}

	return $mysqli;
}

/*********************************
 **** Fill the Table from DB: ****
 *********************************/

if (isset($_POST['action']) && $_POST['action'] === 'fill'){
	$mysqli = connectToDb();
	try {
		$sql = "SELECT * FROM `locales` ORDER BY `lang_id`";
		$query_select = $mysqli->query($sql);
		throw new Exception($mysqli->error, 1);
	} catch (Exception $e) {
		$error = $e->getMessage();
	}

	if (!$error) {
		echo
		'
		<form method="POST">
		<table class="table table-striped table-bordered">
			<tr>
				<th>Lang</th>
				<th>Shortcut</th>
				<th>Lang Id</th>
				<th colspan="2">Actions</th>
			</tr>';

		while ($row = $query_select->fetch_object()) {
			$id = $row->id;
			$lang = htmlspecialchars($row->lang);
			$lang_shortcut = htmlspecialchars($row->lang_shortcut);
			$lang_id = htmlspecialchars($row->lang_id);

			echo
				'<tr>
					<td>'.$lang.'</td>
					<td>'.$lang_shortcut.'</td>	
					<td>'.$lang_id.'</td>	
					<td><a href="?edit='.$id.'" class="edit">Edit</a></td>
					<td><a href="?del='.$id.'" class="del">Delete</a></td>
				</tr>';

			unset($id, $lang, $lang_shortcut, $lang_id);
		}
		echo
				'<tr class="info">
					<td><input type="text" class="form-control" name="lang" placeholder="Language"></td>
					<td><input type="text" class="form-control" name="lang_shortcut" placeholder="Shortcut"></td>
					<td><input type="text" class="form-control" name="lang_id"	placeholder="?L="></td>
					<td colspan="2">
						<input type="hidden" name="action" value="addnew">
						<a href="#" class="addnew btn btn-sm btn-info btn-block">Add new</a>
	                </td>
				</tr>
			</table>
		</form>';
	}
}

/*********************************
 * Delete selected lang from DB: *
 *********************************/

$deletedLangId = !empty($_GET['del']) ? $_GET['del'] : false;

if (!empty($deletedLangId)) {
	$mysqli = connectToDb();
	$sql = "DELETE FROM `locales` WHERE `id`='$deletedLangId'";
	$query_delete = $mysqli->query($sql);
	//sleep(1);
	header('Location: ./');
	exit();
}

/**********************************
 ********** Edit lang: ************
 **********************************/

$editedLangId = !empty($_GET['edit']) ? $_GET['edit'] : false;

if (!empty($editedLangId)) {
	$mysqli = connectToDb();

	$sql = "SELECT * FROM `locales` WHERE `id` = '$editedLangId'";
	$query_edit = $mysqli->query($sql);

	while ($row = $query_edit->fetch_object()) {
		$lang = $row->lang;
		$lang_id = $row->lang_id;
		$lang_shortcut = $row->lang_shortcut;
	}
	echo '
		<td class="edited_lang"><input type="text" class="form-control" name="lang" value="'.$lang.'"></td>
		<td class="edited_lang_shortcut"><input type="text" class="form-control" name="lang_shortcut" value="'.$lang_shortcut.'"></td>
		<td class="edited_lang_id"><input type="text" class="form-control" name="lang_id" value="'.$lang_id.'"></td>
		<td><button type="button" name="save" data-id="'.$editedLangId.'" class="save btn btn-sm btn-success btn-block">Save</button></td>
		<td><button type="reset" name="cancel" class="cancel btn btn-sm btn-danger btn-block">Cancel</button></td>
		';
}


/**********************************
 ********* Save lang: ***********
 **********************************/

if(isset($_GET['save'])){
	$mysqli = connectToDb();

	$error = false;
	$lang = htmlspecialchars(ucwords(strtolower(trim($_GET['lang']))));
	$lang_id = htmlspecialchars(trim($_GET['lang_id']));
	$lang_shortcut = htmlspecialchars(strtoupper($_GET['lang_shortcut']));
	$id = $_GET['id'];

	if (strlen($lang) === 0 || is_numeric($lang)) { $error .= '<li>Enter the Language name!</li>'; }
	if (strlen($lang_id) === 0 || !(is_numeric($lang_id))) {$error .= '<li>Enter the Language numeruc ID!</li>';}
	if (strlen($lang_shortcut) !== 2 || is_numeric($lang_shortcut)) {$error .= '<li>Enter the Language 2-letter shortcut!</li>';}

	if ($error) {
		echo '<h4 class="text-danger">Fix the errors:</h4><ul class="text-danger">'.$error."</ul>";
	} else {
		$sql = "UPDATE `locales` SET `lang`='$lang', `lang_id`='$lang_id', `lang_shortcut`='$lang_shortcut' WHERE `id`='$id' ";
		$mysqli->query($sql);
	}
	unset($id, $lang, $lang_id, $lang_shortcut);
}


/**********************************
 ****** Add new lang to DB: *******
 **********************************/

if (isset($_POST['action']) && $_POST['action'] === 'addnew') {
	$lang = htmlspecialchars(ucwords(strtolower(trim($_POST['lang']))));
	$lang_id = htmlspecialchars(trim($_POST['lang_id']));
	$lang_shortcut = htmlspecialchars(strtoupper($_POST['lang_shortcut']));

	if (strlen($lang) === 0 || is_numeric($lang)) { $error .= '<li>Enter the Language name!</li>'; }
	if (strlen($lang_id) === 0 || !(is_numeric($lang_id))) {$error .= '<li>Enter the Language numeruc ID!</li>';}
	if (strlen($lang_shortcut) !== 2 || is_numeric($lang_shortcut)) {$error .= '<li>Enter the Language 2-letter shortcut!</li>';}

	$mysqli = connectToDb();

	$sql_check_uniq_lang = "SELECT * FROM `locales` WHERE `lang` LIKE '$lang'";
	$sql_check_uniq_lang_id = "SELECT * FROM `locales` WHERE `lang_id` LIKE '$lang_id'";
	$sql_check_uniq_lang_shortcut = "SELECT * FROM `locales` WHERE `lang_shortcut` LIKE '$lang_shortcut'";

	$query_check_uniq_lang = $mysqli->query($sql_check_uniq_lang);
	$query_check_uniq_lang_id = $mysqli->query($sql_check_uniq_lang_id);
	$query_check_uniq_lang_shortcut = $mysqli->query($sql_check_uniq_lang_shortcut);

	if ($query_check_uniq_lang->num_rows > 0) {
		$error .= "<li>Language is not unique!</li>";
	}
	if ($query_check_uniq_lang_id->num_rows > 0) {
		$error .= "<li>Language ID is not unique!</li>";
	}
	if ($query_check_uniq_lang_shortcut->num_rows > 0) {
		$error .= "<li>Language Shortcut is not unique!</li>";
	}

	if ($error) {
		echo '<h4 class="text-danger">Fix the errors:</h4><ul class="text-danger">'.$error."</ul>";
	} else {
		$sql = "INSERT INTO `locales` (`lang`, `lang_id`, `lang_shortcut`) VALUES ('$lang', '$lang_id', '$lang_shortcut')";
		$query_insert = $mysqli->query($sql);
	}
}