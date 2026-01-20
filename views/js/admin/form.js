/**
 * Owl carousel
 *
 * @copyright Commercial License By LeoTheme.Com 
 * @email leotheme.com
 * @visit http://www.leotheme.com
 */

(function($) {
	$.fn.PavMegaMenuList = function(opts) {
		// default configuration
		var config = $.extend({}, {
			action:null, 
			addnew : null,
			confirm_del:'Are you sure delete this?'
		}, opts);

		function checkInputHanlder(){
			var _updateMenuType = function(){
				$(".menu-type-group").parent().parent().hide();
				$("[for^=content_text_]").parent().hide();

				if( $("#menu_type").val() =='html' ){
					$("[for^=content_text_]").parent().show();
				}else {
					$("#"+$("#menu_type").val()+"_type").parent().parent().show();
				}
			};
			_updateMenuType(); 
			$("#menu_type").change(  _updateMenuType );

			var _updateSubmenuType = function(){
				if( $("#type_submenu").val() =='html' ){
					$("[for^=submenu_content_text_]").parent().show();
				}else{
					$("[for^=submenu_content_text_]").parent().hide();
				}
			};
			_updateSubmenuType();
			$("#type_submenu").change(  _updateSubmenuType );

		}

		function manageTreeMenu(){
			if($('ol').hasClass("sortable")){
				$('ol.sortable').nestedSortable({
						forcePlaceholderSize: true,
						handle: 'div',
						helper:	'clone',
						items: 'li',
						opacity: .6,
						placeholder: 'placeholder',
						revert: 250,
						tabSize: 25,
						tolerance: 'pointer',
						toleranceElement: '> div',
						maxLevels: 4,

						isTree: true,
						expandOnHover: 700,
						startCollapsed: true,
						
						stop: function(){ 							
							var serialized = $(this).nestedSortable('serialize');
						
							$.ajax({
								type: 'POST',
								url: config.action.replace(/amp;/g, '')+"&doupdatepos=1&rand="+Math.random(),
								data : serialized+'&updatePosition=1' 
							}).done( function (msg) {
								 showSuccessMessage(msg);
							} );
						}
					});
				
				// $('#serialize').click(function(){
					// var serialized = $('ol.sortable').nestedSortable('serialize');
				 	// var text = $(this).val();
				 	// var $this  = $(this);
				 	// $(this).val( $(this).data('loading-text') );
					// $.ajax({
						// type: 'POST',
						// url: config.action+"&doupdatepos=1&rand="+Math.random(),
						// data : serialized+'&updatePosition=1' 
					// }).done( function () {
						 // $this.val( text );
					// } );
				// });
				
				$('#addcategory').click(function(){
					location.href=config.addnew.replace(/amp;/g, '');
				});
			}	
		}
	 	/**
	 	 * initialize every element
	 	 */
		this.each(function() {  
	 		$(".quickedit",this).click( function(){  
	 			location.href=config.action.replace(/amp;/g, '')+"&id_leoblogcat="+$(this).attr('rel').replace("id_","");
	 		} );

	 		$(".quickdel",this).click( function(){  
	 			if( confirm(config.confirm_del) ){
	 				location.href=config.action.replace(/amp;/g, '')+"&dodel=1&id_leoblogcat="+$(this).attr('rel').replace("id_","");
	 			}
	 			
	 		} );
                        
                        $(".delete_many_menus",this).click( function(){
                            if (confirm('Delete selected items?'))
                            {
                                var list_menu = '';
                                $('.quickselect:checkbox:checked').each(function () {
                                    list_menu += $(this).val() + ",";

                                });

                                if(list_menu != ''){
                                    location.href=config.action.replace(/amp;/g, '')+"&delete_many_menu=1&list="+list_menu;
                                }
                            }
                        });

	 		manageTreeMenu();
	 	 




		});

		return this;
	};
	
})(jQuery);


jQuery(document).ready(function(){
 	$(".leo-modal").fancybox({
	 	'type':'iframe',
	 	'width':980,
	 	'height':500,
	 	afterLoad:function(   ){
	 		if( $('body',$('.fancybox-iframe').contents()).find("#main").length  ){  
		 		$('body',$('.fancybox-iframe').contents()).find("#header").hide();
		 		$('body',$('.fancybox-iframe').contents()).find("#footer").hide();
	 		}else { 
	 			 
	 		}
	 	}
	});
 	
 	$("#widgetds a.btn").fancybox( {'type':'iframe'} );

 	$(".leo-modal-action, #widgets a.btn").fancybox({
	 	'type':'iframe',
	 	'width':950,
	 	'height':500,
	 	afterLoad:function(   ){
	 		if( $('body',$('.fancybox-iframe').contents()).find("#main").length  ){  
		 		$('body',$('.fancybox-iframe').contents()).find("#header").hide();
		 		$('body',$('.fancybox-iframe').contents()).find("#footer").hide();
	 		}else { 
	 			 
	 		}
	 	},
 		afterClose: function (event, ui) {  
		//	location.reload();
		},	
	});
	
	//DONGND:: delete image uploaded
	if ($('#image_link-images-thumbnails').length > 0)
	{
				
		leoblog_del_img($('#image_link-images-thumbnails'), 'image');
	}
	
	if ($('#thumb_link-images-thumbnails').length > 0)
	{
				
		leoblog_del_img($('#thumb_link-images-thumbnails'), 'thumb');
	}
	
	$('.leoblog-del-img-bt').click(function(){
		if( confirm(leoblog_del_img_mess) ){
			var id_parent = $(this).data('id');
			$('#'+id_parent).parent().fadeOut(function(){
				$(this).remove();
			});
			var id_element = $(this).data('element');
			$('#'+id_element).val('');
		}
		return false;
	})

	//load data template
	$('#template').change(function(){
		$.ajax({
	    	type: 'GET',
	    	url: url_ajax_blog,
	    	headers: { "cache-control": "no-cache" },
	    	async: true,
	    	cache: false,
	    	dataType: "json",
	    	data: {
				template: $(this).val(),
				action: 'loadDataTemplate'
	    	},
	    	success: function (data_)
	    	{
	    		var data = JSON.parse(data_.message);

	    		console.log(data);
	    		console.log(data.listing_show_categoryinfo);
	    		$("[name = indexation]").removeAttr('checked');
	    		$("[name = indexation][value="+data.indexation+"]").attr('checked','checked').click();
	    		$('#rss_limit_item').val(data.rss_limit_item);
	    		$('#rss_title_item').val(data.rss_title_item);

	    		$("[name = listing_show_categoryinfo]").removeAttr('checked');
	    		$("[name = listing_show_categoryinfo][value="+data.listing_show_categoryinfo+"]").attr('checked','checked').click();
	    		$("[name = listing_show_subcategories]").removeAttr('checked');
	    		$("[name = listing_show_subcategories][value="+data.listing_show_subcategories+"]").attr('checked','checked').click();

	    		$('#listing_leading_column').val(data.listing_leading_column);
	    		$('#listing_leading_limit_items').val(data.listing_leading_limit_items);
	    		$('#listing_leading_img_width').val(data.listing_leading_img_width);
	    		$('#listing_leading_img_height').val(data.listing_leading_img_height);
	    		$('#listing_secondary_column').val(data.listing_secondary_column);
	    		$('#listing_secondary_limit_items').val(data.listing_secondary_limit_items);
	    		$('#listing_secondary_img_width').val(data.listing_secondary_img_width);
	    		$('#listing_secondary_img_height').val(data.listing_secondary_img_height);

	    		$("[name = listing_show_title]").removeAttr('checked');
	    		$("[name = listing_show_title][value="+data.listing_show_title+"]").attr('checked','checked').click();
	    		$("[name = listing_show_description]").removeAttr('checked');
	    		$("[name = listing_show_description][value="+data.listing_show_description+"]").attr('checked','checked').click();
	    		$("[name = listing_show_readmore]").removeAttr('checked');
	    		$("[name = listing_show_readmore][value="+data.listing_show_readmore+"]").attr('checked','checked').click();
	    		$("[name = listing_show_image]").removeAttr('checked');
	    		$("[name = listing_show_image][value="+data.listing_show_image+"]").attr('checked','checked').click();
	    		$("[name = listing_show_author]").removeAttr('checked');
	    		$("[name = listing_show_author][value="+data.listing_show_author+"]").attr('checked','checked').click();
	    		$("[name = listing_show_category]").removeAttr('checked');
	    		$("[name = listing_show_category][value="+data.listing_show_category+"]").attr('checked','checked').click();
	    		$("[name = listing_show_created]").removeAttr('checked');
	    		$("[name = listing_show_created][value="+data.listing_show_created+"]").attr('checked','checked').click();
	    		$("[name = listing_show_hit]").removeAttr('checked');
	    		$("[name = listing_show_hit][value="+data.listing_show_hit+"]").attr('checked','checked').click();
	    		$("[name = listing_show_counter]").removeAttr('checked');
	    		$("[name = listing_show_counter][value="+data.listing_show_counter+"]").attr('checked','checked').click();
	    		$('#item_img_width').val(data.item_img_width);
	    		$('#item_img_height').val(data.item_img_height);

	    		$("[name = item_show_description]").removeAttr('checked');
	    		$("[name = item_show_description][value="+data.item_show_description+"]").attr('checked','checked').click();
	    		$("[name = item_show_image]").removeAttr('checked');
	    		$("[name = item_show_image][value="+data.item_show_image+"]").attr('checked','checked').click();
	    		$("[name = item_show_author]").removeAttr('checked');
	    		$("[name = item_show_author][value="+data.item_show_author+"]").attr('checked','checked').click();
	    		$("[name = item_show_category]").removeAttr('checked');
	    		$("[name = item_show_category][value="+data.item_show_category+"]").attr('checked','checked').click();
	    		$("[name = item_show_created]").removeAttr('checked');
	    		$("[name = item_show_created][value="+data.item_show_created+"]").attr('checked','checked').click();
	    		$("[name = item_show_hit]").removeAttr('checked');
	    		$("[name = item_show_hit][value="+data.item_show_hit+"]").attr('checked','checked').click();
	    		$("[name = item_show_counter]").removeAttr('checked');
	    		$("[name = item_show_counter][value="+data.item_show_counter+"]").attr('checked','checked').click();

	    		$('#social_code').val(data.social_code);
	    		$("[name = item_show_listcomment]").removeAttr('checked');
	    		$("[name = item_show_listcomment][value="+data.item_show_listcomment+"]").attr('checked','checked').click();
	    		$("[name = item_show_formcomment]").removeAttr('checked');
	    		$("[name = item_show_formcomment][value="+data.item_show_formcomment+"]").attr('checked','checked').click();
	    		$("#item_comment_engine option").removeAttr('selected');
	    		$("#item_comment_engine option[value="+data.item_comment_engine+"]").attr('selected','selected');
	    		$('#item_limit_comments').val(data.item_limit_comments);
	    		$('#item_diquis_account').val(data.item_diquis_account);
	    		$('#item_facebook_appid').val(data.item_facebook_appid);
	    		$('#item_facebook_width').val(data.item_facebook_width);
	    		$("#url_use_id option").removeAttr('selected');
	    		$("#url_use_id option[value="+data.url_use_id+"]").attr('selected','selected');
	    		data.url_use_id == 0 ? $('.url_use_id_sub').show(500) : $('.url_use_id_sub').hide(300)
	    		
	    		$("[name = show_popular_blog]").removeAttr('checked');
	    		$("[name = show_popular_blog][value="+data.show_popular_blog+"]").attr('checked','checked').click();
	    		$('#limit_popular_blog').val(data.limit_popular_blog);
	    		$("[name = show_recent_blog]").removeAttr('checked');
	    		$("[name = show_recent_blog][value="+data.show_recent_blog+"]").attr('checked','checked').click();
	    		$('#limit_recent_blog').val(data.limit_recent_blog);
	    		$("[name = show_all_tags]").removeAttr('checked');
	    		$("[name = show_all_tags][value="+data.show_all_tags+"]").attr('checked','checked').click();
	    		for (var id_lang of data_.id_lang) {
		    		$('#meta_title_'+id_lang).val(data.meta_title_1);
		    		$('#meta_description_').val(data.meta_description_1);
		    		$('#meta_keywords_'+id_lang).val(data.meta_keywords_1);
	    		}
	    	},
	   });
	});

});

//DONGND;; function delete image uploaded

function leoblog_del_img(img_id_element, img_name_e)
{
	img_id_element.append('<a class="btn btn-default leoblog-del-img-bt" href="#" data-element="'+img_name_e+'" data-id="'+img_id_element.attr('id')+'"><i class="icon-trash"></i>'+leoblog_del_img_txt+'</a>');
}

 
jQuery(document).ready(function(){
	// var i = 0;
	// $("#bloggeneralsetting > form > .panel .form-group").hide();
	// $("#bloggeneralsetting > form > .panel .panel-footer").hide();

	// $("#bloggeneralsetting > form > .panel").each( function() {
		// var panel = $(this);
		// $( "h3, .panel-heading" , this ).click( function(){
		 	 // $("#bloggeneralsetting > form > .panel .form-group").hide();
	 

		 	 // $(".form-group",panel).show();
		 	 // $(".panel-footer",panel).show();
		// } );
		// if(i++==0){
			 // $(".form-group",this).show();
		 	 // $(".panel-footer",this).show();
		// } 

	// } );
		
	var id_panel = $("#bloggeneralsetting .leoblog-globalconfig li.active a").attr("href");
	$(id_panel).addClass('active').show();
	$('.leoblog-globalconfig li').click(function(){
		if(!$(this).hasClass('active'))
		{
			var default_tab = $(this).find('a').attr("href");			
			$('#LEOBLOG_DASHBOARD_DEFAULTTAB').val(default_tab);
		}
	})
});

/*
 * SHOW HIDE - URL include ID
 */
 $(document).ready(function(){
    $('.form-action').change(function(){
        var elementName = $(this).attr('name');
        $('.'+elementName+'_sub').hide(300);
        $('.'+elementName+'-'+$(this).val()).show(500);
    });
    $('.form-action').trigger("change");

 });