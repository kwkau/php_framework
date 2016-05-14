<?php

class item extends Model{
    protected $relations = array(
        array(
            "table" => "category",
            "type" => Model::ONE_TO_ONE,
            "foreign_key_location" => "item",
            "foreign_key" => "category_id"
        )
    );
}