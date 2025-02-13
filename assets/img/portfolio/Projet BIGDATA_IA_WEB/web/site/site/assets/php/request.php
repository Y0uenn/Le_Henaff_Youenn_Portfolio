<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('utils/utils.php');
require_once('utils/sendData.php');


spl_autoload_register(function($class){
	$file = 'class/'.str_replace("\\", DIRECTORY_SEPARATOR, $class).'.php';
	if(!file_exists($file)){
		return false;
	}else{
		require $file;
		return true;
	}
});

try{
	$db = new Database();
	$tables = $db->query('SHOW TABLES;', null, null, true);
	$DB_ = array();
	foreach($tables as $ta){
		$cols = $db->query('SHOW COLUMNS FROM '.$ta[0], null, null, true);
		$DB_[$ta[0]] = array();
		foreach($cols as $col){
			$DB_[$ta[0]][$col[0]] = array();
			for($i = 1; $i < count($col) / 2; $i++){
				$DB_[$ta[0]][$col[0]][] = $col[$i];
			}
		}
	}
	define('DB_TABLES', $DB_);
}catch(PDOException $e){
	header('HTTP/1.1 503 Service Unavailable');
	var_dump_pre($e);
	exit;
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
$request = substr($_SERVER['PATH_INFO'], 1);
$request = explode('/', $request);
$requestRessource = array_shift($request);

parse_str(file_get_contents('php://input'), $_PUT);

$data = null;
$id = array_shift($request);
if($id == '') $id = null;

function getVal($valName){ // https://www.php.net/manual/en/language.variables.scope.php
	$val = null;
	global $_PUT;
	if(isset($_GET[$valName])){
		$val = $_GET[$valName];
	}else if(isset($_POST[$valName])){
		$val = $_POST[$valName];
	}else if(isset($_PUT[$valName])){
		$val = $_PUT[$valName];
	}

	return $val;
}
$wantedNames = array('type', 'model', 'mail', 'nom', 'mdp', 'conf_m', 'token', 'start', 'amount', 'sort', 'ordre', 'filtre', 'nb_clust'); // Variables Names
$arbre_fields = $db->getOnlyFields($db->getFields('arbre'));
$wantedNames = array_merge($wantedNames, $arbre_fields);
$wanted = array();
foreach($wantedNames as $name){
	$wanted[$name] = getVal($name);
}
$headers = apache_request_headers();
if(isset($headers['Authorization_'])){
	if(preg_match('/Bearer (.*)/', $headers['Authorization_'], $tab)){
		$wanted['token'] = str_replace('"', '', str_replace('\\', '', $tab[1]));
		$wanted['mail'] = $db->query('SELECT mail FROM `tokens` WHERE token = :token ORDER BY creation DESC;', ['token'=>$wanted['token']])[0];
	}else if(preg_match('/Basic (.*)/', $headers['Authorization_'], $tab)){
		$t = explode(':', $tab[1]);
		$wanted['mail'] = $t[0];
		$wanted['mdp'] = $t[1];
	}
}else{
	$wanted['token'] = null;
}

$send_res = true;
switch($requestMethod){
	case 'POST':
		switch($requestRessource){
			case 'bdd':
				if(!check_token($db, $wanted['token'], $wanted['mail'])){
					header('HTTP/1.1 403 Forbidden');
					exit;
				}
				$requestedTable = 'arbre';
				$values = array();
				foreach($arbre_fields as $name){
					if($name === 'id') continue;
					array_push($values, $wanted[$name]);
				}
				break;
			case 'account':
				$requestedTable = 'utilisateur';
				if($wanted['mdp'] !== $wanted['conf_m']){
					header('HTTP/1.1 400 BAD POST');
					exit;
				}
				$values = array($wanted['mail'], $wanted['nom'], $wanted['mdp']);
				break;
			default:
				header('HTTP/1.1 400 BAD POST');
				exit;
		}
		$r = $db->create($requestedTable, $values);
		if($r !== true){
			header('HTTP/1.1 400 '.$r);
			exit;
		}
		if($requestRessource == 'account'){
			$token = get_token($db, $wanted['mail'])['token'];
			$send_res = send_data($token, 'json', 200);
		}
		break;
	case 'GET':
		switch($requestRessource){
			case 'arbres':
				switch($id){
					case 'tableau':
						if($wanted['filtre'] === 'all'){
							$values = array();
						}else{
							foreach(explode('&', $wanted['filtre']) as $filtre){
								$f = explode('=', $filtre);
								$values[$f[0]] = $f[1];
							}
						}
						$arbres = $db->queryLimit('arbre', $values, $wanted['amount'], $wanted['sort'], $wanted['ordre']=='croissant', $wanted['start']);
						$send_res = send_data($arbres, 'json', 200, true);
						break;
					case 'cluster':
						$values = array(default_values($wanted['model'], 'kmeans'), default_values($wanted['nb_clust'], '2'));
						$shell_output = shell_exec("python3 ../python/predict_cluster.py ".$values[0]." ".$values[1]);
						$send_res = send_data($shell_output, 'html', 200);
						break;
					default:
						header('HTTP/1.1 400 BAD REQUEST');
						exit;// ERR
				}
				break;
			case 'predict':
				$values = array((int)$id, $wanted['type'], default_values($wanted['model'], 'Random_Forest'));
				$arbre = $db->getFromTable('arbre', $id)[0];
				if($values[1] === 'age'){
					$data = array(
						'haut_tot' => $arbre->haut_tot,
						'tronc_diam' => $arbre->tronc_diam,
						'fk_prec_estim' => $arbre->fk_prec_estim
					);
					$shell_output = shell_exec("python3 ../python/predict_age.py '".json_encode($data)."' ".$values[2]);
				}else if($values[1] === 'dera'){
					$shell_output = shell_exec("python3 ../python/predict_risque.py ".$values[0]." ".$values[2]);
					$shell_output = str_replace("'", '"', $shell_output);
				}else{
					header('HTTP/1.1 400 BAD REQUEST');
					exit;
				}
				$send_res = send_data($shell_output, 'json', 200);
				break;
			case 'account':
				if($wanted['mdp'] != null){
					if($db->query('SELECT SHA2(:mdp, 256)=(SELECT mdp FROM `utilisateur` WHERE mail = :mail);', ['mdp'=>$wanted['mdp'], 'mail'=>$wanted['mail']])[0]){
						$token = base64_encode(openssl_random_pseudo_bytes(12));
						$db->create('tokens', array($token, date('Y-m-d H:i:s'), $wanted['mail']));
						$send_res = send_data($token, 'json', 200);
					}
				}
				break;
			case 'entry':
				if(empty($wanted['id'])){
					$data = $db->getAll($id);
				}else{
					$fields = $db->getFields($id);
					foreach($fields as $key => $val){
						if($val[3]){
							$pk_name = $key;
							break;
						}
					}
					$data = $db->getFromTable($id, $wanted['id'], $pk_name);
				}
				$send_res = send_data($data, 'json', 200);
				break;
			case 'champs':
				$data = $db->getFields($id);
				$send_res = send_data($data, 'json', 200);
				break;
			default:
				header('HTTP/1.1 400 BAD GET');
				exit;
		}
		break;
	case 'PUT':
		switch($requestRessource){
			case 'bdd':
				if(!check_token($db, $wanted['token'], $wanted['mail'])){
					header('HTTP/1.1 403 Forbidden');
					exit;
				}
				$wanted['id'] = $id;
				$db->update('arbre', $wanted);
				break;
			case 'account':
				if(!check_token($db, $wanted['token'], $wanted['mail'])){
					header('HTTP/1.1 403 Forbidden');
					exit;
				}
				$values = array('mail'=>$wanted['mail'], 'nom'=>$wanted['nom']);
				$db->update('utilisateur', $values);
				break;
			default:
				header('HTTP/1.1 400 BAD PUT');
				exit;
		}
		break;
	case 'DELETE':
		switch($requestRessource){
			case 'bdd':
				if(!check_token($db, $wanted['token'], $wanted['mail'])){
					header('HTTP/1.1 403 Forbidden');
					exit;
				}
				$db->delete('arbre', array($id));
				break;
			case 'account':
				if(!check_token($db, $wanted['token'], $wanted['mail'])){
					header('HTTP/1.1 403 Forbidden');
					exit;
				}
				$db->delete('utilisateur', array($wanted['mail']));
				break;
			default:
				header('HTTP/1.1 400 BAD DELETE');
				exit;
		}
		break;
	default:
		header('HTTP/1.1 400 BAD REQUEST');
		exit;
}
if($send_res === false){
	header('HTTP/1.1 500 INTERNAL SERVER ERROR');
}
?>
