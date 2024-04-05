(function($) {
    $(function() {
        var f = arguments.callee;
        $(window).resize(f);

        var dv = '', height, padding = 0;
        var $testDevice = $("<div class='hidden-xs'>&nbsp;</div>");
        $testDevice.appendTo("body");
        if (!$testDevice[0].offsetWidth) {
            dv = 'xs';
        }

        $testDevice.remove();

        if (!$(".test")[0]) {
            var $test = $("<div style='visibility:hidden' class='container'><div class='row'><div class='col-lg-2 col-md-2 col-sm-2 col-xs-2'><div class='test'>&nbsp;</div></div></div></div>");
            $test.appendTo("body");
            padding = ($test.find('.test').parents().outerWidth() - $test.find('.test').outerWidth()) / 2;
            height = $test.find('.test').outerWidth();
            $test.remove();
        } else {
            height = $(".test").outerWidth();
            padding = ($(".test").parent("div").outerWidth() - $(".test").outerWidth()) / 2;
        }

        window.PADDING = padding;
        window.HEIGHT = height;

        $(".box").each(function() {
            var size = parseInt($(this).data(dv + '-size')) || parseInt($(this).data('size')), pad = padding;
            if ($(this).parent("div").parent("div").hasClass("row-no-padding")) {
                $(this).parent("div").css({
                    'padding-left': '0px',
                    'padding-right': '0px'
                });
                $(this).parent("div").parent("div").css({
                    'padding-left': padding + 'px',
                    'padding-right': padding + 'px',
                    'margin-bottom': 2 * padding + 'px'
                });
                pad = 0;
            }
            if (!size)
                return true;
            $(this).css({
                height: size * height + 2 * (size - 1) * pad + 'px',
                overflow: 'hidden',
                marginBottom: 2 * pad + 'px'
            });
        });
    });

})(jQuery);

/* album */
function removeImg(elem) {
    var height = elem.offsetHeight;
    //var size = Math.min(2, 2 * Math.round((height + 2 * (window.PADDING)) / ((window.HEIGHT) + 2 * (window.PADDING))));
    // height = size * window.HEIGHT + 2 * (size - 1) * window.PADDING;
    var html = '<div class="clearfix" style="border: 1px solid #eee;cursor:pointer;position:relative;width:100%;margin-bottom:' + (2 * window.PADDING) + 'px;height:' + height + 'px" ontouchstart="this.classList.toggle(\'hover\');">'
            + '<img style="width:100%" src="' + elem.src + '"/>'
            + '<div style="background:rgba(0,0,0,0.8);color:#fff;position:absolute;z-index:1;width:100%;padding:5px;bottom:0;left:0">'
            + elem.albumTitle + ' (' + elem.images.length + ')'
            + '</div>'
            + '</div>';

    var $a = $(html)
            .mouseover(function() {
                $(this).css({opacity: 0.9});
            })
            .mouseout(function() {
                $(this).css({opacity: 1});
            })
            .click(function() {
                $.fancybox(elem.images, {
                    autoScale: false,
                    fitToView: false,
                    prevEffect: 'none',
                    nextEffect: 'none',
                    helpers: {
                        title: {
                            type: 'outside'
                        },
                        thumbs: {
                            width: 50,
                            height: 50
                        }
                    }
                });
            })
            .fadeOut();

    $(elem).wrapAll($a);
    $a.fadeIn(300);
}

function appendImg(albums) {
    var album = (albums || []).shift();
    if (!album) {
        return;
    }

    var h = 0, idx = 0;
    for (var j = 0; j < 2; j++) {
        var H = $(".cols > div").slice(j, j + 1).height();
        if (h == 0 || h > H) {
            idx = j;
            h = H;
        }
    }

    //setTimeout(function() {
    $('<img onerror=\'appendImg(this.albums);removeImg(this);\' onload=\'appendImg(this.albums);removeImg(this);\' style="z-index:1;position:relative;top:0;left:0;width:100%" src="' + album.thumb + '"/>')
            .each(function() {
                $(this).prop('albums', albums);
                $(this).prop('albumTitle', album.title);
                $(this).prop('images', album.images);
            })
            .appendTo($(".cols > div")[idx])
            .css({
                opacity: 0.2
            });
    //}, 400);

}

function loadAlbumPage(url) {
    $.ajax({
        url: url,
        dataType: 'json',
        beforeSend: function() {
            $("#album-load").show();
            $("#album-more").hide();
        },
        complete: function() {
            $("#album-load").hide();
        },
        success: function(data) {
            appendImg(data.albums);
            $("#album-more")
                    .off('click');
            if (data.next_url) {
                $("#album-more")
                        .show()
                        .on('click', function() {
                            loadAlbumPage(data.next_url);
                        });

            } else {
                $("#album-more").hide();
            }
        }
    });
}