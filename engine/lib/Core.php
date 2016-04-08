<?php

/** @define "ROOT" "../../" */

class Core
{
    private $db = null,
            $cookie = null,
            $request = null,
            $session = null,
            $user = null,
            $error = null,
            $tpl = null;

    /**
     * @return DB|null
     */
    public function db(){
        if($this->db==null){
            require_once ROOT . 'engine/lib/DB.php';

            $this->db = DB::getInstance();
        }
        return $this->db;
    }

    /**
     * @return Cookie|null
     */
    public function cookie(){
        if($this->cookie==null){
            require_once ROOT . 'engine/lib/Cookie.php';
            $this->cookie = new Cookie();
        }
        return $this->cookie;
    }

    /**
     * @return null|Request
     */
    public function request(){
        if($this->request==null){
            require_once ROOT . 'engine/lib/Request.php';
            $this->request = new Request();
        }
        return $this->request;
    }

    /**
     * @return null|Template
     */
    public function tpl(){
        if($this->tpl==null){
            require_once ROOT . 'engine/lib/Template.php';
            $this->tpl = Template::getInstance();
        }
        return $this->tpl;
    }

    /**
     * @return null|Session
     */
    public function session(){
        if($this->session==null){
            require_once ROOT . 'engine/lib/Session.php';
            $this->session = Session::getInstance();
        }
        return $this->session;
    }

    /**
     * User Array
     * @return mixed|null
     */
    public function user(){
        return $this->session()->getUser();
    }

    /**
     * @return Error|null
     */
    public function error(){
        if($this->error==null){
            require_once ROOT . 'engine/lib/Error.php';
            $this->error = new Error();
        }
        return $this->error;
    }


}