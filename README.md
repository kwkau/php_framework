# agile php_framework

this is a php framework i created whiles working on project work for final year. i took a lot of inspiration from asp.net mvc 4.0  and also from laravel and as such you will find a lot of structures and builds similar to the frame works mentioned above. agile fully supports MVC architecture.

i added a few other things also such as support for websockets and a mini ORM which has support for relations between table.

the main aim of this framework is rapid development with focus on reducing the number lines you need to write to achieve a task, from this aim users of this framework can achieve a lot with just one line for example creating a model and using that model to insert a record into a database

#example

<?php

//creating your item model to insert items into your db

class item extends Model{}

?>

<?php

class items extends Controller{

  public function add_item(Request $request){
  
    Response::json(item::instance()->populate($request->json_content())->insert());
    
  }
  
}

?> 

# default api CRUD functions
agile presents you will pre written CRUD API functions for REST API meaning you dnt need to write over and over again code to insert, update or delete data in your database its already done; all you have to do is create your rest api controllers then you point your routes to them and the Controller base class that your REST API controller inherits from takes care of the rest

#example

<?php

//REST API controller

class items extends Controller{

	public function __construct(){
		
		//specify the name of the model you want to bind to this controller
		
		parent::__construct("item");
		
		}
		
	}
	
}
?>

the REST API controller items that we just created above can perform all the CRUD operations with your db without the need for you to write any other code, its already been written for you. now the next step is to point our route to this controller

<?php

class RouteFactory {

    public function __construct(){
    
        //http routes
        
        //register route follow to an base url
        
        Route::base("api");
        
        //items routes
        
        Route::post("api/item","items", "add");
        
        Route::get("api/item/:id","items", "get");
        
        Route::get("api/items","items", "lyst");
        
        Route::put("api/item","items", "update");
        
        Route::delete("api/item/:id","item", "delete");
      }
      
    }
    
  }
  
  now your REST API controller is ready to respond to REST API calls and also perform CRUD operations with the data it receives from those calls;
  
  
#connecting to a database
currently agile has support only for mysql and postgres, looking to add support for MSSQL. to connect to database you will need to provide these details in the config.json file which you in the config folder, DB_TYPE, HOST_NAME, DB_USER, DB_PASSWORD and DB_NAME
  
  
#example
"server": {
  
        "DB_TYPE":"mysql",
        
        "HOST_NAME" : "localhost",
        
        "DB_USER" : "root",
        
        "DB_PASS" : "",
        
        "DB_NAME":"test_db",
        
        "HOST_URL" : "http://localhost/agile",
        
        "DOMAIN_NAME" : "http://localhost/agile",
        
        "DIGEST":"9015432640eb37b1f1343ef387cd3e89d6dbc48b38a1e3a9e5fb8d2543ef770207afcd50063900de672a96d9f45129ab8fc7d91f9267bb8178bda1b400a6688f",
        
        "ADMIN_EMAIL":"admin@gmail.com",
        
        "PORT": 1250,
        
        "ENCRYPT" : false
        
    }
