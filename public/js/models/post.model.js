
    var Post_mdl = Model.create("Post_mdl");

    Post_mdl.attributes =
    {
        id:0,
        magic_id: "",
        user_prof : "",
        content_type:"",
        post_text : "",
        pic_url : "",
        comments : 0,
        smileys:0,
        flag: 0,
        created_at: "",
        post_type:"",
        target:""
    };


    var Post_form_mdl = Model.create("Post_form_mdl");
    Post_form_mdl.attributes = {
        id:0,
        content_type:"",
        smileys : [],
        comments : [],
        magic_id:"",
        post_text:"",
        created_at:"",
        pic_url:null,
        flag:0,
        target:""
    };

    Post_form_mdl.populate({
        id:0,
        content_type:"",
        smileys : [],
        comments : [],
        magic_id:"",
        post_text:null,
        created_at:"",
        pic_url:null,
        flag:0,
        target:""
    });

    var Noti_Post_mdl = Model.create("Noti_Post_mdl");

    Noti_Post_mdl.attributes = {
        id:0,
        magic_id: "",
        user_prof : "",
        content_type:"",
        post_text : "",
        pic_url : "",
        comments : 0,
        smileys:0,
        flag: 0,
        created_at: "",
        post_type:"",
        target:""
    };