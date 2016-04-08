<?php

/**
 * Class Request
 */
class Request {

    /**
     * Getting GET request
     * @param $key
     * @param null $defaultValue
     * @return string
     */
    function get($key, $defaultValue = null){
        return isset($_GET[$key]) ? $_GET[$key] : $defaultValue;
    }

    /**
     * Getting POST request
     * @param $key
     * @param null $defaultValue
     * @return string
     */
    function post($key, $defaultValue = null){
        return isset($_POST[$key]) ? $_POST[$key] : $defaultValue;
    }

}