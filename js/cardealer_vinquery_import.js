( function( $ ) {
	jQuery(document).ready(function ($) {
		var cdhl_cars_html;
		var open_parents = [];
		var open_children = [];
		$("#cdhl_vin_items, .cdhl_cars_attributes").sortable({
			items: 'li',
			connectWith: ".cdhl_form_data",
			placeholder: "ui-state-highlight",
			forcePlaceholderSize: false,
			create: function (e, ui) {
				cdhl_cars_html = $("#cdhl_vin_items").html();
				// proper height calculation for the the accordions, after html stored
				if ($("#cdhl_vin_items .accordion")) {
					$("#cdhl_vin_items .accordion_child").accordion({
						collapsible: true
					});
					$("#cdhl_vin_items .accordion_child").accordion("option", "active", false);


					$("#cdhl_vin_items .accordion_parent").accordion({
						collapsible: true,
						active: false,
						activate: function (event, ui) {
							var index = $(".accordion_parent").index($(this));
							open_parents.push(index);
							open_children[index] = "";
						}
					});
					$("#cdhl_vin_items .accordion_parent").accordion("option", "active", false);
				}
				var removeButton = $(".rmv-row").click(function () {
					var parentLi = $(this).parent();
					$(this).remove();
					parentLi.appendTo($('#cdhl_vin_items'))
				  //  $('#cdhl_vin_items li').sort(asc_sort).appendTo($('#cdhl_vin_items'));
				});
				$(ui.item).append(removeButton);
			},
			start: function (e, ui) {
				ui.placeholder.height(ui.item.height());
			},
			receive: function (event, ui) {
				var $this = $(this);

				if ($this.data("limit") == 1 && $this.children('li').length > 1 && $this.attr('id') != "items") {
					alert('Only one per list!');
					$(ui.sender).sortable('cancel');
				}

				// set val
				var name = $this.data('name');
				var name_attr = ($this.data("limit") == 1 ? "vin_import[" + name + "]" : "vin_import[" + name + "][]");

				ui.item.find('input[type="hidden"]').attr("name", name_attr);
			},
			stop: function (event, ui) {
				var $this = $(this);
				$("#cdhl_vin_items").html(cdhl_cars_html);

				if ($("#cdhl_vin_items .accordion")) {
					$("#cdhl_vin_items .accordion_child").accordion({
						collapsible: true
					});
					$("#cdhl_vin_items .accordion_child").accordion("option", "active", false);
					$("#cdhl_vin_items .accordion_child").accordion("refresh");


					$("#cdhl_vin_items .accordion_parent").accordion({
						collapsible: true,
						active: false,
						activate: function (event, ui) {
							open_parents.push($(".accordion_parent").index($(this)));
						}
					});
					$("#cdhl_vin_items .accordion_parent").accordion("option", "active", false);

					if (open_parents != []) {
						for (i = 0; i <= open_parents.length; i++) {
							$(".accordion_parent:eq(" + open_parents[i] + ")").accordion("option", "active", 0);
						}
					}
				}
			}
		}).disableSelection();

		$(document).on("click", ".remove_element", function () {
			$(this).closest("li").remove();
			cdhl_cars_html = $("#cdhl_vin_items").html();
		});

		$(document).on("keyup", ".vin-field", function () {
			if ( $( '.vin-search-input-count .vin-search-input-count-value' ).length > 0 ) {
				$( '.vin-search-input-count .vin-search-input-count-value' ).html( this.value.length );
			}			
		});

		$("form[name='cars_vin_import_form']").width(($(".cdhl_car_vin_import").width() - $("#cdhl_vin_items").width()) + "px").show();

		$(window).resize(function () {
			$("form[name='cars_vin_import_form']").width(($(".cdhl_car_vin_import").width() - $("#cdhl_vin_items").width()) + "px").show();
		});

		$(document).on("click", ".current_vin_mapping", function (e) {

			e.preventDefault();

			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'cdvqi_save_vin_current_mapping',
					form: $("#cars_vin_import_form").serialize(),
					nonce: $("#cars_vin_import_form").find( '#cdvqi_vinquery_import_nonce' ).val(),
				},
				dataType: 'json',
				beforeSend: function () {
					jQuery('.cdhl-loader-img').html('<img src="' + cdhl.cdhl_url + 'images/loader-20x20.gif" alt="loading.." width="20px" height="20px"/>');
				},
				success: function (response) {
					if (response.status == 'success') {
						jQuery('.cdhl-loader-img').html('');
						jQuery('.res-msg').css('color', 'green');
						jQuery('.res-msg').show();
						jQuery('.res-msg').html(response.message).delay(5000).fadeOut('slow');
					} else {
						jQuery('.cdhl-loader-img').html('');
						jQuery('.res-msg').css('color', 'red');
						jQuery('.res-msg').show();
						jQuery('.res-msg').html(response.message).delay(5000).fadeOut('slow');
					}

				}
			});
		});


		$('.cdhl_cars_attributes').sortable({
			update: function (ev, ui) {
				var widget = $(this);
				var removeButton = $("<span>X</span>").click(function () {
					var parentLi = $(this).parent();
					$(this).remove();
					parentLi.appendTo($('#cdhl_vin_items'))
				  //  $('#cdhl_vin_items li').sort(asc_sort).appendTo($('#cdhl_vin_items'));
				});
				$(ui.item).append(removeButton);
			}
		}).disableSelection();

		jQuery(".cdhl_submit_vin").click(function () {
			jQuery('.cdhl-loader-img').html('<img src="' + cdhl.cdhl_url + 'images/loader-20x20.gif" alt="loading.." width="20px" height="20px"/>');
			jQuery("#cars_vin_import_form").submit();
		});

		jQuery(function () {
			jQuery("#tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
			jQuery("#tabs li").removeClass("ui-corner-top").addClass("ui-corner-left");
		});
	});
	function asc_sort(a, b) {
		return (jQuery(b).text().toUpperCase()) < (jQuery(a).text().toUpperCase());
	}
} )( jQuery );
