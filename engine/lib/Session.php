<?php

/** @define "ROOT" "../../" */

class Session extends Core{
    /**
     * @var bool
     */
    private static $instance = false;

    public $id = null,
           $user = null;

    /**
     * return instance Session
     * @return Session
     */
    public static function getInstance () {
        if (self::$instance === false) {
            self::$instance = new Session;
        }
        return self::$instance;
    }

    /**
     * Init, session checking
     */
    private function __construct () {
        if(($secret_key_1 = $this->cookie()->get('s1', false)) &&
            ($secret_key_2 = $this->cookie()->get('s2', false)))
        {

            $res = DB::getInstance()->query(
                'SELECT * FROM user WHERE user_hash=:secret_key_1 AND deleted=0 LIMIT 1',
                array('secret_key_1'=>$secret_key_1));

            if($res->rowCount() == 1){
                $user = $res->fetch();

                require_once ROOT . 'engine/lib/Security.php';
                if($secret_key_2 == Security::getHash(SALT . $user['login'] . $user['pass'])){
                    $this->user = $user;
                    $this->id = $user['id'];
                }else{
                    $this->destroySession();
                }
            }else{
                $this->destroySession();
            }
        }else{
            $this->destroySession();
        }
    }

    /**
     * Destroy user session (logout)
     */
    public function destroySession(){
        $this->id = null;
        $this->user = null;
        $this->cookie()->delete('s1');
        $this->cookie()->delete('s2');
    }

    /**
     * User authorization
     *
     * @param $login
     * @param $pass
     * @return bool
     */
    public function authorization($login, $pass){
        $this->destroySession();

        $res = $this->db()
            ->where(array(
                'login'  => $login,
                'deleted'=> 0,
            ))
            ->limit(1)
            ->get('user')
        ;
        if($res->rowCount() == 1){
            $user = $res->fetch();
            if($user['pass'] == $this->getHashedPass($user['login'], $pass)) {

                $this->cookie()->set('s1', $user['user_hash']);
                $this->cookie()->set('s2', Security::getHash(SALT . $user['login'] . $user['pass']));

                $this->user = $user;
                $this->id = $user['id'];
                return true;
            }
        }

        return false;
    }

    /**
     * User registration
     *
     * @param $user array
     * @return string|array
     */
    public function registration($user){
        if(!isset($user['pass'])){
            return 'empty_pass';
        }

        if(!isset($user['login'])){
            return 'empty_login';
        }

        if(isset($user['id']))unset($user['id']);
        if(isset($user['user_hash']))unset($user['user_hash']);

        if(isset($user['pass']))$user['pass'] = $this->getHashedPass($user['login'], $user['pass']);

        if($this->db()
            ->where(array(
                'login'=>$user['login'],
                'deleted'=>0))
            ->limit(1)
            ->count('user')!=0
        ){
            return 'login_busy';
        }

        do{
            $user_hash = Security::getHash(Security::getRandomString(15));
        }while($this->db()
            ->where(array(
                'user_hash'=>$user_hash,
                'deleted'=>0))
            ->limit(1)
            ->count('user') != 0);

        $user['user_hash'] = $user_hash;
        $this->db()->insert('user', $user);
        if($user['id'] = $this->db()->lastInsertId()){
            return $user;
        }else{
            return 'error';
        }
    }

    /**
     * Get user hashed password for security_key_2 cookie
     * @param $login
     * @param $pass
     * @return string
     */
    public function getHashedPass($login, $pass){
        return Security::getHash($login . $pass . SALT);
    }

    /**
     * Return user array for $this->user()
     * @return array()|null
     */
    public function getUser(){
        return $this->user;
    }
}