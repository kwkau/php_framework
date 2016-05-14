<?php

class alpha_mdl extends Model{

    function __construct(){
        parent::__construct();
    }

    public function model_log_err(Exception $error_info,$query=null, $params=array()){
        $date = $this->dt->get_date('date.timezone');
        $this->pdo_insert("insert into error_log (error_type,created_at,user_ip,user_port,uri,sswap_module) values ('?','?','?','?','?','?')",
            array('MODEL_ERROR',$date,$_SERVER['REMOTE_ADDR'],$_SERVER['REMOTE_PORT'],$_SERVER['REQUEST_URI'],'Model'));

        $result = $this->pdo_fetch('select error_log_id from error_log where created_at = ?',
            array($date),PDO::FETCH_ASSOC);

        $this->pdo_insert("insert into model_error (error_line,error_file,error_code,error_msg,error_stackTrace,query,parameters,error_log_id)values ('?','?','?','?','?','?','?','?')",
            array($error_info->getLine(),$error_info->getFile(),$error_info->getCode(),$error_info->getMessage(),$error_info->getTraceAsString(),$query,join(' : ',$params),$result['error_log_id']));
    }

    public function view_log_err(Exception $error_info,$page,$view_error_type){
       /* $date = $this->dt->get_date('date.timezone');
        $this->pdo_insert("insert into error_log (error_type,created_at,user_ip,user_port,uri,sswap_module) values ('?','?','?','?','?','?')",
            array('VIEW_ERROR',$date,$_SERVER['REMOTE_ADDR'],$_SERVER['REMOTE_PORT'],$_SERVER['REQUEST_URI'],'View'));

        $result = $this->pdo_fetch('select error_log_id from error_log where created_at = ?',
            array($date),PDO::FETCH_ASSOC);

        $this->pdo_insert("insert into view_error (error_line,error_file,error_code,error_msg,error_stackTrace,page_error,view_error_type,error_log_id)values ('?','?','?','?','?','?','?','?','?','?')",
            array($error_info->getLine(),$error_info->getFile(),$error_info->getCode(),$error_info->getMessage(),$error_info->getTraceAsString(),$page,$view_error_type,$result['error_log_id']));
    */}

    public function controller_log_err(Exception $error_info,$controller_name,$controller_error_type){
        /*$date = $this->dt->get_date('date.timezone');
        $this->pdo_insert("insert into error_log (error_type,created_at,user_ip,user_port,uri,sswap_module) values ('?','?','?','?','?','?')",
            array('VIEW_ERROR',$date,$_SERVER['REMOTE_ADDR'],$_SERVER['REMOTE_PORT'],$_SERVER['REQUEST_URI'],'View'));

        $result = $this->pdo_fetch('select error_log_id from error_log where created_at = ?',
            array($date),PDO::FETCH_ASSOC);

        $this->pdo_insert("insert into controller_error (error_line,error_file,error_code,error_msg,error_stackTrace,controller_name,controller_error_type,error_log_id)values ('?','?','?','?','?','?','?','?','?','?')",
            array($error_info->getLine(),$error_info->getFile(),$error_info->getCode(),$error_info->getMessage(),$error_info->getTraceAsString(),$controller_name,$controller_error_type,$result['error_log_id']));*/
    }
} 