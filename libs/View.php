<?php
/**
 * Class View
 */
class View extends alpha {

    public $page ='';
    public $model;

    /**
     * Provides access to views
     */
    function __construct() {
        parent::__construct();
    }

    /*------------------------
     * view display functions
     *-----------------------*/

    /**
     * function to fetch the view for a page
     * @param $page string the name of the page who's view you want to fetch
     * @param null $layout the name of the layout you want to be rendered with the view
     */
    public function render($page,$layout=null){
        $file = "view/{$page}/index.php";
        $this->page = $page;
        if(!empty($layout)) {
            try
            {
                //render page with a layout
                if(file_exists($layout_file = "view/shared/{$layout}.php")){
                    require $layout_file;
                }else{
                    throw new LayoutException;
                }
            }catch(LayoutException $layout_error){
                $this->page_error($layout_error,'LAYOUT_ERROR',$layout_file);
            }
        }else{
            try
            {
                //render page without layout
                if (file_exists($file)){
                    require $file;
                }else{
                    throw new PageException;
                }
            }catch (PageException $page_error){
                $this->page_error($page_error,'PAGE_ERROR',$file);
            }
        }



    }

    public function shared($file){
        if(is_dir($file) && file_exists($file)){
            require $file;
        }else{
            try
            {
                if (file_exists("view/shared/{$file}.php")){
                    require "view/shared/{$file}.php";
                }else{
                    throw new PageException;
                }
            }catch(PageException $page_error){
                $this->page_error($page_error,'PAGE_ERROR',"view/shared/{$file}.php");
            }
        }
    }

    public function layout_body($body_page){
        try
        {
            $body_file = "view/{$body_page}/index.php";
            if(file_exists($body_file)){
                require $body_file;
            }else{
                throw new PageException;
            }
        }catch(PageException $page_error){
            $this->page_error($page_error,'PAGE_ERROR',$body_file);
        }
    }

    public function display_uniq($page){
        try
        {
            $file = "view/{$page}";
            if (file_exists($file)){
                require $file;
            } else {
                throw new PageException;
            }
        }catch (PageException $page_error){
            $this->page_error($page_error,'PAGE_ERROR',$file);
        }
    }

    private function page_error(Exception $err_info,$view_error_type,$page){
        $error = new error_handler();
        $this->view_error_log($err_info,$page,$view_error_type);
        $error->missing_page();
        return false;
    }




    /*----------------------------------
        * html helper functions
        *-----------------------------------*/

    /**
     *
     * @param array $dict
     * @return HtmlPrps
     */
    public function setprps($dict = array()){
        $prps = new HtmlPrps();
        foreach($dict as $key => $val){
            $prps->{$key} = $val;
        }
        return $prps;
    }

    public function htmlAnchor($controller, $link_name, $action_method = null, $param = null) {
        //we will need to come up with a way to identify the domain name automatically
        if (is_null($param) && is_null($action_method)) {
            $href = "{$controller}";
        } else if (!is_null($action_method) && is_null($param)) {
            $href = "{$controller}/{$action_method}";
        } else {
            $href = "{$controller}/{$action_method}/{$param}";
        }

        return "<a href=\"" . DOMAIN_NAME . "/{$href}\">{$link_name}</a>";
    }

    public function htmlIMG($imgPath, $class=array(), $alt = null) {
        return "<img class = \"".join(" ",$class)."\" src=\" " . IMAGES . $imgPath . " \" alt= \"{$alt}\"/> \n";
    }

    public function htmlLink ($filename,$rel=null) {
        $file_dir = CSS."$filename";
        return "<link rel =\"{$rel}\" type=\"text/css\" href=\"{$file_dir}\" media=\"screen\" /> \n";
    }


    public function htmlScript($filename,$type=null){
        $type = !empty($type)? $type:'text/javascript';
        $file_dir = JS."$filename";
        return "<script type=\"{$type}\" src=\"{$file_dir}\"></script>\n";
    }


    public function htmlForm(htmlPrps $htmlprps,$fields){

        /*$model = new RedBean_SimpleModel();
        $model_properties = $this->inspector->getClassProperties($model);*/

    }


    /**
     * this function will display data stored in the variable passed to it as html, depending on
     * what kind of data is stored in it
     * @param $entity
     */
    public function htmlDisplay($entity){
        if(is_array($entity)){
            echo "<pre>";
            foreach($entity as $key => $value){
                echo $value;
            }
            echo "</pre>";
        }else{
            echo "<text type=\"text\">{$entity}</text>";
        }
    }



    /**
     * this function will display data stored in the variable passed to it in an appropriate
     * html form element, depending on what kind of data is stored in it
     * @param $entity
     * @param null $name
     * @param null $class
     */
    public function htmlFormElement($entity,$name=null,$class=null){
        if(is_array($entity)){
            echo "<select name = \"{$name}\" class=\"{$class}\">";
            foreach($entity as $key => $value){
                echo "<option value=\"{$value}\">{$key}</option>";
            }
            echo "</select>";
        }else{
            echo "<input type=\"text\" value=\"{$entity}\" name=\"{$name}\" class=\"{$class}\" />";
        }
    }



    function year($start,$end){
        for ($x = $start; $x <= $end; $x++){
            echo "<option value=$x>$x</option>";
        }
    }

    function month(){
        $month = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $i = 0;
        echo "<option value=\"\" >Month</option>";
        for ($y = 0; $y < 12; $y++) {
            echo '<option value="'.++$i.'">'.$month[$y].'</option>';
        }
    }

    function day() {
        echo "<option value=\"\" >Day</option>";
        for ($x = 1; $x <= 31; $x++) {
            echo "<option value=$x>$x</option>";
        }
    }


    /*---------------------------
     * hcms data fetch functions
     *--------------------------*/

    /**
     * function to fetch the content of main or sub page containers
     * @param $mainContainerTag string  the name of the main container
     * @param $mcContentType string  the type of data that the main container holds(text or picture or video)
     * @param null $scContentType string  the type of data that the sub container holds(text or picture or video),
     * if this parameter is null then sub containers will not be fetched but if it is not null then sub containers will
     * be fetched along with its main containers
     * @return array returns a array of data which are the contents of the main container and its sub container if present
     */
    public function container_fetch($mainContainerTag,$mcContentType,$scContentType = null) {

        if(!empty($scContentType)){
            /*fetch both a main container together with its sub containers*/
            return $this->model->main_sub_fetch($mainContainerTag, $mcContentType, $scContentType, $this->page);
        } else {

            return $this->model->singular_fetch($mainContainerTag,$mcContentType,$this->page);
        }

    }

}

