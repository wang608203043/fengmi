function reDraw(base64, bili,callback,img_container,upload_url) {
    console.log("执行缩放程序,bili=" + bili);
    var _img = new Image();
    _img.src = base64;
    _img.onload = function () {
        var _canvas = document.createElement("canvas");
        var w = this.width / bili;
        var h = this.height / bili;
        _canvas.setAttribute("width", w);
        _canvas.setAttribute("height", h);
        _canvas.getContext("2d").drawImage(this, 0, 0, w, h);
        var base64 = _canvas.toDataURL("image/jpg");
        _canvas.toBlob(function (blob) {
            console.log(blob.size);
            if (blob.size > 1024 * 1024) {
                reDraw(base64, bili, callback,img_container,upload_url);
            } else {
                callback(blob, base64,img_container,upload_url);
            }
        }, "image/jpg");
    }
}

//img_container 图片容器$('#container') upload_url上传路径
function filesChange(fileList,img_container,upload_url,callback){
    var fr = new FileReader();
    var i = 0;
    var index = setInterval(function () {
        fr.readAsDataURL(fileList[i]);
        fr.onloadend = function (e) {
            reDraw(e.target.result,2,callback,img_container,upload_url)
        };
        i++;
        if (i===fileList.length){
            layer.msg('上传完成!',{icon:1,time:1000})
            clearInterval(index);
        }
    },300);
}

// function (blob, base64) {
//     var img = '<img src="'+base64+'" style="margin-right: 5px">';
//     img_container.append(img);
//     $.ajax({
//         url: upload_url,
//         async: false,
//         type:'post',
//         data:{file:base64},
//         processData: false,//用于对data参数进行序列化处理 这里必须false
//         contentType: false, //必须
//         success:function (res) {
//             console.log(i,res);
//         }
//     });
// }