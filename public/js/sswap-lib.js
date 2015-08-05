/*controller*/
// Use global context, rather than the window
// object, to create global variables
/*global events*/
/*
* we need to create a filter for sorting of long lists of items
* how are we going to achieve this?
*1.we will need a filter parameter
*   - the filter parameter will store the will be used to perform the filtering of the list of items

};*/

"use strict";
var SWsocket = {
    records: [],
    inherited: function () {},
    created: function(){},
    prototype : {
        init : function(){}
    },
    create: function(url,type){
        var socket = Object.create(this.prototype);
        socket.parent = this;
        socket.url = url;
        socket.type = type;
        return socket;
    },
    include: function(o){
        var included = o.included;
        for(var i in o)this.prototype[i] = o[i];
        if(included)included(this);
    },
    extend: function(o){
        var extended = o.extended;
        for(var i in o)this[i] = o[i];
        if(extended)extended(this);
    }
};

SWsocket.include({
    cache:{},
    reconnected: false,
    handlers:[],
    post: function(data,func){
        func = func || function(){};
        if(this.check()){//check to see if we have created and opened a socket
            var packet = JSON.stringify({"channel":this.type, "payload":data});
            /*alert(packet)*/
            this.sckt.send(packet);
            func.apply(this);
        }else{
            alert(this.type+" is experiencing some problems, please try again later")
        }

    },
    timestamp: function () {
        var date = new Date();
        //2015-02-13 12:34:56
        return date.getFullYear() +"-"+ (date.getMonth()+1) +"-"+date.getDate()+" "+date.getHours()+":"+date.getMinutes()+":"+date.getSeconds();
    },
    chck_data:function(data){
        var packet = JSON.parse(data);
        return packet.channel == this.type?packet:false;
    },
    signal: function (socket_type, data) {
        //obtain the socket we want communicate with
        if(this.parent.records[socket_type]){
            var socket = this.parent.records[socket_type];
            socket.post(data);
        }else{
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
        //create a new socket and add it to our socket object
        this.sckt = new WebSocket(this.url);
        //now that we have our socket object we set our socket methods
        
        this.sckt.onopen = $.proxy(obj.onopen,obj.scope||this.sckt) || function(data){};
        this.sckt.onmessage = $.proxy(obj.onmessage,obj.scope||this.sckt) || function(data){};
        this.sckt.onclose = $.proxy(obj.onclose,obj.scope||this.sckt) || function(){};
        this.sckt.onerror = $.proxy(obj.onerror,obj.scope||this.sckt) || function(){};

        //next we set our socket metadata
        this.type = this.type || "default";
        this.open = this.sckt.OPEN == 1;
        this.created_at = this.created_at || this.timestamp();

        //we need to give each socket a unique id for identification
        if(!this.id){ this.id = Math.guid();
        this.parent.records[this.type] = this; this.cache = obj}
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
            this.sckt.close();
            this.reconnected = true;
        }
    }
});

var Global = function () {
    var mod = {
        items:[],
        set_item: function (key, val) {
            this.items[key] = val;
        },
        get_item: function (key) {
            if(this.items[key]){
                return this.items[key]
            }
            return false;
        }
    }
    return mod;
}()

var Controller = {
    records: [],
    bindings: {},
    props : [],
    cntrl_vars:[],
    resources: {},
    inherited: function(){},
    created: function(){},
    prototype: {
        init:function(){}
    },
    create: function(name,obj) {
        window.scrollTo(0,0);
        this.search();
        var instance = Object.create(this.prototype);
        instance.name = name;
        instance.parent = this;
        this.instance = instance;
        this.load_data(instance);
        this.include(obj,instance);
        this.load_res(instance);
        instance.set_selectors();
        instance.ld_events();
        this.set_glblevents(instance);
        instance.init.apply(instance, arguments);
        return instance;
    },
    extend: function(o) {
        var extended = o.extended;
        for(var i in o)this[i] = o[i];
        if(extended) extended(this);
    },
    include: function(o,inst) {
        var included = o.shared;
        o.shared = {};
        for(var i in o) this.prototype[i] = o[i];

        if(included){
            //adding shared methods
            for(var j in included.methods) this.prototype[j] = included.methods[j];

            //adding shared props
            for(var y in included.properties) {
                inst[y] = inst[y] || {};
                if(y == 'elements'){ //adding shared elements
                    for(var l in included.properties[y]) inst[y][l] = included.properties[y][l];
                }else if(y == 'events'){ //adding shared events
                    for(var x in included.properties[y]){
                        inst[y][x] = inst[y][x]||[];
                        for(var k in included.properties[y][x]) inst[y][x].push(included.properties[y][x][k]);
                    }
                }

            }
        }
    },
    set_glblevents: function(inst){
/*
        model event to sync changes in the model with the view:
        triggered by model functions that will make change to the stats of a model
*/
        swev.set("model_view_sync", jQuery.proxy(inst.model_view_sync,inst));

        swev.set("view_model_sync",jQuery.proxy(inst.view_model_sync,inst));

        swev.set("model_prop_change",jQuery.proxy(inst.model_prop_change,inst))
    }
};



Controller.include({
    tmpls: {},
    views: {},
    uguid: function () {
        var d = new Date();
        return Math.guid()+"-"+ d.getDay()+""+ d.getMonth()+""+d.getYear();
    },
    timestamp: function () {
        var date = new Date();
        //2015-02-13 12:34:56
        return date.getFullYear() +"-"+ (date.getMonth()+1) +"-"+date.getDate()+" "+date.getHours()+":"+date.getMinutes()+":"+date.getSeconds();
    },
    model_prop_change: function (data) {
        //obtain template and view
        var prop_tmpl = this.parent.props['tmpl'][data.prop_name],
            prop_view = this.parent.props['view'][data.prop_name],
            parent_view = this.parent.props['view'][data.model_name],
            model = window[data.model_name];

        //render prop data in parent view
        var target_view;
        if(data.noti_render){
            target_view = data.noti_container;
        }else{
            target_view = parent_view.children().eq(data.view_index).find("*[data-sw-editable='"+prop_view.data("swEditable")+"']");
        }


        this.append(data.prop_data,target_view,prop_tmpl,data.position||"bottom");

    },
    container_process: function (container, data, model_name,apnd) {
        data = Array.isArray(data)?data:[data];
        var container_model = container.data("swContainer").split(":"),i;
        if(apnd){
            if(Array.isArray(container_model)){
                i = container_model.indexOf(model_name);
                this.append(data,container,this.parent.props['tmpl'][container_model[i]],"bottom");
            }else{
                this.append(data,container,this.parent.props['tmpl'][container_model],"bottom");
            }
        }else{
            if(Array.isArray(container_model)){
                i = container_model.indexOf(model_name);
                this.apply(data,container,this.parent.props['tmpl'][container_model[i]]);
            }else{
                this.apply(data,container,this.parent.props['tmpl'][container_model]);
            }
        }

    },
    watch : function(prop,handler){
        //todo: we will need to improve our watch function so as to allow event tripping
        var oldval = this[prop], newval = oldval,
            getter = function(){
                return newval;
            },
            setter = function(val){
                oldval = newval;
                return newval = handler.call(this,prop,oldval,val);
            };
        if(delete this[prop]){//cant watch constants
            if(Object.defineProperty){
                Object.defineProperty(this,prop,{
                    get:getter,
                    set:setter
                });
            }else if(Object.prototype.__defineGetter__ && Object.prototype.__defineGetter__){
                Object.prototype.__defineGetter__.call(this,prop,getter);
                Object.prototype.__defineSetter__.call(this,prop,setter);
            }
        }
    },
    //repeated ajax asynchronous calls
    repeat : function (obj) {
        var that = this;
        return $.ajax({
            url: obj.url,
            data: obj.data,
            success: function (data) {
                obj.success(data);
            },
            complete: function (data) {
                if (obj.complete) obj.complete(data);
                //settimeout
                setTimeout($.proxy(function () {
                    that.repeat(obj)
                }, that), obj.interval);
            }
        }).fail(obj.fail);
    },
    post : function (obj) {
        return $.ajax({
            url: obj.url,
            data: obj.data,
            type: 'post'
        }).done(obj.success).fail(obj.fail);
    },
    chain : function (deffered, chainee) {
        return deffered.pipe(chainee);
    },
    proxy : function (func){
        return $.proxy(func, this);
    },
    toArray : function (obj) {
        var ret = [];
        for (var i in obj)ret.push(obj[i]);
        return ret;
        
    },
    apply : function (data,elem,tmpl) {
        if(elem){
            elem.html(doT.template(tmpl)(data));
            elem.children().each(function(index,value) {
                $(this).data("ele_data",data[0]);
            });
        }
    },
    append : function(data,elem,tmpl,pos) {
        if(pos =="bottom" && elem){
            elem.append(doT.template(tmpl)(data));
        }else if(pos =="top" && elem){
            elem.prepend(doT.template(tmpl)(data));
        } else {
            /*throw Error("the append method specified is not recognised");*/
        }

    },
    set_selectors : function () {
        if (this.elements) {
            for (var i in this.elements)
                this[i] = this.$(this.elements[i]);
        }
        this['window'] = this.$(window);
        this['document'] = this.$(document);
    },
    /*
    * instance function to set events to their respective elements by using the events
    * object provided by the user
    * */
    ld_events : function () {
        var data,event_type;
        for(var i in this.events){
            if(this.events[i].length){
                for(var y in this.events[i]){
                    event_type = this.events[i][y].type || "default";
                    data = this.events[i][y].data||{};
                    data['that'] = this;
                    if(event_type == "default" || event_type == "on"){
                        this[this.events[i][y].target].on(i,data, this[this.events[i][y].handler]);
                    }else if(event_type == "delegate"){
                        this[this.events[i][y].target].delegate(this.events[i][y].selector,i, data, this[this.events[i][y].handler]);
                    }else if(event_type == "once"){
                        this[this.events[i][y].target].one(i,data, this[this.events[i][y].handler]);
                    }
                }
            } else{
                event_type = this.events[i].type || "default";
                data = this.events[i].data||{};
                data['that'] = this;
                if(event_type == "default" || event_type == "on"){
                    alert("target: " + this.events[i].target)
                    this[this.events[i].target].on(i, data, this[this.events[i].handler]);
                }else if(event_type == "delegate"){
                    this[this.events[i].target].delegate(this.events[i].selector,i, data, this[this.events[i].handler]);
                }else{
                    throw Error("the event type specified is not supported by the framework, our supported event type\n" +
                    "is as folows, delegate, on")
                }
            }
        }
    },
    $ : function (selector) {
        return jQuery(selector);
    },
    /* *
     * prop_change event callback to sync changes in the state of a model with its bound view
     * */
    model_view_sync: function(model) {
        var disp_mod = model.display_mode.split(":");
        if(disp_mod[0] == "append") {
            this.append(model.records,this.views[model.name],this.tmpls[model.name],disp_mod[1]);
        } else if (disp_mod[0] == "apply") {
            this.apply(model.records,this.views[model.name],this.tmpls[model.name]);
        } else {
            throw Error("sync error you must specify a display_mode property on your model for either appending or applying data to your view");
        }
        model.firing = false;
    },
    /* *
     * view_change event callback to sync changes in the state of a view with its bound model
    * */
    view_model_sync:function(instance){
        var model = instance.parent_name;
        this.apply(model.records,this.views[model.name],this.tmpls[model.name]);
    },

    /* *
     * filter event to filter a set of matched elements when the filter event is triggered
    * */
    view_filter: function(){
        //how do we filter a view?
        //do we filter it by using the view itself or we sort the data in the model and apply it to the view?
        //which process will be more beneficial to use and will also offer us more speed?

    },
     /*---------------------------
      javascript helper functions
     *---------------------------*/

    //form validation
    form_validate : function(form) {
        form.on('submit',{that:this},this.submitHandler);/*check to see if all the fields of the form are valid for submission*/
        /*form fields other than password check using the blur handler*/
        form.find('input[type="email"],input[type="text"],select,textatrea').on('blur',{that:this,type:'fields'}, this.blurHandler);
        /*password check using the blur handler*/
        form.find('input[type="password"]').on('blur', {that:this,type:'password'}, this.blurHandler);
    },
    blurHandler: function(e){
        if(e.data.type == 'password'){
            if(e.data.that.isEmpty(this) || $(this).val().length < 6){
                e.data.that.write($(this));
            } else {
                e.data.that.remove($(this));
            }
        }else{
            if(e.data.that.isEmpty(this)){
                e.data.that.write($(this));
            } else {
                e.data.that.remove($(this));
            }
        }
    },
    form_check: function (form) {
        var that = this,
        count = 0;
        form.find('input,select,textarea').not("input[type=hidden]").each(function(){

            if(that.isEmpty(this)){
                ++count;
            }
        });
        //if count is zero there are no empty fields that means this func will return true if count is zero
        return count == 0;
    },
    form_reset: function (form) {
        var that = this;
        form.find('input,select,textarea').each(function(){
            $(this).val("");
        });
    },
    write: function(field){
        this.remove(field);
        field.addClass("usap-error");
    },
    submitHandler: function(e) {

        var count = 0,pass1,pass2 = null,
            that = e.data.that,
            form = $(this);

        /*input field of type text validation*/
        form.find('input[type="text"],select,input[type="email"],textarea').each(function(){
            if(that.isEmpty(this)){

                ++count;
                that.write($(this));
            }
            //index number validation
            if($(this).attr("name") == "index_number"){
                if($(this).val().search(/[a-zA-Z]{2,3}\/[a-zA-Z]{3}\/[0-9]{2,3}\/[0-9]/i) < 0){
                    ++count;
                    that.write($(this));
                }
            }
        });


        /*input field of type password validation*/
        form.find('input[type="password"]').each(function(){
            if(form.find('input[type="password"]').length >1){
                var hash;
                pass1 = $(this).val();
                if(pass2 != null){
                    if(pass2 != pass1 || (pass1.length < 6 || pass2.length < 6)){
                        ++count;
                        that.write($(this));
                        pass2 = null;
                    }else{
                        hash = form.find('input[name*="-hash"]');
                        if(hash.val().length == 0){

                            hash.val(that.hexMD5w(form.find('input[type="password"]').val()));
                        }
                    }
                }
                pass2 = pass1;
            }else{
                if(that.isEmpty(this) || $(this).val().length < 6){
                    ++count;
                    that.write($(this));
                }else{
                    hash = form.find('input[name*="-hash"]');
                    if(hash.val().length == 0){

                        hash.val(that.hexMD5w(form.find('input[type="password"]').val()));
                    }
                }
            }
        });
        return count == 0;
    },
    remove: function(field){
        field.removeClass("usap-error");
    },
    isEmpty: function(field){
        if($(field).val() == null)return false;
        return ($(field).val().search(/\w/) < 0 || $(field).val().search(/[a-zA-Z0-9!@#\$%\^&\*\(\)\\\+=<>,\.\?]/) < 0);
    }
    /*
     * timezone functions
     *  */

 });

      

Controller.extend({
    load_data : function(inst) {
        var i;
        for( i in this.props['tmpl'])inst.tmpls[i] = this.props['tmpl'][i];
        for( i in this.props['view'])inst.views[i] = this.props["view"][i];
        /*for( i in this.props['filter']){
            inst[i];
            inst.watch(i,this.filter_handler);
        }*/
        //show binding
        for( i in this.props["show"]){
            inst[i] = this.props["show"][i] || inst[i];
            inst.watch(i,this.show_handler);
        }
        //increment binding
        for( i in this.props["increm"]){
            !function (view, i){
                inst[i] = function(parent,index){
                    if(parent){
                        //we have a parent model hence we will have to obtain the parents view
                        //find the particular incrementor view that we want to increment
                        var target_increm = this.views[parent].children().eq(index).find("*[data-sw-increm='"+i+"']"),
                        p_val = target_increm.text();
                        //check if the content of the view is numeric
                        if($.isNumeric(p_val) || p_val.length == 0)target_increm.text(++p_val);
                    }else{
                        var val = view.text();
                        //check if the content of the view is numeric
                        if($.isNumeric(val) || val.length == 0) view.text(++val);

                    }
                }
            }(this.props["view"][i],i);
        }
    },
    watch : function(prop,handler){
        var oldval = this[prop], newval = oldval,
            getter = function(){
                return newval;
            },
            setter = function(val){
                oldval = newval;
                return newval = handler.call(this,prop,oldval,val);
            };
        if(delete this[prop]){//cant watch constants
            if(Object.defineProperty){
                Object.defineProperty(this,prop,{
                    get:getter,
                    set:setter
                });
            }else if(Object.prototype.__defineGetter__ && Object.prototype.__defineGetter__){
                Object.prototype.__defineGetter__.call(this,prop,getter);
                Object.prototype.__defineSetter__.call(this,prop,setter);
            }
        }
    },
    filter_handler:function(prop,oldval,newval){
        //select the current filter
        /*this.view[prop].filter();*/
        //what method and structures will be the most appropriate to filter a view?
    },
    show_handler: function(prop,oldval,newval){
        if(oldval && !newval){//oldval: true newval: false hide element
            this.views[prop].hide();
        }else if(!oldval && newval){//oldval false newval true: show element
            this.views[prop].show();
        }
        return newval;
    },
    load_res : function(inst) {
        for(var i in this.resources)inst[i] = this.resources[i];
    },
    change_handler: function(e){
        var cntrl = e.data.that;
        //this is an event handler which will respond to changes being made to a model by a view
        /*alert(e.keyCode);*/
        //        if($(this).val() == ""){
        //            var val = e.keyCode;
        //        }
        //check to see if the sync will provide a model instance id
        if(cntrl.sync_id){
            var id = $(this).siblings('input[type="hidden"]').val();
        }
        if(e.data.model in window){
            var model = window[e.data.model];
            if(e.keyCode !== 8){
                model.touch($(this).val()+String.fromCharCode(e.charCode),$(this).attr("name"),id||null);
            }else{
                model.touch($(this).val().slice(0,$(this).val().length-1),$(this).attr("name"),id||null);
            }
        }else{
            throw("the model "+ e.data.model+" does not exist");
        }

     },
    search: function(){
        var that = this;
        /*app name*/
        this.props["app"] = $('head').attr('data-sw-app');
        /*view controller*/
        this.props["controller"] = $('body').attr('data-sw-controller');
        /*view templates*/
        this.props['tmpl'] = []; this.props['view'] = []; this.props['filter'] = []; this.props['order'] = [];
        this.props["show"] = [], this.props["increm"] = [];
        $("*").each(function(){


            if($(this).attr('data-sw-show')){
                //we need to get the value of the attribute
                var showvar = $(this).attr('data-sw-show').split(":");
                showvar[1] = showvar[1] == "false"? false:true;
                if(!showvar[1])$(this).hide();
                that.props["show"][showvar[0]] = showvar[1];
                that.props["view"][showvar[0]] = $(this);
            }

            /* check for a view model sync binding
            */
        if($(this).attr('data-sw-sync')){
            var sync_parms = $(this).attr('data-sw-sync');
            /* *
             * we will need to know the element which has been bound
             * elements that can be bound to a view mode sync:
             * forms
             * view model sync will can only work with forms so we need to check if the element is a form
             * if it is not we need to abort the bind.
             * after we find and check the element we will need to set events to listen
             * for entering of new values
             * */
            //check to see if the element is a form

            if($(this).is("form")){
                $(this).children().find("select, input, textarea").each(function(){
                    //add a onchange event to every form element
                    if($(this).attr("type") === "hidden"){
                        this.sync_id = true;
                    }else{
                        $(this).on("change",{that:this,model:sync_parms},that.change_handler);
                    }
                });
            }

        }


            if($(this).attr('data-sw-model')) {
                var model_parms = $(this).attr('data-sw-model').split(":");

                //check for a loop
                var loop = model_parms[0].split(" ").length > 1,
                    model = model_parms[0].split(" ")[0],
                    pointer = model_parms[0].split(" ")[1];

                that.props['tmpl'][model] = loop ? "{{~it :" + pointer + "}}" + $(this).html() + "{{~}}" : $(this).html();
                that.props["view"][model] = $(this);

                if(model_parms.length == 2){
                    //we have a filter
                    var filter = model_parms[1].split(" ")[1];
                    that.props["filter"][filter] = $(this);
                    that.props["view"][filter] = $(this);
                }

                if(model_parms.length == 3){
                    //we have a order spec
                    //todo: finish the order sorting binding
                }

                //check for an editable instance property
                $(this).find("*").each(function () {
                    var elem = $(this);
                    if(elem.attr("data-sw-editable")){
                        var model_prop_params = elem.attr("data-sw-editable");
                        var prop_loop = model_prop_params.split(" ").length > 1,
                            model_prop = model_prop_params.split(" ")[0],
                            pointer = model_prop_params.split(" ")[1] || "";
                        that.props['tmpl'][model_prop] = prop_loop ? "{{~it :" + pointer + "}}" + $(this).html() + "{{~}}" : $(this).html();
                        that.props["view"][model_prop] = elem;
                    }
                });



                $(this).html('');
            }


            if($(this).attr('data-sw-increm')) {
                //we have an increment binding
                /*--------------------------------------------------------------
                 * how do we handle the incrementation of a value in a text box
                 *------------------------------------------------------------*/
                //we need to obatain the name of the incrementor
                var incrementor = $(this).attr('data-sw-increm');

                that.props["increm"][incrementor]=incrementor;
                that.props["view"][incrementor] = $(this);
            }



        });


        /*view model bindings*/



    },
    service: function(name,callback){
        this.resources[name] = callback($.proxy(this.resource,this));

        return this;
    },
    resource : function(route,deflt,obj){
        var that = Controller.prototype;
        var req = {};
        for(var i in obj){
            if(obj[i].type && obj[i].type == "websocket"){
                //socket resource
               req[i] = SWsocket.create(obj[i].params.url,obj[i].params.type);
            }else{

                //ajax resource
                var rep = obj[i].repeat || false;

                if(rep){
                    (function (i,rep){
                        req[i] = function (objs) {
                            return $.ajax({
                                url: route.replace(/:operation/,function(){return obj[i].params.operation;}),
                                data: objs.data,
                                type: objs.method || obj[i].method ,
                                success: objs.callback,
                                complete: function(o) {
                                    if(objs.complete)objs.complete(o);
                                    if(rep)setTimeout($.proxy(function (){req[i](objs)},that),obj[i].interval||10000);
                                },
                                error: obj.error || function(){}
                            });
                        };
                    })(i,rep);

                }else{
                    (function (i){
                        req[i] = function (objs) {
                            /*for(var i in obj){
                             alert(i +" : "+obj[i])
                             }*/
                            return $.ajax({
                                url: route.replace(/:operation/,function(){return obj[i].params.operation;}),
                                data: objs.data||{},
                                type: obj[i].method,
                                success: objs.callback,
                                complete: objs.complete || function(){},
                                error: objs.error || function(){}
                            });
                        };
                    })(i);

                }
            }

        }
        return req;
    }
});




/*model*/
/*
 *create archives for the model instances
 * archives will store data from previous instances for later use or storage
 *
 * */

var Model = {
    records: [],
    archives: [],
    sync_mode : "",
    child: {},
    settings: {
        auto_persist: false
    },
    inherited: function (){},
    created: function (){},
    prototype: {
        init:function (){}
    },
    create: function (name) {
        var object = Object.create(this);
        object.parent = this;
        object.prototype = object.fn = Object.create(this.prototype);
        object.name = name;
        object.created();
        this.inherited(object);
        this.child[name] = object;
        return object;
    },
    observe: function(object,property,handler){
        alert("we are waiting for our attributes");
        object.watch(property,jQuery.proxy(handler,object));
    },
    init: function(obj){
        var instance = Object.create(this.prototype);
        instance.parent = this;
        instance.init.apply(instance, arguments);
        /*alert(instance.parent.name +" : " +JSON.stringify(obj))*/
        if(obj){
            for(var i in obj) {
                /*alert(i +" : "+typeof this.attributes[i])*/
                    instance[i] =  obj[i];
                    this.good_model = true;
            }
            instance.save();
            //set our instance prop change event
            instance.change(function(){

            });
        }
        return instance;
    },
    extend: function(o) {
        var extended = o.extended;
        for(var i in o) {
            this[i] = o[i];
        }
        if(extended) extended(this);
    },
    include: function(o){
        var included = o.included;
        for(var i in o)this.prototype[i] = o[i];
        if(included) included(this);
    }
};



Model.include({
    newRecord : true,
    fire: function (prop_name, oldval, newval) {
        alert(oldval+" : "+newval);
        if (this.parent.sync_mode == "singular") {
            swev.fire("prop_change", this);
        }
        return newval;
    },
    create: function(){
        if (!this.instance_id) this.instance_id = Math.guid();
        this.newRecord = false;
        this.parent.records.push(this.dup().get_attributes());
    },
    change: function(callback){
        if (callback) {
            if ( !this._change ) this._change = [];
            this._change.push(callback);
        } else {
            if ( !this._change ) return;
            for (var i=0; i < this._change.length; i++)
                this._change[i].apply(this);
        }
    },
    destroy: function(){
        delete this.parent.records[this.instance_id];
    },
    update: function(){
        this.parent.records[this.instance_id] = this.dup();
    },
    save: function(){
        this.newRecord ? this.create() : this.update();

    },
    watch : function(prop,handler){
        var oldval = this[prop], newval = oldval,
            getter = function(){
                return newval;
            },
            setter = function(val) {
                oldval = newval;
                return newval = handler.call(this,prop,oldval,val);
            };
        if(delete this[prop]){//cant watch constants
            if(Object.defineProperty){
                Object.defineProperty(this,prop,{
                    get:getter,
                    set:setter
                });
            } else if(Object.prototype.__defineGetter__ && Object.prototype.__defineGetter__){
                Object.prototype.__defineGetter__.call(this,prop,getter);
                Object.prototype.__defineSetter__.call(this,prop,setter);
            }
        }
    },
    dup: function(){
        return jQuery.extend(true,{},this);
    },
    get_attributes: function() {
        var result = {};
        for(var i in this.parent.attributes){
            result[i] = this[i];
        }
        result.instance_id = this.instance_id;
        result.parent_name = this.parent.name;
        /*result.created_at = this.timestamp();*/
        return result;
    },
    toJSON: function() {
        return this.get_attributes();
    },
    toString: function () {
        return JSON.stringify(this);
    },
    createRemote: function(url,callback) {
        $.post(url, this.get_attributes(), callback);
    },
    updateRemote: function(url,callback) {
        $.ajax({
            url: url,
            data: this.get_attributes(),
            success: callback,
            type: "PUT"
        });
    }
});




Model.extend({
    /*
    * we are about to create a function to edit the values of a particular instance of a model together with the view which it has been used to render
    * every model instance has a unique id that we can use to identitfy them inidividually
     * so first we will have have find a way to obatain the id of the instance whos bound view has just been changed
    * */
    find: function(id) {
        var record = false;
        for(var i in this.records){
            if(this.records[i].instance_id == id){
                record = this.records[i];
            }
        }
        return record;
    },
    change: function(callback){
        if (callback) {
            if ( !this._change ) this._change = [];
            this._change.push(callback);
        } else {
            if ( !this._change ) return;
            for (var i=0; i < this._change.length; i++)
                this._change[i].apply(this);
        }
    },
    created: function () {
        this.records = [];
        this.attributes = {};
    },
    populate: function(values) {
        this.sync_mode = "array";
        this.records = [];

        values = typeof values == 'string' ? JSON.parse(values) : values;
        if(!Array.isArray(values)){
            values =[values];
        }
        var record;
        for (var i in values) {
            record = this.init(values[i]);
            this.archive(record);
        }
        swev.fire("model_view_sync",this);
        return record;
    },
    archive: function(instance){
        this.archives.push(instance);
    },
    saveLocal: function(name){
        //turn records into array
        localStorage[name] = JSON.stringify(this.records);
    },
    loadLocal: function(name){
        this.populate(JSON.parse(localStorage[name]));
    },
    saveArchive: function(name){
        localStorage[name] = JSON.stringify(this.archives);
    },
    loadArchives: function(name) {
        this.populate(JSON.parse(localStorage[name]));
    },
    auto: function() {
        if(this.settings.auto_persist) {

        }
    },
    fetch: function(){

    },
    touch: function (val,prop,id) {
        this.sync_mode = "singular";
        var instance =  id ?this.records[id]:this.records[0];
            instance[prop] = val;
        swev.fire("view_model_sync", instance);
    }
});


/*view*/
/*Laura Doktorova https://github.com/olado/doT*/
(function() {
    "use strict";

    var doT = {
        version: '1.0.1',
        templateSettings: {
            evaluate:    /\{\{([\s\S]+?(\}?)+)\}\}/g,
            interpolate: /\{\{=([\s\S]+?)\}\}/g,
            encode:      /\{\{!([\s\S]+?)\}\}/g,
            use:         /\{\{#([\s\S]+?)\}\}/g,
            useParams:   /(^|[^\w$])def(?:\.|\[[\'\"])([\w$\.]+)(?:[\'\"]\])?\s*\:\s*([\w$\.]+|\"[^\"]+\"|\'[^\']+\'|\{[^\}]+\})/g,
            define:      /\{\{##\s*([\w\.$]+)\s*(\:|=)([\s\S]+?)#\}\}/g,
            defineParams:/^\s*([\w$]+):([\s\S]+)/,
            conditional: /\{\{\?(\?)?\s*([\s\S]*?)\s*\}\}/g,
            iterate:     /\{\{~\s*(?:\}\}|([\s\S]+?)\s*\:\s*([\w$]+)\s*(?:\:\s*([\w$]+))?\s*\}\})/g,
            varname:	'it',
            strip:		true,
            append:		true,
            selfcontained: false
        },
        template: undefined, //fn, compile template
        compile:  undefined  //fn, for express
    }, global;

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = doT;
    } else if (typeof define === 'function' && define.amd) {
        define(function(){return doT;});
    } else {
        global = (function(){ return this || (0,eval)('this'); }());
        global.doT = doT;
    }

    function encodeHTMLSource() {
        var encodeHTMLRules = { "&": "&#38;", "<": "&#60;", ">": "&#62;", '"': '&#34;', "'": '&#39;', "/": '&#47;' },
            matchHTML = /&(?!#?\w+;)|<|>|"|'|\//g;
        return function() {
            return this ? this.replace(matchHTML, function(m) {return encodeHTMLRules[m] || m;}) : this;
        };
    }
    String.prototype.encodeHTML = encodeHTMLSource();

    var startend = {
        append: { start: "'+( ",      end: " )+'",      endencode: "||'').toString().encodeHTML()+'" },
        split:  { start: "';out+=( ", end: " );out+='", endencode: "||'').toString().encodeHTML();out+='"}
    }, skip = /$^/;

    function resolveDefs(c, block, def) {
        return ((typeof block === 'string') ? block : block.toString())
            .replace(c.define || skip, function(m, code, assign, value) {
                if (code.indexOf('def.') === 0) {
                    code = code.substring(4);
                }
                if (!(code in def)) {
                    if (assign === ':') {
                        if (c.defineParams) value.replace(c.defineParams, function(m, param, v) {
                            def[code] = {arg: param, text: v};
                        });
                        if (!(code in def)) def[code]= value;
                    } else {
                        new Function("def", "def['"+code+"']=" + value)(def);
                    }
                }
                return '';
            })
            .replace(c.use || skip, function(m, code) {
                if (c.useParams) code = code.replace(c.useParams, function(m, s, d, param) {
                    if (def[d] && def[d].arg && param) {
                        var rw = (d+":"+param).replace(/'|\\/g, '_');
                        def.__exp = def.__exp || {};
                        def.__exp[rw] = def[d].text.replace(new RegExp("(^|[^\\w$])" + def[d].arg + "([^\\w$])", "g"), "$1" + param + "$2");
                        return s + "def.__exp['"+rw+"']";
                    }
                });
                var v = new Function("def", "return " + code)(def);
                return v ? resolveDefs(c, v, def) : v;
            });
    }

    function unescape(code) {
        return code.replace(/\\('|\\)/g, "$1").replace(/[\r\t\n]/g, ' ');
    }

    doT.template = function(tmpl, c, def) {
        c = c || doT.templateSettings;
        var cse = c.append ? startend.append : startend.split, needhtmlencode, sid = 0, indv,
            str  = (c.use || c.define) ? resolveDefs(c, tmpl, def || {}) : tmpl;

        str = ("var out='" + (c.strip ? str.replace(/(^|\r|\n)\t* +| +\t*(\r|\n|$)/g,' ')
            .replace(/\r|\n|\t|\/\*[\s\S]*?\*\//g,''): str)
            .replace(/'|\\/g, '\\$&')
            .replace(c.interpolate || skip, function(m, code) {
                return cse.start + unescape(code) + cse.end;
            })
            .replace(c.encode || skip, function(m, code) {
                needhtmlencode = true;
                return cse.start + unescape(code) + cse.endencode;
            })
            .replace(c.conditional || skip, function(m, elsecase, code) {
                return elsecase ?
                    (code ? "';}else if(" + unescape(code) + "){out+='" : "';}else{out+='") :
                    (code ? "';if(" + unescape(code) + "){out+='" : "';}out+='");
            })
            .replace(c.iterate || skip, function(m, iterate, vname, iname) {
                if (!iterate) return "';} } out+='";
                sid+=1; indv=iname || "i"+sid; iterate=unescape(iterate);
                return "';var arr"+sid+"="+iterate+";if(arr"+sid+"){var "+vname+","+indv+"=-1,l"+sid+"=arr"+sid+".length-1;while("+indv+"<l"+sid+"){"
                    +vname+"=arr"+sid+"["+indv+"+=1];out+='";
            })
            .replace(c.evaluate || skip, function(m, code) {
                return "';" + unescape(code) + "out+='";
            })
            + "';return out;")
            .replace(/\n/g, '\\n').replace(/\t/g, '\\t').replace(/\r/g, '\\r')
            .replace(/(\s|;|\}|^|\{)out\+='';/g, '$1').replace(/\+''/g, '')
            .replace(/(\s|;|\}|^|\{)out\+=''\+/g,'$1out+=');

        if (needhtmlencode && c.selfcontained) {
            str = "String.prototype.encodeHTML=(" + encodeHTMLSource.toString() + "());" + str;
        }
        try {
            return new Function(c.varname, str);
        } catch (e) {
            if (typeof console !== 'undefined') console.log("Could not create a template function: " + str);
            throw e;
        }
    };

    doT.compile = function(tmpl, def) {
        return doT.template(tmpl, null, def);
    };
}());





//object to create and execute global events
var swev = function(){
    var mod = {};
    mod.set=function(ev,callback){
        //create callback object unless it already exits
        (this._callbacks || (this._callbacks = {}));

        // Create an array for the given event key, unless it exists, then
        // append the callback to the array
        (this._callbacks[ev] || (this._callbacks[ev] = [])).push(callback);
        return this;
    };
    mod.fire = function(){
        //convert arguments object into a real array
        var args = Array.prototype.slice.call(arguments,0),
            ev = args.shift();//select the first argument which is the name of the event

        // Return if there isn't a _callbacks object, or
        // if it doesn't contain an array for the given event
        var calls,list, i,l;
        if(!(calls = this._callbacks))return this;
        if(!(list = this._callbacks[ev]))return this;

        //fire callbacks for the specified event ev
        for(i in list)
            list[i].apply(this,args);
        return this;
    };

    mod.rem = function(ev){
        delete this._callbacks[ev];
    };

    return mod;
}();





  //hash extension
        Controller.include({
             //hashing functions
    hexMD5: function  (str) {
        return this.binl2hex(this.coreMD5(this.str2binl(str))).toUpperCase();
    },
    hexMD5w: function (str) {
        return this.binl2hex(this.coreMD5(this.strw2binl(str))).toUpperCase();
    },
    b64MD5: function  (str) {
        return this.binl2b64(this.coreMD5(this.str2binl(str))).toUpperCase();
    },
    b64MD5w: function (str) {
        return this.binl2b64(this.coreMD5(this.strw2binl(str))).toUpperCase();
    },
    /* Backward compatibility */
    calcMD5: function (str) {
        return this.binl2hex(this.coreMD5(this.str2binl(str))).toUpperCase();
    },
    /**
     *@description Convert an array of little-endian words to a hex string.
     */
    binl2hex: function (binarray){
        var hex_tab = "0123456789abcdef",
            str = "";
        for(var i = 0; i < binarray.length * 4; i++)
        {
            str += hex_tab.charAt((binarray[i>>2] >> ((i%4)*8+4)) & 0xF) +
                hex_tab.charAt((binarray[i>>2] >> ((i%4)*8)) & 0xF);
        }
        return str;
    },
    /*
     * Convert an array of little-endian words to a base64 encoded string.
     */
    binl2b64: function (binarray){
        var tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
            str = "";
        for(var i = 0; i < binarray.length * 32; i += 6)
        {
            str += tab.charAt(((binarray[i>>5] << (i%32)) & 0x3F) |
                ((binarray[i>>5+1] >> (32-i%32)) & 0x3F));
        }
        return str;
    },
    /**
     *@description Convert an 8-bit character string to a sequence of 16-word blocks, stored
     * as an array, and append appropriate padding for MD4/5 calculation.
     * If any of the characters are >255, the high byte is silently ignored.
     */
    str2binl: function (str){
        var nblk = ((str.length + 8) >> 6) + 1, // number of 16-word blocks
            blks = new Array(nblk * 16);
        for(var i = 0; i < nblk * 16; i++) blks[i] = 0;
        for(var i = 0; i < str.length; i++)
            blks[i>>2] |= (str.charCodeAt(i) & 0xFF) << ((i%4) * 8);
        blks[i>>2] |= 0x80 << ((i%4) * 8);
        blks[nblk*16-2] = str.length * 8;
        return blks;
    },

    /**
     *@description Convert a wide-character string to a sequence of 16-word blocks, stored as
     * an array, and append appropriate padding for MD4/5 calculation.
     */
    strw2binl: function (str){
        var nblk = ((str.length + 4) >> 5) + 1, // number of 16-word blocks
            blks = new Array(nblk * 16);
        for(var i = 0; i < nblk * 16; i++) blks[i] = 0;
        for(var i = 0; i < str.length; i++)
            blks[i>>1] |= str.charCodeAt(i) << ((i%2) * 16);
        blks[i>>1] |= 0x80 << ((i%2) * 16);
        blks[nblk*16-2] = str.length * 16;
        return blks
    },
    coreMD5: function (x){
        var a =  1732584193,
            b = -271733879,
            c = -1732584194,
            d =  271733878;

        for(var i = 0; i < x.length; i += 16)
        {
            var olda = a,
                oldb = b,
                oldc = c,
                oldd = d;

            a = this.ff(a, b, c, d, x[i+ 0], 7 , -680876936);
            d = this.ff(d, a, b, c, x[i+ 1], 12, -389564586);
            c = this.ff(c, d, a, b, x[i+ 2], 17,  606105819);
            b = this.ff(b, c, d, a, x[i+ 3], 22, -1044525330);
            a = this.ff(a, b, c, d, x[i+ 4], 7 , -176418897);
            d = this.ff(d, a, b, c, x[i+ 5], 12,  1200080426);
            c = this.ff(c, d, a, b, x[i+ 6], 17, -1473231341);
            b = this.ff(b, c, d, a, x[i+ 7], 22, -45705983);
            a = this.ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
            d = this.ff(d, a, b, c, x[i+ 9], 12, -1958414417);
            c = this.ff(c, d, a, b, x[i+10], 17, -42063);
            b = this.ff(b, c, d, a, x[i+11], 22, -1990404162);
            a = this.ff(a, b, c, d, x[i+12], 7 ,  1804603682);
            d = this.ff(d, a, b, c, x[i+13], 12, -40341101);
            c = this.ff(c, d, a, b, x[i+14], 17, -1502002290);
            b = this.ff(b, c, d, a, x[i+15], 22,  1236535329);

            a = this.gg(a, b, c, d, x[i+ 1], 5 , -165796510);
            d = this.gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
            c = this.gg(c, d, a, b, x[i+11], 14,  643717713);
            b = this.gg(b, c, d, a, x[i+ 0], 20, -373897302);
            a = this.gg(a, b, c, d, x[i+ 5], 5 , -701558691);
            d = this.gg(d, a, b, c, x[i+10], 9 ,  38016083);
            c = this.gg(c, d, a, b, x[i+15], 14, -660478335);
            b = this.gg(b, c, d, a, x[i+ 4], 20, -405537848);
            a = this.gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
            d = this.gg(d, a, b, c, x[i+14], 9 , -1019803690);
            c = this.gg(c, d, a, b, x[i+ 3], 14, -187363961);
            b = this.gg(b, c, d, a, x[i+ 8], 20,  1163531501);
            a = this.gg(a, b, c, d, x[i+13], 5 , -1444681467);
            d = this.gg(d, a, b, c, x[i+ 2], 9 , -51403784);
            c = this.gg(c, d, a, b, x[i+ 7], 14,  1735328473);
            b = this.gg(b, c, d, a, x[i+12], 20, -1926607734);

            a = this.hh(a, b, c, d, x[i+ 5], 4 , -378558);
            d = this.hh(d, a, b, c, x[i+ 8], 11, -2022574463);
            c = this.hh(c, d, a, b, x[i+11], 16,  1839030562);
            b = this.hh(b, c, d, a, x[i+14], 23, -35309556);
            a = this.hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
            d = this.hh(d, a, b, c, x[i+ 4], 11,  1272893353);
            c = this.hh(c, d, a, b, x[i+ 7], 16, -155497632);
            b = this.hh(b, c, d, a, x[i+10], 23, -1094730640);
            a = this.hh(a, b, c, d, x[i+13], 4 ,  681279174);
            d = this.hh(d, a, b, c, x[i+ 0], 11, -358537222);
            c = this.hh(c, d, a, b, x[i+ 3], 16, -722521979);
            b = this.hh(b, c, d, a, x[i+ 6], 23,  76029189);
            a = this.hh(a, b, c, d, x[i+ 9], 4 , -640364487);
            d = this.hh(d, a, b, c, x[i+12], 11, -421815835);
            c = this.hh(c, d, a, b, x[i+15], 16,  530742520);
            b = this.hh(b, c, d, a, x[i+ 2], 23, -995338651);

            a = this.ii(a, b, c, d, x[i+ 0], 6 , -198630844);
            d = this.ii(d, a, b, c, x[i+ 7], 10,  1126891415);
            c = this.ii(c, d, a, b, x[i+14], 15, -1416354905);
            b = this.ii(b, c, d, a, x[i+ 5], 21, -57434055);
            a = this.ii(a, b, c, d, x[i+12], 6 ,  1700485571);
            d = this.ii(d, a, b, c, x[i+ 3], 10, -1894986606);
            c = this.ii(c, d, a, b, x[i+10], 15, -1051523);
            b = this.ii(b, c, d, a, x[i+ 1], 21, -2054922799);
            a = this.ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
            d = this.ii(d, a, b, c, x[i+15], 10, -30611744);
            c = this.ii(c, d, a, b, x[i+ 6], 15, -1560198380);
            b = this.ii(b, c, d, a, x[i+13], 21,  1309151649);
            a = this.ii(a, b, c, d, x[i+ 4], 6 , -145523070);
            d = this.ii(d, a, b, c, x[i+11], 10, -1120210379);
            c = this.ii(c, d, a, b, x[i+ 2], 15,  718787259);
            b = this.ii(b, c, d, a, x[i+ 9], 21, -343485551);

            a = this.safe_add(a, olda);
            b = this.safe_add(b, oldb);
            c = this.safe_add(c, oldc);
            d = this.safe_add(d, oldd);
        }
        return [a, b, c, d];
    },
    safe_add: function (x, y) {
        var lsw = (x & 0xFFFF) + (y & 0xFFFF);
        var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
        return (msw << 16) | (lsw & 0xFFFF);
    },
    rol: function (num, cnt){
        return (num << cnt) | (num >>> (32 - cnt));
    },
    cmn: function (q, a, b, x, s, t){
        return this.safe_add(this.rol(this.safe_add(this.safe_add(a, q), this.safe_add(x, t)), s), b)
    },
    ff: function (a, b, c, d, x, s, t){
        return this.cmn((b & c) | ((~b) & d), a, b, x, s, t);
    },
    gg: function (a, b, c, d, x, s, t){
        return this.cmn((b & d) | (c & (~d)), a, b, x, s, t);
    },
    hh: function (a, b, c, d, x, s, t){
        return this.cmn(b ^ c ^ d, a, b, x, s, t);
    },
    ii: function (a, b, c, d, x, s, t){
        return this.cmn(c ^ (b | (~d)), a, b, x, s, t);
    }
        });


/*Globally Unique Identifier (GUID) generator.*/
Math.guid = function(){
    return 'xxxxxxxx-xxxx-7xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);return v.toString(16);}).toUpperCase();
};


