<?php
/** @define "APP" "../../app/" */

class Template extends Core
{
    /**
     * @var null
     */
    private $path = null,
            $vars = array(
                'title'=> '',
            );

    /**
     * @var bool
     */
    private static $instance = false;

    /**
     * return instance Template
     * @return Template
     */
    public static function getInstance () {
        if (self::$instance === false) {
            self::$instance = new Template;
        }
        return self::$instance;
    }

    /**
     * @param $template_path
     * @return $this
     */
    public function setTemplate($template_path){
        $this->path = $template_path;

        return $this;
    }

    /**
     * @param $key
     * @param null $value
     * @return $this
     */
    public function set($key, $value = null){
        if(is_array($key)){
            $this->vars = array_merge($this->vars, $key);
        }else{
            $this->vars[$key] = $value;
        }

        return $this;
    }

    /**
     * @param bool|true $show_header_footer
     */
    public function show($show_header_footer = true){
        if(empty($this->path)){
            $this->error()->set('Template file is not defined');
        }

        $view_path = APP . 'view/';
        $tpl_path = $view_path . $this->path . '.php';

        if(!file_exists($tpl_path)){
            $this->error()->set("Template file `$tpl_path` not found");
        }

        extract($this->vars);

        if($show_header_footer){
            include $view_path . 'common/header.php';
        }

        /** @noinspection PhpIncludeInspection */
        include $tpl_path;

        if($show_header_footer){
            include $view_path . 'common/footer.php';
        }

        exit();
    }
}