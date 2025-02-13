<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function safe_json_encode($value){
if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
    $encoded = json_encode($value, JSON_PRETTY_PRINT);
} else {
    $encoded = json_encode($value);
}
switch (json_last_error()) {
    case JSON_ERROR_NONE:
        return $encoded;
    case JSON_ERROR_DEPTH:
        return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
    case JSON_ERROR_STATE_MISMATCH:
        return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
    case JSON_ERROR_CTRL_CHAR:
        return 'Unexpected control character found';
    case JSON_ERROR_SYNTAX:
        return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
    case JSON_ERROR_UTF8:
        $clean = utf8ize($value);
        return safe_json_encode($clean);
    default:
        return 'Unknown error'; // or trigger_error() or throw new 
Exception();
}
}


function utf8ize($mixed) {
if (is_array($mixed)) {
    foreach ($mixed as $key => $value) {
        $mixed[$key] = utf8ize($value);
    }
} else if (is_string ($mixed)) {
    return utf8_encode($mixed);
}
return $mixed;
}

function send_data($data, $type, $code, $cache=False){
	switch($type){
		case 'json':
			header('Content-Type: application/json; charset=utf-8');
			break;
		case 'html':
			header('Content-Type: text/html; charset=utf-8');
			break;
		default:
			header('Content-Type: text/plain; charset=utf-8');
	}
	if(!$cache){
		header('Cache-control: no-store, no-cache, must-revalidate');
		header('Pragma: no-cache');
	}
	header('HTTP/1.1 '.$code.' OK');
	if($type === 'json'){
		$t = json_encode(utf8ize($data)); // Wrong-encoding: https://stackoverflow.com/a/46305914
		if(json_last_error_msg()=="Malformed UTF-8 characters, possibly incorrectly encoded" ) {
			$t = json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR );
		}
		if($t !== false){
			echo($t);
		}else{
		    die("json_encode fail: " . json_last_error_msg());
		}
	}else{
		echo $data;
	}
}
