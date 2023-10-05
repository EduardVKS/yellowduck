<?php
namespace App\Models;

use PDO;

class DB {
	
	private static $db;


	public function __construct($dbinfo = null) {
		if(!$dbinfo) return;
		static::$db = new PDO('mysql:host='.$dbinfo->db_host.';dbname='.$dbinfo->db_name.';charset=utf8',
		    $dbinfo->db_user,
		    $dbinfo->db_password,
		    $dbinfo->options,
		);
	}

	public function getData ($query, $data=[]) {
		$stmt = static::$db->prepare($query);
		$stmt->execute($data);
		return $stmt->fetch();
	}

	public function getDataAll ($query, $data=[], $option = null) {
		$stmt = static::$db->prepare($query);
		$stmt->execute($data);
		return ($option == 'FETCH_GROUP') ? $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP) : $stmt->fetchAll();
	}

	public function setData ($query, Array $data) {
		$stmt = static::$db->prepare($query);
		$stmt->execute($data);
		return static::$db->lastInsertId();
	}

	public function updateData ($query, $data=[]) {
		$stmt = static::$db->prepare($query);
		if(!is_array($data[array_key_first($data)])) {
			$stmt->execute($data);
			return $stmt->rowCount();
		} else {
			$result = [];
			foreach ($data[0] as $value) {
				$stmt->execute($value);
				if($stmt->rowCount()) $result[] = $value;
			}
			return $result;
		}
	}

	public function deleteData ($query, $data=[]) {
		$stmt = static::$db->prepare($query);
		if(!is_array($data[array_key_first($data)])) {
			$stmt->execute($data);
			return $stmt->rowCount();
		} else {
			$result = [];
			foreach ($data[0] as $value) {
				$stmt->execute($value);
				if($stmt->rowCount()) $result[] = $value;
			}
			return $result;
		}
	}
}
