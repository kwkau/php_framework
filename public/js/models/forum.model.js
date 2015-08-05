
    var Forum_mdl = Model.create ("Forum_mdl");

    Forum_mdl.attributes =
    {
        id : 0,
        tag: "",
        created_at: "",
        comments: 0,
        user_prof: {},
        topic: "",
        type: "",
        magic_id: "",
        department: "",
        group_id: 0
    };
    Forum_mdl.display_mode = "append:top";

    var Comment_mdl = Model.create("Comment_mdl");
    Comment_mdl.attributes =
    {
        id: 0,
        magic_id: "",
        user_prof:{},
        text: {},
        created_at:"",
        smileys:[]
    };

    var Forum_form_mdl = Model.create("Forum_form_mdl");

    Forum_form_mdl.attributes =
    {
        topic: "",
        tag: "",
        type: "",
        timezone: "",
        comments: [],
        magic_id: "",
        user_prof: {},
        created_at:""
    };

    Forum_form_mdl.populate({
        topic: "",
        tag: "",
        type: "",
        comments: [],
        timezone:"",
        magic_id: "",
        user_prof: {},
        created_at:""
    });

    var Noti_Forum_mdl = Model.create("Noti_Forum_mdl");

    Noti_Forum_mdl.attributes = {
        id : 0,
        tag: "",
        created_at: "",
        comments: 0,
        user_prof: {},
        topic: "",
        type: "",
        magic_id: "",
        department: "",
        group_id: 0
    };