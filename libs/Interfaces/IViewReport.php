<?php
const LAYOUT_ERROR = 1;
const DISPLAY_ERROR = 2;
const SHARED_ERROR = 3;


interface IViewReport {
    function view_error_log(Exception $error_info,$page,$view_error_type);
} 