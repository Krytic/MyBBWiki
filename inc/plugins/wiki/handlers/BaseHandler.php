<?php

abstract class BaseHandler {
    private static $instance = null;
    protected $db = null;
    protected $mybb = null;
    protected $cache = null;

    public static function singleton() {
        $class = get_called_class();
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new $class();
        }
        return self::$instance[$class];
    }

    private function __construct() {
        global $db, $mybb, $cache;

        $this->db = $db;
        $this->mybb = $mybb;
        $this->cache = $cache;

        $this->constructor();
    }

    private function _allowed_datum() {
        return array('db', 'mybb', 'cache');
    }

    abstract protected function constructor();
}