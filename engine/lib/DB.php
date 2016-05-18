<?php

/**
 * Class DB
 */
class DB {
    /**
     * @var null|PDO
     */
    private $engine = null;
    /**
     * @var bool
     */
    private static $instance = false;

    /**
     * @var array
     */
    private $query_builder = array();

    /**
     * return instance DB
     * @return DB
     */
    public static function getInstance () {
        if (self::$instance === false) {
            self::$instance = new DB;
        }
        return self::$instance;
    }

    /**
     * PDO init
     */
    private function __construct () {
        try {
            $this->engine = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
            $this->engine->exec("SET CHARACTER SET utf8");
            $this->engine->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch(PDOException $e) {
            $error = new Error();
            $error->set($e->getMessage());
        }
    }

    public function __destruct() {
        $this->engine = null;
    }

    /**
     *
     * DB query
     * @param string $sQuery prepared string
     * @param array $data
     * @return PDOStatement
     */
    public function query ($sQuery, $data = array()) {
        try {
            $result = $this->engine->prepare ($sQuery);
            $result->setFetchMode(PDO::FETCH_ASSOC);
            $result->execute($data);
        }
        catch(PDOException $e) {
            $result = null;
            $error = new Error();
            $error->set($e->getMessage());
        }

        return $result;
    }

    /**
     * Last inserted id
     * @return string
     */
    public function lastInsertId(){
        return $this->engine->lastInsertId();
    }

    /**
     * Generate WHERE block.
     *
     * @param $params string|array
     * @param null $binds
     * @return $this
     */
    public function where($params, $binds = null)
    {
        if (!is_array($binds)) {
            $binds = array();
        }

        if (is_array($params)) {
            $current_where = isset($this->query_builder['where']) ? $this->query_builder['where'] : '';

            foreach ($params as $field => $value) {
                if (!empty($current_where)) {
                    $current_where .= ' AND';
                }

                if($value===null){
                    $current_where .= " s1.$field IS NULL";
                }else{
                    $last_ch = substr($field, -1);
                    if($last_ch == '<' or $last_ch == '>'){
                        $field = trim(substr($field, 0, -1));
                    } else
                        if($last_ch == '='){
                            $last_ch = substr($field, -2);
                            $field = trim(substr($field, 0, -2));
                        } else {
                            $last_ch = '=';
                        }
                    $current_where .= " s1.$field $last_ch :$field";

                    $binds[$field] = $value;
                }
            }

            $this->query_builder['where'] = $current_where;
        }elseif(!empty($params)){
            $this->query_builder['where'] = $params;
        }

        if (count($binds)) {
            $current_binds = isset($this->query_builder['binds']) ? $this->query_builder['binds'] : array();
            $this->query_builder['binds'] = array_merge($current_binds, $binds);
        }

        return $this;
    }

    /**
     * ORDER BY
     * @param $params
     * @return $this
     */
    public function order($params){
        $this->query_builder['order'] = $params;
        return $this;
    }

    /**
     * GROUP BY
     * @param $params
     * @return $this
     */
    public function group($params){
        $this->query_builder['group'] = $params;
        return $this;
    }

    /**
     * LIMIT
     * @param $param
     * @return $this
     */
    public function limit($param){
        $this->query_builder['limit'] = $param;
        return $this;
    }

    /**
     * OFFSET
     * @param $param
     * @return $this
     */
    public function offset($param){
        $this->query_builder['offset'] = $param;
        return $this;
    }

    /**
     * SELECT
     * @param string $params
     * @return $this
     */
    public function select($params = '*'){
        $this->query_builder['select'] = $params;
        return $this;
    }

    /**
     * JOIN another table
     *
     * @param $table_name string - joined table name
     * @param $params string - sql-request after "JOIN .. ON "
     * @param string $type string - inner, left, outer
     * @param null $binds
     * @return $this
     */
    public function join($table_name, $params, $type = 'inner', $binds = null)
    {
        if(!isset($this->query_builder['join'])){
            $this->query_builder['join'] = '';
        }

        if(!isset($this->query_builder['join_count'])){
            $this->query_builder['join_count'] = 0;
        }

        switch($type){
            case 'inner':
                $join = 'INNER JOIN';
                break;
            case 'left':
                $join = 'LEFT OUTER JOIN';
                break;
            case 'outer':
                $join = 'FULL OUTER JOIN';
                break;
            default:
                $error = new Error();
                $error->set('Unknown join type');
                return $this;
        }

        if(empty($params)){
            $error = new Error();
            $error->set('Empty join params');
            return $this;
        }

        if (!is_array($binds)) {
            $binds = array();
        }

        if (count($binds)) {
            $current_binds = isset($this->query_builder['binds']) ? $this->query_builder['binds'] : array();
            $this->query_builder['binds'] = array_merge($current_binds, $binds);
        }

        $join .= ' '.$table_name.' AS s'.($this->query_builder['join_count']+2).' ON ' . $params;

        $this->query_builder['join_count']++;
        $this->query_builder['join'] .= $join;
        return $this;
    }

    /**
     * Count of results
     * @param $table_name
     * @return int
     */
    public function count($table_name){
        $this->query_builder['select'] = 'count(*)';
        return (int) $this->get($table_name)->fetchColumn(0);
    }

    /**
     * Simple selection from table
     *
     * @param $table_name
     * @return PDOStatement
     */
    public function get($table_name){
        if(isset($this->query_builder['select'])){
            $sql = 'SELECT ' . $this->query_builder['select'];
        }else{
            $sql = 'SELECT *';
        }

        $sql .= ' FROM ' . $table_name . ' AS s1 ';

        if(isset($this->query_builder['join'])){
            $sql .= ' ' . $this->query_builder['join'] . ' ';
        }

        if(isset($this->query_builder['where'])){
            $sql .= 'WHERE ' . $this->query_builder['where'];
        }

        if(isset($this->query_builder['order'])){
            $sql .= ' ORDER BY s1.' . $this->query_builder['order'];
        }

        if(isset($this->query_builder['group'])){
            $sql .= ' GROUP BY s1.' . $this->query_builder['group'];
        }

        if(isset($this->query_builder['limit'])){
            $sql .= ' LIMIT ' . $this->query_builder['limit'];
        }

        if(isset($this->query_builder['offset'])){
            $sql .= ' OFFSET ' . $this->query_builder['offset'];
        }

        if(!isset($this->query_builder['binds'])){
            $this->query_builder['binds'] = array();
        }

        $binds = $this->query_builder['binds'];
        $this->query_builder = array();

        return $this->query($sql, $binds);
    }

    /**
     * Insert values
     *
     * @param $table_name
     * @param array $values
     * @return PDOStatement
     */
    public function insert($table_name, $values = array()){
        return $this->build_insert('INSERT', $table_name, $values);
    }

    /**
     * Replace values
     * @param $table_name
     * @param array $values
     * @return PDOStatement
     */
    public function replace($table_name, $values = array()){
        return $this->build_insert('REPLACE', $table_name, $values);
    }

    /**
     * Wrapper for insert and replace requests
     *
     * @param $type string - insert|replace
     * @param $table_name
     * @param $values
     * @return PDOStatement
     */
    private function build_insert($type, $table_name, $values){
        $sql = $type . ' INTO `' . $table_name . '` ';

        $keys = '';
        $bind = '';

        foreach($values as $key => $value){
            if($keys!=''){
                $keys .= ',';
                $bind .= ',';
            }
            $keys .= '`'.$key.'`';
            $bind .= ':'.$key.'';
        }

        if($keys!=''){
            $sql .= '(' . $keys . ') VALUE (' . $bind . ')';
        }

        $this->query_builder = array();
        return $this->query($sql, $values);
    }

    /**
     * Delete values
     *
     * @param $table_name
     * @return PDOStatement
     */
    public function delete($table_name){
        $sql = 'DELETE FROM `s1` USING `' . $table_name . '` AS `s1` ';

        if(isset($this->query_builder['where'])){
            $sql .= 'WHERE ' . $this->query_builder['where'];
        }

        if(isset($this->query_builder['order'])){
            $sql .= ' ORDER BY `' . $this->query_builder['order'] . '`';
        }

        if(isset($this->query_builder['limit'])){
            $sql .= ' LIMIT ' . $this->query_builder['limit'];
        }

        if(!isset($this->query_builder['binds'])){
            $this->query_builder['binds'] = array();
        }

        $binds = $this->query_builder['binds'];
        $this->query_builder = array();

        return $this->query($sql, $binds);
    }

    /**
     * Update values
     *
     * @param $table_name
     * @param $values
     * @return PDOStatement
     */
    public function update($table_name, $values){
        if(empty($values)){
            $error = new Error();
            $error->set('There is nothing to update');
        }

        $sql = 'UPDATE `' . $table_name . '` AS `s1` SET ';

        if(!isset($this->query_builder['binds'])){
            $this->query_builder['binds'] = array();
        }

        $set = '';

        foreach($values as $key => $value){
            if($set!=''){
                $set .= ',';
            }

            if(isset($this->query_builder['binds'][$key])){
                $bind_key = $key.'_1';
            }else{
                $bind_key = $key;
            }

            $this->query_builder['binds'][$bind_key] = $value;

            $set .= '`'.$key.'`=:'.$bind_key;
        }

        $sql .= $set;

        if(isset($this->query_builder['where'])){
            $sql .= ' WHERE ' . $this->query_builder['where'];
        }

        if(isset($this->query_builder['order'])){
            $sql .= ' ORDER BY `' . $this->query_builder['order'] . '`';
        }

        if(isset($this->query_builder['limit'])){
            $sql .= ' LIMIT ' . $this->query_builder['limit'];
        }

        $binds = $this->query_builder['binds'];
        $this->query_builder = array();

        return $this->query($sql, $binds);
    }

}