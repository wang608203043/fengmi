function layerFull(title,url) {
    var index = layer.open({
        type: 2,
        title: title,
        content: url
    });
    layer.full(index);
}

function dels(obj,url,id) {
    layer.confirm('确认要删除吗？',function(index){
        $.ajax({
            type: 'POST',
            url: url,
            data:{id:id},
            dataType: 'json',
            success: function(res){
                if (res.code === 10000){
                    $(obj).parents("tr").remove();
                    layer.msg('已删除!',{icon:6,time:1000});
                }else {
                    layer.msg(res.message,{icon: 2,time:1000});
                }
            },
            error:function(data) {
                console.log(data);
            }
        });
    });
}

/*批量删除*/
function chunk_del(url){
    var ids = [];
    $("input[name='ids']:checked").each(function (index,element) {
        ids.push(element.value)
    })
    if (ids.length>0){
        $.post(url,{ids:ids},function (res) {
            if (res.code==1){
                layer.msg('删除成功!',{icon: 1,time:1000});
                setTimeout(function () {
                    location.reload();
                },1000)

            }
            if (res.code==0){
                layer.msg(res.message,{icon: 2,time:1000});
                setTimeout(function () {
                    location.reload();
                },1000)
            }
        });
    }
}

function stop(obj,id,down_text,up_text,url){
    layer.confirm('确认要'+down_text+'吗？',function(index){
        $.post(url,{id:id,is_down:0},function (res) {
            if (res.code==1){
                $(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="start(this,'+id+',\''+down_text+'\',\''+up_text+'\',\''+url+'\')" href="javascript:;" title="'+up_text+'"><i class="Hui-iconfont">&#xe603;</i></a>');
                $(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius">'+down_text+'</span>');
                $(obj).remove();
                layer.msg('已'+down_text+'!',{icon: 5,time:1000});
            }
            if (res.code==0){
                layer.msg(res.message,{icon: 7,time:1000});
            }
        })

    });
}

function start(obj,id,down_text,up_text,url){
    layer.confirm('确认要'+up_text+'吗？',function(index){
        $.post(url,{id:id,is_down:1},function (res) {
            if (res.code==1){
                $(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="stop(this,'+id+',\''+down_text+'\',\''+up_text+'\',\''+url+'\')" href="javascript:;" title="'+down_text+'"><i class="Hui-iconfont">&#xe6de;</i></a>');
                $(obj).parents("tr").find(".td-status").html('<span class="label label-success radius">'+up_text+'</span>');
                $(obj).remove();
                layer.msg('已'+up_text+'!',{icon: 6,time:1000});
            }
            if (res.code==0){
                layer.msg(res.message,{icon: 7,time:1000});
            }
        })

    });
}

function direct(obj,direct,id,url) {
    if (direct==1){
        layer.confirm('确认要取消推荐吗？',function(index){
            $.post(url,{is_direct:0,id:id},function (res) {
                if (res.code==1){
                    $(obj).removeClass('label-success');
                    $(obj).addClass('label-defaunt');
                    $(obj).text('未推荐');
                    $(obj).attr('title','点击推荐');
                    $(obj).attr('onClick','direct(this,0,'+id+')');
                    layer.msg('已取消!',{icon: 5,time:1000});
                }else {
                    layer.msg(res.message,{icon: 7,time:1000});
                }
            });

        });
    }else {
        layer.confirm('确认要推荐吗？',function(index){
            $.post(url,{is_direct:1,id:id},function (res) {
                if (res.code==1){
                    $(obj).removeClass('label-defaunt');
                    $(obj).addClass('label-success');
                    $(obj).text('已推荐');
                    $(obj).attr('title','点击取消推荐');
                    $(obj).attr('onClick','direct(this,1,'+id+')');
                    layer.msg('已推荐!',{icon: 6,time:1000});
                }else {
                    layer.msg(res.message,{icon: 7,time:1000});
                }
            });
        });
    }
}

function showModel(model){
    $(model).modal("show")
}

function readFileAsBase64(obj,img_element) {
    var file = obj.files[0];
    //限定上传文件的类型，判断是否是图片类型
    if (!/image\/\w+/.test(file.type)) {
        alert("只能选择图片");
        return false;
    }
    var reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function (e) {
        base64=this.result;
        $(img_element).attr('src',this.result);
    }
}