// JavaScript Document


jQuery(function() {		
	if (window.PIE) {			
		window.onload=happycode ;
		
	}
});
function happycode(){
		jQuery('.typo-button').each(function() {
	//jQuery(this).css('background','#456');
			//console.log(jQuery(this).attr('class'));
			PIE.attach(this);				
			//alert('d');
			//alert(jQuery(this).attr('class'));
			
		});
		
		
		jQuery('.bj-body-body').each(function() {
			PIE.attach(this);
		});
		jQuery('.bj-body-right .moduletable').each(function() {
			PIE.attach(this);
		});
		jQuery('.bj-body-left .moduletable').each(function() {
			PIE.attach(this);
		});
		jQuery('.button').each(function() {
			PIE.attach(this);
		});	
        /*jQuery('.bj-typo-message-rounded-grey').each(function() {
			PIE.attach(this);
		});
        jQuery('.bj-typo-message-rounded-white').each(function() {
			PIE.attach(this);
		});
        jQuery('.bj-typo-message-rounded-yellow').each(function() {
			PIE.attach(this);
		});	*/
	}


jQuery(function(){
	var number = jQuery('.choice-table').attr('id');	
	var text = jQuery('.choice-table').html();	
	jQuery('.choice-table').html('<div class="bj-typo-table-head"><span class="left"></span><span class="right"></span></div><div class="bj-typo-table-text">'+ text +'</div>');
	jQuery('.choice-table .bj-typo-table-head').css('width',jQuery('.bj-typo-table-red .choice-table').width() + 14 + 20);
	jQuery('.bj-typo-table tr').each(function(){
		jQuery(this).find('td').eq(number-1).addClass('old');
	});
});
