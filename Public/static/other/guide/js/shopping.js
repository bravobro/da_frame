   KISSY.use("node, ua, ajax",function(a){a.ready(function(a){function b(a){return k*a}function c(a){return l*a}function d(){switch(m){case 1:h.f_f&&n&&!h.s_f?g.cut(2,g[2]):n&&h.s_f&&g.cut(2);break;case 2:h.s_f&&n&&!h.t_f?(e(".cover").show(),f.shirt_s.show().animate({bottom:255-k,left:260,width:"auto"},1,"easeNone",function(){f.shirt_s.hide(),f.shirt_t_f.show()}),g.cut(3,g[3])):n?n&&h.t_f&&g.cut(3):(h.s_f=!0,g.cut(1));break;case 3:h.t_f&&n&&!h.fo_f?(f.shirt_t_f.hide(),f.shirt_t.show().animate({bottom:200-b(1),left:260},1,"easeNone",function(){f.shirt_t.hide(),f.shirt_f.show()}),g.cut(4,g[4])):n?n&&h.fo_f&&g.cut(4):(h.t_f=!0,g.cut(2));break;case 4:h.fo_f&&n&&!h.fi_f?g.cut(5,g[5]):n?n&&h.fi_f&&g.cut(5):(h.fo_f=!0,g.cut(3));break;case 5:h.fi_f&&n&&!h.si_f?(f.item_fi.css("overflow","visible"),f.shirt_fi_s.hide(),f.shirt_fi_t.show().animate({bottom:506-b(1),width:65,left:"40%"},1,"easeNone",function(){f.item_fi.css("overflow","hidden"),f.shirt_fi_t.hide()}),f.box_si.animate({left:"38%"},1,"easeNone",function(){f.box_si.animate({bottom:40},.6,"easeIn",function(){f.box_si.hide()})}),g.cut(6,g[6])):n?n&&h.si_f&&g.cut(6):(h.fi_f=!0,g.cut(4));break;case 6:h.si_f&&n&&!h.se_f?g.cut(7,g[7]):n?n&&h.se_f&&g.cut(7):(h.si_f=!0,g.cut(5));break;case 7:h.se_f&&n?g.cut(8):n||(h.se_f=!0,g.cut(6));break;case 8:n||g.cut(7)}}var e=a.all,f={},g={},h={},i=!1,j=!1,k=parseInt(e(window).height(),10),l=parseInt(e(window).width(),10),m=Math.ceil(e(window).scrollTop()/k)+1,n=!0;f={fly:e(".fly"),goods:e(".goods"),word_s_f:e(".word-02-1"),word_s_s:e(".word-02-2"),search_s:e(".search"),search_w:e(".search-words"),search_s_s:e(".search-02-1"),goods_s:e(".spdgoods-02"),shirt_s:e(".shirt-02"),box:e("#box"),img_t_a:e(".img-03-a"),btn_t_a:e(".btn-03-a"),shirt_t:e(".shirt-03"),shirt_t_f:e(".shirt-03-1"),shirt_f:e(".shirt-04"),item_fo:e(".item-4"),cart_f:e(".cart"),word_f_f:e(".word-04-1"),word_f_s:e(".word-04-2"),note_f:e(".note"),word_f:e(".word-04"),cloud_f:e(".cloud"),item_fi:e(".item-5"),hand_fi:e(".hand-05"),mouse_fi:e(".mouse-05-a"),shirt_fi:e(".shirt-05"),shirt_fi_s:e(".shirt-05-1"),shirt_fi_t:e(".shirt-05-2"),order_fi:e(".order-05"),box_si:e(".box-06"),words_si_f:e(".words-06-1"),words_si_s:e(".words-06-2"),cloud_si:e(".cloud-06"),cloud_si_f:e(".cloud-06-1"),cloud_si_s:e(".cloud-06-2"),item_si:e(".item-6"),bus_si:e(".bus-05"),pop_si_f:e(".pop-06-1"),pop_si_s:e(".pop-06-2"),pop_si_t:e(".pop-06-3"),boy:e(".boy-06"),girl:e(".girl-06"),open:e(".door-06"),star_1:e(".star-07-1"),star_2:e(".star-07-2"),star_3:e(".star-07-3"),star_4:e(".star-07-4"),star_5:e(".star-07-5"),good_se:e(".good-07"),hand_e:e(".hand-08"),next:e(".next")},h={f_f:!1,s_f:!1,t_f:!1,fo_f:!1,fi_f:!1,si_f:!1,si_isMod:!1,se_f:!1},g={init:function(){e(window).animate({scrollTop:0},1),600>k&&(k=600),e(".item").css("height",k),e("#"+m+" .b").show(),a.UA.ie&&a.UA.ie<8&&(f.item_si.css("background-position-x",c(.25)-625+"px"),j=c(1)-2500+"px",e(".item-4").css("background-position-x",c(.5)-1e3+"px")),e(".btn-08").css("height",b(1)-365)},delay:function(a,b){setTimeout(a,1e3*b)},showNext:function(){f.next.animate({opacity:1},.3,"backBoth").addClass("next_updown")},hideNext:function(){f.next.animate({opacity:0},.3,"easeNone",function(){f.next.removeClass("next_updown")})},cut:function(a,b){g.hideNext(),i=!0,e(window).animate({scrollTop:k*(a-1)},1,"easeNone",function(){i=!1,b?b():b||8==a||g.showNext()})},1:function(){f.fly.animate({right:"20%"},2,"easeIn",function(){f.goods.animate({opacity:"1"},.6,"easeIn",function(){g.showNext(),h.f_f=!0}),e(".shirt01").animate({opacity:"1"},.6).addClass("shirt_updown")})},2:function(){g.delay(function(){f.search_s.show().animate({right:370},1,"easeIn",function(){f.search_w.animate({opacity:"1"},.7,"easeNone",function(){f.word_s_f.hide(),f.search_s.hide(),f.search_s_s.show().animate({bottom:452,right:250,height:30},.7),f.word_s_f.hide(),f.word_s_s.animate({opacity:1},.5),f.goods_s.show().animate({height:218},.7,"easeIn",function(){g.showNext(),h.s_f=!0},.2)})})},.2)},3:function(){g.delay(function(){f.img_t_a.show()},.4),g.delay(function(){f.btn_t_a.show(),g.showNext(),h.t_f=!0},1.1)},4:function(){f.cart_f.css("left",f.cart_f.offset().left+250+"px"),g.delay(function(){f.cart_f.animate({left:c(1.2)},1.5,"easeIn",function(){f.cart_f.hide()})},.2),g.delay(function(){f.word_f_f.hide(),f.word_f_s.show(),f.note_f.animate({opacity:1},1,"easeNone",function(){f.word_f.animate({opacity:1},.5,"easeNone",function(){g.showNext(),h.fo_f=!0})})},1.6)},5:function(){g.delay(function(){f.hand_fi.show().animate({bottom:0},.8,"easeIn",function(){f.mouse_fi.show(),f.shirt_fi.show().animate({bottom:204},1,"easeNone",function(){f.shirt_fi.hide(),f.shirt_fi_s.show().animate({bottom:70},.5),f.order_fi.show().animate({bottom:390},.8,"easeNone",function(){g.showNext(),h.fi_f=!0})})})},.2)},6:function(){g.delay(function(){f.item_si.animate({"background-position":(j||"100%")+" 100%"},3,"easeBoth",function(){f.boy.show().animate({bottom:116,height:305},1.5),f.words_si_s.animate({opacity:1},.5),f.boy.css("right",c(1)-f.boy.offset().left+"px"),f.boy.animate({right:500},1,"easeNone",function(){f.pop_si_t.show(),g.showNext(),h.si_f=!0}),g.delay(function(){f.open.show(),g.delay(function(){f.girl.show().animate({height:305,right:350},1)},.2)},1)}),f.words_si_f.animate({left:"-5%",opacity:"0"},3,"easeBoth"),f.cloud_si.animate({left:"10%"},3,"easeBoth"),g.delay(function(){f.pop_si_f.show()},.2),g.delay(function(){f.pop_si_f.hide()},1),g.delay(function(){f.pop_si_s.show()},1.5),g.delay(function(){f.cloud_si_f.addClass("cloud_back_1"),f.cloud_si_s.addClass("cloud_back_2")},1.8)},.8)},7:function(){g.delay(function(){f.star_1.show(),g.delay(function(){f.star_2.show()},.2),g.delay(function(){f.star_3.show()},.4),g.delay(function(){f.star_4.show()},.6),g.delay(function(){f.star_5.show()},.8),g.delay(function(){f.good_se.show().animate({width:90},.2).animate({width:72},.2,"easeNone",function(){g.showNext(),h.se_f=!0})},.9)},.2)},8:function(){}},g.init(),g[1](),e(document).on("mousewheel",function(a){a.preventDefault(),n=a.deltaY<0,i||d()}),f.next.on("click",function(){n=!0,d()}),e(window).on("scroll",function(){m=Math.ceil(e(window).scrollTop()/k)+1,tag=Math.round(e(window).scrollTop()/k)+1,e(".b").hide(),e("#"+tag+" .b").show(),(e(window).scrollTop()>b(1)||e(window).scrollTop()<b(7))&&(e(".ms").hide(),e(".mouse"+tag).show())}),e(document).on("keydown",function(a){40==a.which?(a.halt(),n=!0,d()):38==a.which?(a.halt(),n=!1,d()):32==a.which?(a.halt(),n=!0,d()):13==a.which&&8==m&&(window.location="<%=basePath%>")}),e(".item-8").on("mousemove",function(a){var c=a.pageX-10,d=a.pageY-b(7)+10;f.hand_e.css("left",c),d>b(1)-449?f.hand_e.css("bottom",b(1)-(d+449)):f.hand_e.css("bottom",0)}),e(".btn-08").on("mouseenter",function(){e(".btn-08-1").hide(),e(".btn-08-2").show()}),e(".btn-08").on("mouseleave",function(){e(".btn-08-2").hide(),e(".btn-08-1").show()}),e(".btn-08").on("mousedown",function(){e(".btn-08-2").hide(),e(".btn-08-1").hide(),e(".btn-08-3").show(),window.location="/index.php"}),e(".btn-08").on("mouseup",function(){e(".btn-08-3").hide(),e(".btn-08-1").show()}),e(".again").on("click",function(){h={f_f:!1,s_f:!1,t_f:!1,fo_f:!1,fi_f:!1,si_f:!1,si_isMod:!1,se_f:!1},e("*").attr("style",""),g.init(),g[1]()}),e(".shirt01").on("click",function(){h.s_f?g.cut(2):g.cut(2,g[2])})})});