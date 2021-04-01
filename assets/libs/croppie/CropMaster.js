var cropMaster = new CropMaster();
function CropMaster()
{
    var _this = this;
    _this.uploadCrop;
    _this.divid = "";
    _this.vwidth = 160; // view port width
    _this.vheight = 160; //view port height
    _this.bwidth = 300; //boundary width
    _this.bheight = 300; //boundary height
    _this.owidth = 300; //output width
    _this.oheight = 300; //output height
    _this.viewtype = "square";// or 'circle'
    _this.imgurl = "";
    _this.rawimg="";
    _this.rawfile="";
    this.setup = function (divid, vw, vh, bw, bh, ow, oh, vt) {
        _this.divid = divid;
        _this.vwidth = vw;
        _this.vheight = vh;
        _this.bwidth = bw;
        _this.bheight = bh;
        _this.owidth = ow;
        _this.oheight = oh;
        _this.viewtype = vt;
        _this.rawfile="";
        _this.rawimg="";
        _this.uploadCrop = $('#' + divid).croppie({
            // viewport options
            viewport: {
                width: _this.vwidth,
                height: _this.vheight,
                type: _this.viewtype
            },
            // boundary options
            boundary: {
                width: _this.bwidth,
                height: _this.bheight
            },
            mouseWheelZoom: true,
            showZoomer: true
        });
    }

    // bind an image to croppie
    this.bind = function (imgurl) {
        _this.imgurl = imgurl;
        $('#' + _this.divid).croppie('bind', {
            url: _this.imgurl
        });
    }
    // get result from croppie
    //$('#demo').croppie('get');

    this.getCropImg = function (callback) {
        $('#' + _this.divid).croppie('result', {
            type: 'canvas',
            format : 'jpeg',
            quality: 1,
            backgroundColor : "#ffffff",
            size:{
                width:_this.owidth,
                height:_this.oheight
            }
            //size: 'viewport'
        }).then(function (img) {
            _this.rawimg=img;
            console.log(img);
            callback(img);
        });
    }
    
    /*
    $dt["vwidth"] = 300; //default out width will be 4 point plus that means 200px
    $dt["vheight"] = 225; //default out height will be 4 point plus that means 200px
    $dt["bwidth"] = 300;
    $dt["bheight"] = 225;
    $dt["isimportreq"] = TRUE;
    */        

    this.addFileDialog = function (fileid, callback) {
        $('#' + fileid).on('change', function () {
            var reader = new FileReader();
            reader.onload = function (e) {
                _this.uploadCrop.croppie('bind', {
                    url: e.target.result
                }).then(function () {
                    //console.log('jQuery bind complete');
                    
                });
                callback();
            }
            reader.readAsDataURL(this.files[0]);
        });
    }
}