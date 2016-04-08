<?php
/** @define "ROOT" "../" */
/** @define "APP" "../app" */
/** @define "$module_path" "../app/controllers/AuthController.php" */

spl_autoload_register(function ($class) {
    if(strpos($class, 'Helper')){
        $helper_path = APP.'helpers/'.$class.'.php';
        if(file_exists($helper_path)){
            /** @noinspection PhpIncludeInspection */
            require_once $helper_path;
            return true;
        }
    }

    $model_path = APP.'models/'.$class.'.php';
    if(file_exists($model_path)){
        /** @noinspection PhpIncludeInspection */
        require_once $model_path;
        return true;
    }

    $lib_path = ROOT.'engine/lib/'.$class.'.php';
    if(file_exists($lib_path)){
        /** @noinspection PhpIncludeInspection */
        require_once $lib_path;
        return true;
    }

    return false;
});

require_once APP . 'config/local.config.php';
require_once APP . 'config/config.php';

$url = isset($_GET['u']) ? $_GET['u'] : '';
$url = strtolower(trim ($url, '/'));

if ($url == '') {
    $url = DEFAULT_MODULE.'/'.DEFAULT_ACTION;
}

$urlParts = explode ('/', $url);
$cntParts = count ($urlParts);
$callParams = array();

if($cntParts > 1){
    $module = $urlParts[0];
    $action = $urlParts[1];
    for($i=2;$i<$cntParts;$i++){
        $callParams[] = $urlParts[$i];
        $_GET['param'.($i-2)] = $urlParts[$i];
    }
}else{
    $module = DEFAULT_MODULE;
    $action = DEFAULT_ACTION;
}

$_action = explode('-', $action);
$action = '';
foreach($_action as $_action_part){
    $action .= ucfirst($_action_part);
}

define('CURRENT_MODULE',  ucfirst($module));
define('CURRENT_ACTION',  $action);

$module = ucfirst($module).'Controller';
$action = 'action' . ucfirst($action);
$module_path = APP . "controllers/$module.php";

if(file_exists($module_path)){
    /** @noinspection PhpIncludeInspection */
    require_once($module_path);

    if(class_exists($module) && method_exists($module, $action)){
        $_GET['module'] = $module;
        $_GET['action'] = $action;

        $one = new $module;
        $data = call_user_func_array(array($one, $action), $callParams);

        if(is_array($data)){
            // Json result
            $options = defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0;
            print json_encode($data, $options);
        }else{
            Template::getInstance()->show();
        }
        exit;
    }
}

$error = new Error();
$error->set404();