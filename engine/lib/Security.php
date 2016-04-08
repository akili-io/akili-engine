<?php

/**
 * Class Security
 */
class Security
{
    /**
     * Get SHA256 hash from string
     * @param $str
     * @return string
     */
    public static function getHash($str){
        return hash('sha256', $str . '__' . SALT);
    }

    /**
     * Generate random string
     * @param int $length
     * @return string
     */
    public static function getRandomString($length = 8){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, strlen($characters)-1)];
        }
        return $random_string;
    }
}