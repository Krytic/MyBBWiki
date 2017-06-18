<?php

require_once 'BaseHandler.php';

class PermissionHandler extends BaseHandler {
	private $permissions = array();

	protected function constructor() {
		$permissions = $this->cache->read('wiki_permissions');
		$this->permissions = $permissions["gid_{$mybb->user['usergroup']}"];
	}

	protected function _validate_self($cache_to_validate) {
		$actions = $this->get_actions();

		$perms = $this->permissions; // so we can be more brutal.

		foreach($actions as $action) {
			if(in_array($action, $this->permissions)) {
				unset($perms[$action]);
			}
		}

		if(count($perms) > 1) {
			if(count($this->permissions) > count($actions)) {
				// check actions
				$errno = 200;
			}
			else {
				$errno = 201;
			}
			throw new Exception("Cache / Database Mismatch with the wiki", $errno);
		}
	}

	protected function _repair() {
		// TBD
	}

	public function register_group($gid) {
		$gid = intval($gid);
		// Set the default permissions
		$this->db->write_query("INSERT INTO " . TABLE_PREFIX . "wiki_perms(`gid`,`can_view`,`can_create`,`can_edit`,`can_protect`,`can_export`) VALUES('" . $gid . "','1','1','1','0','0')");

		// and cache them
			$cache_arr['gid_' . $gid] = array(
				'can_view'		=>	1,
				'can_create'	=>	1,
				'can_edit'		=>	1,
				'can_protect'	=>	0,
				'can_export'	=>	0
				);

		$this->cache->update('wiki_permissions', $cache_arr);
	}

	public function delete_group($gid) {
		$gid = intval($gid);
		$this->db->write_query(sprintf("DELETE FROM `%swiki_perms` WHERE `gid`='{$gid}'"), TABLE_PREFIX);

		$cache_arr['gid_' . $gid] = "";
		$this->cache->update('wiki_permissions', $cache_arr);
	}

	public function check($gid, $action) {
		if(in_array($action, $this->get_actions())) {
			return $this->permissions[$action];
		}

		return false;
	}

	public function get_actions() {
		$query = $this->db->write_query(sprintf("DESCRIBE `%swiki_perms`", TABLE_PREFIX));

		$actions = [];

		while($row = $this->db->fetch_array($query)) {
			$actions[] = $row['Field'];
		}

		return $actions;
	}
}