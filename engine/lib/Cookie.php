<?php

/**
 * Class Cookie
 */
class Cookie {

    /**
     * @param $name
     * @param string $value
     * @param bool|true $http_only
     * @param bool|false $lifetime_seconds
     * @return bool
     */
    public function set ($name, $value= '', $http_only = true, $lifetime_seconds = false) {
        if ($lifetime_seconds === false){
            $lifetime_seconds = defined('COOKIE_LIFE_TIME') ? COOKIE_LIFE_TIME : 86400*30;
        }

        return setcookie ((string) $name, (string) $value, time() + $lifetime_seconds, '/', '', false, $http_only);
    }

    /**
     * @param $cookie_name
     * @param null $default_value
     * @return string
     */
    public function get($cookie_name, $default_value = null){
        return isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : $default_value;
    }

    /**
     * @param $cookie_name
     */
    public function delete($cookie_name){
        $this->set($cookie_name, '', true, -300);
    }

}