

    var UserNoti_mdl = Model.create("UserNoti_mdl");

    UserNoti_mdl.attributes = {
        id:0,
        type:"",
        created_at:"",
        perp_prof:{},
        noti_text:"",
        target_id:0,
        target_object_magic_id:0,
        target_object_type:"",
        magic_id:""
    };

    var DepNoti_mdl = Model.create("DepNoti_mdl");

    DepNoti_mdl.attributes = {
        id:0,
        type:"",
        created_at:"",
        perp_prof:{},
        noti_text:"",
        department_id:0,
        target_object_magic_id:0,
        target_object_type:"",
        magic_id:""
    };

    var GroupNoti_mdl = Model.create("GroupNoti_mdl");

    GroupNoti_mdl.attributes = {
        id:0,
        type:"",
        created_at:"",
        perp_prof:{},
        noti_text:"",
        group_id:0,
        target_object_magic_id:0,
        target_object_type:"",
        magic_id:""
    };