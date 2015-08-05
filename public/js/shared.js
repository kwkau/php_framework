var Upload_mdl = Model.create("Upload_mdl");

Upload_mdl.attributes = {
    id:0,
    file_name : '',
    file_size : 0,
    file_type :'',
    magic_id: '',
    file_url: '',
    created_at: '',
    type: '',
    user_prof: {}
};

var SearchUser_mdl = Model.create("SearchUser_mdl");
SearchUser_mdl.attributes = {
    user_id : 0,
    profile_pic_thumb : "",
    full_name:"",
    index_number:0,
    black_list:0
};

var SearchGrp_mdl = Model.create("SearchGrp_mdl");
SearchGrp_mdl.attributes = {
    group_name : ""
};

var frndNum_mdl = Model.create("frndNum_mdl");

frndNum_mdl.attributes = {
    num: 0
};

var Friend_mdl = Model.create("Friend_mdl");

Friend_mdl.attributes = {
    id:0,
    user_prof:{}
};

var Groups_mdl = Model.create("Groups_mdl");

Groups_mdl.attributes = {
    id: 0,
    name:"",
    admin: {},
    created_at: "",
    privacy_type: "",
    description:""
};

var notiNum_mdl = Model.create("notiNum_mdl");

notiNum_mdl.attributes = {
    num: 0
};

var grpNum_mdl = Model.create("grpNum_mdl");

grpNum_mdl.attributes = {
    num: 0
};


frndNum_mdl.display_mode = "apply";
grpNum_mdl.display_mode = "apply";
notiNum_mdl.display_mode = "apply";

var Tab_mdl = Model.create("Tab_mdl");
Tab_mdl.attributes = {
    id:"",
    name:"",
    close:""
};
Tab_mdl.display_mode = "apply";

var general = {
    start: function(target){
        Tab_mdl.populate([
            {id:"#forum-pane", name:"Forums",close:false},
            {id:"#post-pane", name:"Posts",close:false}
        ]);
        /*--------------------------------------------------------------------------------------
         * this function will fetch all the data that the dropzone needs to display to the user
         *-------------------------------------------------------------------------------------*/

        if(target == "user"){
            this.DropZoneResource.loadUser({
                callback: $.proxy(this.handleData,this)
            });
        }else if(target == "group"){
            this.GroupResource.loadGroup({
                callback: $.proxy(this.handleGroupData,this)
            });
        }else{

            this.DepartmentResource.loadDepartment({
                callback: $.proxy(this.handleDepData,this)
            });
        }

        this.form_validate(this.grp_create);
    },
    handleDepData: function (o) {
        var data = JSON.parse(o);
        this.id = data.mem_prof.user_id;
        this.dep = data.department;
        this.dep_id = data.mem_prof.department.id;
        this.page = "department";
        Global.set_item("id",this.id);
        Global.set_item("dep_name",this.dep.name);
        this.user_prof = data.mem_prof;
        User_mdl.display_mode = "apply";
        User_mdl.populate(this.user_prof);
        this.socket_start();

        /*
         * start notification socket
         * */
        this.start_noti();

    },
    handleGroupData: function (o) {
        var data = JSON.parse(o);
        this.id = data.mem_prof.user_id;
        this.group = data.group;
        this.page = "group";
        Global.set_item("id",this.id);
        Global.set_item("group_name",this.group.name);
        this.user_prof = data.mem_prof;
        GroupSide_mdl.display_mode = "apply";
        GroupSide_mdl.populate(this.group);
        this.socket_start();

        /*---------------------------
         * start notification socket
         *-------------------------*/
        this.start_noti();
    },
    handleData : function (o) {
        var data = JSON.parse(o);
        this.id = data.user_id;
        Global.set_item("id",this.id);
        this.page = "user";
        this.user_prof = data;
        User_mdl.display_mode = "apply";
        User_mdl.populate(o);
        this.socket_start();

        /*
         * start notification socket
         * */
        this.start_noti();
    },
    start_noti: function () {
        /*
         * start notification socket
         * */
        this.noti_socket = this.DropZoneResource.notiSocket;
        this.noti_socket.start({
            onopen: this.notiOpen,
            onmessage: this.notiGet,
            onclose: this.notiClose,
            onerror: this.notiError,
            scope:this
        });
    },
    /*-----------------------------
     * noti socket event handlers
     *---------------------------*/
    notiGet: function (o) {
        var packet = this.noti_socket.chck_data(o.data);
        if(packet && packet.packet_type == "noti_load"){
            this.noti_socket.fire("noti_load_ev",packet);
        }else if(packet && packet.packet_type == "department_noti_load"){
            this.noti_socket.fire("department_noti_load_ev",packet);
        }else if(packet && packet.packet_type == "group_noti_load"){
            this.noti_socket.fire("group_noti_load_ev",packet);
        }else if(packet && packet.packet_type == "fetch_post"){
            this.noti_socket.fire("fetch_post_ev",packet);
        }else if(packet && packet.packet_type == "fetch_forum"){
            this.noti_socket.fire("fetch_forum_ev",packet);
        }else if(packet && packet.packet_type == "noti_login"){
            this.noti_socket.fire("noti_login_ev",packet);
        }else if(packet && packet.packet_type == "insert_noti"){
            this.noti_socket.fire("insert_noti_ev",packet);
        }else if(packet && packet.packet_type == "noti_forum_comment_load"){
            this.noti_socket.fire("noti_forum_comment_load_ev",packet);
        }else if(packet && packet.packet_type == "noti_post_comment_load"){
            this.noti_socket.fire("noti_post_comment_load_ev",packet);
        }
    },
    notiClose: function (o) {

    },
    notiError: function (o) {

    },
    notiOpen: function (o) {
        this.noti_socket.post(this.id);

        /*-------------------------
         * set noti socket events
         *------------------------*/
        this.noti_socket.bind("noti_load_ev", $.proxy(this.noti_load_handler,this));
        this.noti_socket.bind("department_noti_load_ev", $.proxy(this.department_noti_load_handler,this));
        this.noti_socket.bind("group_noti_load_ev", $.proxy(this.group_noti_load_handler,this));
        this.noti_socket.bind("fetch_post_ev", $.proxy(this.fetch_post_handler,this));
        this.noti_socket.bind("fetch_forum_ev", $.proxy(this.fetch_forum_handler,this));
        this.noti_socket.bind("noti_login_ev", $.proxy(this.noti_login_handler,this));
        this.noti_socket.bind("insert_noti_ev", $.proxy(this.insert_noti_handler,this));
        this.noti_socket.bind("noti_forum_comment_load_ev", $.proxy(this.noti_forum_comment_load_handler,this));
        this.noti_socket.bind("noti_post_comment_load_ev", $.proxy(this.noti_post_comment_load_handler,this));

    },
    noti_post_comment_load_handler: function (packet) {

        this.comment_render(packet.payload.post_magic_id,packet.payload.comments,"noti:post",true,"top");
    },
    noti_forum_comment_load_handler: function (packet) {

        this.comment_render(packet.payload.forum_magic_id,packet.payload.comments,"noti:forum",true,"top");
    },
    insert_noti_handler: function (packet) {
        this.noti_num("add");
        if(packet.payload.type == "user"){
            UserNoti_mdl.display_mode = "append:top";
            UserNoti_mdl.populate(packet.payload);
        }else if(packet.payload.type == "department"){
            DepNoti_mdl.display_mode = "append:top";
            DepNoti_mdl.populate(packet.payload);
        }else if(packet.payload.type == "group"){
            GroupNoti_mdl.display_mode = "append:top";
            GroupNoti_mdl.populate(packet.payload);
        }
    },
    noti_load_handler: function (packet) {
        /*alert(JSON.stringify(packet));*/
        UserNoti_mdl.display_mode = "append:top";
        UserNoti_mdl.populate(packet.payload);
    },
    department_noti_load_handler: function (packet) {
        DepNoti_mdl.display_mode = "append:top";
        DepNoti_mdl.populate(packet.payload);
    },
    group_noti_load_handler: function (packet) {
        GroupNoti_mdl.display_mode = "append:top";
        GroupNoti_mdl.populate(packet.payload);
    },
    fetch_post_handler: function (packet) {
        this.container_process(this.noti_socket.container,packet.payload,"Post_mdl");
    },
    fetch_forum_handler: function (packet) {

        this.container_process(this.noti_socket.container,packet.payload,"Forum_mdl");
    },
    noti_login_handler: function (packet) {
        if(packet.payload != "valid"){
            this.noti_socket.close(3,"invalid user");
        }
    },
    /*--------------------------------
     * startup socket event handlers
     *------------------------------*/
    startup_open: function (o) {
        /*-------------------------------------------------------------------------------------------
         * when ever a socket is opened the id of the user will have to be sent to the socket server
         * to allow the server to uniquely identify each user by fetching all their information using
         * their user_id
         *-----------------------------------------------------------------------------------------*/

        this.startup_socket.post(this.id);


        /*----------------------------
         * set startup_socket events
         *---------------------------*/
        this.startup_socket.bind("user_load", $.proxy(this.user_load_handler,this));

    },
    startup_get: function (o) {
        var packet = this.startup_socket.chck_data(o.data);
        if(packet && packet.packet_type == "user_load"){
            this.startup_socket.fire("user_load",packet);
        }


    },
    startup_close: function (o) {

    },
    comment_render: function (magic_id, comment_data,type,load,pos) {
        //logic to render a forum_comment to its rightful forum
        load = load||false;
        var pod,mdl_name;
        type = type ||"forum";
        if(type.search(/noti/) >= 0){//render comments for a noti post or forum
            type = type.split(":");
            mdl_name = type[1] == "forum"? "Forum_mdl":"Post_mdl";
            pod = "#Notifications .usap-pod";
        }else{
            pod = type == "forum"?"#forum-pane .usap-pod":"#post-pane .usap-pod";

        }
        var target_pod,field,render_data,that = this;
        $(pod).each(function () {
            target_pod = $(this);
            if(target_pod.data("mgcid") == magic_id){
                //we have found our target forum
                field = target_pod.find("textarea");
                render_data = {
                    instance_id: field.attr("data-instance-id"),
                    view_index: target_pod.index(),
                    prop_data: comment_data,
                    prop_name: field.attr("name"),
                    model_name: Array.isArray(type)? mdl_name:field.attr("data-model-name"),
                    position: pos||"bottom",
                    noti_render: Array.isArray(type),
                    noti_container: that.noti_socket.container
                };

                if(!load){
                    /*that.comment_num(field.attr("data-model-name"),target_forum.index());*/
                    var com_num = target_pod.find("*[data-sw-increm]");
                    com_num.text((isNaN(parseInt(com_num.text()))?0:parseInt(com_num.text()))+1);
                }

                that.model_prop_change(render_data);
            }
        });

    },
    forum_comnt_smiley_engine: function (smiley_box,that,sckt) {
        //obtain data needed to create the smiley
        var forum_target = smiley_box.parents(".usap-pod"),
            forum_comment = smiley_box.parents(".comment-pod"),
            post_data ={
                forum_comment_smiley_data: {
                    forum_type:forum_target.data("type"),
                    user_id: forum_target.data("id"),
                    comment_magic_id: forum_comment.data("comment"),
                    forum_magic_id: forum_target.data("mgcid")
                },
                type:"forum_comment_smiley"
            },
            num = isNaN(parseInt(smiley_box.find(".smiley-num").text()))?0:parseInt(smiley_box.find(".smiley-num").text());

        //check if the user has liked the comment
        if(!forum_comment.data("smiley") && !forum_comment.data("unsmiley")){//user has not liked this comment

            forum_comment.data("smiley",false);
            forum_comment.data("unsmiley",true);
            smiley_box.addClass("liked");
            sckt.post(post_data);
            //increase the number of likes
            smiley_box.find(".smiley-num").text(num+1);

            //send a notification to the user the forum belongs to
            if(forum_comment.data("uid") != that.id){
                that.create_user_noti({
                    target_id: forum_comment.data("uid"),
                    to_mgcid: post_data.forum_comment_smiley_data.forum_magic_id,
                    to_type: "forum",
                    text: that.user_prof.full_name + that.forum_comment_like_noti_text,
                    created_at: that.timestamp()
                });
            }
        }else if(forum_comment.data("smiley") && !forum_comment.data("unsmiley")){//user has already unliked the comment

            forum_comment.data("smiley",false);
            forum_comment.data("unsmiley",true);
            smiley_box.addClass("liked");
            sckt.post(post_data);
            //increase the number of likes
            smiley_box.find(".smiley-num").text(num+1);

            //send a notification to the user the forum belongs to
            if(forum_comment.data("uid") != that.id){
                that.create_user_noti({
                    target_id: forum_comment.data("uid"),
                    to_mgcid: post_data.forum_comment_smiley_data.forum_magic_id,
                    to_type: "forum",
                    text: that.user_prof.full_name + that.forum_comment_like_noti_text,
                    created_at: that.timestamp()
                });
            }

        }else if(!forum_comment.data("smiley") && forum_comment.data("unsmiley")){//user has already liked the comment
            post_data.type = "del_forum_comment_smiley";

            forum_comment.data("smiley",true);
            forum_comment.data("unsmiley",false);
            smiley_box.removeClass("liked");
            sckt.post(post_data);
            //decrease the number of likes
            smiley_box.find(".smiley-num").text(num-1==0?"":num-1);

        }
    },
    smiley_process: function (smiley_data,type) {
        type = type ||"add";
        this.forum_usap_pod_wrapper.find(".usap-pod").each(function () {
            var forum_target = $(this);
            if(forum_target.data("mgcid") == smiley_data.forum_magic_id){
                //next we find the comment
                var comment = forum_target.find(".comment-pod[data-comment='"+smiley_data.comment_magic_id+"']"),
                    num = isNaN(parseInt(comment.find(".smiley .smiley-num").text()))?0:parseInt(comment.find(".smiley .smiley-num").text());
                if(type=="add"){
                    comment.find(".smiley .smiley-num").text(num+1);
                }else{
                    comment.find(".smiley .smiley-num").text(num-1 == 0?"":num-1);
                }
            }
        });
    },
    post_smiley_process: function (smiley_data,type) {
        type = type ||"add";
        this.post_usap_pod_wrapper.find(".usap-pod").each(function () {
            var post_target = $(this);
            if(post_target.data("mgcid") == smiley_data.post_magic_id){
                //next we find the smiley container
                var smiley_box = post_target.find(".comments-cont-header .smiley"),
                    num = isNaN(parseInt(smiley_box.find(".smiley-num").text()))?0:parseInt(smiley_box.find(".smiley-num").text());
                if(type=="add"){
                    smiley_box.find(".smiley-num").text(num+1);
                }else{
                    smiley_box.find(".smiley-num").text(num-1 == 0?"":num-1);
                }
            }
        });
    },
    post_comment_smiley_process: function (smiley_data,type) {
        type = type ||"add";
        this.post_usap_pod_wrapper.find(".usap-pod").each(function () {
            var post_target = $(this);
            if(post_target.data("mgcid") == smiley_data.post_magic_id){
                //next we find the comment
                var comment = post_target.find(".comment-pod[data-comment='"+smiley_data.comment_magic_id+"']"),
                    num = isNaN(parseInt(comment.find(".smiley .smiley-num").text()))?0:parseInt(comment.find(".smiley .smiley-num").text());
                if(type=="add"){
                    comment.find(".smiley .smiley-num").text(num+1);
                }else{
                    comment.find(".smiley .smiley-num").text(num-1 == 0?"":num-1);
                }
            }
        });
    },
    smiley_box_process: function (smiley_box, sckt, data, type) {
        var num = isNaN(parseInt(smiley_box.find(".smiley-num").text()))?0:parseInt(smiley_box.find(".smiley-num").text());
        if(!smiley_box.data("smiley") && !smiley_box.data("unsmiley")){//user has not liked this post

                smiley_box.data("smiley",false);
                smiley_box.data("unsmiley",true);
                smiley_box.addClass("liked");
                sckt.post(data);
                //increase the number of likes
                smiley_box.find(".smiley-num").text(num+1);

            //send a notification to the user who created the post
            if(smiley_box.parents(".usap-pod").data("id") != this.id){
                this.create_user_noti({
                    target_id: smiley_box.parents(".usap-pod").data("id"),
                    to_mgcid: smiley_box.parents(".usap-pod").data("mgcid"),
                    to_type: "post",
                    text: this.user_prof.full_name+this.post_like_noti_text,
                    created_at: this.timestamp()
                });
            }

        }else if(smiley_box.data("smiley") && !smiley_box.data("unsmiley")){//user has already unliked the post

                smiley_box.data("smiley",false);
                smiley_box.data("unsmiley",true);
                smiley_box.addClass("liked");
                sckt.post(data);
                //increase the number of likes
                smiley_box.find(".smiley-num").text(num+1);

            //send a notification to the user who created the comment
            if(smiley_box.parents(".usap-pod").data("id") != this.id){
                this.create_user_noti({
                    target_id: smiley_box.parents(".usap-pod").data("id"),
                    to_mgcid: smiley_box.parents(".usap-pod").data("mgcid"),
                    to_type: "post",
                    text: this.user_prof.full_name+this.post_like_noti_text,
                    created_at: this.timestamp()
                });
            }

        }else if(!smiley_box.data("smiley") && smiley_box.data("unsmiley")){//user has already liked the post
            data.type = type;

                smiley_box.data("smiley",true);
                smiley_box.data("unsmiley",false);
                smiley_box.removeClass("liked");
                sckt.post(data);
                //decrease the number of likes
                smiley_box.find(".smiley-num").text(num-1==0?"":num-1);


        }
    },
    comment_smiley_box_process: function (smiley_box, sckt, data, type) {
        var num = isNaN(parseInt(smiley_box.find(".smiley-num").text()))?0:parseInt(smiley_box.find(".smiley-num").text());
        var prof = this.user_prof || this.user_prof;
        if(!smiley_box.data("smiley") && !smiley_box.data("unsmiley")){//user has not liked this post comment

                smiley_box.data("smiley",false);
                smiley_box.data("unsmiley",true);
                smiley_box.addClass("liked");
                sckt.post(data);
                //increase the number of likes
                smiley_box.find(".smiley-num").text(num + 1);


            //send a notification to the user who created the comment
            if(smiley_box.parents(".comment-pod").data("uid") != this.id){
                this.create_user_noti({
                    target_id: smiley_box.parents(".comment-pod").data("uid"),
                    to_mgcid: smiley_box.parents(".usap-pod").data("mgcid"),
                    to_type: "post",
                    text: prof.full_name + this.post_comment_like_noti_text,
                    created_at: this.timestamp()
                });
            }

        }else if(smiley_box.data("smiley") && !smiley_box.data("unsmiley")){//user has already unliked the comment

                smiley_box.data("smiley",false);
                smiley_box.data("unsmiley",true);
                smiley_box.addClass("liked");
                sckt.post(data);
                //increase the number of likes
                smiley_box.find(".smiley-num").text(num+1);

            //send a notification to the user who created the comment
            if(smiley_box.parents(".comment-pod").data("uid") != this.id){

                this.create_user_noti({
                    target_id: smiley_box.parents(".comment-pod").data("uid"),
                    to_mgcid: smiley_box.parents(".usap-pod").data("mgcid"),
                    to_type: "post",
                    text: prof.full_name+this.post_comment_like_noti_text,
                    created_at: this.timestamp()
                });
            }

        }else if(!smiley_box.data("smiley") && smiley_box.data("unsmiley")){//user has already liked the comment
            data.type = type;

                smiley_box.data("smiley",true);
                smiley_box.data("unsmiley",false);
                smiley_box.removeClass("liked");
                sckt.post(data);
                //decrease the number of likes
                smiley_box.find(".smiley-num").text(num-1==0?"":num-1);


        }
    },
    user_load_handler: function (packet) {
        //set the friend, group and unseen notifications number/count
        frndNum_mdl.populate(packet.payload.frnd_count);
        grpNum_mdl.populate(packet.payload.grp_count);
        notiNum_mdl.populate(packet.payload.noti_count);
    },


    mission_click: function (e) {
        var that = e.data.that,chck=0,ele=this;


        //check if a tab has already been created for the mission control item
        that.tab_nav.find("li").each(function () {
            if($(ele).data("tab") == $(this).data("tabname")){//a tab for the mission control item exists
                chck++;
            }
        });

        if(chck == 0 && $(ele).data("tab")){
            Tab_mdl.display_mode = "append:bottom";
            Tab_mdl.populate([
                {id:"#"+$(ele).data("tab"), name:$(ele).data("tab"), close:true}
            ]);

            that.tab_nav.find("li").each(function () {
                if($(this).hasClass("active"))$(this).removeClass("active");
                if($(ele).data("tab") == $(this).data("tabname")){
                    $(this).addClass("active");
                }
            });

            //we have to remove the active class from the current tab content before we give the active class to the new tab content
            that.tab_content.find(".tab-pane").each(function () {
                if($(this).hasClass("active"))$(this).removeClass("active");
                if($(this).attr("id") == $(ele).data("tab"))$(this).addClass("active");
            });
        }else if($(ele).data("tab")){
            that.tab_nav.find("li").each(function () {
                if($(this).hasClass("active"))$(this).removeClass("active");
                if($(ele).data("tab") == $(this).data("tabname")){
                    $(this).addClass("active");
                }
            });

            //we have to remove the active class from the current tab content before we give the active class to the new tab content
            that.tab_content.find(".tab-pane").each(function () {
                if($(this).hasClass("active"))$(this).removeClass("active");
                if($(this).attr("id") == $(ele).data("tab"))$(this).addClass("active");
            });
        }

        that.noti_tab.find("#user_noti").addClass("active");//notifications tab hack
        that.noti_tab_click({data:{that:that}});//notifications tab hack

        if(!$(this).data("loaded")){

            switch ($(ele).data("tab")){
                case "Friends":
                    //load friends of the user using ajax
                    that.DropZoneResource.fetchFriends({
                        data: {"user":that.id},
                        callback: function (data) {
                            Friend_mdl.display_mode = "apply";
                            Friend_mdl.populate(data);
                        }
                    });
                    $(this).data("loaded",true);
                    break;
                case "Bookmarks":
                    /*
                     * load post bookmarks from the dropzone controller
                     * */
                    that.DropZoneResource.fetchBookmarks({
                        data:{type:"post"},
                        callback: function (o) {
                            var bkmks = JSON.parse(o);
                            for(var bkmk in bkmks){
                                that.container_process(that.bkmrk_post,bkmks[bkmk].post,"Post_mdl","bottom");
                            }
                            $(ele).data("loaded",true);
                        }
                    });
                    break;
                case "Notifications":
                    //load the user's notifications
                    that.noti_socket.post({type:"noti_load"});
                    $(this).data("loaded",true);
                    break;
                case "Groups":
                    //load the list of groups the user belongs to
                    that.DropZoneResource.fetchGroups({
                        data: {"user":that.id},
                        callback: function (data) {
                            Groups_mdl.display_mode = "apply";
                            Groups_mdl.populate(data);
                        }
                    });
                    $(this).data("loaded",true);
                    break;
                case "Upload":
                    var id = that.page == 'user'?that.id : that.page == 'group'?that.group.id:that.dep_id;
                    that.SharedResource.fetchUploads({
                        data: {tag: that.page, t_id:id},
                        callback: function (data) {
                            Upload_mdl.display_mode = "apply";
                            Upload_mdl.populate(data);
                        }
                    });

                    $(this).data("loaded",true);
                    break;
                default:
                    break;
            }
        }

    },
    create_user_noti: function (obj) {
        var post_data = {
            type:"insert_noti",
            noti_data:{
                type: "user",
                target_id: obj.target_id,
                target_object_magic_id: obj.to_mgcid,
                target_object_type: obj.to_type,
                noti_text: obj.text,
                created_at: obj.created_at,
                perp_prof: this.user_prof,
                magic_id: this.uguid()
            }
        };
        /*alert(JSON.stringify(post_data))*/
        this.noti_socket.post(post_data);
    },
    create_dep_noti: function (obj) {
        var post_data = {
            type:"insert_noti",
            noti_data: {
                type: "department",
                department_id: obj.department_id,
                target_object_magic_id: obj.to_mgcid,
                target_object_type: obj.to_type,
                noti_text: obj.text,
                created_at: obj.created_at,
                perp_prof: this.user_prof,
                magic_id: this.uguid()
            }
        };

        this.noti_socket.post(post_data);
    },

    /**
     * function to create a group notification
     * @param obj Object  {
                group_id: obj.group_id,
                target_object_magic_id: obj.to_mgcid,
                target_object_type: obj.to_type,
                noti_text: obj.text,
                created_at: obj.created_at,
            }
    */
    create_grp_noti: function (obj) {
        var post_data = {
            type:"insert_noti",
            noti_data: {
                type: "group",
                group_id: obj.group_id,
                target_object_magic_id: obj.to_mgcid,
                target_object_type: obj.to_type,
                noti_text: obj.text,
                created_at: obj.created_at,
                perp_prof: this.user_prof,
                magic_id: this.uguid()
            }

        };

        this.noti_socket.post(post_data);
    },
    noti_tab_click: function (e) {
        var that = e.data.that;
        $(this).siblings().each(function () {
            $(this).removeClass("bar");
        });
        $(this).addClass("bar");
    },
    bkmrk_tab_click: function (e) {
        var that = e.data.that;
        $(this).siblings().each(function () {
            $(this).removeClass("bar");
        });
        $(this).addClass("bar");

        var type = $(this).data("type");
        if(!$(this).data("loaded")){
            switch (type){
                case "forum":
                    /*
                     * load the forum bookmarks from the dropzone controller
                     * */
                    that.DropZoneResource.fetchBookmarks({
                        data:{type:type},
                        callback: function (o) {
                            var bkmks = JSON.parse(o);
                            for(var bkmk in bkmks){
                                that.container_process(that.bkmrk_forum,bkmks[bkmk].forum,"Forum_mdl","bottom");
                            }
                        }
                    });
                    break;
                default :

                    break;
            }
        }
        $(this).data("loaded",true);
    },
    noti_delete: function (e) {
        var that = e.data.that,ele = this,
            post_data= {
                type: "delete_noti",
                noti_data:{
                    magic_id: $(this).parents(".noti-pod").data("mgcid"),
                    type: $(this).parents(".noti-pod").data("type")
                }
            };
        that.noti_remove(post_data,ele);

    },
    noti_remove: function (data,ele) {
        if(this.noti_socket.check()){
            //remove the noti pod from the view
            $(ele).parents(".noti-pod").hide(500, function () {
                $(ele).parents(".noti-pod").remove();
            });
        }
        this.noti_socket.post(data);

        this.noti_num("subtract");
    },
    noti_container_handler: function (e) {
        var that = e.data.that;
       /*------------------------------------------------------------------------------
        * obtain the type of notification that we are dealing with be it a forum or a post
        * obtain the magic id of the target object
        *----------------------------------------------------------------------------*/
        var noti_pod = $(this).parents(".noti-pod"),
            tom = noti_pod.data("tom"),
            top = noti_pod.data("top");

        if(top == "post"){//request for a post
            that.noti_socket.post({type:"fetch_post",post_mgcid:tom});
        }else if(top == "forum"){//request for a forum
            that.noti_socket.post({type:"fetch_forum",forum_mgcid:tom});
        }

        //cache the container of this noti pod on the noti_socket object
        that.noti_socket.container = noti_pod.find(".noti-object");
    },
    noti_num: function (action) {
        if(action == "clear"){
            this.noti_numb.text("none");
        }else if(action == "add"){
            this.noti_numb.text((isNaN(parseInt(this.noti_numb.text()))?0:parseInt(this.noti_numb.text()))+1);
        }else if(action == "subtract"){
            this.noti_numb.text((isNaN(parseInt(this.noti_numb.text()))?0:parseInt(this.noti_numb.text()))-1);
            this.noti_numb.text(parseInt(this.noti_numb.text()) == 0?"none":parseInt(this.noti_numb.text()));
        }
    },
    frnd_num: function () {
        this.frnd_numb.text((isNaN(parseInt(this.frnd_numb.text()))?0:parseInt(this.frnd_numb.text()))+1);
    },
    tab_close: function (e) {
        var that = e.data.that,ele = this;
        setTimeout(function () {
            $(ele).parents("li").prev().addClass("active");
            that.tab_content.find(".tab-pane[id='"+$(ele).parents("li").data("tabname")+"']").removeClass("active");
            that.tab_content.find(".tab-pane[id='"+$(ele).parents("li").prev().data("tabname")+"']").addClass("active");
            $(ele).parents("li").remove();
        },2);

    },
    noti_forum_comment_post: function (e) {
        var that = e.data.that;
        if(e.keyCode == 13){
            //the enter key has been pressed
            if(!that.isEmpty(this)){
                var socket_data = {},field = $(this),
                    forum_comment_data = {
                        text: field.val(),
                        user_prof: that.user_prof,
                        created_at: that.timestamp(),
                        forum_magic_id: field.parents(".usap-pod").data("mgcid"),
                        magic_id: that.uguid(),
                        forum_type: field.parents(".usap-pod").data("type"),
                        user_id: field.parents(".usap-pod").data("id"),
                        smileys:[]
                    };
                field.val("");

                that.noti_socket.container = $(this).parents(".usap-pod").find("*[data-sw-editable]");

                //check the comments for this forum have been loaded
                if(field.parents(".usap-pod").data("commentLoad")){//comments have been loaded
                    that.comment_render(forum_comment_data.forum_magic_id,[forum_comment_data],"noti:forum");
                }else{//comments not loaded
                    //load previous comments before you add the new one
                    //obtain the magic id of the forum
                    var post_data = {
                        type: "noti_forum_comment_load",
                        magic_id: forum_comment_data.forum_magic_id
                    };


                    //send the forum_comment to the server if the user is connected to it
                    that.noti_socket.post(post_data);
                    field.parents(".usap-pod").data("commentLoad",true);

                    that.comment_render(forum_comment_data.forum_magic_id,[forum_comment_data],"noti:forum");
                }

                socket_data.forum_comment_data = forum_comment_data;
                socket_data.type = "forum_comment";


                //send the forum_comment to the socket server
                that.noti_socket.post(socket_data);

                //send a notification to the user the forum belongs to
                if(field.parents(".usap-pod").data("id") != that.id){
                    that.create_user_noti({
                        target_id: forum_comment_data.user_id,
                        to_mgcid: forum_comment_data.forum_magic_id,
                        to_type: "forum",
                        text: that.user_prof.full_name + that.forum_comment_noti_text,
                        created_at: forum_comment_data.created_at
                    });
                }

            }else{
                $(this).val("");
            }

        }
    },
    noti_forum_comments_load: function (e) {
        if(!$(this).parents(".usap-pod").data("commentLoad")) {
            var that = e.data.that,
            //obtain the magic id of the forum
                post_data = {
                    type: "forum_comment_load",
                    magic_id: $(this).parents(".usap-pod").data("mgcid")
                };

            //send the forum_comment to the server if the user is connected to it
            that.noti_socket.post(post_data);
            $(this).parents(".usap-pod").data("commentLoad",true);
            that.noti_socket.container = $(this).parents(".usap-pod").find("*[data-sw-editable]");
        }

    },
    noti_forum_comment_smiley: function (e) {
        var that = e.data.that,
            smiley_box = $(this);

        that.forum_comnt_smiley_engine(smiley_box,that,that.frm_socket);
    },
    noti_post_comment_post: function (e) {
        var that = e.data.that;
        if(e.keyCode == 13){
            if(!that.isEmpty(this)){
                var socket_data = {},field = $(this),
                    mgcid = field.parents(".usap-pod").data("mgcid"),
                    post_comment_data = {
                        text:field.val(),
                        user_prof:that.user_prof,
                        created_at:that.timestamp(),
                        post_magic_id: mgcid,
                        magic_id: that.uguid(),
                        target: field.parents(".usap-pod").data("target"),
                        user_id: field.parents(".usap-pod").data("id"),
                        smileys:[]
                    };
                field.val("");
                that.noti_socket.container = $(this).parents(".usap-pod").find("*[data-sw-editable]");
                //check the comments for this forum have been loaded
                if(field.parents(".usap-pod").data("commentLoad")){//comments have been loaded
                    that.comment_render(mgcid,[post_comment_data],"noti:post");
                }else{//comments not loaded
                    //load previous comments before you add the new one
                    var post_data = {
                        type: "post_comment_load",
                        magic_id: mgcid
                    };


                    //send the post_comment_load request to the server
                    that.post_socket.post(post_data);
                    field.parents(".usap-pod").data("commentLoad",true);

                    that.comment_render(mgcid,[post_comment_data],"noi:post");
                }



                socket_data.post_comment_data = post_comment_data;
                socket_data.type = "post_comment";
                //send the post_comment to the server if the user is connected to it
                that.noti_socket.post(socket_data);

                //send a notification to the user the post belongs to
                if(post_comment_data.user_id != that.id){
                    that.create_user_noti({
                        target_id: post_comment_data.user_id,
                        to_mgcid: mgcid,
                        to_type: "post",
                        text: that.user_prof.full_name + that.post_comment_noti_text,
                        created_at: post_comment_data.created_at
                    });
                }

            }else{
                $(this).val("");
            }
        }
    },
    noti_post_comment_load: function (e){

        if(!$(this).parents(".usap-pod").data("commentLoad")){
            var that = e.data.that,
            //obtain the magic id of the post
                post_data = {
                    type: "post_comment_load",
                    magic_id: $(this).parents(".usap-pod").data("mgcid")
                };

            //send the post_comment_load request to the server
            that.noti_socket.post(post_data);
            $(this).parents(".usap-pod").data("commentLoad",true);
            that.noti_socket.container = $(this).parents(".usap-pod").find("*[data-sw-editable]");
        }
    },
    noti_post_smiley: function (e) {
        var that = e.data.that,
            post_smiley_box = $(this),
        //obtain the data needed to create the smiley
            post_target = post_smiley_box.parents(".usap-pod"),
            post_data = {
                post_smiley_data:{
                    target: post_target.data("target"),
                    user_id: post_target.data("id"),
                    post_magic_id: post_target.data("mgcid")
                },
                type: "post_smiley"
            };


        //check if the user has liked the post
        that.smiley_box_process(post_smiley_box,that.post_socket,post_data,"post_smiley_del");

    },
    noti_post_comment_smiley: function (e) {
        var that = e.data.that,
            smiley_box = $(this),
        //obtain data needed to create the smiley
            post_target = smiley_box.parents(".usap-pod"),
            post_comment = smiley_box.parents(".comment-pod"),
            post_data = {
                post_comment_smiley_data: {
                    target: post_target.data("target"),
                    user_id: post_target.data("id"),
                    comment_magic_id: post_comment.data("comment"),
                    post_magic_id: post_target.data("mgcid")
                },
                type: "post_comment_smiley"
            };

        //check if the user has liked the comment
        that.comment_smiley_box_process(smiley_box,that.post_socket,post_data,"post_comment_smiley_del");

    },
    textarea_anim: function (e) {
        var that = e.data.that;
        $(this).parents(".create-form").css({height:"auto"});
        $(this).animate({height:"4.5em"},400);
    },
    frnd_req_accpt_handler: function (e) {
        var that = e.data.that,ele = this,
            perp_id = $(this).parents(".friend-req").data("perp"),
            post_data = {
                type: "friend-accept",
                accept_data: {
                    uid: perp_id,
                    magic_id: $(this).parents(".noti-pod").data("tom")
                }
            };
        that.startup_socket.post(post_data);
        if(that.startup_socket.check()){
            that.frnd_num();
            //delete the notification from the database if the friend request is successfully accepted
            post_data= {
                type: "delete_noti",
                noti_data:{
                    magic_id: $(this).parents(".noti-pod").data("mgcid"),
                    type: $(this).parents(".noti-pod").data("type")
                }
            };
            that.noti_remove(post_data,ele);
        }

    },
    frnd_req_rejct_handler: function (e) {
        var that = e.data.that,ele = this,
            post_data = {
                type: "friend-reject",
                reject_data: {
                    magic_id: $(this).parents(".noti-pod").data("tom")
                }
            };

        that.startup_socket.post(post_data);
        if(that.startup_socket.check()){
            $(this).parents(".noti-pod").hide(500, function () {
                $(ele).parents(".noti-pod").remove();
            });
        }
    },
    grp_join_handler: function (e) {
        var that = e.data.that;
        that.GroupResource.groupJoin({
            data: {"user_id": that.id, "grp_id":that.group.id},
            callback: function (o) {
                var data = JSON.parse(o);
                if(data.state){
                   /*
                    * redirect the user to the dropzone of the group the user just joined
                    * */
                    window.location = "http://localhost/usap/group/"+that.group.name;
                }
            }
        });
    },
    flag_click: function (e) {
        var that = e.data.that;
        if($(this).data("opened")){//menu opened
            $(this).next().hide();
            $(this).data("opened",false);
        }else{//menu closed
            $(this).next().show();
            $(this).data("opened",true);
        }
    },
    post_bookmark: function (e) {
        var that = e.data.that;
        that.SharedResource.setBookmark({
            data:{
                bkmrk_type:"post",
                magic_id: $(this).parents(".usap-pod").data("mgcid"),
                created_at: that.timestamp()
            }
        })
    },
    forum_bookmark: function (e) {
        var that = e.data.that;
        that.SharedResource.setBookmark({
            data:{
                bkmrk_type:"forum",
                magic_id: $(this).parents(".usap-pod").data("mgcid"),
                created_at: that.timestamp()
            }
        })
    },
    post_flag: function (e) {
        var that = e.data.that;
        that.SharedResource.flag({
            data:{
                flag_type:"post",
                magic_id: $(this).parents(".usap-pod").data("mgcid")
            }
        })
    },
    forum_flag: function (e) {
        var that = e.data.that;
        that.SharedResource.flag({
            data:{
                flag_type:"forum",
                magic_id: $(this).parents(".usap-pod").data("mgcid")
            }
        })
    },
    search_handler: function (e) {
        var that = e.data.that,ele = this;
        /*-------------------------------------
         * send the search query to the server
         *-----------------------------------*/
        if(!that.isEmpty($(this).next("input"))){
            that.SharedResource.search({
                data: {query:$(this).next("input").val()},
                callback: function (o) {
                    var data = JSON.parse(o);
                    SearchUser_mdl.display_mode = "apply";
                    SearchUser_mdl.populate(data.users);

                    SearchGrp_mdl.display_mode = "apply";
                    SearchGrp_mdl.populate(data.groups);
                    $(ele).data("loaded",true);
                }
            });
        }

    },
    upload_submit_handler : function (e) {
        var that = e.data.that;
        $(this).find("#magc_id").val(that.uguid());
        //check if the file size is not greater than the upload file size limit
        var form = $(this),
            file_data = form.find("input[type='file']")[0].files[0];
        if(file_data.size > (1*1024*1024)){
            alert("File size too large. Maximum upload size is 1Mb");
            return false;
        }
    },
    forum_comment_noti_text:" commented on a Forum you created",
    forum_comment_like_noti_text: " liked a comment you wrote on a Forum",
    post_comment_noti_text: " commented on a Post you created",
    post_comment_like_noti_text: " liked a comment you wrote on a Post",
    post_like_noti_text : " liked a Post you created",
    friend_req_text: " has sent you a friend request"
};

var drpzone_events =
{
    submit : {target:"forum_form", handler: "forum_submit_handler"}
};

var usap_ele = {
    upload_form: "#upload-form",
    forum_form: "#forum-pane .create-form form",
    post_form: "#post-pane .create-form form",
    forum_usap_pod_wrapper: "#forum-pane #usap-pod-wrapper",
    post_usap_pod_wrapper: "#post-pane #usap-pod-wrapper",
    mission_control: "#mission-control",
    tab_nav: "#tab-nav",
    tab_content: ".tab-content",
    noti_tab:"#Notifications",
    bkmrk_tab:"#Bookmarks",
    noti_numb:"#noti-num",
    frnd_numb: "#frnd-num",
    textarea: ".create-form textarea",
    profile:".profile-details",
    frnd_req_btn: "#friend-request",
    grp_create: ".grp-creation-form",
    grp_join: ".join",
    bkmrk_post:"#post-bkmrk",
    bkmrk_forum:"#forum-bkmrk",
    search_btn: ".search-btn",
    p_flag:".p-flag"
};

var ussap_events = {
    click:[
        {target:"noti_tab", selector: "*[data-top='forum'] .comment-num", handler:"noti_forum_comments_load", type:"delegate"},
        {target:"noti_tab", selector: "*[data-top='forum'] .smiley", handler:"noti_forum_comment_smiley", type:"delegate"},
        {target:"noti_tab", selector: "*[data-top='post'] .comment-num", handler:"noti_post_comment_load", type:"delegate"},
        {target:"noti_tab", selector: "*[data-top='post'] .comments-cont-header  .smiley", handler:"noti_post_smiley", type:"delegate"},
        {target:"noti_tab", selector: "*[data-top='post'] .comment-pod-wrapper .smiley", handler:"noti_post_comment_smiley", type:"delegate"},
        {target:"mission_control", selector: "li", handler:"mission_click", type:"delegate"},
        {target:"tab_nav", selector: ".close-item", handler:"tab_close", type:"delegate"},
        {target:"tab_nav", selector: "li[data-tabname='Posts']", handler:"post_pane_click", type:"delegate"},
        {target:"noti_tab", selector: ".noti-menu li", handler:"noti_tab_click", type:"delegate"},
        {target:"noti_tab", selector: ".noti-close", handler:"noti_delete", type:"delegate"},
        {target:"noti_tab", selector: ".show-container", handler:"noti_container_handler", type:"delegate"},
        {target:"noti_tab", selector: "#friend-req-accept", handler:"frnd_req_accpt_handler", type:"delegate"},
        {target:"bkmrk_tab", selector: ".noti-menu li", handler:"bkmrk_tab_click", type:"delegate"},
        {target:"post_usap_pod_wrapper", selector: ".flag-ico", handler:"flag_click", type:"delegate"},
        {target:"post_usap_pod_wrapper", selector: ".p-bkmrk", handler:"post_bookmark", type:"delegate"},
        {target:"post_usap_pod_wrapper", selector: ".p-flag", handler:"post_flag", type:"delegate"},
        {target:"forum_usap_pod_wrapper", selector: ".flag-ico", handler:"flag_click", type:"delegate"},
        {target:"forum_usap_pod_wrapper", selector: ".f-bkmrk", handler:"forum_bookmark", type:"delegate"},
        {target:"forum_usap_pod_wrapper", selector: ".f-flag", handler:"forum_flag", type:"delegate"},
        {target:"noti_tab", selector: "#friend-req-reject", handler:"frnd_req_rejct_handler", type:"delegate"},
        {target:"search_btn", handler:"mission_click"},
        {target:"search_btn", handler:"search_handler"}
    ],
    keyup:
        [
            {target: "noti_tab", selector: "*[data-top='forum'] textarea", handler: "noti_forum_comment_post", type:"delegate"},
            {target: "noti_tab", selector: "*[data-top='post'] textarea", handler: "noti_post_comment_post", type:"delegate"}
        ],
    focus: [{target:"textarea", handler:"textarea_anim"}]
};






