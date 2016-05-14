<?php

class View extends alpha {

    public $page ='';

    function __construct() {
        parent::__construct();
    }


    /**
     * function to render the a view for a controller using the name of the page specified by the constructor of the controller
     * @param $page string the name of the view to be rendered
     * @param null $layout the name of the layout to render the view in
     * @param bool $binding the name of the model you want to bind to the view
     */
    public function render($page,$layout=null,$binding=false){

        $this->page = $page;
        $file = "view/{$page}/index.php";
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
        if($binding){

        }

    }

    /**
     * function to call shared layout files from the layout folder
     * @param $file string the name of the file
     */
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

    /**
     * function to display the main content in our layout file
     */
    public function layout_body(){
        $body_file = "view/{$this->page}/index.php";
        try
        {
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

    public function htmlAnchor($controller, $link_name, $action_method = null, $param = null,$text=null) {
        //we will need to come up with a way to identify the domain name automatically
        if (is_null($param) && is_null($action_method)) {
            $href = "{$controller}";
        } else if (!is_null($action_method) && is_null($param)) {
            $href = "{$controller}/{$action_method}";
        } else {
            $href = "{$controller}/{$action_method}/{$param}";
        }

        return "<a href=\"" . DOMAIN_NAME . "{$href}\">{$link_name}{$text}</a>";
    }

    public function htmlIMG($imgPath, $class=array(), $alt = null) {
        return "<img class = \"".join(" ",$class)."\" src=\" " . DOMAIN_NAME. IMAGES . $imgPath . " \" alt= \"{$alt}\"/> \n";
    }

    /**
     * function to create a link tag to reference the specified file in your html
     * @param $filename string the name of the css file
     * @param null $rel
     */
    public function htmlLink ($filename,$rel=null) {
        $file_dir = DOMAIN_NAME.CSS."$filename";
        echo "<link rel =\"{$rel}\" type=\"text/css\" href=\"{$file_dir}\" media=\"screen\" /> \n";
    }


    /**
     * function to generate a script tag to call the specified script file
     * @param $filename string the name of the script file
     * @param null $type
     */
    public function htmlScript($filename,$type=null){
        $file_dir = DOMAIN_NAME.JS."$filename";
        echo "<script type=\"{$type}\" src=\"{$file_dir}\"></script>\n";
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
     * @description this function will display data stored in the variable passed to it in an appropriate
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



    function year(){
        echo "<option value=\"\" >Birth</option>";
        echo "<option value=\"\" >Year</option>";
        for ($x = 1950; $x <= 1998; $x++){
            echo "<option value=$x>$x</option>";
        }
    }

    function month(){
        $month = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $i = 0;
        echo "<option value=\"\" >Of</option>";
        echo "<option value=\"\" >Month</option>";
        for ($y = 0; $y < 12; $y++) {
            echo '<option value="'.++$i.'">'.$month[$y].'</option>';
        }
    }

    function day() {
        echo "<option value=\"\" >Date</option>";
        echo "<option value=\"\" >Day</option>";
        for ($x = 1; $x <= 31; $x++) {
            echo "<option value=$x>$x</option>";
        }
    }

}
