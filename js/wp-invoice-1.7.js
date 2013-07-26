function wp_invoice_add_time(add_days) {
	
	if(add_days == 'clear') {
	
	jQuery("#mm").val('');
	jQuery("#jj").val('');
	jQuery("#aa").val('');
	}
	 else
	 {

	myDate = new Date();
	var week_from_now = new Date(myDate.getTime() + add_days*24*60*60*1000);;
	month = week_from_now.getMonth() + 1;

	jQuery("#mm").val(month);
	jQuery("#jj").val(week_from_now.getDate());
	jQuery("#aa").val(week_from_now.getFullYear());
		}
		
		
	return false;
		
}
		
		
jQuery(document).ready(function(){

	
	tooltip();
	

	jQuery(".wp_invoice_make_editable").click( function() {
	var element_name = jQuery(this).attr('id');
	var width = jQuery(this).width() * 2;
	var original_content = jQuery(this).html();
	var draw_input_field = "<input style='width: " + width +"px;' value='" + jQuery(this).html() +"' name='" + element_name +"' class='" + element_name +"'>";
	
	if(!jQuery("input." + element_name).length > 0) { jQuery("#" + element_name).html(draw_input_field);  jQuery("input." + element_name).focus(); }
	
	jQuery("input." + element_name).blur(function() {
		if(jQuery("input." + element_name).val() == original_content || jQuery("input." + element_name).val() == '') jQuery("#" + element_name).html(original_content);
	
	}  );
	})
	  
	jQuery("#invoices-filter").submit(function() {  if(jQuery("#invoices-filter select").val() == '-1') { return false;} })
	jQuery("#wp_invoice_tax").keyup(function() { recalc(); }) 
	jQuery("a.wp_invoice_custom_invoice_id").click(function() { jQuery("input.wp_invoice_custom_invoice_id").toggle(); return false;}) 
	
	jQuery("#wp_invoice_show_archived").click(function() { jQuery(".wp_invoice_archived_invoices").toggle(); return false;}) 
	jQuery("#wp_invoice_need_mm").click(function() { jQuery(".wp_invoice_credit_card_processors").toggle();  }) 
	jQuery("#wp_invoice_copy_invoice").click(function() { jQuery(".wp_invoice_copy_invoice").toggle();jQuery("#wp_invoice_create_new_invoice").toggle();jQuery("#wp_invoice_copy_invoice").toggle();  }) 
	jQuery("#wp_invoice_copy_invoice_cancel").click(function() { jQuery(".wp_invoice_copy_invoice").toggle();jQuery("#wp_invoice_create_new_invoice").toggle();jQuery("#wp_invoice_copy_invoice").toggle();  }) 

	jQuery("#wp_invoice_merchantplus_prefill").click(function() { jQuery("#wp_invoice_gateway_url").val('https://gateway.merchantplus.com/cgi-bin/PAWebClient.cgi');  }) 
	jQuery("#wp_invoice_merchantexpress_prefill").click(function() { jQuery("#wp_invoice_gateway_url").val('https://gateway.merchantexpress.com');  }) 
	jQuery("#wp_invoice_merchantwarehouse_prefill").click(function() { jQuery("#wp_invoice_gateway_url").val('https://gateway.merchantwarehouse.com');  }) 
	
	if(jQuery('#wp_invoice_payment_method').val() == 'cc') { jQuery('.gateway_info').show();  }
	if(jQuery('#wp_invoice_payment_method').val() == 'paypal') {  jQuery('.paypal_info').show();  }

	jQuery('#wp_invoice_payment_method').change(function(){
		if(jQuery(this).val() == 'paypal') {  jQuery('.paypal_info').show();  jQuery('.gateway_info').hide();  }
		if(jQuery(this).val() == 'cc') {  jQuery('.gateway_info').show(); jQuery('.paypal_info').hide(); }
	});

	if(jQuery('#first_name').val() == '') {jQuery('#first_name').addClass("error"); }
	if(jQuery('#last_name').val() == '') {jQuery('#last_name').addClass("error"); }
	if(jQuery('#streetaddress').val() == '') {jQuery('#streetaddress').addClass("error"); }
	if(jQuery('#state').val() == '') {jQuery('#state').addClass("error"); }
	if(jQuery('#city').val() == '') {jQuery('#city').addClass("error"); }
	if(jQuery('#zip').val() == '') {jQuery('#zip').addClass("error"); }
	
	jQuery('#delete_all_databases').click(function() {
		var txt = 'Are you sure you want to delete all the databases?  All your invoice and log data will be lost forever. ';
		jQuery.prompt(txt,{	buttons:{Delete:true, Cancel:false}, callback: function(v,m){ if(v){  document.location = "admin.php?page=new_invoice&tctiaction=complete_removal"; }	}
	});
	return false
	});

	var tog = false; // or true if they are checked on load
	 jQuery('#CheckAll').click(function() {
	    jQuery("input[type=checkbox]").attr("checked",!tog);
	  tog = !tog;
	 }); 
	 
	jQuery('.invoice_description_box').autogrow();
	jQuery('.autogrow').autogrow();
	jQuery('#add_itemized_item').bind('click', add_itemized_list_row);
		
	jQuery('.subsubsub a').click(function() {
		jQuery("#FilterTextBox").val(jQuery(this).attr('class'));
		var s = jQuery(this).attr('class').toLowerCase().split(" ");
		jQuery("#invoice_sorter_table tr:hidden").show();
		jQuery.each(s, function(){
		   jQuery("#invoice_sorter_table tr:visible .indexColumn:not(:contains('"
			  + this + "'))").parent().hide();
		});
		return false; 
	});

	jQuery("#invoice_sorter_table tr:has(td)").each(function(){
	   var t = jQuery(this).text().toLowerCase(); //all row text
	   jQuery("<td class='indexColumn'></td>")
		.hide().text(t).appendTo(this);
	});//each tr
 
	jQuery("#FilterTextBox").keyup(function(){
	   var s = jQuery(this).val().toLowerCase().split(" ");
	   //show all rows.

	   jQuery("#invoice_sorter_table tr:hidden").show();
	   jQuery.each(s, function(){
		   jQuery("#invoice_sorter_table tr:visible .indexColumn:not(:contains('"
			  + this + "'))").parent().hide();
	   });//each
	 });//key up.
 

	jQuery('#new_invoice_form').submit(function() {
	      if(jQuery("#invoice_subject").val() == '') { jQuery("#invoice_subject").addClass("error"); jQuery("#invoice_subject").blur(); return false; } 
	});
		
	jQuery("#invoice_sorter_table").tablesorter({headers:{0:{sorter:false},6:{sorter:false}}}); 
	jQuery("#invoice_list").delegate("keyup", "input", function(event) {recalc();});
	recalc();
}); 	


function recalc(){
	jQuery("[@id^=total_item]").calc(
		// the equation to use for the calculation
		"qty * price",
		// define the variables used in the equation, these can be a jQuery object
		{
			qty: 	jQuery("[@id^=qty_item_]"),
			price: 	jQuery("[@id^=price_item_]") 
		},
		// define the formatting callback, the results of the calculation are passed to this function
		function (s){
			// return the number as a dollar amount

			
			return s.toFixed(2);
		},
		// define the finish callback, this runs after the calculation has been complete
		function ($this){
			// sum the total of the $("[@id^=total_item]") selector
			var tax = jQuery('#wp_invoice_tax').val() / 100;
			var sum = $this.sum() + ($this.sum() * tax);
			
			jQuery("#amount").html(sum.toFixed(2));
			jQuery("#total_amount").val(sum.toFixed(2));
		}
	);

	
}
	
function add_itemized_list_row() {
	var lastRow = jQuery('#invoice_list tr:last').clone();
	var id = parseInt(jQuery('.id', lastRow).html()) + 1;;

	jQuery('.id', lastRow).html(id);
	jQuery('.item_name', lastRow).attr('name', 'itemized_list[' + id + '][name]');
	jQuery('.item_description', lastRow).attr('name', 'itemized_list[' + id + '][description]');
	jQuery('.item_quantity', lastRow).attr('name', 'itemized_list[' + id + '][quantity]');
	jQuery('.item_price', lastRow).attr('name', 'itemized_list[' + id + '][price]');
	jQuery('.item_total', lastRow).attr('id', 'total_item_' + id + '');

	jQuery('.item_name', lastRow).val('');
	jQuery('.item_description', lastRow).val('');
	jQuery('.item_quantity', lastRow).val('');
	jQuery('.item_price', lastRow).val('');
	jQuery('.item_total', lastRow).html('');

	jQuery('#invoice_list').append(lastRow);

	recalc();
	

	return false;
		
}
	
	
this.tooltip = function(){	

	/* CONFIG */		
		xOffset = 10;
		yOffset = 20;		
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result		
	/* END CONFIG */		
	jQuery(".wp_invoice_tooltip").hover(function(e) {									  
		this.t = this.title;
		this.title = "";									  
		jQuery("body").append("<p id='wp_invoice_tooltip'>"+ this.t +"</p>");
		jQuery("#wp_invoice_tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");		
    },
	function(){
		this.title = this.t;		
		jQuery("#wp_invoice_tooltip").remove();
    });	
	jQuery("a.wp_invoice_tooltip").mousemove(function(e){
		jQuery("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px");
	});			
};

	
