<?php

/**
 * Class Error
 */
class Error extends Core
{

    /**
     * Show Error and stop
     *
     * @param string $message
     * @param int $code
     */
    public function set($message = 'Internal Server Error', $code = 500){
        header ("HTTP/1.0 $code $message");

        $this->tpl()->setTemplate('common/error');

        $trace = false;

        if(DEBUG){
            ob_start();
            debug_print_backtrace();
            $trace = ob_get_clean();
        }

        $this->tpl()->set(array(
            'error_text' => "Error: $message",
            'trace' => $trace,
            'title' => "Error :: $code",
            'is_error' => true,
        ));

        $this->tpl()->show();

        exit();
    }

    /**
     * Set 401 Error
     */
    public function set401(){
        $this->set('Unauthorized', 401);
    }

    /**
     * Set 403 Error
     */
    public function set403(){
        $this->set('Forbidden', 403);
    }

    /**
     * Set 404 Error
     */
    public function set404(){
        $this->set('Not Found', 404);
    }

}