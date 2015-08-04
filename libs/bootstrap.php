<?php

class bootstrap {

    function __construct() {
        /**************************************************************************************************
         * we are sanitizing and preparing the the url, in sanitizing i mean removing the forward slash
         * and in preparing we are dividing the url into classes, functions and parameters
         **************************************************************************************************/

        @$route = $this->get_route($_GET['hive_url']);
//        print_r(http_get_request_headers());
        /*******************************************************************************
         * we want to always display the home page when the url is either empty
         * or is home
         *******************************************************************************/
        if (empty($route['controller'])){
            //we are going home
            $home = new login();
            if(method_exists($home,"index"))
            $home->index();
            return false;
        }



        /*if (isset($url[3])) {
            $error = new error_handler();
            $error->missing_page();
            return false;
        }*/

        $controller = $this->get_controller($route['controller']);

        /*-------------------------------------------------------------
         * creating an alternate route proc for our profile controller
         *-----------------------------------------------------------*/
        if($route["controller"] == "profile" && $route['action_method'] != "uploads"){
            if(method_exists($controller,"index")){
                $controller->index($route['action_method']);
            }
            return false;
        }

        /*--------------------------------------------------------------
         * creating an alternate route proc for our dropzone controller
         *------------------------------------------------------------*/
        if($route["controller"] == "dropzone" && is_numeric($route['action_method'])){
            if(method_exists($controller,"index")){
                $controller->index($route['action_method']);
            }
            return false;
        }

        /*-----------------------------------------------------------
         * creating an alternate route proc for our group controller
         *---------------------------------------------------------*/
        if($route["controller"] == "group" && (!isset($_POST["grp_name"]) && !method_exists($controller,$route['action_method']))){

            if(method_exists($controller,"index")){
                $controller->index($route['action_method']);
            }
            return false;
        }

        /*----------------------------------------------------------------
         * creating an alternate route proc for our department controller
         *--------------------------------------------------------------*/
        if($route["controller"] == "department" && !method_exists($controller,$route['action_method'])){

            if(method_exists($controller,"index")){
                $controller->index($route['action_method']);
            }
            return false;
        }

        /*-----------------------------------------------------------------
         * we are checking to se if the url contains a call for a function
         * or  a function and a parameter 
         *---------------------------------------------------------------*/
        if (isset($route['parameter_list']) && !empty($route['parameter_list'])){
            if(method_exists($controller,$route['action_method'])){
                $controller->{$route['action_method']}($route['parameter_list']);
            }else{
                header("location:http://". HOST_NAME ."/error_handler/missing_page");
                exit();
            }
        } else if (isset($route['action_method']) && !empty($route['action_method'])){
            if(method_exists($controller,$route['action_method'])){
                $controller->{$route['action_method']}();
            }else{
                header("location:http://". HOST_NAME ."/error_handler/missing_page");
                exit();
            }
        }else if (!isset($route['action_method']) && empty($route['action_method'])){
            if(method_exists($controller,"index"))
            $controller->index();
        }
    }

    /**
     * @description function to generate route values for the current request
     * @param $url
     * @return array an array with the possible route values controller, action_method and parameter_list
     */
    private  function get_route($url){
        $route = array();
        $url_cln = (isset($url)) ? explode('/', rtrim($url, '/')) : null;
        $route['controller'] = isset($url_cln[0]) ? $url_cln[0] : null;
        $route['action_method'] = isset($url_cln[1]) ? $url_cln[1] : null;

        for($i =2; $i <= count($url_cln);$i++){
            $route['parameter_list'][] = isset($url_cln[$i]) ? $url_cln[$i] : null;
        }

        return $route;
    }

    private function get_controller($controller){
        $file = "controller/{$controller}.php";

        /***********************************************************************
         * we are checking to see if the requested controller in the url exists
         * if it does we load by creating an instance of it's class,  if it
         * doesn't we call the error handler
         **********************************************************************/
        if (file_exists($file)) {
            return new $controller;
        } else {
            $error = new error_handler();
            $error->missing_page();
            return false;
        }
    }

    private function get_parameters($parameter_array){
        if(isset($_POST)){
            //obtain the keys of the array
           $parameters = array_keys($_POST);
            extract($_POST);
        }
    }

}


