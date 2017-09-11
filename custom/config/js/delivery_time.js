$(function(){
    $('.segment').dimmer('show');
    render_table();
    load_default();

    $("#reset").click(reset);
    $("#save").click(save);
});

function render_table() {
    for(var i = 0;i<24;i++){
        var tr_name = "timerange_"+ i;
        $("#main_delivery_time").append("<tr id='"+tr_name+"'></tr>");
        $("#"+tr_name).append("<td>"+i+":00 - "+(i+1)+":00</td>");
        for(var j = 0;j<7;j++){
            var td_name = "td_"+i+"_"+j;
            var td_input_name = "td_input_"+j+"_"+i;
            $("#"+tr_name).append("<td id='"+td_name+"'></td>");
            $("#"+td_name).append("<div class='inline field'></div><div class='inline field input_field'><input class='order_numbers' type='number' id='"+td_input_name+"'></div>")
        }
    }
}

function clear(){
    $(".order_numbers").val(null);
}

function load_default(){
    var param = {};
    param.type = "config";
    $.ajax({
        type: "get",
        url : basePath+"/shipping/time?t="+new Date().getTime(),
        data: param,
        success : function(res){
            res = eval("("+res+")");
            if(res.success == 1){
                clear();
                for(var i = 0; i<7;i++){
                    for(var j=0;j<res.data[i].length;j++){
                        $("#td_input_"+i+"_"+res.data[i][j]).val(3);
                    }
                }
                load_custom();
            }

        }
    });
}

function load_custom(){
    $.ajax({
        type: "get",
        url : basePath+"/delivery/time?t="+new Date().getTime(),
        success : function(res){
            res = eval("("+res+")");
            if(res.success == 1){
                for(var i = 0;i<res.data.length;i++){
                    for(var v in res.data[i]){
                        $("#td_input_"+i+"_"+v).val(res.data[i][v]);
                    }
                }
            }
            $('.segment').dimmer('hide');
        }
    });
}

function save(){
    var time_config = [];
    for(var i = 0;i<7;i++){
        var _date_config = [];
        for(var j = 0;j<24;j++) {
            var td_input_name = "td_input_"+i+"_"+j;
            _date_config.push({"time":j,"value":$("#" + td_input_name).val()});
        }
        time_config.push(_date_config);
    }
    var param = {};
    param.configs = time_config;
    $.ajax({
        type: "put",
        url : basePath+"/delivery/time?t="+new Date().getTime(),
        data: param,
        success : function(res){
            res = eval("("+res+")");
            if(res.success == 1){
                console.log(res);
            }
        }
    });
    console.log(time_config);
    alert("Saved");
}

function reset(){
    load_default();
    save();
}