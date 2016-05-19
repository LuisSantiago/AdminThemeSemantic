/**
 * ProcessWire Admin Theme jQuery/Javascript
 *
 * Copyright 2016 by Ryan Cramer
 *
 */

var ProcessWireAdminTheme = {

	/**
	 * Initialize the default ProcessWire admin theme
	 *
	 */
	init: function() {
		// fix annoying fouc with this particular button
		var $button = $("#head_button > button.dropdown-toggle").hide();

		this.setupCloneButton();
		ProcessWireAdmin.init();

		var $body = $("body");
		if($body.hasClass('hasWireTabs') && $("ul.WireTabs").length == 0) $body.removeClass('hasWireTabs');
		$('#content').removeClass('fouc_fix'); // FOUC fix, deprecated
		$body.removeClass("pw-init").addClass("pw-ready");

		if($button.length > 0) $button.show();
	},


	/**
	 * Clone a button at the bottom to the top
	 *
	 */
	setupCloneButton: function() {

		if($("body").is(".modal")) return;
		var $buttons = $("button.head_button_clone, button.head-button");
		if($buttons.length == 0) return;

		var $head = $("#head_button");
		if($head.length == 0) $head = $("<div id='head_button' class='item'></div>").prependTo("#topnav .right.menu");

		$buttons.each(function() {
			var $t = $(this);
			var $a = $t.parent('a');
			if($a.length > 0) {
				$button = $t.parent('a').clone(true);
				$head.prepend($button);
			} else if($t.hasClass('head_button_clone') || $t.hasClass('head-button')) {
				$button = $t.clone(true);
				$button.attr('data-from_id', $t.attr('id')).attr('id', $t.attr('id') + '_copy');
				$button.click(function() {
					$("#" + $(this).attr('data-from_id')).click();
					return false;
				});
				$head.prepend($button);
			}
			if($button.hasClass('dropdown-toggle') && $button.attr('data-dropdown')) {


			}
		});
		$head.show();
	},



};

$(document).ready(function() {
	ProcessWireAdminTheme.init();




	/* notices */


	if ($('#notices li.message').length > 0) {

		$('#notices').not('a').on('click', function() {
		    $(this).transition('fade');
		});
		$('#notices').delay(5000).fadeOut();

	}



/* Tabs */

$('.WireTabs.nav').removeClass().addClass('ui pointing stackable menu').children('li').addClass('item');
$('.ui.pointing.menu a.on').closest('li').addClass('active');
$('ul.ui.pointing.menu li a').on('click', function() {
		$('ul.ui.pointing.menu li.active').removeClass('active');
		$(this).parent('li').addClass('active');
});

/* Modules filter */
	$('form#modules_form select').dropdown({
	    onChange: function(val) {
				$('.modules_section').hide();
				$(".modules_"+val).show();
				if(val == 0){
					$('.modules_section').show();
				};
	    },
			placeholder: 'All'
	  });


/* dropdowns */
						$('.ui.dropdown.link, div.InputfieldContent>select').dropdown({
					    on: 'hover'
					  });


/* Responsive */
						$('.ui.sidebar').sidebar( 'attach events', '#mobile_icon');
						$("#topnav div.ui.dropdown.link.item a").not('.item').clone().addClass('item').appendTo('.sidebar');
						function hideDiv(){

						};


/* login form */
							if(document.getElementById("ProcessLoginForm") !== null){
							$('#logo2').prependTo('.ui.form').show();
							$('body').css('background', '#555');
							$("#notices").css({'z-index': '9999', 'position':'fixed', 'width': '100%'});
							$('body > .grid').css('height', '100%');
							$('#wrap_login_name, #wrap_login_pass').removeClass().addClass('field');
							$('form').removeClass().addClass('ui large form');
							$('.Inputfields').removeClass().addClass('ui stacked segment');
							$('#content').removeClass().addClass('ui center aligned grid').css({'background-color': '#555','padding' : '0px','margin': '0px'}).children('.column').css('max-width', '450px').addClass('ui middle aligned grid');
						};

/* Page list */


$('#search_item').hide();
$( "#search_icon" ).click(function() {
	$('.ui.dropdown.link.item, #head_button, #search_icon').hide();
	$('#search_item, #search_item div.ui.dropdown').css('display', 'flex').addClass('focus activo').children('.results').removeClass('hidden').addClass('visible');
	$( "#search_item input" ).focus();
});

$(".pusher").click(function(){
    $('#search_item').hide();
		$('.ui.dropdown.link.item, #head_button, #search_icon').show();
});


});
