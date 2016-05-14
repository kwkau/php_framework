<?php

class RouteFactory {

    public function __construct()
    {
        //http routes
        //register route follow to an base url
        Route::base("api");
        //attendance routes
        Route::post("api/item","item", "add");
        Route::get("api/item/:id","item", "get");
        Route::get("api/items","item", "lyst");
        Route::put("api/item","item", "update");
        Route::delete("api/item/:id","item", "delete");


        //websocket routes
        WSRoute::channel("test_channel");
        WSRoute::register("test_data","test","response");
    }

}