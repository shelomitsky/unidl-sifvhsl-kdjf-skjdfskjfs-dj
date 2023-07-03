var this_url = window.location.search.split('&'), token = this_url[1];

for(i in this_url) {
	if(this_url[i].indexOf('user_token')+1) {
		token = this_url[i];
	}
}

$(function() {
	set_color('#tab-topmenu');
	
	$('.nav-stacked li a, .tab-content-new > div > .nav-tabs li a').on('click', function() {
		set_color($(this).attr('href'));
	});
	
	if($(window).width() > 767) {
		$('.nav-pills li').not('.new').on('click', function() {
			var destination = $('.nav-pills').offset().top-60;
			$('html, body').animate({scrollTop: destination}, 400);
		});
	}
		
	$(window).scroll(function() {	
		if($(this).scrollTop()>100) {
			if(!$('.scroll_button').length) {
				$('body').append('<div class="scroll_button"></div>');
				$('.btns').clone().appendTo('.scroll_button');
				$('[data-toggle=\'tooltip\']').tooltip({container:'body', placement:'bottom'});
			}
		} else {
			$('.scroll_button').remove();
		}
	});	
		
	$('input[name="uni_set[save_date]"]').val(Date.now());
	
	$('.container-fluid_new > .nav a').on('click', function(e) {
		e.preventDefault();
	
		if (confirm(uni_text_alert)) {
			location = $(this).attr('href');
		}
	});
	
	if($('.uni-alert').length) {
		$('.btns .btn-success').attr('disabled', true);
	}
	
	$('body').on('click', '.btns button', () => {
		saveSet();
	})
	
	uniFindSettings.init();
	
	$('#content .nav li a').each(function() {
		$(this).addClass($(this).attr('href').replace('#', ''));
	});
	
	$(window).scroll(() => {
		if($('.tooltip').length) $('.tooltip').remove();
	});
	
	setTimeout(() => { 
		$.get('index.php?route=design/theme/history&'+token, function(data) {
			if($(data).find('tbody td').text('unishop2').length > 1) {
				$('.container-fluid_new').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+uni_text_error_design+' <a href="index.php?route=design/theme&'+token+'" target="_blank" style="text-decoration:underline">'+uni_text_error_design_link+'</a></div>');
			}
		});
	}, 5000);
	
	$('body').on('click', '.phones-is-second', function() {
		$(this).closest('.tab-pane').find('.phones-is-second').not($(this)).find('input').prop('checked', false).attr('checked', false);
	});
	
	$('#column-left').append('<div class="show-column-left"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-right"></i></div>');
	
	$('.show-column-left').on('click', function() {
		$(this).toggleClass('active');
		$('#column-left').toggleClass('active');
	});
	
	$('body').on('click', '.infolink > a', function() {
		if(!$(this).next().hasClass('dropdown-menu')) {
			$(this).after($('.info-news-list').html());
		}
	});
	
	$('body').on('click', '.infolink li a', function() {
		const elem = $(this), target = elem.closest('.infolink').prev(), lang_id = elem.closest('.infolink').data('lang');
		
		let href = elem.data('href');
		
		if(elem.data('href-seo')) {
			let seohref = JSON.parse(elem.data('href-seo').replace(/'/g, '"'))[lang_id];
			
			if(typeof(seohref) != 'undefined') {
				href = seohref;
			}
		}
											
		target.find('input:first').val(elem.data('name'));
		target.find('input:last').val(href);
	});
	
	$('input[name=\'catalog_featured_product\']').autocomplete({
		source: function(request, response) {
			$.ajax({
				url: 'index.php?route=catalog/product/autocomplete&'+token+'&filter_name='+encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					response($.map(json, (item) => {
						return {
							label: item['name'],
							value: item['product_id']
						}
					}));
				}
			});
		},
		select: function(item) {
			$('input[name=\'catalog_featured_product\']').val('');
		
			$('.catalog-featured-product-' + item['value']).remove();
		
			$('.catalog-featured-product').append('<div class="catalog-featured-product-'+item['value']+'"><i class="fa fa-minus-circle"></i>'+item['label']+'<input type="hidden" name="uni_set[catalog][featured_page][products][]" value="'+item['value']+'" /></div>');	
		}
	});
	
	$('.catalog-featured-product').on('click', '.fa-minus-circle', function() {
		$(this).parent().remove();
	});
	
	$('body').on('click', '.landinglinks-category', function() {
								
		const $this = $(this);
							
		$this.autocomplete({
			'source': function(request, response) {
				$.ajax({
					url: 'index.php?route=extension/module/uni_category_wall_v2/autocomplete&'+token+'&filter_name='+encodeURIComponent(request)+'&max_level=1&no_child=1',
					dataType: 'json',
					success: function(json) {
						response($.map(json, (item) => {
							return {
								label: item['name'],
								value: item['category_id']
							}
						}));
					}
				});
			},
			'select': function(item) {
				const category_id = item['value'];
									
				$this.data('cat-id', category_id).attr('name', 'uni_set[menu][landinglinks]['+category_id+']').val(item['label']);
				
				$this.parent().find('.ll-text').each(function() {
					$(this).attr('name', 'uni_set[menu][landinglinks]['+category_id+']['+$(this).data('key')+'][text]['+$(this).data('lang-id')+']');
				});
				
				$this.parent().find('.ll-link').each(function() {
					$(this).attr('name', 'uni_set[menu][landinglinks]['+category_id+']['+$(this).data('key')+'][link]['+$(this).data('lang-id')+']');
				});
				
				$this.parent().find('.ll-sort').each(function() {
					$(this).attr('name', 'uni_set[menu][landinglinks]['+category_id+']['+$(this).data('key')+'][sort_order]');
				});
			}
		});
	});
	
	$('body').on('click', '.textblock-category', function() {
								
		const $this = $(this);
							
		$this.autocomplete({
			'source': function(request, response) {
				$.ajax({
					url: 'index.php?route=extension/module/uni_category_wall_v2/autocomplete&'+token+'&filter_name='+encodeURIComponent(request),
					dataType: 'json',
					success: function(json) {
						json.unshift({
							manufacturer_id: 0,
							name: ' --- Не выбрано --- '
						});
						
						response($.map(json, (item) => {
							return {
								label: item['name'],
								value: item['category_id']
							}
						}));
					}
				});
			},
			'select': function(item) {
				const id = item['value'], name = item['label'];
									
				$this.attr('name', 'uni_set[product][textblock][category]['+id+'][name]').val(name);
				$this.prev().attr('name', 'uni_set[product][textblock][category]['+id+'][id]').val(id);
				
				$this.parent().find('.input-subcategory').attr('name', 'uni_set[product][textblock][category]['+id+'][subcategory]');
				$this.parent().find('.input-quickorder').attr('name', 'uni_set[product][textblock][category]['+id+'][quickorder]');
				
				$this.parent().find('textarea').each(function() {
					$(this).attr('name', 'uni_set[product][textblock][category]['+id+'][text]['+$(this).data('lang-id')+']');
				});
				
				setTimeout(() => {
					if($this.next().hasClass('dropdown-menu')) {
						$this.next().remove();
					}
				}, 100);
			}
		});
	});
	
	$('body').on('click', '.textblock-manufacturer-autocomplete', function() {
		
		const $this = $(this);
		
		$this.autocomplete({
			'source': function(request, response) {
				$.ajax({
					url: 'index.php?route=catalog/manufacturer/autocomplete&'+token+'&filter_name='+encodeURIComponent(request),
					dataType: 'json',
					success: function(json) {
						json.unshift({
							manufacturer_id: 0,
							name: ' --- Не выбрано --- '
						});

						response($.map(json, function(item) {
							return {
								label: item['name'],
								value: item['manufacturer_id']
							}
						}));
					}
				});
			},
			'select': function(item) {
				const id = item['value'], name = item['label'];
									
				$this.attr('name', 'uni_set[product][textblock][manufacturer]['+id+'][name]').val(name);
				$this.prev().attr('name', 'uni_set[product][textblock][manufacturer]['+id+'][id]').val(id);
				
				$this.parent().find('.input-quickorder').attr('name', 'uni_set[product][textblock][manufacturer]['+id+'][quickorder]');
				
				$this.parent().find('textarea').each(function() {
					$(this).attr('name', 'uni_set[product][textblock][manufacturer]['+id+'][text]['+$(this).data('lang-id')+']');
				});
				
				setTimeout(() => {
					if($this.next().hasClass('dropdown-menu')) {
						$this.next().remove();
					}
				}, 100);
			}
		});
		
	});
	
	const getPopularColor = () => {
		let arr = [];
										
		$('body .uni-color:not(".excluded")').each(function() {
			const c = $(this).val();
												
			if(c) {
				if (arr[c] != undefined) {
					++arr[c];
				} else {
					arr[c] = 1;
				}
			}
		});
											
		const getSortedKeys = (obj) => {
			return Object.keys(obj).sort((a,b) => {return obj[b]-obj[a]});
		}
											
		const res = getSortedKeys(arr).slice(0, 5);
											
		let i = 0;
											
		for (i in res) {
			$('input[name="popular_color_'+i+'"]').val(res[i]).css('background', '#'+res[i])
		}
	}
	
	//setTimeout(() => {
		getPopularColor();
	//}, 5000);
										
	$('.replace-color').on('click', function() {
											
		const $this = $(this), text = $this.text(), color_1 = $('input[name="popular_color_in"]').val(), color_2 = $('input[name="popular_color_out"]').val();
											
		if(color_1 && color_2) {
			$('body .uni-color:not(".excluded")').each(function() {
				if($(this).val() == color_1) {
					$(this).val(color_2).css('background', color_2);
				}
			});
											
			$this.html('<i class="fa fa-spinner"></i>')
											
			setTimeout(() => { 
				$this.html('<i class="fa fa-check"></i>');
			}, 1000);
												
			setTimeout(() => { 
				$this.text(text);
			}, 1500);
										
			getPopularColor();
												
			$('body .uni-color').each(function() {
				$(this).css('background', '#'+$(this).val());
			});
												
			set_color('.tab-content-new');
												
			setTimeout(() => { 
				$('.uni-color.excluded').each(function() {
					$(this).css('background', '#'+$(this).val());
		
					var bg = $(this).css('background-color').replace(/[^\d,]/g, '').split(',');

					if(bg[0] > 125 && bg[1] > 125 && bg[2] > 125) {
						$(this).css('color', '#000');
					} else {
						$(this).css('color', '#fff');
					}
				});
			}, 150);
		} else {
			$this.text(uni_error_color_not_selected);
											
			setTimeout(() => { 
				$this.text(text);
			}, 2000);
		}
	});
	
	$('.uni-color').each(function() {
		$(this).css('background', '#'+$(this).val());
		
		var bg = $(this).css('background-color').replace(/[^\d,]/g, '').split(',');

		if(bg[0] > 125 && bg[1] > 125 && bg[2] > 125) {
			$(this).css('color', '#000');
		} else {
			$(this).css('color', '#fff');
		}
	});
});
	
	let s_url = 'index.php?route=extension/module/uni_settings/save&'+token, s_data = $('#unishop input, #unishop textarea, #unishop select').serialize();
	
	function set_color(data) {
		$(data+' .uni-color').colorpicker({
			format:'hex',
			hexNumberSignPrefix:false
		}).on('changeColor', function(e) {
			$(this).css('background-color', e.color.toString('hex'));
		   
			var bg = e.color.toRGB();

			if(bg['r'] > 125 && bg['g'] > 125 && bg['b'] > 125) {
				$(this).css('color', '#000');
			} else {
				$(this).css('color', '#fff');
			}
		});
	}
	
	function popup_icons(id) {
		$('.fontawesome-icon-list').load('index.php?route=extension/module/uni_settings/getIconBlock&'+token, function() {
			$('#modal-icons-form').modal('show');
		
			$('#modal-icons-form i').on('click', function() {
				var this_class = $(this).attr('class');
			
				$('#'+id).find('i').attr('class', this_class);
				$('#'+id).next().val(this_class);
			
				$('#modal-icons-form').modal('hide');
			});
		});
	}
	
	function addHeaderLinks(lang_id, elem) {
		var key = $('#tab-header #headerlinks-'+lang_id+' .input-group').length+1;

		html = '<div class="input-group">';
		html += '<input type="text" name="uni_set[toplinks]['+key+'][title]['+lang_id+']" value="" placeholder="'+uni_placeholder_heading+'" class="form-control" />';
		html += '<input type="text" name="uni_set[toplinks]['+key+'][link]['+lang_id+']" value="" placeholder="'+uni_placeholder_link+'" class="form-control" />';
		html += '<span class="btn-default" onclick="$(this).parent().next().remove(); $(this).parent().remove();" title="'+uni_text_delete+'"><i class="fa fa-close"></i></span>';
		html += '</div>';
		html += '<div class="infolink">';
		html += '<a data-toggle="dropdown">'+uni_text_article_link+'</a>';
		html += '</div>';
											
		$(elem).before(html);
	}
	
	function addHeaderLinks2(lang_id, elem) {
		let key = 1, data_key = $('#headerlinks2-'+lang_id+' .headerlinks2-wrap').last().data('key');
		
		if(data_key) {
			key = data_key+1;
		}
															
		html = '<div class="headerlinks2-wrap headerlinks2-wrap-'+lang_id+'-'+key+'" data-key="'+key+'">';
		html += '<div class="headerlinks2">';
		html += '<div>';
		html += '<ul class="nav nav-tabs">';
		for (i in uni_img_or_ico_arr) {
			html += '<li class="m '+(i == 'img' ? 'active' : '')+'">';
			html += '<a href="#header-headerlinks2-icon-type-'+lang_id+'-'+key+'-'+i+'" data-toggle="tab">'+uni_img_or_ico_arr[i]+'</a>';
			html += '</li>';
		}
		html += '</ul>';
		html += '<div class="tab-content">';
		for (i in uni_img_or_ico_arr) {
			html += '<div id="header-headerlinks2-icon-type-'+lang_id+'-'+key+'-'+i+'" class="tab-pane menu-icon '+(i == 'img' ? 'active' : '')+'">';
			if (i == 'img') {
				html += '<a href="" id="thumb-image_header-headerlinks2-icon-'+lang_id+'-'+key+'" data-toggle="image" class="img-thumbnail img">';
				html += '<img src="'+uni_img_placeholder+'" alt="" title="" data-placeholder="'+uni_img_placeholder+'" style="max-width:100px;max-height:100px" />';
				html += '</a>';
				html += '<input type="hidden" name="uni_set[header][headerlinks2]['+key+'][img]['+lang_id+']" value="" id="image_header-headerlinks2-icon-'+lang_id+'-'+key+'" />';
			} else {
				html += '<a id="h-hl2-b-icon-'+lang_id+'-'+key+'" class="menu-icon__i" onclick="popup_icons($(this).attr(\'id\'))">';
				html += '<i class="fa fa-plus-circle"></i>';
				html += '</a>';
				html += '<input type="hidden" name="uni_set[header][headerlinks2]['+key+'][icon]['+lang_id+']" value="" />';
			}
			html += '</div>';
		}
		html += '</div>';
		html += '</div>';
		html += '<div>';
		html += '<div class="input-group">';
		html += '<input type="text" name="uni_set[header][headerlinks2]['+key+'][title]['+lang_id+']" value="" placeholder="'+uni_text_text+'" class="form-control" />';
		html += '<input type="text" name="uni_set[header][headerlinks2]['+key+'][link]['+lang_id+']" value="" placeholder="'+uni_text_link+'" class="form-control" />';
		html += '</div>';
		html += '<div class="infolink">';
		html += '<a data-toggle="dropdown">'+uni_text_article_link+'</a>';
		html += '</div>';
		html += '<a onclick="addHeaderLinks2Sub('+lang_id+', '+key+', this);" title="'+uni_text_add_link_second_level+'" data-toggle="tooltip" class="add-sub btn btn-success"><i class="fa fa-plus"></i></a>';
		html += '</div>';
		html += '</div>';
		html += '<br />';
		html += uni_text_sort+'<input type="text" name="uni_set[header][headerlinks2]['+key+'][sort_order]['+lang_id+']" value="1" class="form-control">';
		html += uni_text_column+'<input type="text" name="uni_set[header][headerlinks2]['+key+'][column]['+lang_id+']" value="1" class="form-control">';
		html += '<br />';
		html += '<label><input type="checkbox" name="uni_set[header][headerlinks2]['+key+'][show_in_cat]['+lang_id+']" value="1"><span></span>'+uni_text_headerlinks2_incat+'</label>';
		html += '<br />';
		html += '<button type="button" onclick="$(this).parent().remove()" class="btn btn-default" style="margin:10px 0 0">'+uni_text_delete+'</button>';
		html += '<hr style="margin:15px 0" />';
		html +=' </div>';
		
		$(elem).before(html);
	}
	
	function addHeaderLinks2Sub(lang_id, parent_key, elem) {
		let key = 1, data_key = $(elem).parent().find('.submenu').last().data('key');
		
		if(data_key) {
			key = data_key+1;
		}
		
		html  = '<div class="submenu headerlinks2__submenu" data-key="'+key+'">';
		html  += '<div class="input-group">';
		html  += '<i class="fas fa-level-up-alt"></i>';
		html  += '<input type="text" name="uni_set[header][headerlinks2]['+parent_key+'][children]['+key+'][title]['+lang_id+']" value="" placeholder="'+uni_text_text+'" class="form-control" />';
		html  += '<input type="text" name="uni_set[header][headerlinks2]['+parent_key+'][children]['+key+'][link]['+lang_id+']" value="" placeholder="'+uni_text_link+'" class="form-control" />';
		html  += '<span class="input-group-btn btn-default" onclick="$(this).parent().parent().remove();" title="'+uni_text_delete+'"><i class="fa fa-close"></i></span>';
		html  += '</div>';
		html  += '<a onclick="addHeaderLinks2Sub2('+lang_id+', '+parent_key+', '+key+', this);" title="'+uni_text_add_link_third_level+'" data-toggle="tooltip" class="add-sub btn btn-info"><i class="fa fa-plus"></i></a>';
		html  += '</div>';
		
		$(elem).before(html);
		
		$('[data-toggle=\'tooltip\']').tooltip({container:'body', trigger:'hover'});
	}
	
	function addHeaderLinks2Sub2(lang_id, parent_key, parent_key2, elem) {
		let key = 1, data_key = $(elem).parent().find('.submenu2').last().data('key');
		
		if(data_key) {
			key = data_key+1;
		}
		
		html  = '<div class="submenu2" data-key="'+key+'">';
		html  += '<div class="input-group">';
		html  += '<i class="fas fa-level-up-alt"></i>';
		html  += '<input type="text" name="uni_set[header][headerlinks2]['+parent_key+'][children]['+parent_key2+'][children]['+key+'][title]['+lang_id+']" value="" placeholder="'+uni_text_text+'" class="form-control" />';
		html  += '<input type="text" name="uni_set[header][headerlinks2]['+parent_key+'][children]['+parent_key2+'][children]['+key+'][link]['+lang_id+']" value="" placeholder="'+uni_text_link+'" class="form-control" />';
		html  += '<span class="input-group-btn btn-default" onclick="$(this).parent().parent().remove();" title="'+uni_text_delete+'"><i class="fa fa-close"></i></span>';
		html  += '</div>';
		html  += '</div>';
		
		$(elem).before(html);
		
		$('[data-toggle=\'tooltip\']').tooltip({container:'body', trigger:'hover'});
	}
	
	function addMainPhones(lang_id, elem) {
		let key = 2, data_key = $('#main-phones-'+lang_id+' .header-contacts-wrap').last().data('key');
		
		if(data_key) {
			key = data_key+1;
		}

		html  = '<div class="header-contacts-wrap" data-key="'+key+'">';
		html  += '<div class="header-contacts">';
		html  += '<div>';
		html  += '<div class="menu-icon__title"></div>';
		html  += '<ul class="nav nav-tabs">';
		for (i in uni_img_or_ico_arr) {
			html  += '<li class="m '+(i == 'img' ? 'active' : '')+'">';
			html  += '<a href="#header-contacts-icon-type-'+key+'-'+lang_id+'-'+i+'" data-toggle="tab">'+uni_img_or_ico_arr[i]+'</a>';
			html  += '</li>';
		}
		html  += '</ul>';
		html  += '<div class="tab-content">';
		for (i in uni_img_or_ico_arr) {
			html  += '<div id="header-contacts-icon-type-'+key+'-'+lang_id+'-'+i+'" class="tab-pane menu-icon '+(i == 'img' ? 'active' : '')+'">';
			if (i == 'img') {
				html  += '<a href="" id="thumb-image_header-contacts-icon-'+key+'-'+lang_id+'" data-toggle="image" class="img-thumbnail img">';
				html  += '<img src="'+uni_img_placeholder+'" alt="" title="" data-placeholder="'+uni_img_placeholder+'" style="max-width:100px;max-height:100px" />';
				html  += '</a>';
				html  += '<input type="hidden" name="uni_set[header][contacts][main]['+key+'][img]['+lang_id+']" value="" id="image_header-contacts-icon-'+key+'-'+lang_id+'" />';
			} else {
				html  += '<a id="h-co-icon-'+key+'-'+lang_id+'" class="menu-icon__i" onclick="popup_icons($(this).attr(\'id\'))">';
				html  += '<i class="fa fa-plus-circle"></i>';
				html  += '</a>';
				html  += '<input type="hidden" name="uni_set[header][contacts][main]['+key+'][icon]['+lang_id+']" value="" />';
			}
			html  += '</div>';
		}
		html  += '</div>';
		html  += '</div>';
		html  += '<div>';
		html  += '<div class="menu-icon__title"></div>';
		html  += '<div>';
		html  += '<input type="text" name="uni_set[header][contacts][main]['+key+'][text]['+lang_id+']" value="" placeholder="'+uni_text_mf_text+'" class="form-control" />';
		html  += '<input type="text" name="uni_set[header][contacts][main]['+key+'][number]['+lang_id+']" value="" placeholder="'+uni_text_mf_number+'" class="form-control" />';
		html  += '<select name="uni_set[header][contacts][main]['+key+'][type]['+lang_id+']" class="form-control">';
		html  += '<optgroup label="По клику:" />';
		html  += '<option value="">Ничего</option>';
		for (i in uni_contact_actions) {
			html  += '<option value="'+i+'">'+uni_contact_actions[i]+'</option>';
		}
		html  += '</select>';
		html  += '</div>';
		html  += '</div>';
		html  += '</div>';
		html  += '<br />';
		html  += '<label class="phones-is-second"><input type="checkbox" name="uni_set[header][contacts][main]['+key+'][is_second]['+lang_id+']" value="1" /><span></span>'+uni_text_contact_is_second+'</label>';
		html  += '<label class="phones-contact-page"><input type="checkbox" name="uni_set[header][contacts][main]['+key+'][contact_page]['+lang_id+']" value="1" /><span></span>'+uni_text_contact_contact_page+'</label>';
		html  += '<label><input type="checkbox" name="uni_set[header][contacts][main]['+key+'][contact_page_as_text]['+lang_id+']" value="1" /><span></span>'+uni_text_contact_contact_page_as_text+'</label>';
		html  += '<br />';
		html  += '<button type="button" onclick="$(this).parent().remove()" class="btn btn-default" style="margin:5px 0 0">'+uni_text_delete+'</button>';
		html  += '<hr style="margin:15px 0" />';
		html  += '</div>';

		$(elem).before(html);
	}
	
	function addContacts(lang_id, elem) {
		let key = 1, data_key = $('#additional-contact-'+lang_id+' .header-contacts-addit-wrap').last().data('key');
		
		if(data_key) {
			key = data_key+1;
		}

		html  = '<div class="header-contacts-addit-wrap" data-key="'+key+'">';
		html  += '<div class="header-contacts-addit">';
		html  += '<div>';
		html  += '<div class="menu-icon__title"></div>';
		html  += '<ul class="nav nav-tabs">';
		for (i in uni_img_or_ico_arr) {
			html  += '<li class="m '+(i == 'img' ? 'active' : '')+'">';
			html  += '<a href="#header-contacts-addit-icon-type-'+key+'-'+lang_id+'-'+i+'" data-toggle="tab">'+uni_img_or_ico_arr[i]+'</a>';
			html  += '</li>';
		}
		html  += '</ul>';
		html  += '<div class="tab-content">';
		for (i in uni_img_or_ico_arr) {
			html  += '<div id="header-contacts-addit-icon-type-'+key+'-'+lang_id+'-'+i+'" class="tab-pane menu-icon '+(i == 'img' ? 'active' : '')+'">';
			if (i == 'img') {
				html  += '<a href="" id="thumb-image_header-contacts-addit-icon-'+key+'-'+lang_id+'" data-toggle="image" class="img-thumbnail img">';
				html  += '<img src="'+uni_img_placeholder+'" alt="" title="" data-placeholder="'+uni_img_placeholder+'" style="max-width:100px;max-height:100px" />';
				html  += '</a>';
				html  += '<input type="hidden" name="uni_set[header][contacts][addit]['+key+'][img]['+lang_id+']" value="" id="image_header-contacts-addit-icon-'+key+'-'+lang_id+'" />';
			} else {
				html  += '<a id="h-co-addit-icon-'+key+'-'+lang_id+'" class="menu-icon__i" onclick="popup_icons($(this).attr(\'id\'))">';
				html  += '<i class="fa fa-plus-circle"></i>';
				html  += '</a>';
				html  += '<input type="hidden" name="uni_set[header][contacts][addit]['+key+'][icon]['+lang_id+']" value="" />';
			}
			html  += '</div>';
		}
		html  += '</div>';
		html  += '</div>';
		html  += '<div>';
		html  += '<div class="menu-icon__title"></div>';
		html  += '<div>';
		html  += '<input type="text" name="uni_set[header][contacts][addit]['+key+'][text]['+lang_id+']" value="" placeholder="'+uni_text_mf_text+'" class="form-control" />';
		html  += '<input type="text" name="uni_set[header][contacts][addit]['+key+'][number]['+lang_id+']" value="" placeholder="'+uni_text_mf_number+'" class="form-control" />';
		html  += '<select name="uni_set[header][contacts][addit]['+key+'][type]['+lang_id+']" class="form-control">';
		html  += '<optgroup label="По клику:" />';
		html  += '<option value="">Ничего</option>';
		for (i in uni_contact_actions) {
			html  += '<option value="'+i+'">'+uni_contact_actions[i]+'</option>';
		}
		html  += '</select>';
		html  += '</div>';
		html  += '</div>';
		html  += '</div>';
		html  += '<br />';
		html  += '<label class="phones-contact-page"><input type="checkbox" name="uni_set[header][contacts][addit]['+key+'][contact_page]['+lang_id+']" value="1" /><span></span>'+uni_text_contact_contact_page+'</label>';
		html  += '<label><input type="checkbox" name="uni_set[header][contacts][addit]['+key+'][contact_page_as_text]['+lang_id+']" value="1" /><span></span>'+uni_text_contact_contact_page_as_text+'</label>';
		html  += '<br />';
		html  += '<button type="button" onclick="$(this).parent().remove()" class="btn btn-default" style="margin:5px 0 0">'+uni_text_delete+'</button>';
		html  += '<hr style="margin:15px 0" />';
		html  += '</div>';

		$(elem).before(html);
	}
	
	function addFooterLinksNew(elem, parent_id, lang_id) {
		
		const key = $(elem).parent().find('.input-group').length+1;
		
		html = '<div class="input-group">';
		html += '<input type="text" name="uni_set[footer_columns]['+parent_id+'][links]['+key+'][title]['+lang_id+']" value="" placeholder="'+uni_placeholder_heading+'" class="form-control" />';
		html += '<input type="text" name="uni_set[footer_columns]['+parent_id+'][links]['+key+'][link]['+lang_id+']" value="" placeholder="'+uni_placeholder_link+'" class="form-control" />';
		html += '<input type="text" name="uni_set[footer_columns]['+parent_id+'][links]['+key+'][sort_order]['+lang_id+']" value="1" placeholder="'+uni_text_sort+'" class="form-control" style="width:100px" />';
		html += '<span class="input-group-btn btn-default" onclick="$(this).parent().remove()" title="'+uni_text_delete+'"><i class="fa fa-close"></i></span>';
		html += '</div>';
		
		$(elem).before(html);
	}
	
	function addSocials(data) {
		var socials_num = $('#tab-footer .socials-icon .input-group').length+1;

		html = '<div class="input-group">';
		html += '<select name="uni_set[socials]['+socials_num+'][icon]" class="form-control">';
		html += data;
		html += '</select>';
		html += '<input type="text" name="uni_set[socials]['+socials_num+'][link]" value="" placeholder="'+uni_placeholder_link+'" class="form-control" />';
		html += '<span class="btn-default" onclick="$(this).parent().remove()" title="'+uni_text_delete+'"><i class="fa fa-close"></i></span>';
		html += '</div>';
		
		socials_num = socials_num+1;
		
		$('#tab-footer .socials-icon').append(html);
	}
	
	function addProductBannerDefault(lang_id) {
		let key = 1, data_key = $('#product-banner-'+lang_id+' .product-banners-default').last().data('key');
		
		if(data_key) {
			key = data_key+1;
		}

		html = '<div class="product-banners-default" data-key="'+key+'">';
		html += '<hr style="margin:15px 0" />';
		html += '<div class="product-banners product-banners-'+key+'">';
		html += '<div>';
		html += '<div class="menu-icon__title"></div>';
		html += '<ul class="nav nav-tabs">';
		for (i in uni_img_or_ico_arr) {
			html += '<li class="m '+(i == 'img' ? 'active' : '')+' ">';
			html += '<a href="#product-banner-icon-type-'+key+'-'+lang_id+'-'+i+'" data-toggle="tab">'+uni_img_or_ico_arr[i]+'</a>';
			html += '</li>';
		}
		html += '</ul>';
		html += '<div class="tab-content">';
		for (i in uni_img_or_ico_arr) {
			html += '<div id="product-banner-icon-type-'+key+'-'+lang_id+'-'+i+'" class="tab-pane menu-icon '+(i == 'img' ? 'active' : '')+' ">';
			if (i == 'img') {
				html += '<a href="" id="thumb-image_product-banner-icon-'+key+'-'+lang_id+'" data-toggle="image" class="img-thumbnail img">';
				html += '<img src="'+uni_img_placeholder+'" alt="" title="" data-placeholder="'+uni_img_placeholder+'" style="max-width:100px;max-height:100px" />';
				html += '</a>';
				html += '<input type="hidden" name="uni_set[product][text_banner][default]['+key+'][img]['+lang_id+']" value="" id="image_product-banner-icon-'+key+'-'+lang_id+'" />';
			} else {
				html += '<a id="p-b-icon-'+key+'-'+lang_id+'" class="menu-icon__i" onclick="popup_icons($(this).attr(\'id\'))">';
				html += '<i class="fa fa-plus-circle"></i>';
				html += '</a>';
				html += '<input type="hidden" name="uni_set[product][text_banner][default]['+key+'][icon]['+lang_id+']" value="" />';
			}
			html += '</div>';
		}
		html += '</div>';
		html += '</div>';
		html += '<div>';
		html += '<div class="menu-icon__title"></div>';
		html += '<div>';
		html += '<input type="text" name="uni_set[product][text_banner][default]['+key+'][text]['+lang_id+']" value="" placeholder="'+uni_text_text+'" class="form-control" />';
		html += '<input type="text" name="uni_set[product][text_banner][default]['+key+'][link]['+lang_id+']" value="" placeholder="'+uni_text_link+'" class="form-control" />';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<br />';
		html += '<label><input type="checkbox" name="uni_set[product][text_banner][default]['+key+'][link_popup]['+lang_id+']" value="1" /><span></span> '+uni_text_link_popup+'</label>';
		html += '<label><input type="checkbox" name="uni_set[product][text_banner][default]['+key+'][hide]['+lang_id+']" value="1" /><span></span> '+uni_text_hide_small_screen+'</label>';
		html += '<br />';
		html += '<button type="button" onclick="$(this).parent().remove()" class="btn btn-default" style="margin:5px 0 0">'+uni_text_delete+'</button>';
		html += '</div>';
			
		$('#product-banner-'+lang_id+' > hr').before(html);
	}
	
	function saveSet() {
		$('.note-editable').each(function() {
			$(this).closest('.tab-pane').find('textarea').html($(this).html());
		});
		
		$('.cke_wysiwyg_frame').each(function() {
			$(this).parent().parent().parent().prev().val($(this).contents().find('.cke_editable').html());
		});
		
		$('input[name="uni_set[save_date]"]').val(Date.now());
		
		let $btn = $('.btns button');
		
		$.ajax({
			url: s_url,
			type: 'post',
			data: $('#unishop input, #unishop textarea, #unishop select').serialize(),
			dataType: 'json',
			beforeSend: function() {
				$btn.html('<i class="fa fa-spinner"></i>');
			}, 
			success: function(data) {
				$('.unishop-set-alert').remove();
				
				if(data == 'success') {
					$btn.html('<i class="fa fa-check"></i>');
					setTimeout(function() {
						$btn.html('<i class="fa fa-save"></i>');
					}, 1000);
				} else {
					$btn.html('<i class="fa fa-remove"></i>').data('original-title', uni_text_alert_validate).attr('class', 'btn btn-danger');
					$('.container-fluid_new').prepend('<div class="unishop-set-alert alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+uni_text_alert_validate+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
				
				setTimeout(() => {
					$.get('index.php?route=marketplace/modification/refresh&'+token, () => {
						$.get('index.php?route=catalog/review&'+token);
					});
				}, 1500);
			}
		});
	}
	
	uniFindSettings = {
		init:function() {
			var base = this;
			
			base.input = 'input[name="search-setting"]';
			base.item = '.find-settings__a';
			base.result = '.find-settings__result';
			base.separator = '🠒';
			base.area = '#unishop .nav, #unishop .col-sm-2';
			base.minlength = 3;
			base.timer;
			base.delay = 500;
			
			$(base.input).on('keyup', function() {
				base.keyUp();
			});
			
			$('html body').on('click', base.item, function() {
				base.itemClick(this);
			});
		},
		keyUp:function() {
			var base = this,
				html;
				
				base.phrase = $(base.input).val().trim();
		
			if (base.phrase.length >= base.minlength) {
		
				clearTimeout(base.timer);
			
				base.timer = setTimeout(function(){
		
					$(base.area).removeClass('description');
					
					base.mark_unmark(base.area, base.phrase);
					
					html = '<ul class="list-unstyled">';
					
					$('.highlight').each(function(){
				
						let $this = $(this),
							item_class = $this.parent().attr('class');
				
						if($this.closest('#unishop .col-sm-2').length) {
							item_class = $this.parent().parent().parent().attr('id')+' description';
					
							$this.closest('#unishop .col-sm-2').addClass(item_class);
						}
					
						let cc = $this.closest('.tab-pane').attr('id'),
							cc2 = $('#'+cc).parent().closest('.tab-pane').attr('id');
				
						$txt_0 = $('#unishop a.'+cc).text().replace('New', '');
						$txt_1 = $('#unishop a.'+cc2).text().replace('New', '');
				
						let $text = $this.parent();

							$text.find('span').remove();
				
							$txt_2 = $text.text().replace('New', '');
			
						if(!$this.closest('span').length) { 
							html += '<li>&#8226; <a class="find-settings__a" data-class="'+item_class+'">';
							html += $txt_1 ? $txt_1+' '+base.separator+' ' : '';
							html += $txt_0 ? $txt_0+' '+base.separator+' ' : '';
							html += $txt_2;
							html += '</a></li>';
						}
					});
					
					html += '</ul>';
			
					$(base.result).html(html);
			
					if($('.highlight').length) {
						$(base.result).show();
					} else {
						$(base.result).hide();
					}
					
				}, base.delay);
			} else {
				$(base.result).hide();
			}
		},
		itemClick:function(el) {
			var base = this;
			
			elem_class = $(el).data('class');
			
			txt_arr = $(el).text().split(base.separator);
			
			base.mark_unmark(base.area, txt_arr[txt_arr.length - 1]);
		
			if(elem_class.includes('description')) {
				elem_class = elem_class.replace('description', '');
			
				setTimeout(() => {
					$('html, body').animate({scrollTop: $('#unishop').find('.col-sm-2.'+elem_class).offset().top-50}, 400);
				}, 300);
			}
			
			$('#unishop .'+elem_class).click();
			$('#unishop .'+$('#unishop .'+elem_class).closest('.tab-pane').attr('id')).click();
		},
		mark_unmark:function(el, txt){
			var base = this;
			
			$(el).unhighlight({element:'mark', className:'highlight'}).highlight(txt.trim(), {element:'mark', className:'highlight'});
		},
		remove:function() {
			var base = this;
				
			$(base.input).val('');
			$(base.result).hide();
			$(base.area).unhighlight({element:'mark', className:'highlight'});
		}
	}
	
///////////////////
	
	function addLandingLinksItem(languages) {
		const key = 'new-' + $('.landinglinks > ul li').length;
											
		$('.landinglinks > ul').append('<li class="landinglinks-'+key+'"><a href="#landinglinks-'+key+'" style="text-decoration:none" data-toggle="tab">'+uni_text_landinglinkmenu_new_block+'</a></li>');
												
		html = '<div id="landinglinks-'+key+'" class="landinglinks__item tab-pane">';
		html += '<input type="text" name="uni_set[menu][landinglinks]['+key+']" value="" placeholder="'+uni_text_category_autocomplete+'" data-cat-id="'+key+'" class="landinglinks-category form-control" style="width:100% !important;max-width:1000px" />';
		html += '<br />';
		html += '<div class="landinglinks__btns">';
		html += '<button type="button" onclick="addLandingLinksItemLinks(this, languages);" title="" class="btn btn-success">'+uni_text_landinglinkmenu_add_link+'</button>';
		html += '<button type="button" onclick="removeLandingLinksItem(\''+key+'\')" class="btn btn-default">'+uni_text_landinglinkmenu_del_block+'</button>';
		html += '</div>';
		html += '</div>';
												
		$('.landinglinks > .tab-content').append(html);
		$('.landinglinks-'+key+' a').tab('show');
	}
	
	function addLandingLinksItemLinks(item, languages) {
		const elem = $(item).closest('.landinglinks__item');
												
		const cat_id = elem.find('.landinglinks-category').data('cat-id'), key = elem.find('.landinglinks__item-links').length;
												
		html = '<div class="landinglinks__item-links landinglinks__item-links-'+cat_id+'">';
		html += '<ul class="nav nav-tabs">';
		for (i in languages) {
			html += '<li><a href="#landinglinks-'+cat_id+'-'+key+'-'+languages[i]['id']+'" data-toggle="tab"><img src="language/'+languages[i]['code']+'/'+languages[i]['code']+'.png" title="'+languages[i]['name']+'" /></a></li>';
		}
		html += '</ul>';
		html += '<div class="tab-content">';
		for (i in languages) {
		html += '<div id="landinglinks-'+cat_id+'-'+key+'-'+languages[i]['id']+'" class="tab-pane">';
		html += '<div class="input-group">';
		html += '<input type="text" name="uni_set[menu][landinglinks]['+cat_id+']['+key+'][text]['+languages[i]['id']+']" value="" placeholder="'+uni_text_text+'" data-key="'+key+'" data-lang-id="'+languages[i]['id']+'" class="ll-text form-control" />';
		html += '<input type="text" name="uni_set[menu][landinglinks]['+cat_id+']['+key+'][link]['+languages[i]['id']+']" value="" placeholder="'+uni_placeholder_link+'" data-key="'+key+'" data-lang-id="'+languages[i]['id']+'" class="ll-link form-control" />';
		html += '</div>';
		html += '</div>';
		}
		html += '</div>';
		html += '<div class="landinglinks__item-sort">';
		html += uni_text_headerlinks2_sort+'<input type="text" name="uni_set[menu][landinglinks]['+cat_id+']['+key+'][sort_order]" value="0" data-key="'+key+'" class="ll-sort form-control" />';
		html += '<button type="button" onclick="$(this).parent().parent().remove()" title="" class="add-sub btn btn-default">'+uni_button_remove+'</button>';
		html += '</div>';
		html += '</div>';
												
		$(item).parent().before(html)
												
		$('.landinglinks__item-links .nav-tabs').each(function() {
			$(this).find('li:first a').tab('show');
		});
	}
	
	function removeLandingLinksItem(id) {
		$('#landinglinks-'+id+', .landinglinks-'+id).remove();
												
		$('.landinglinks > .nav-tabs').each(function() {
			$(this).find('li:first a').tab('show');
		});
	}
	
	function addProductTextblock(languages) {
		const key = 'new-' + $('.textblock > ul li').length;
											
		$('.textblock > ul').append('<li class="textblock-'+key+'"><a href="#textblock-'+key+'" style="text-decoration:none" data-toggle="tab">'+uni_text_landinglinkmenu_new_block+'</a></li>');
												
		html = '<div id="textblock-'+key+'" class="tab-pane">';
		html += '<input type="hidden" name="uni_set[product][textblock][category]['+key+'][id]" value="" />';
		html += '<input type="text" name="uni_set[product][textblock][category]['+key+'][name]" value="" placeholder="'+uni_text_category_autocomplete+'" class="textblock-category form-control" style="width:100%" />';
		html += '<br />';
		html += '<label><input type="checkbox" name="uni_set[product][textblock][category]['+key+'][subcategory]" value="1" class="input-subcategory" /><span></span>'+uni_text_product_text_block_subcategory+'</label>';
		html += '<label><input type="checkbox" name="uni_set[product][textblock][category]['+key+'][quickorder]" value="1" class="input-quickorder" /><span></span>'+uni_text_product_text_block_quickorder+'</label>';
		html += '<br /><br />';
		html += '<ul class="nav nav-tabs">';
		for (i in languages) {
			html += '<li><a href="#textblock-'+key+'-'+languages[i]['id']+'" data-toggle="tab"><img src="language/'+languages[i]['code']+'/'+languages[i]['code']+'.png" title="'+languages[i]['name']+'" /></a></li>';
		}
		html += '</ul>';
		html += '<div class="tab-content">';
		for (i in languages) {
			html += '<div id="textblock-'+key+'-'+languages[i]['id']+'" class="tab-pane">';
			html += '<textarea name="uni_set[product][textblock][category]['+key+'][text]['+languages[i]['id']+']" data-lang-id='+languages[i]['id']+' class="form-control"></textarea>';
			html += '</div>';
		}
		html += '</div>';
		html += '<br />';
		html += '<button type="button" onclick="removeProductTextblock(\''+key+'\')" class="btn btn-default">'+uni_button_remove+'</button>';
		html += '</div>';
												
		$('.textblock > .tab-content').append(html);
		$('.textblock-'+key+' a').tab('show');
		$('#textblock-'+key+' li:first a').tab('show');
	}
											
	function removeProductTextblock(id) {
		$('#textblock-'+id+', .textblock-'+id).remove();
												
		$('.textblock > .nav-tabs').each(function() {
			$(this).find('li:first a').tab('show');
		});
	}
	
	function addProductTextblock2(languages) {
		const key = 'new-' + $('.textblock-manufacturer > ul li').length;
											
		$('.textblock-manufacturer > ul').append('<li class="textblock-manufacturer-'+key+'"><a href="#textblock-manufacturer-'+key+'" style="text-decoration:none" data-toggle="tab">'+uni_text_landinglinkmenu_new_block+'</a></li>');
												
		html = '<div id="textblock-manufacturer-'+key+'" class="tab-pane">';
		html += '<input type="hidden" name="uni_set[product][textblock][manufacturer]['+key+'][id]" value="" />';
		html += '<input type="text" name="uni_set[product][textblock][manufacturer]['+key+'][name]" value="" placeholder="'+uni_text_category_autocomplete+'" class="textblock-manufacturer-autocomplete form-control" style="width:100%" />';
		html += '<br />';
		html += '<label><input type="checkbox" name="uni_set[product][textblock][manufacturer]['+key+'][quickorder]" value="1" class="input-quickorder" /><span></span>'+uni_text_product_text_block_quickorder+'</label>';
		html += '<br /><br />';
		html += '<ul class="nav nav-tabs">';
		for (i in languages) {
			html += '<li><a href="#textblock-manufacturer-'+key+'-'+languages[i]['id']+'" data-toggle="tab"><img src="language/'+languages[i]['code']+'/'+languages[i]['code']+'.png" title="'+languages[i]['name']+'" /></a></li>';
		}
		html += '</ul>';
		html += '<div class="tab-content">';
		for (i in languages) {
			html += '<div id="textblock-manufacturer-'+key+'-'+languages[i]['id']+'" class="tab-pane">';
			html += '<textarea name="uni_set[product][textblock]['+key+'][manufacturer][text]['+languages[i]['id']+']" data-lang-id='+languages[i]['id']+' class="form-control"></textarea>';
			html += '</div>';
		}
		html += '</div>';
		html += '<br />';
		html += '<button type="button" onclick="removeProductTextblock2(\''+key+'\')" class="btn btn-default">'+uni_button_remove+'</button>';
		html += '</div>';
												
		$('.textblock-manufacturer > .tab-content').append(html);
		$('.textblock-manufacturer-'+key+' a').tab('show');
		$('#textblock-manufacturer-'+key+' li:first a').tab('show');
	}
											
	function removeProductTextblock2(id) {
		$('#textblock-manufacturer-'+id+', .textblock-manufacturer-'+id).remove();
												
		$('.textblock-manufacturer > .nav-tabs').each(function() {
			$(this).find('li:first a').tab('show');
		});
	}
	
	function addPickupItem(elem, languages) {
		
		let items = $('.checkout-pickup-item').length, html = '';
		
		html += '<div class="checkout-pickup-item checkout-pickup-item-'+items+'">';
		html += '<ul class="nav nav-tabs">';
		for (i in languages) {
			html += '<li><a href="#checkout_pickup_item-'+items+'-'+languages[i]['id']+'" data-toggle="tab"><img src="language/'+languages[i]['code']+'/'+languages[i]['code']+'.png" title="'+languages[i]['name']+'" /></a></li>';
		}
		html += '</ul>';
		html += '<div class="tab-content">';
		
		for (i in languages) {
			
			const lang_id = languages[i]['id'];
			
			html += '<div id="checkout_pickup_item-'+items+'-'+languages[i]['id']+'" class="tab-pane">';
			html += '<small>'+uni_text_pickup_title+'</small><br />';
			html += '<input type="text" name="uni_set[checkout][pickup][items]['+items+'][title]['+lang_id+']" value="" placeholder="" class="form-control" />';
			html += '<small>'+uni_text_pickup_address+'</small><br />';
			html += '<input type="text" name="uni_set[checkout][pickup][items]['+items+'][address]['+lang_id+']" value="" placeholder="" class="form-control" />';
			html += '<small>'+uni_text_pickup_time+'</small><br />';
			html += '<input type="text" name="uni_set[checkout][pickup][items]['+items+'][working_hours]['+lang_id+']" value="" placeholder="" class="form-control" />';
			html += '<small>'+uni_text_pickup_life+'</small><br />';
			html += '<input type="text" name="uni_set[checkout][pickup][items]['+items+'][shelf_life]['+lang_id+']" value="" placeholder="" class="form-control" />';
			html += '<small>'+uni_text_pickup_map+'</small><br />';
			html += '<textarea name="uni_set[checkout][pickup][items]['+items+'][map]['+lang_id+']" placeholder="" class="form-control"></textarea>';
			html += '</div>';
		}
										
		html += '</div>';
		html += '<button type="button" class="btn btn-xs btn-default" onclick="$(this).parent().remove()">'+uni_button_pickup_remove+'</button>';
		html += '<hr style="margin:15px 0" />';
		html += '</div>';
		
		$(elem).before(html);
		$('.checkout-pickup-item-'+items+' li:first a').tab('show');
	}
	
	function addTrial(btn) {
		$('.container-fluid_new > .alert').remove();
			
		if (!$('#trial input[name=\'trial\']').is(':checked')) {
			$('.container-fluid_new').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+uni_text_error_agree+'</div>');
			return false;
		}
				
		$.ajax({
			url: 'index.php?route=extension/module/uni_settings/addTrial&'+token,
			type: 'post',
			dataType: 'json',
			beforeSend: function() {
				$(btn).button('loading');
			}, 
			complete: function() {
				$(btn).button('reset');
			},
			success: function(json) {
				if(json['success']) {
					$(btn).remove();
					window.location.reload();
				} else {
					$('.container-fluid_new').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+uni_text_error_trial+'</div>');
				}
			}
		});
	}
	
	function addKey(btn) {
		$('.container-fluid_new > .alert').remove();
	
		if ($(btn).prev().val() == '') {
			$('.container-fluid_new').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+uni_text_error_key_empty+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			$('html, body').animate({scrollTop: $('.container-fluid_new').offset().top-150}, 200);
			return false;
		}
				
		$.ajax({
			url:'index.php?route=extension/module/uni_settings/addKey&'+token,
			type:'post',
			data:$(btn).prev().serialize(),
			dataType:'json',
			beforeSend:function() {
				$(btn).button('loading');
			}, 
			complete:function() {
				$(btn).button('reset');
			},
			success:function(json) {
				if(json['success']) {
					$(btn).remove();
					window.location.reload();
				} else {
					$('.container-fluid_new').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+uni_text_error_key+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					$('html, body').animate({scrollTop: $('.container-fluid_new').offset().top-150}, 200);
				}
			}
		});
	}
	
	function addKey2(btn) {
		$('.container-fluid_new > .alert').remove();
	
		$.ajax({
			url: 'index.php?route=extension/module/uni_settings/addKey2&'+token,
			dataType: 'json',
			beforeSend:function() {
				$(btn).button('loading');
			}, 
			complete:function() {
				$(btn).button('reset');
			},
			success: function(json) {
				if(json['success']) {
					$('.container-fluid_new').prepend('<div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> '+uni_text_full_key_added+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>')
				} else {
					$('.container-fluid_new').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+uni_text_error_key2+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
				
				$('html, body').animate({scrollTop: $('.container-fluid_new').offset().top-150}, 200);
			}
		});
	}