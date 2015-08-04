<?php

class hcms_ini {
    private $pages = array();
    private $main_containers = array();
    private $sub_containers = array();
    public function __construct(){
        //we need access to the database that means we need a database instance
        $this->db = new database("pdo");
        $this->load_data();
        $this->insert_data();
    }

    /**
     * @description function to fetch config data from the HCMS config file hcms_config.json
     *
     */
    private function load_data(){
        try{
            $config_json = fread(fopen( __DIR__.'/hcms_config.json','r'),4*1024);
        }catch (Exception $ex){
            die("We are very sorry for the inconvenience caused but we are having problems with our server, please try again later");
        }
        $data = (array)json_decode($config_json);

        foreach ($data as $page) {

            /*obtain page name*/
            $s_p = (array)$page;
            $this->pages[] = $s_p["name"];
            /*obtain main containers*/
            $containers = (array)$s_p["containers"];
            foreach ($containers as $std_obj) {
                $main_container = (array)$std_obj;
                $this->main_containers[] = array(
                    "page_name" => $s_p["name"],
                    "tag" => $main_container["tag"],
                    "content_type" => $main_container["content_type"]
                );
                $sub_containers = @(array)$main_container["sub_container"];
                /*obtain sub containers*/
                foreach ($sub_containers as $std_obj1) {
                    $sub_container = (array)$std_obj1;
                    $this->sub_containers[] = array(
                        "main_container_tag" => $main_container["tag"],
                        "tag" => $sub_container["tag"],
                        "content_type" => $sub_container["content_type"],
                        "page_name" => $s_p["name"]
                    );
                }
            }

        }

    }

    /**
     * @description function to insert hcms config data into the hcms database
     *
     */
    private function insert_data(){
        $sth_check = $this->db->prepare("SELECT * FROM hcms.page,hcms.page_main_container,hcms.page_sub_container");
        $sth_check->execute();
        //check to see if the the hcms dashboard tables are empty
        if($sth_check->rowCount() <= 0 && HCMS){
            //if empty HCMS is set to true, go ahead and insert hcms page data into hcms database
            foreach($this->pages as $key => $pageName) {
                //insert pages parameter list(in pagename varchar (45))
                $sth_hcms = $this->db->prepare("CALL hcms.insert_page(:name)");
                $sth_hcms->execute(array(
                    ":name" => $pageName
                ));

            }
            foreach ($this->main_containers as $mc) {
                //insert main container parameter list(in tag varchar (45),in pagename varchar (45),in content_type varchar (45))
                $sth_hcms = $this->db->prepare("CALL hcms.insert_main_container(:tag,:name,:content_type)");
                $sth_hcms->execute(array(
                    ":tag" => $mc["tag"],
                    ":name"=> $mc["page_name"],
                    ":content_type" => $mc["content_type"]
                ));
            }


            foreach ($this->sub_containers as $sc) {
                //insert sub container parameter list(in tag varchar (45),in main_container_tag varchar (45),in pagename varchar (45), in content_type varchar (45))
                $sth_hcms = $this->db->prepare("CALL hcms.insert_sub_container(:tag,:main_container_tag,:name,:content_type)");
                $sth_hcms->execute(array(
                    ":tag" => $sc["tag"],
                    ":main_container_tag" => $sc["main_container_tag"],
                    ":name"=> $sc["page_name"],
                    ":content_type" => $sc["content_type"]
                ));
            }



        }
    }
}