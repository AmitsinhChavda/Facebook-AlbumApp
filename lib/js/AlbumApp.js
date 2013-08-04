//AlbumApp Script

//Download All / Move All /Download Selected/Move Selected

function downloadalbum(id)
{
    var Checkbox="";
    var move=0; //move=1 then share otherwise Download

    //Download All / Move All /Download Selected/Move Selected
    if(id=='da' || id=='ma')
    {
        $('input:checkbox').attr('checked','checked');
        $('input[type=checkbox]').each(function () {
            if(Checkbox=="")
            {
                Checkbox=$(this).val();

            }else
            {
                Checkbox=Checkbox+","+$(this).val();
            }
        });
    }else if(id=='ds' || id=='ms')
    {
        $('input[type=checkbox]').each(function () {
            if(this.checked)
            {
                if(Checkbox=="")
                {
                    Checkbox=$(this).val();

                }else
                {
                    Checkbox=Checkbox+","+$(this).val();
                }
            }
        });
    }
    if(id=='ma' || id=='ms')
    {
        move=1;
    }
    if((id=="ds" || id=='ms') && Checkbox=="")
    {
        $("html, body").animate({ scrollTop: 0 }, "slow");
        $('#msg').html('Please Select at least one Album.');
        $('#alertbox').removeClass();
        $('#alertbox').addClass('alert-box');
        $('#alertbox').addClass('alert');
        $('#alertbox').show();
    }else
    {
        if(move==1)
        {
            proDownload(Checkbox,move);
        }else
        {
            $('#move').val(move);
            $('#albumlist').val(Checkbox);
	     $('#emailmsg').hide();
            $('#fbEmail').val('');
            $.fancybox.open({
                href     : "#dumpLink",
                autoSize : true,
                fitToView: true
            });
        }

    }
}


//Choose option Download Zip 0r Notify me letter
function options(option)
{
    
    if(option=='Download')
    {
	 $.fancybox.close();

        if($('#photoId').val()!="")
        {
            showAjaxLoader();
            $.ajax({
                type:"POST",
                url: "album/downloadalbum",
                data:"photo_id="+$('#photoId').val()+"+&move="+$('#move').val(),
                success:function(result)
                {
                    hideAjaxLoader();
        	      $('#photoId').val('');
                    $('#albumlist').val('');
                    $('#move').val('');
                    if(result!='error')
                    {
                        zip(result);
                    }
                }
            });

        }else{
            proDownload($('#albumlist').val(),$('#move').val());
        }
    }else
    {
       if($('#fbEmail').val()!=''){

            //check valid email address
            var x=$('#fbEmail').val();
            var atpos=x.indexOf("@");
            var dotpos=x.lastIndexOf(".");
            if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
            {
                $('#emailmsg').html('Not a valid Email address.');
                $('#emailmsg').show();
            }else
            {
                $.fancybox.close();
		  
                $('input:checkbox').removeAttr('checked');
                $.ajax({
                    type:"POST",
                    url: "album/downloadBack",
                    data:"selected_checkbox="+$('#albumlist').val()+"&move="+$('#move').val()+"&email="+$('#fbEmail').val(),
                    success:function(){
                        $('#emailmsg').hide();
		          $('#photoId').val('');
		          $('#albumlist').val('');
                        $('#move').val('');
                    }
                });
            }
        }else{
           $('#emailmsg').html('Email id required.');
           $('#emailmsg').show();
        }
    }
    
}

// Download With progressbar
function proDownload(Checkbox,move)
{
    showAjaxLoader();
	 $.ajax({
        type:"POST",
        url: "album/CheckCache",
        data:"selected_checkbox="+Checkbox+"&move="+move,
        success:function(Result){
        if(Result==0)
        {

        $('.ajax_loader').hide();
        $('.progress').show();
        $("html, body").animate({ scrollTop: 0 }, "slow");

        $.getJSON('album/albumdetail?selected_checkbox='+Checkbox+'&move='+move, function(data) {
            var totalPhotos=data[0]['all']
            var timestamp=data[0]['timestamp'];
            var pro=100/totalPhotos;
            cur=0;

            var totalAlbum=data.length;
            $.each(data, function(key, val) {
                var albumName=val.name;
                var total=val.total;
                var left=total;
                $.getJSON('album/createAlbum?name='+val.name+'&move='+move+'&id='+val.id+'&timestamp='+timestamp, function(data) {

                    $.each(data, function(i, photo) {
                        if(move==1)
                        {
                            var url="name="+albumName+"&move="+move+"&source="+photo.source+'&picasaId='+photo.picasaId+'&timestamp='+timestamp;
                        }else{
                            var url="name="+albumName+"&move="+move+"&source="+photo.source+'&timestamp='+timestamp;
                        }
                        $.ajax({
                            type:"POST",
                            url: "album/saveAlbum",
                            data:url,
                            success:function(Result){
                                cur=cur+1;
                                left=left-1;
                                $('.meter').width((pro * (cur))+'%');
                                $('.pr_photos').html(left+'/'+total +' Left');
                                $('.pr_album').html(albumName);
                                if(key==totalAlbum-1 && left==0)
                                {
                                    $('.meter').width('100%');
                                    $('.pr_photos').html('Done');
                                    var t=setTimeout(function(){
                                        //Create Zip
                                        $.ajax({
                                            type:"POST",
                                            url: "album/CreateZip",
                                            data:"move="+move+'&timestamp='+timestamp,
                                            success:function(Result){
                                                hideAjaxLoader();
                                                $('.progress').hide();
                                                $('.meter').width('0%');
                                                $('.pr_photos').html('');
                                                $('.pr_album').html('Preparing');
                                                $('input:checkbox').removeAttr('checked');
                                                $('#photoId').val('');
                                                $('#albumlist').val('');
                                                $('#move').val('');
                                                if(move==0)
                                                {
                                                   zip(timestamp);
                                                }else
                                                {
                                                    $("html, body").animate({ scrollTop: 0 }, "slow");
                                                    $('#msg').html('Album Shared successfully.');
                                                    $('#alertbox').removeClass();
                                                    $('#alertbox').addClass('alert-box');
                                                    $('#alertbox').addClass('success');
                                                    $('#alertbox').show();
                                                }
                                            }
                                        });
                                    },100);

                                }
                            }
                        });
                    });
                });
            });
        });
        }else
        {
            hideAjaxLoader();
            $('input:checkbox').removeAttr('checked');
            zip(Result);
        }
      }
 });
}

// Download or Share Single Photo
function SinglePhoto(title)
{
    var move=0;
    if(title=='Share')
    {
        move=1;
    }
    showAjaxLoader();
    $.ajax({
        type:"POST",
        url: "album/downloadalbum",
        data:"photo_id="+$('#photo_id').val()+"+&move="+move,
        success:function(data)
        {
            hideAjaxLoader();
            if(data!='error')
            {
                if(move==0)
                {
                    zip(data);
                }else
                {
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                    $('#msg').html('Photo Shared successfully.');
                    $('#alertbox').removeClass();
                    $('#alertbox').addClass('alert-box');
                    $('#alertbox').addClass('success');
                    $('#alertbox').show();
                }
            }else
            {
                $("html, body").animate({ scrollTop: 0 }, "slow");
                $('#msg').html('Their is some error, Try again.');
                $('#alertbox').removeClass();
                $('#alertbox').addClass('alert-box');
                $('#alertbox').addClass('alert');
                $('#alertbox').show();
            }
        }
    });
}

function alertclose()
{
    $('#alertbox').hide('slow');
}


//Single Album  Download or Share

function single(val)
{
    var id=val.split("_");
    var move=0; //move=1 then share otherwise Download
    $('#move').val('0');
    if(id[1]=='p')
    {
        move=1;
        $('#move').val('1');
    }
    $('#albumlist').val(id[0]);
    if(move==0)
    {
        $('#emailmsg').hide();
        $('#fbEmail').val('');
        $.fancybox.open({
            href     : "#dumpLink",
            autoSize : true,
            fitToView: true
        });
    }else
    {
        proDownload(id[0],move);
    }
}

//Album Fullscreen View
function viewalbum(albumId) {

    showAjaxLoader();
    $('#gallery').load("home/viewalbum", {
            id: albumId,
            type: 'slideshow'
        },function (data) {
            hideAjaxLoader();

            $('a.fancybox').fancybox({
                cyclic: true,
                autoPlay: true,
                playSpeed: 4000,
                onUpdate: function () {
                    var IMG_WIDTH = 500,
                        currentImg = 0,
                        speed = 500,
                        imgs,
                        swipeOptions = {
                            triggerOnTouchEnd: true,
                            swipeStatus: swipeStatus,
                            allowPageScroll: "vertical",
                            threshold: 75
                        };

                    $(function () {
                        imgs = $(".fancybox-skin");
                        imgs.swipe(swipeOptions);
                    });

                    function swipeStatus(event, phase, direction, distance) {
                        //If we are moving before swipe, and we are going Lor R in X mode, or U or D in Y mode then drag.
                        if (phase == "move" && (direction == "left" || direction == "right")) {
                            var duration = 0;

                            if (direction == "left") {
                                scrollImages((IMG_WIDTH * currentImg) + distance, duration);
                            } else if (direction == "right") {
                                scrollImages((IMG_WIDTH * currentImg) - distance, duration);
                            }

                        } else if (phase == "cancel") {
                            scrollImages(IMG_WIDTH * currentImg, speed);
                        } else if (phase == "end") {
                            if (direction == "right") {
                                $.fancybox.prev();
                            } else if (direction == "left") {
                                $.fancybox.next();
                            }
                        }
                    }
                    function scrollImages(distance, duration) {
                        imgs.css("-webkit-transition-duration", (duration / 1000).toFixed(1) + "s");

                        //inverse the number we set in the css
                        var value = (distance < 0 ? "" : "-") + Math.abs(distance).toString();

                        imgs.css("-webkit-transform", "translate3d(" + value + "px,0px,0px)");
                    }

                }
            });
            $('a.fancybox:first').trigger('click'); // Acts as Click event for First Album photo to start the slideshow
        }
    );
}

//Ajax loader

function showAjaxLoader(){
    ajaxProgress = new ajaxLoader($('body'),{height:$(document).height(),width:$(document).width()});
}

function hideAjaxLoader(){
    if (ajaxProgress)
        ajaxProgress.remove();
}


//Zip Download

function zip(timestamp)
{
    window.location = 'album/getzip?timestamp='+timestamp;
    $.fancybox.close();
    $('#dumpLink').hide();
}




