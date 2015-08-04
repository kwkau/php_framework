<?php
/**
 * Created by PhpStorm.
 * User: kwaku
 * Date: 7/25/14
 * Time: 10:32 PM
 */

interface IModelReport{
    function model_error_log(Exception $error_info,$query=null,$params=null);
} 