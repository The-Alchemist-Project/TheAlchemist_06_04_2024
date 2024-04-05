(function($) {
    $(function() {
        $(".playlist").each(function() {
            var $player = $(this).find(".media-player"), $list = $(this).find(".media-list");
            $player.css({
                height: (315 / 560) * $player.width() + 'px'
            });

            $list.find("li").each(function() {
                var url = $(this).data('url');
                if (url.match(/v=(.*)$/i)) {
                    var vid = RegExp.$1;
                    $(this).html("<div class='clearfix'>"
                            + "<img style='width:100px;height:75px;margin:5px' src='http://i1.ytimg.com/vi/" + vid + "/mqdefault.jpg' class='pull-left'/>"
                            + this.innerHTML
                            + "</div>");
                } else {
                    $(this).remove();
                }
            });

            $list.find("li").click(function() {
                var url = $(this).data('url');
                if (url.match(/v=(.*)$/i)) {
                    url = "//www.youtube.com/embed/" + RegExp.$1;
                    $player.find("iframe").remove();
                    $player.html('<iframe width="100%" height="100%" src="' + url + '" frameborder="0" allowfullscreen></iframe>');
                } else {
                    alert("Video này tạm thời không thể xem được");
                }
            });
            $(this).find(".media-list").jScrollPane();
        });
    });
})(jQuery);