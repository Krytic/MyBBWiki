<?php

class to_1_2_0 {

	private $error = "";

	public function info() {
		return array(
			'version'	=> '1.2.0',
			'desc'		=> 'Upgrades automatically to v1.2.0.',
			'warning'	=> 'This upgrade will reset all of your categories to Meta, or whatever that has been renamed to.'
			);
	}

	public function validate() {
		// validate() checks whether the update is "runnable".
		// validate() should return false if the upgrade has ran.
		global $db;

		$res = $db->write_query(sprintf("SELECT * FROM `%swiki_categories` LIMIT 1", TABLE_PREFIX));
		$res = $db->fetch_array($res);

		return !array_key_exists('description', $res);
	}

	public function run() {
		global $db;

		$db->write_query(sprintf("ALTER TABLE `%swiki_categories` ADD description TEXT(255);", TABLE_PREFIX));
		$db->write_query(sprintf("ALTER TABLE `%swiki` MODIFY category INT(10)", TABLE_PREFIX));
		$db->write_query(sprintf("UPDATE `%swiki` SET `category`='1' WHERE 1=1", TABLE_PREFIX));

		return true;
	}

	public function error() {
		return $this->error;
	}
}