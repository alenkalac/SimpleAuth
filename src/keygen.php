<?php
namespace xample;

class keygen {
	private $base = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

	public function generateKey($size = 16) {
		$key = '';
		$hash = str_shuffle($this->base);
		for($i = 0; $i < $size; $i++) {
			if($i % 4 == 0 && $i != 0) $key = $key . "-";
			$hash = str_shuffle($hash);
			$rand = rand(0, strlen($hash)-1);
			$key = $key . $hash[$rand];
		}

		return $key;
	}

	public function savekey($key, $db) {
		$q = $db->prepare("INSERT INTO serial VALUES(NULL, :KEY, '-1', '-1')");
		$q->execute([
			"KEY" => $key
		]);
	}
}
	