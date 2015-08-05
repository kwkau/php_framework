//services
var host = 'localhost';
Controller.service("SharedResource", function (resource) {
    return resource("http://"+host+"/usap/dropzone/:operation",
        {operation: "initialise"},
        {
            testService: {
                method: "POST",
                params: {
                    operation: "test"
                }
            }
        }
    )
});