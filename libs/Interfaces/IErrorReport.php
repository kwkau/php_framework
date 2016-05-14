<?php

interface IErrorReport {
    function model_error_log(Exception $error_info,$query = null,$params = null);
    function view_error_log(Exception $error_info,$page,$view_error_type);
    function controller_error_log(Exception $error_info,$controller_name,$controller_error_type);
} 