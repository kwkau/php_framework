Controller.create("home",{
    elements:{body:"body",header:"header",footer:"footer"},
    events:{
        click:[
            {target:"body", handler:"body_click"},
            {target:"header", handler:"header_click"},
            {target:"footer", handler:"footer_click"}
        ]
    },
    init: function(){
        alert("our controller has just been loaded");
    },
    body_click: function () {
        alert("you have clicked my body");
    },
    header_click: function () {
        alert("you have clicked my head");
    },
    footer_click: function () {
        alert("you have clicked my foot");
    }
});