/**
 * Owl carousel
 *
 * @copyright Commercial License By LeoTheme.Com 
 * @email leotheme.com
 * @visit http://www.leotheme.com
 */
$(document).ready( function(){
	/*  */
	var src =  $('#comment-form img.comment-capcha-image').attr('src');
	$("#comment-form").submit( function() {
		var action = $(this).attr('action');
		var data = $('#comment-form').serialize();
		
		if( $("#comment-form").parent().find('.comment-message').length<=0 ){
			var msg = $('<div class="comment-message"></div>');
			$("#comment-form").before( msg );
		}else {
			var msg = $("#comment-form").parent().find(".comment-message");
		}
		// $('.btn-submit-comment').addClass('disable');
		$('.btn-submit-comment').css({'display':'none'});
		$('.btn-submit-comment-wrapper').addClass('bt-active');
		$('.leoblog-cssload-container').css({'display':'block'});
	 	$.ajax( {
			url:action,
			data: data+"&submitcomment="+Math.random(),
			type:'POST',
			dataType: 'json',
			success:function( ct ){ 
				if( !ct.error ){
					$( msg ).html('<div class="alert alert-info">'+ct.message+'</div>');
					$( 'input[type=text], textarea', '#comment-form' ).each( function(){
						$(this).val('');
						var srcn = src.replace('captchaimage','rand='+Math.random()+"&captchaimage");
						$('#comment-form img.comment-capcha-image').attr( 'src', srcn );
					} );
				}else {
					$( msg ).html('<div class="alert alert-warning">'+ct.message+'</div>');
				}
				// $('.btn-submit-comment').removeClass('disable');
				$('.btn-submit-comment').css({'display':'block'});
				$('.btn-submit-comment-wrapper').removeClass('bt-active');
				$('.leoblog-cssload-container').css({'display':'none'});
			}
		} );  
		return false;
	} );
	
	$('.top-pagination-content a.disabled').click(function(){
		return false;
	})
	
	//DONGND:: update link in language block
	var current_lang = prestashop.language.iso_code;
	if (typeof array_list_rewrite != 'undefined')
	{	
		var current_list_rewrite = array_list_rewrite[current_lang];
		var current_blog_rewrite = array_blog_rewrite[current_lang];
		var current_category_rewrite = array_category_rewrite[current_lang];
		var current_config_blog_rewrite = array_config_blog_rewrite[current_lang];
		var current_config_category_rewrite = array_config_category_rewrite[current_lang];
		
		$.each(array_list_rewrite, function(iso_code, list_rewrite){
			if (iso_code != current_lang)
			{
				var url_search = prestashop.urls.base_url + iso_code;			
				// console.log(url_search);
				//DONGND:: update for module blockgrouptop and default
				if ($('#leo_block_top').length)
				{
					var parent_o = $('#leo_block_top .language-selector');
				}
				else
				{
					var parent_o = $('.language-selector-wrapper');
				}
				
				parent_o.find('li a').each(function(){
					
					var lang_href = $(this).attr('href');
					
					if(lang_href.indexOf(url_search) > -1 )
					{
						if ($('body#module-leoblog-list').length)
						{
							var url_change = lang_href.replace('/'+current_list_rewrite+'.html', '/'+list_rewrite+'.html');
						}
						else
						{
							var url_change = lang_href.replace('/'+current_list_rewrite+'/', '/'+list_rewrite+'/');
						}
						
						if ($('body#module-leoblog-blog').length)
						{
							if (config_url_use_id == 0)
							{
								url_change = url_change.replace('/'+current_config_blog_rewrite+'/', '/'+array_config_blog_rewrite[iso_code]+'/');
							}
							url_change = url_change.replace('/'+current_blog_rewrite, '/'+array_blog_rewrite[iso_code]);
						}
						
						if ($('body#module-leoblog-category').length)
						{
							if (config_url_use_id == 0)
							{
								url_change = url_change.replace('/'+current_config_category_rewrite+'/', '/'+array_config_category_rewrite[iso_code]+'/');
							}
							url_change = url_change.replace('/'+current_category_rewrite, '/'+array_category_rewrite[iso_code]);
						}
						
						$(this).attr('href', url_change);					
					}
				});
			}		
		})
		
	}
} );