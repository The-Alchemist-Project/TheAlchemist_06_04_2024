
$(document).ready(function() {
            $(window).scroll(function() {
                if ($(this).scrollTop() != 0) {
                    $('.icon-back-top').fadeIn();
                } else {
                    $('.icon-back-top').fadeOut();
                }
                });
                $('.icon-back-top').click(function() {
                    $('body,html').animate({
                    scrollTop : 0
                }, 200);
            }); 
			//// 

});

///search			
$(".btn-more-alphabet").click(function(){         
				if($(".content-alphabet").hasClass("open")) {
					 $(".content-alphabet").removeClass("open");
				} else {
					$(".content-alphabet").addClass("open");
				}  
				
			});	
	$(".view-pass").click(function(){         
				if($(".pass").hasClass("open")) {
					 $(".pass").removeClass("open");
				} else {
					$(".pass").addClass("open");
				}  
				
			});				