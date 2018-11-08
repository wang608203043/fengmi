function Cut(img,inputImg) {
    this.$image = $(img);
    this.$inputImage = $(inputImg);
}

Cut.prototype = {
    init:function (options) {
            var _this = this;
            _this.$image.on({
                'build.cropper': function (e) {
                    console.log(e.type);
                },
                'built.cropper': function (e) {
                    console.log(e.type);
                },
                'dragstart.cropper': function (e) {
                    console.log(e.type, e.dragType);
                },
                'dragmove.cropper': function (e) {
                    console.log(e.type, e.dragType);
                },
                'dragend.cropper': function (e) {
                    console.log(e.type, e.dragType);
                },
                'zoomin.cropper': function (e) {
                    console.log(e.type);
                },
                'zoomout.cropper': function (e) {
                    console.log(e.type);
                }
            }).cropper(options);

            _this.$inputImage.change(function () {
                var files = this.files,
                    file;

                if (files && files.length) {
                    file = files[0];

                    if (/^image\/\w+$/.test(file.type)) {
                        blobURL = URL.createObjectURL(file);
                        _this.$image.one('built.cropper', function () {
                            URL.revokeObjectURL(blobURL); // Revoke when load complete
                            console.log(blobURL)
                        }).cropper('reset', true).cropper('replace', blobURL);
                        _this.$inputImage.val('');
                    } else {
                        alert('Please choose an image file.');
                    }
                }
            });
    },
    getCroppedCanvas: function (option) {
        return this.$image.cropper('getCroppedCanvas',option);
    },
    reset:function () {
        this.$image.cropper('reset',true);
    }
}