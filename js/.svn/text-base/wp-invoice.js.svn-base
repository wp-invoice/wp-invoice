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

			
			return "$" + s.toFixed(2);
		},
		// define the finish callback, this runs after the calculation has been complete
		function ($this){
			// sum the total of the $("[@id^=total_item]") selector
			var sum = $this.sum();
			
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
	jQuery('.invoice_description_box').autogrow();

	return false;
		
}
	
	
	
jQuery(document).ready(function(){

	if(jQuery('#first_name').val() == '') {jQuery('#first_name').addClass("error"); }
	if(jQuery('#last_name').val() == '') {jQuery('#last_name').addClass("error"); }
	if(jQuery('#streetaddress').val() == '') {jQuery('#streetaddress').addClass("error"); }
	if(jQuery('#city').val() == '') {jQuery('#city').addClass("error"); }
	if(jQuery('#zip').val() == '') {jQuery('#zip').addClass("error"); }
	if(jQuery('#zip').val() == '') {jQuery('#zip').addClass("error"); }
	if(jQuery('#wp_invoice_paypal_address').val() == '') {jQuery('#wp_invoice_paypal_address').addClass("error"); }
	
	jQuery('#delete_all_databases').click(function() {

	var txt = 'Are you sure you want to delete all the databases?  All your invoice and log data will be lost forever. ';
			
	jQuery.prompt(txt,{
		buttons:{Delete:true, Cancel:false},
		callback: function(v,m){
				if(v){  document.location = "admin.php?page=new_invoice&tctiaction=complete_removal"; }	
	}
	});

	return false
			
	});



	var tog = false; // or true if they are checked on load
	 jQuery('#CheckAll').click(function() {
	    jQuery("input[type=checkbox]").attr("checked",!tog);
	  tog = !tog;
	 }); 
	 
	jQuery("#phonenumber").mask("999-999-9999");
	jQuery("#zip").mask("99999");
	jQuery("#state").mask("aa");
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