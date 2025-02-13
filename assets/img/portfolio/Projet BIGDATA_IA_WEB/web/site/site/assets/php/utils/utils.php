<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


function var_dump_ret($mixed = null){ // https://www.php.net/manual/fr/function.var-dump.php#51119
	ob_start();
	var_dump($mixed);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}
function var_dump_pre($mixed = null) {
	echo '<pre>';
	var_dump($mixed);
	echo '</pre>';
	return null;
}

function default_values($value, $default){
	if(isset($value) && !empty($value)){
		return $value;
	}else{
		return $default;
	}
}


// --- Check Values --- \\
function checkValue($value, $condition){
	if($condition[0] == 'date'){
		return checkValue($value, array('', $condition[1])) || $value <= date('Y-m-d H:i:s');
	}else if($condition[0] == 'int'){
		return checkValue($value, array('', $condition[1])) || is_numeric($value);
	}else if($condition[0] == 'mail'){
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}else if($condition[1] == 'null'){
		return true;
	}else{
		return !empty($value);
	}
}
function checkValues($arr){
	foreach($arr as $key => $val){
		if(!checkValue($key, $val)) return false;
	}

	return true;
}
// --- Check Values --- \\


if(!function_exists('str_contains')){ // https://www.php.net/manual/en/function.str-contains.php#125977
	function str_contains($haystack, $needle){
		return $needle !== '' && mb_strpos($haystack, $needle) !== false;
	}
}

// --- TOKEN --- \\
function get_token($db, $mail){
	return $db->query(
		'SELECT * FROM tokens WHERE mail = :mail ORDER BY creation DESC;',
		['mail' => $mail]
	);
}

function check_token($db, $token, $mail){
	$token_expire = "+7 days";
	$token_db = get_token($db, $mail);
	if($token !== $token_db['token']) return false;
	$creation = new DateTime($token_db['creation']);
	 $creation->modify($token_expire);
	$now = new DateTime();
	if($creation < $now) return false;
	return true;
}
// --- TOKEN --- \\
