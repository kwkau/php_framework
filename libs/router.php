<?php


class router {

    function __construct() {
        /*----------------------------------------------------------------------------------------------
         * we are sanitizing and preparing the the url, in sanitizing i mean removing the forward slash
         * and in preparing we are dividing the url into classes, functions and parameters
         * http://hbcmisup.dev:870/api/currencies
         * http://hbcmisup.dev:870/api/inventory/category/update
         *                         api/inventory/category/update
         *-------------------------------------------------------------------------------*/

        @$route = $this->get_route($_GET['app_url']);

        /*----------------------------------------------------------------------
         * we want to always display the home page when the url is either empty
         * or is home
         *----------*/
        if (empty($route['controller'])){
            //we are going home
            $home = new home();
            if(method_exists($home,"index"))
                $home->index();
            return false;
        }

        /*if (isset($url[3])) {
            $error = new error_handler();
            $error->missing_page();
            return false;
        }*/

        //obtain instance of the controller class
        $controller = $this->get_controller($route['controller']);

        /*-----------------------------------------------------------------
         * we are checking to se if the url contains a call for a function
         * or  a function and a parameter
         *------------------------------*/
        if (isset($route['parameter_list']) && !empty($route['parameter_list'])){
            if(method_exists($controller,$route['action_method'])){
                $controller->{$route['action_method']}($route['parameter_list']);
            }else{
                $this->missing_page();
            }
        }elseif(isset($route['param'])){//rest api parameter check
            if(method_exists($controller,$route['action_method'])){
                $controller->{$route['action_method']}(new Request(),$route['param']);
            }else{
                $this->missing_page();
            }
        }else if (isset($route['action_method']) && !empty($route['action_method'])){
            if(method_exists($controller,$route['action_method'])){
                //check if we are dealing with a custom route
                if(isset($route["custom"]) && $route["custom"]){
                    $controller->{$route['action_method']}(new Request());
                }else{
                    $controller->{$route['action_method']}();
                }
            }else{
                $this->missing_page();
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

        /*---------------------------------------------------------------------
         * we will first need to check for custom routes and then return their
         * mapping for their execution
         *---------------------------*/
        /*echo "<pre>";
       print_r(Route::$mappings);
       echo "</pre>";*/
        $maps = Route::$mappings;
        $req = new Request();
        $route = array();
        $route["request_method"] = $req->get_method();
        $url_cln = (isset($url)) ? explode('/', rtrim($url, '/')) : null;
        $count = count($url_cln);
        /*----------------------------------------------------------------------------------------
         * checking if the base address in our url matches one of the bases in our route mappings
         *--------------------------------------------------------------------------------------*/

        if(isset($url_cln[0]) && isset($maps[$url_cln[0]])){

            $target_routes = array();

            /*---------------------------------------------------------------------------
             * obtain all the routes that where assigned with the current request method
             * check if the url of this route has the same number of components as the
             * url we have received
             *--------------------*/
            foreach ($maps[$url_cln[0]] as $val) {
                if(array_search($req->get_method(),$val) && count(explode('/', rtrim($val["url"], '/'))) == $count){
                    $target_routes[] = $val;
                }
            }

            /*------------------------------------------------------------------
             * now we check the target routes for a possible match with our url
             *----------------------------------------------------------------*/
            foreach ($target_routes as $rte){
                $brk_dwn = explode('/', $rte["url"]);

                if($this->match_routes($brk_dwn, $url_cln)){
                    /*----------------------------------------
                     * we have successfully matched our route
                     *--------------------------------------*/
                    $route['controller'] = $rte["controller"];
                    $route['action_method'] = $rte["action"];

                    /*---------------------------------------------
                     * check our url if it contains ant parameters
                     *-------------------------------------------*/
                    $route["param"] = $this->param_check($brk_dwn, $url_cln);
                    $route["custom"] = true;
                    /*print_r($route);*/
                    return $route;
                }
            }
            $route["custom"] = true;
            return $route;
        }


        $route['controller'] = isset($url_cln[0]) ? $url_cln[0] : null;
        $route['action_method'] = isset($url_cln[1]) ? $url_cln[1] : null;


        for($i = 2; $i <= count($url_cln);$i++){
            $route['parameter_list'][] = isset($url_cln[$i]) ? $url_cln[$i] : null;
        }

        return $route;
    }

    private function get_controller($controller){
        $file = "controller/{$controller}.php";

        /*------------------------------------------------------------------
         * we are checking to see if the requested file in the url exists
         * if it does we load by creating an instance of it's class,  if it
         * doesn't we call the error handler
         *---------------------------------*/
        if (file_exists($file)) {
            return new $controller;
        } else {
            $error = new error_handler();
            $error->missing_page();
            return false;
        }
    }

    private function param_check($route_url,$request_url)
    {
        $parameter = false;
        foreach ($route_url as $key => $component) {
            if(strstr($component,":")){
                //this route url component is a parameter
                $_GET[ltrim($component,":")] = $request_url[$key];
                $parameter = true;
            }
        }
        return $parameter;
    }

    private function match_routes($route_url, $request_url)
    {
        foreach ($route_url as $key => $component) {
            if(!strstr($component,":")){//check if the component is a parameter
                if($component != $request_url[$key]) return false;
            }
        }
        return true;
    }

    private function missing_page()
    {
        header("location:http://". DOMAIN_NAME ."/error_handler/missing_page");
        exit();
    }


}


