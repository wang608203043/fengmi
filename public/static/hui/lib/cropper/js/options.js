function CutOption(options) {
    $.extend(true,this,options)
}

CutOption.prototype = {
    // strict: false,
    //responsive: false,
    //checkImageOrigin: false,

    // modal: false,
    //guides: true,
    //highlight: true,
    // background: false,

    //  autoCrop: false,
    autoCropArea: 0.5,
    //dragCrop: false,
    // movable: false,
    resizable: false,
    rotatable: false,
    // zoomable: false,
    // touchDragZoom: false,
    // mouseWheelZoom: false,

    minCanvasWidth: 400,
    minCanvasHeight: 400,
    minCropBoxWidth: 400,
    minCropBoxHeight: 400,
    minContainerWidth: 400,
    minContainerHeight: 400,

    //build: null,
    // built: null,
    // dragstart: null,
    // dragmove: null,
    // dragend: null,
    // zoomin: null,
    // zoomout: null,

    aspectRatio: 160/80,
    preview: '.img-preview',
    crop: function (data) {

    }
}