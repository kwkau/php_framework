<?php

class error_handler extends Controller {
    /**
     * the error handler must keep log of every error it encounters
     * and it must also save information on the kind of error and also 
     * data on the user who caused that error. data such as IP address,
     * browser name and info. dnt 4get!
     */

    function __construct(){
        parent::__construct('error_handler');
    }

    public function missing_page(){
        $this->view();
    }

    /**
     * function this function is used to react to an exception in any written sswap code.
     * it takes a log of the error and inserts it into the database for future reference and also
     * redirects the user to an error screen which provides them with information on what to do
     * depending on the error received.
     * @param $type
     * @param $datetime
     * @param $errinfo
     * @internal param \Exception $statics function
     * @return void
     */
    public function exception($type,$datetime,$errinfo){
//        $this->err_log($type, $datetime, $errinfo);
        $this->view('error_handler/exception',false);
    }


}
