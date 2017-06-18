<?php

require_once 'Handler.php';

/**
 * SettingsHandler
 * The SettingsHandler allows us to abstract our handling of settings to a class.
 */
class SettingsHandler extends Handler {
    /**
     * _validate_self() is required in all Handlers.
     * It is a function that is called in the constructor of the
     * parent Handler.
     * This should throw an exception if it fails.
     */
    protected function _validate_self($cache_to_validate) {
        // validation logic
    }

    /**
     * _repair() is also required. It allows us to try to recover from _validate_self() failing.
     * For example, if _validate_self() checks that the cache is the same as the database, _repair() would
     * add any missing database entries to the cache and vice-versa. If you cannot repair, throw an exception and the
     * parent will Handle it (if you'll pardon the pun)
     */
    protected function _repair() {
        // reparation logic
    }

    public function get_settings() {
        // load from cache or some shit
        echo $this->db;
    }
}