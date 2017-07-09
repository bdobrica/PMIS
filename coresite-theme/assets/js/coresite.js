/*
Name: coresite-script
Version: 1.7
Dependencies: coresite-jquery-ui,jquery-slimscroll,bootstrap,metismenu,icheck,jquery-sparkline
Footer: true
*/
/**
 * HOMER - Responsive Admin Theme
 * version 1.7
 *
 */

var $CS = new function () {
	this.ajax = '/wp-content/plugins/coresite-plugin/rpc/ajax.php';
	this.queue = [];

	this.modal = function(id, args){
		var modal = $(id).modal('show');
		var qed = {i:id,m:modal,r:{},o:args.o,e:args.e,a:''};

		$('form button', modal).off('click').on('click', qed, function(ev){
			ev.data.a = $(this).attr('name');
			});
		$('form', modal).off('submit').on('submit', qed, function(ev){
			ev.preventDefault();
			var post = new FormData($(this)[0]);
			post.append(ev.data.a, 1);
			//var post = $(this).serialize();
			//#post += '&' + ev.data.a + '=1';

			$('.progress-bar', ev.data.m).css({width:0}).text('0%');
			$('.progress', ev.data.m).removeClass('hidden');

			$.ajax({
				url:$CS.ajax,
				data:post,
				context:ev.data,
				method:'POST',

				cache: false,
				contentType: false,
				processData: false,

				success:function(data){
					console.log (data);
					this.r = JSON.parse(data);
					$('.progress-bar', this.m).css({width:'100%'}).text('100%');

					for (var key in this.r)
						if (this.r.hasOwnProperty(key)) {
							var elm = $('[data-update="' + this.o + '.' + key + '"]');
							if (elm.length > 0) {
								var tag = elm.prop('tagName').toLowerCase();
								if (tag == 'img')
									$('[data-update="' + this.o + '.' + key + '"]').attr('src',this.r[key]);
								else
									$('[data-update="' + this.o + '.' + key + '"]').html(this.r[key]);
								}
							elm = $('[data-create="' + this.o + '.' + key + '"]');
							if (elm.length > 0) {
								var add = $(this.r[key]);
								console.log (add.data('sortby'));
								elm.append(add);
								}
							}
					$CS.queue.pop();

					$('.progress', this.m).addClass('hidden');
					this.m.modal('hide');
					},
				error:function(xhr,error){
					// error in timeout / error / abort / parseerror
					},
				xhr:function(){
					var cxhr = $.ajaxSettings.xhr();
					var pbar = function(ev){
						if (ev.lengthComputable) {
							var percent = Math.floor(100 * ev.loaded / ev.total) + '%';
							$('.progress-bar', this.m).css({width:percent}).text(percent);
							}
						};
					if (cxhr.upload) cxhr.upload.addEventListener('progress', pbar, false); else cxhr.addEventListener('progress', pbar, false);	
					return cxhr;
					}
				});
			});
		modal.off('hide.bs.modal').on('hide.bs.modal', qed, function(ev){
			});

		this.queue.push(qed);
		};
	};

$(document).ready(function () {

    // Add special class to minimalize page elements when screen is less than 768px
    setBodySmall();

    // Handle minimalize sidebar menu
    $('.hide-menu').click(function(event){
        event.preventDefault();
        if ($(window).width() < 769) {
            $("body").toggleClass("show-sidebar");
        } else {
            $("body").toggleClass("hide-sidebar");
        }
    });

    // Initialize metsiMenu plugin to sidebar menu
    $('#side-menu').metisMenu();

    // Initialize iCheck plugin
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
    });

    // Initialize animate panel function
    $('.animate-panel').animatePanel();

    // Function for collapse hpanel
    $('.showhide').click(function (event) {
        event.preventDefault();
        var hpanel = $(this).closest('div.hpanel');
        var icon = $(this).find('i:first');
        var body = hpanel.find('div.panel-body');
        var footer = hpanel.find('div.panel-footer');
        body.slideToggle(300);
        footer.slideToggle(200);

        // Toggle icon from up to down
        icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
        hpanel.toggleClass('').toggleClass('panel-collapse');
        setTimeout(function () {
            hpanel.resize();
            hpanel.find('[id^=map-]').resize();
        }, 50);
    });

    // Function for close hpanel
    $('.closebox').click(function (event) {
        event.preventDefault();
        var hpanel = $(this).closest('div.hpanel');
        hpanel.remove();
    });

    // Open close right sidebar
    $('.right-sidebar-toggle').click(function () {
        $('#right-sidebar').toggleClass('sidebar-open');
    });

    // Function for small header
    $('.small-header-action').click(function(event){
        event.preventDefault();
        var icon = $(this).find('i:first');
        var breadcrumb  = $(this).parent().find('#hbreadcrumb');
        $(this).parent().parent().parent().toggleClass('small-header');
        breadcrumb.toggleClass('m-t-lg');
        icon.toggleClass('fa-arrow-up').toggleClass('fa-arrow-down');
    });

    // Set minimal height of #wrapper to fit the window
    setTimeout(function () {
        fixWrapperHeight();
    });

    // Sparkline bar chart data and options used under Profile image on left navigation panel
    $("#sparkline1").sparkline([5, 6, 7, 2, 0, 4, 2, 4, 5, 7, 2, 4, 12, 11, 4], {
        type: 'bar',
        barWidth: 7,
        height: '30px',
        barColor: '#62cb31',
        negBarColor: '#53ac2a'
    });

    // Initialize tooltips
    $('.tooltip-demo').tooltip({
        selector: "[data-toggle=tooltip]"
    })

    // Initialize popover
    $("[data-toggle=popover]").popover();

    // Move modal to body
    // Fix Bootstrap backdrop issu with animation.css
    $('.modal').appendTo("body")

    // Upload file
    $('.file-control').each(function(n,i){
	$('.file-upload', i).on('click', i, function(ev){
		ev.preventDefault();
		$('[type="file"]', ev.data).click().on('change', ev.data, function(ev){
			$('[type="text"]', ev.data).val(this.value);
			$('.file-clear', ev.data).show();
			});
		});
	$('.file-clear', i).on('click', i, function(ev){
		ev.preventDefault();
		$('[type="text"]', ev.data).val('');
		$('.file-clear', ev.data).hide();
		}).hide();
	$('[type="text"]', i).on('keydown', function(ev){
		ev.preventDefault();
		});
	});
     // CRUDL-control
     $('.crudl').each(function(n,i){
	$('.crudl-control', i).on('click', i, function(ev){
		ev.preventDefault ();
		var crudl = $(this).data('crudl');
		var panels = $('.crudl-panel', i);
		for (var n = 0; n < panels.length; n++) {
			var panel = $(panels[n]);
			if (panel.data('crudl') == crudl)
				panel.removeClass('hidden');
			else
				panel.addClass('hidden');
			}
		});
	});
     // Select 2
     $('.select2').select2();

	$('[data-change]').on('click', function(ev){
		ev.preventDefault();
		var str = $(this).data('change');
		var dot = str.indexOf('.');
		if (dot < 0) return;
		var obj = str.substr(0, dot);
		var key = str.substr(dot + 1);
		$CS.modal('#modal-' + key, {e:$(this),o:obj});
		});
	// Date Picker
	$('.input-daterange').datepicker();

//var get = window.location.search.substr(1).split('&'); if (get.length > 0) { for (var i = 0; i<get.length; i++) if (get[i].indexOf('error') == 0) get.splice(i,1); window.history.replaceState({}, '', get.length > 0 ? window.location.href.replace(/\?.*/, '') + '?' + get.join('&') : window.location.href.replace(/\?.*/, '')); }
});

$(window).bind("load", function () {
    // Remove splash screen after load
    $('.splash').css('display', 'none')
})

$(window).bind("resize click", function () {

    // Add special class to minimalize page elements when screen is less than 768px
    setBodySmall();

    // Waint until metsiMenu, collapse and other effect finish and set wrapper height
    setTimeout(function () {
        fixWrapperHeight();
    }, 300);
})

function fixWrapperHeight() {

    // Get and set current height
    var headerH = 62;
    var navigationH = $("#navigation").height();
    var contentH = $(".content").height();

    // Set new height when contnet height is less then navigation
    if (contentH < navigationH) {
        $("#wrapper").css("min-height", navigationH + 'px');
    }

    // Set new height when contnet height is less then navigation and navigation is less then window
    if (contentH < navigationH && navigationH < $(window).height()) {
        $("#wrapper").css("min-height", $(window).height() - headerH  + 'px');
    }

    // Set new height when contnet is higher then navigation but less then window
    if (contentH > navigationH && contentH < $(window).height()) {
        $("#wrapper").css("min-height", $(window).height() - headerH + 'px');
    }
}


function setBodySmall() {
    if ($(this).width() < 769) {
        $('body').addClass('page-small');
    } else {
        $('body').removeClass('page-small');
        $('body').removeClass('show-sidebar');
    }
}

// Animate panel function
$.fn['animatePanel'] = function() {

    var element = $(this);
    var effect = $(this).data('effect');
    var delay = $(this).data('delay');
    var child = $(this).data('child');

    // Set default values for attrs
    if(!effect) { effect = 'zoomIn'};
    if(!delay) { delay = 0.06 } else { delay = delay / 10 };
    if(!child) { child = '.row > div'} else {child = "." + child};

    //Set defaul values for start animation and delay
    var startAnimation = 0;
    var start = Math.abs(delay) + startAnimation;

    // Get all visible element and set opactiy to 0
    var panel = element.find(child);
    panel.addClass('opacity-0');

    // Get all elements and add effect class
    panel = element.find(child);
    panel.addClass('animated-panel').addClass(effect);

    // Add delay for each child elements
    panel.each(function (i, elm) {
        start += delay;
        var rounded = Math.round(start * 10) / 10;
        $(elm).css('animation-delay', rounded + 's')
        // Remove opacity 0 after finish
        $(elm).removeClass('opacity-0');
    });
}
