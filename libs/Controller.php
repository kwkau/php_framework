<?php
/*
 * we will need to create a way to connect our websockets to our controllers
 * so that our controllers can be used to create and accept connections on our
 * websockets. how the heck do we achieve that
 */

/**
 * Class Controller
 * <p>Enables your class to function as a Controller in this framework</p>
 */
class Controller extends alpha{
    private $cntrl_mdl;
    private $page = '';

    /**
     * Turns your class into a controller to allow it call views and perform other controller functions
     * @param $controller_name string the name of the controller
     */
    function __construct($controller_name){
        parent::__construct();
        $this->ini($controller_name);
        $this->session = new session('_k&a',false);
        $this->view = new View;
        $this->upload = new upload_engine;
        $this->model = !empty($controller_name)? $this->load_model($controller_name):null;
    }

    /**
     * function to initialize the controller upon startup
     * @param $pageName string the name of the controller which is also the name of the page
     */
    private function ini($pageName){
        $this->cntrl_mdl = $this->load_model("Controller");
        $this->page = $pageName;
    }


    /**
     * function to display the view of the current controller or another controller. a view
     * without a layout will be displayed if no arguments are provided
     * @param string $layout the name of the layout you want your view to be rendered with
     * @param object $model the model to be strongly bound to the view
     */
    public function view($layout=null,$model = null){
        //strongly bind a view to a model object
        $this->view->model = !empty($model) ? $model:null;
        //display a view with or without a layout
        $this->view->render($this->page,$layout);
    }

    public function select($name,$layout=null,$model=null)
    {
        //strongly bind a view to a model object
        $this->view->model = $model;
        //display a view with or without a layout
        $this->view->render($name,$layout);
    }


    /**
     * function to create a model instance of the model of the controller
     * @param string $name the name of the model
     * @param bool $etc the name of extra models to be loaded default value
     * is false meaning no extra models
     * return model this function returns a model instance
     */
    public function load_model($name, $etc=false){
        try
        {
            $path = "model/{$name}_mdl.php";
            if (file_exists($path)){
                require $path;
                $model_name = $name . '_mdl';
                return new $model_name;
            }else{
                throw new LoadModelException();
            }
        }catch (LoadModelException $model_load_err)
        {
            $this->controller_error_log($model_load_err,$name,'MODEL_LOAD_ERROR');
        }
        if ($etc)
        {
            $etc_path = 'model/' . $etc . '_mdl.php';
            if (file_exists($etc_path)){
                require $path;
                $etc_model_name = $etc . '_mdl';
                return new $etc_model_name;
            }
        }
    }

    /**
     * function to check whether the session of the current user is a valid session
     * @return bool true if the user session has been verified and false if it could not be verified
     */
    public function login_verify(){
        if (session::get("user_id") && session::get("login_string")){
            if(!$this->cntrl_mdl->l_verf(session::get("user_id"), session::get("login_string"), $_SERVER['HTTP_USER_AGENT'])){
                $this->redirect();
            }
        }else{
            $this->redirect();
        }
    }

    /**
     * this is an html helper function to validate a form
     * @param array $formArray
     * @return bool returns true if the form does not have any errors or problems and false otherwise
     */
    public function form_validation($formArray){
        foreach ($formArray as $field){
            if (empty($field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * function to redirect urls using routes. if no parameters are provided you
     * will be redirected to the home page
     * @param string $controller string the name of the controller
     * @param string $action string the name of the action method for the controller
     */
    public function redirect($controller = null,$action = null){
        if(is_null($controller) && is_null($action)){
            $redirect_path = "location:" . DOMAIN_NAME ;
        }else if(!is_null($controller) && is_null($action)){
            $redirect_path = "location:" . DOMAIN_NAME . "/{$controller}";
        }else if(!is_null($controller) && !is_null($action)){
            $redirect_path = "location:" . DOMAIN_NAME . "/{$controller}/{$action}";
        }else{
            $redirect_path = null;
       }
       if (!empty($redirect_path)) {
            header($redirect_path);
            exit();
        }
    }


    /**
     *function to provide information on the current instance of the server
     */
    public function server_dat (){
        print_r($_REQUEST);
    }

}

