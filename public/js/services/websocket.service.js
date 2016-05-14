/*Globally Unique Identifier (GUID) generator.*/
Math.guid = function(){
    return 'xxxxxxxx-xxxx-7xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);return v.toString(16);}).toUpperCase();
};
app.service("WebSocketService",[function () {
    return{
        cache:{},
        reconnected: false,
        handlers:[],
        post: function(data,data_type,func){
            func = func || function(){};
            if(this.check()){//check to see if we have created and opened a socket
                var packet = JSON.stringify({"channel":this.channel, "data_type":data_type ,"payload":data});
                /*alert(packet)*/
                this.sckt.send(packet);
                func.apply(this);
            }else{
                alert("reconnecting...");
                this.reconnect();
                /*this.post(data,data_type,func);*/
            }

        },
        timestamp: function () {
            var date = new Date();
            //2015-02-13 12:34:56
            return date.getFullYear() +"-"+ (date.getMonth()+1) +"-"+date.getDate()+" "+date.getHours()+":"+date.getMinutes()+":"+date.getSeconds();
        },
        chck_data:function(data){
            var packet = JSON.parse(data);
            return packet.channel == this.channel?packet:false;
        },
        signal: function (socket_type, data) {
            //obtain the socket we want to communicate with
            var socket;
            if (socket = this.parent.records[socket_type]) {
                socket.post(data);
            } else {
                throw("the socket that you are trying to signal does not exist");
            }

        },
        start: function(obj){
            /*
             websocket object properties and functions
             readyState
             bufferedAmount
             extensions
             protocol
             binaryType: blob
             CONNECTING: 0 1
             OPEN:  0 1 2 3
             CLOSING: 0 1 2 3
             CLOSED: 0 1 2 3
             * */
            this.mappings = obj.mappings;
             //create a new socket and add it to our socket object
            if("WebSocket" in window){
                this.sckt = new WebSocket(obj.url);
                this.url = obj.url;
                //now that we have our socket object we set our socket methods
                var that = this;

                this.sckt.onopen = $.proxy(obj.onOpen, obj.scope||this.sckt) || function(data){};

                this.sckt.onmessage = function (o) {
                    var packet = JSON.parse(o.data);
                    if(packet.channel = that.channel){//packet is valid for this channel
                        //map the data to the correct function in our mappings object

                        //Todo:implement a better way of executing our mapped data type functions
                        that.mappings[packet.data_type](packet);
                    }
                };
                this.sckt.onclose = $.proxy(obj.onClose, obj.scope||this.sckt) || function(){};
                this.sckt.onerror = $.proxy(obj.onError, obj.scope||this.sckt) || function(){};

                //next we set our socket metadata
                this.channel = obj.channel || "default";
                this.open = this.sckt.OPEN == 1;
                this.scope = obj.scope || this.sckt;
                this.created_at = this.created_at || this.timestamp();

                //we need to give each socket a unique id for identification
                if(!this.id){
                    this.id = Math.guid();
                    /*this.parent.records[this.channel] = this; this.cache = obj*/
                }
                return this;
            }else{
               /*
                * should we implement logic to fall back to flash or we should
                * let the users handle that themselves
                * */
            }
        },
        bind: function(event,handler){
            if(this.check()){
                this.handlers[event] = handler;
            }
        },
        fire: function(event,packet){
            if(this.check()){
                this.handlers[event].call(this.handlers[event],packet);
            }
        },
        remove_ev: function(event){
            if(this.check()){
                this.handlers[event] = null;
            }
        },
        check: function(){
            return this.open && this.sckt.readyState == 1;
        },
        close: function(){
            if(this.check()){
                this.sckt.close();
            }
        },
        reconnect: function () {
            if(!this.check()){
                this.start({
                    url: this.url,
                    channel: this.channel,
                    scope: this.scope,
                    mappings: this.mappings
                });
                this.reconnected = true;
            }
        }
    }
}]);