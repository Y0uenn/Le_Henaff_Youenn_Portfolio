<?php
class Database{
	private $db_name;
	private $db_user;
	private $db_pass;
	private $db_host;
	private $pdo;

	public function __construct($db_name = "etu0115", $db_user = "etu0115", $db_pass = "ygxmpljt", $db_host = "localhost"){
		$this->db_name = $db_name;
		$this->db_user = $db_user;
		$this->db_pass = $db_pass;
		$this->db_host = $db_host;
	}

	private function getPDO(){
		if($this->pdo === null){
			$pdo = new PDO("mysql:dbname=".$this->db_name.";host=".$this->db_host, $this->db_user, $this->db_pass);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo = $pdo;
		}
		return $this->pdo;
	}

	public function query($str, $to_execute = null, $class_name = null, $all = false, $fetch = true){
		$pdo = $this->getPDO();
		if($pdo === false) return null;
		$stmt = $pdo->prepare($str);
		if($to_execute === null){
			$stmt->execute();
		}else{
			$stmt->execute($to_execute);
		}
		if($fetch === false) return true;
		if($all === true) return $stmt->fetchAll();
		if($class_name === null) return $stmt->fetch();

		return $stmt->fetchAll(PDO::FETCH_CLASS, $class_name);
	}

	public function getAll($table){
		$pdo = $this->getPDO();
		if($pdo === false) return null;
		$stmt = $pdo->prepare('SELECT * FROM '.$table.';');
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_CLASS, ucfirst($table));
	}

	public function modify($stmt){ // Insert/Update
		$this->getPDO()->query($stmt);
	}

	public function getFields($table){
		$cols = DB_TABLES[$table];
		$res = array();

		//var_dump_pre($cols);
		foreach($cols as $key => $val){
			$res[$key] = array(
				str_contains($val[0], '(') ? explode('(', $val[0])[0]:$val[0], // Type
				$val[1] == 'YES', // Null
				$val[4] == 'auto_increment', // Extra
				$val[2] == 'PRI', // Primary Key
				$val[2] == 'MUL'  // Foreign Key
			);
		}

		return $res;
	}

	public function getOnlyFields($fields){
		$only_fields = array();
		foreach($fields as $key => $val) $only_fields[] = $key;

		return $only_fields;
	}

	public function create($table, $values){
		$fields = $this->getFields($table);
		$i = 0;
		$str_fields = '';
		$str_values = '';
		$to_execute = array();

		if(count($values) > count($fields)) return 'ERR: too many values';
		foreach($fields as $key => $val){
			if($val[2]) continue; // Si Auto-Increment
			if($i >= count($values)) break;
			if(!checkValue($values[$i], array($val[0], $val[1] ? 'null':''))){
				return sprintf('ERR[%s]: wrong value (%s|%s)', $key, var_dump_ret($values[$i]), ($val[1]?'true':'false'));
			}
			if(empty($values[$i])){ // Vide ici => null possible
				$i++;
				continue;
			}
			$str_fields .= '`'.$key.'`, ';
			if($key == 'mdp'){
				$str_values .= 'SHA2(:'.$key.', 256), ';
			}else{
				$str_values .= ':'.$key.', ';
			}
			if($val[0] == 'tinyint'){
				$to_execute[$key] = (int)(strtolower($values[$i])=='on');
			}else{
				$to_execute[$key] = $values[$i];
			}
			$i++;
		}
		$str_fields = rtrim($str_fields, ', ');
		$str_values = rtrim($str_values, ', ');
		$qry = sprintf('INSERT INTO %s (%s) VALUES (%s);', $table, $str_fields, $str_values);
		$this->query($qry, $to_execute);

		return true;
	}

	public function delete($table, $values){
		$fields = $this->getFields($table);
		$i = 0;
		$qry = '';
		$to_execute = array();

		if(count($values) > count($fields)) return 'ERR: too many values';
		foreach($fields as $key => $val){
			if(empty($values[$i]) || ($val[2] && !$val[3])) continue; // Si Auto-Increment et pas PK
			if(!checkValue($values[$i], array($val[0], ''))){ // PK => null impossible
				return sprintf('ERR[%s]: wrong value (%s|%s)', $key, $values[$i], ($val[1]?'true':'false'));
			}
			$qry .= sprintf('`%s` = :%s AND ', $key, $key);
			$to_execute[$key] = $values[$i];
			$i++;
		}
		$qry = rtrim($qry, ' AND ');
		$qry = sprintf('DELETE FROM `%s` WHERE %s;', $table, $qry);
		var_dump_pre($qry);
		var_dump_pre($to_execute);
		$this->query($qry, $to_execute, null, true);

		return true;
	}

	public function getFromTable($table, $id, $field='id', $all=false){
		$qry = 'SELECT * FROM '.$table.' WHERE '.$field.' = :id;';
		$to_execute = [$field => $id];
		return $this->query($qry, $to_execute, ucfirst($table), $all);
	}

	public function count($table){
		$req = $this->getPDO()->query('SELECT Count(*) FROM '.$table.';');
		return $req->fetchAll();
	}

	public function queryLimit($table, $values, $amount, $sort, $incr=true, $page=0){
		$fields = $this->getFields($table);
		$enabled = false;
		$i = 0;
		$max = $this->count($table)[0][0];
		if($amount != 0){
			$max = intdiv($max, $amount);
		}
		$str_fields = '*';
		$str_values = '';
		// ---- QUERY MAKER ----
		foreach($fields as $key => $val){
			if(empty($values[$key])){
				$i++;
				continue;
			}
			if(!checkValue($values[$key], array($val[0], 'null'))){
				return sprintf('ERR[%s]: wrong value (%s|%s)', $key, var_dump_ret($values[$i]), ($val[1]?'true':'false'));
			}
			if(!$enabled){
				$str_values .= 'WHERE ';
				$enabled = true;
			}else{
				$str_values .= 'AND ';
			}
			$str_values .= $key.' = :'.$key.' ';
			if($val[0] == 'tinyint'){
				$to_execute[$key] = (int)(strtolower($values[$key])=='on');
			}else{
				$to_execute[$key] = $values[$key];
			}
			$i++;
		}
		$str_fields = rtrim($str_fields, ', ');
		$qry = sprintf('SELECT %s FROM %s %s', $str_fields, $table, $str_values);
		$qryMax = sprintf('SELECT COUNT(*) FROM %s %s', $table, $str_values);
		if(!empty($sort)){
			$qry .= ' ORDER BY '.$sort.($incr ? '':' DESC');
		}
		if($amount != 0){
			$qry .= ' LIMIT '.$amount;
			$qry .= ' OFFSET '.(($page == 0 ? 0 : ($page-1)) * $amount).';';
		}
		if(isset($to_execute)){
			$res = $this->query($qry, $to_execute, $table, true);
		}else{
			$res = $this->query($qry, null, $table, true);
		}

		return $res;
	}

	public function update($table, $values){
		$fields = $this->getFields($table);
		$i = 0;
		$pk_name = '';
		$str_values = '';
		$to_execute = array();

		foreach($fields as $key => $val){
			if($val[3]) $pk_name = $key;
			if(empty($values[$key]) || $val[2]){
				$i++;
				continue;
			}
			if(!checkValue($values[$key], array($val[0], 'null'))){
				return sprintf('ERR[%s]: wrong value (%s|%s)', $key, $values[$key], ($val[1]?'true':'false'));
			}
			$str_values .= $key.' = :'.$key.', ';
			if($val[0] == 'tinyint'){
				$to_execute[$key] = (int)(strtolower($values[$key])=='on');
			}else{
				$to_execute[$key] = $values[$key];
			}
			$i++;
		}
		$str_values = rtrim($str_values, ', ');
		$to_execute[$pk_name] = $values[$pk_name];
		$qry = sprintf('UPDATE %s SET %s WHERE %s = :%s;', $table, $str_values, $pk_name, $pk_name);
		$this->query($qry, $to_execute, null, false, false);

		return true;
}
}
?>
