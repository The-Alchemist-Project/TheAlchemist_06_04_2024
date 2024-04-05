(function ($) {
    $(document).ready(function () {
        var urlOrigin = window.location.pathname;

        $("#container").on("click", "div.social-item-lc-like", function () {
            var relId = $(this).attr('rel-id'),
                    status = $(this).attr('rel-status'),
                    $total = $(this).closest('.social-item'),
                    numlike = parseInt($total.find('.social-item-total-like').attr('rel-num')) || 0;
            if (status == 'none') {
                $(this).addClass('social-item-lc-like-active');
                $(this).attr('rel-status', 'active');
                $total.find('.social-item-total-like')
                        .attr('rel-num', numlike + 1)
                        .html(numlike + 1);
            } else {
                $(this).removeClass('social-item-lc-like-active');
                $(this).attr('rel-status', 'none');
                $total.find('.social-item-total-like')
                        .attr('rel-num', numlike - 1)
                        .html(numlike - 1);
            }

            $.ajax({
                type: "POST",
                cache: false,
                url: urlOrigin + "/post/like",
                data: {
                    id: relId
                },
                success: function (response) {
                    //alert("Like");
                }
            });
        });
        $("#container").on("click", "a.social-item-lc-like-comment", function () {
            var relId = $(this).attr('rel-id'),
                    status = $(this).attr('rel-status');
            if (status == 'none') {
                $(this).addClass('social-item-lc-like-comment-active');
                $(this).attr('rel-status', 'active');
            } else {
                $(this).removeClass('social-item-lc-like-comment-active');
                $(this).attr('rel-status', 'none');
            }

            $.ajax({
                type: "POST",
                cache: false,
                url: urlOrigin + "/post/likecomment",
                data: {
                    id: relId
                },
                success: function (response) {
                    //alert("Like");
                }
            });
        });

        $("div.social-top-setting").click(function () {
            $(".form-change-info-error").html("").hide();
        });
        $("button#form-change-info-submit").click(function () {
            $.ajax({
                type: "POST",
                url: urlOrigin + "/post/editinfo",
                data: $('form#form-change-info').serialize(),
                success: function (res) {
                    var dt = JSON.parse(res);
                    if (dt.error) {
                        $(".form-change-info-error").html(dt.msg);
                        $(".form-change-info-error").show();

                    } else {
                        $(".form-change-info-error").show();
                        $(".form-change-info-error").html(dt.msg);
                        $("#popSetting").slideUp(1000);
                    }
                },
                error: function () {
                    $(".form-change-info-error").hide();
                    alert("Error");
                }
            });
        });
        $('.social-item-comment-edit-l').click(function (event) {
            event.stopPropagation();
            $(this).closest('.social-item-comment-edit').find('.social-item-comment-edit-a').toggle("slow");
        });
        $('body').click(function () {
            $('.social-item-comment-edit-a').hide();
        });
        $('.work-form-item-tab-faq').click(function () {
            $(this).addClass('work-form-item-tab-sub-active');
            $('.work-form-item-tab-info-faq').show();
            $('.work-form-item-tab-info-content').hide();
            $('.work-form-item-tab-info').removeClass('work-form-item-tab-sub-active');
        });
        $('.work-form-item-tab-info').click(function () {
            $(this).addClass('work-form-item-tab-sub-active');
            $('.work-form-item-tab-info-content').show();
            $('.work-form-item-tab-faq').removeClass('work-form-item-tab-sub-active');
            $('.work-form-item-tab-info-faq').hide();
        });



        $("button.button-form-group-add").click(function () {

            var $error = $(this).closest('.form-group-addparent').find(".form-change-group-error");
            var data = $(this).closest('.form-group-addparent').find("form.form-group-add").serialize();

            $.ajax({
                type: "POST",
                url: urlOrigin + "/index/addmember",
                data: data,
                success: function (res) {
                    var dt = JSON.parse(res);

                    if (dt.error) {
                        $error.html(dt.msg);
                        $error.show();

                    } else {
                        $error.show();
                        $error.html(dt.msg);
                        if (dt.groupId) {
                            $("#popGroup" + dt.groupId).slideUp(1000);
                        } else {
                            $("#popGroup").slideUp(1000);
                        }
                        location.reload();
                    }
                },
                error: function () {
                    $error.hide();
                    alert("Error");
                }
            });
        });
        $("button.trade-container-bottom-item2-save").click(function () {

            var $that = $(this);
            $that.addClass('trade-container-bottom-item2-save-ok');
            if ($that.hasClass('.trade-container-bottom-item2-save-ok')) {
                $that.text('Save');
            } else {
                $that.text('Processing');
            }
            $that.closest('table').find('.trade-container-bottom-item2-content').html('Your investment is being analyzed in  <a href="profile" style="padding-left:5px"> My Dashboard</a>');
            $that.prop('disabled', true);

            var ID = $that.attr('rel-id') || 0;
            var type = $that.attr('rel-type') || 'D1';
            data = {
                ID: ID,
                type: type
            };
            $.ajax({
                type: "POST",
                url: urlOrigin + "/index/save",
                data: data,
                success: function (res) {
                    var dt = JSON.parse(res);
                    alert(dt.notice);
                },
                error: function () {
                    alert("Error");
                }
            });
        });

        $("input[name=select-report]").on('change', function () {
            var v = $(this).is(':checked') || false;
            if (v) {
                location.href = urlOrigin + "?type=month";
            } else {
                location.href = urlOrigin + "?type=week";
            }
        });
        $("select[name=detail_id]").on('change', function () {
            var v = $(this).val() || false;
            var type = $(this).attr('rel') || 'week';
            if (type == 'month') {
                urlOrigin = urlOrigin + '?type=month';
            } else {
                urlOrigin = urlOrigin + '?type=week';
            }
            if (v) {
                location.href = urlOrigin + "&detail_id=" + v;
            } else {
                location.href = urlOrigin;
            }
        });
        $("input[id=addVideoSocial]").on('change', function () {
            var v = $(this).is(':checked') || false,
                    $input = $(this).closest('form').find("input[name=url]");
            if (v) {
                $input.removeClass('hidden');
            } else {
                $input.addClass('hidden');
            }
        });


        $(".social-header-sub-right-notice").on("click", function (event) {
            $(".social-header-sub-right-notice-num").attr('style', 'display:none');
        });

    });
})(jQuery);