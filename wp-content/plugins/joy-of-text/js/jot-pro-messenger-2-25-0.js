(function ($) {
    
           
        // Initiate tooltips on history tab
        $(document).ready(function(){
	    $( "[class^=jot][title]" ).tooltip({
		tooltipClass: "jot-tooltip"
	    });
	});
	
        // Open tabs
	$(document).ready(function(){
            
	    //$('div[id^=tab]').hide();
	    //$('#tabgroupdetails').show();
	    $('.jot-subtab').click(function (event) {
		event.preventDefault();
		var tab_id = $(this).attr('href');
		$('.jot-subtab').removeClass('nav-tab-active')
		$(this).addClass('nav-tab-active');
		$('div[id^=jottab]').hide();
		$(tab_id).show();
		
		
		if ($('#jot-plugin-messages\\[jot-message-sendmethod\\]').length > 0) {
			var clicked_tab = $(this).attr('href');			
			$('#jot-plugin-messages\\[jot-message-sendmethod\\]').val(clicked_tab.substring(1));			
		}
		
	    });
	});
	
	
	// Open appropriate send method tab on page LOAD
	$(document).ready(function(){
            if ($('#jot-plugin-messages\\[jot-message-sendmethod\\]').length > 0) {
			var last_saved_tab = "#" + $('#jot-plugin-messages\\[jot-message-sendmethod\\]').val();
			$('.jot-subtab').removeClass('nav-tab-active')
			$('a[href="' + last_saved_tab +'"]').addClass('nav-tab-active');
			$('div[id^=jottab]').hide();			                     
			$(last_saved_tab).show();
	    }
	})
	
	
	
	// Show memberlist for group
	$(document).ready(function(){
	    $( "#jot-group-list-tab" ).on( "click", "a[id^=jot-grp-mem-add]", function( event ) {		
	    	event.preventDefault();		
		var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=group-list&lastid=' + $(this).closest('tr').attr('id') + '&paged=' + $('#jot_grppage').val() + "&subtab=jottabgroupmembers";
		$(location).attr('href',joturl);
		
	    });
	});
	
	
	
	// Open message tab with selected group
	$(document).ready(function(){
	    $( "#jot-group-list-tab" ).on( "click", "a[id^=jot-grp-mem-send]", function( event ) {		
	    	event.preventDefault();		
		var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=messages&grpid=' + $(this).closest('tr').attr('id');
		$(location).attr('href',joturl);
		
	    });
	});
	
		
	// Delete group
	$(document).ready(function(){
	    $( "#jot-group-list-tab" ).on( "click", "a[id^=jot-grp-delete]", function( event ) {
	    	event.preventDefault();
		
		var $tr = $(this).closest('tr');
		
		// jot-grp-delete-<groupid>
		var valarr = $(this).attr('id').split('-');
		var formdata =  { 'jot_grpid' : valarr[3] };
		var data = {
		    'action': 'process_deletegroup',
		    'formdata':  formdata
		};
		
		if (confirm(jot_strings.groupdelete)) {
		    jQuery.post(ajax_object.ajax_url, data, function(response) {				    
			var resp = JSON.parse(response);
			  
			if (resp.errorcode != '0'){			
			    //$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );			   		    
			} else {
			    //$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
			    var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=group-list&paged=' + $('#jot_grppage').val();
			    $(location).attr('href',joturl);	
			    //$tr.find('td').fadeOut('slow',function(){ 
			    //   $tr.remove();                    
			    //}); 
			}
		    });	
		}
	    });
	});
	
	// Add a new member on admin screen
	$(document).ready(function(){
	   
	    $('[id^=jot-mem-new]').click(function(event) {
		event.preventDefault();
		jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\"><img src='" + jot_images.spinner +  "'></div>");		
				
		var valarr = $(this).attr('id').split('-');
		
		var grpid = valarr[3];
		var formdata =  {   'jot_grpid' : valarr[3],
		                    'jot_grpmemname'    : $('#jot-mem-add-name').val(),
				    'jot_grpmemnum'     : $('#jot-mem-add-num').val(),
				    'jot_grpmememail'   : $('#jot-mem-add-email').val(),
				    'jot_grpmemaddress' : $('#jot-mem-add-addr').val(),
			            'jot_grpmemcity'    : $('#jot-mem-add-city' ).val(),
			            'jot_grpmemstate'   : $('#jot-mem-add-state').val(),
			            'jot_grpmemzip'     : $('#jot-mem-add-zip').val()
		};
		
		var data = {
		        'action': 'process_addmem',
		        'formdata':  formdata
		};
		
		$.post(ajax_object.ajax_url, data, function(response) {				    
		    var resp = JSON.parse(response);
			  
		    if (resp.errorcode != '0'){			
			$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
			 $("#" + resp.errorfield).focus();		    
		    } else {
			// Clear old input values
			$('#jot-mem-add-name').val("");
			$('#jot-mem-add-num').val("");
			$('#jot-mem-add-email').val("");
			$('#jot-mem-add-addr').val("");
			$('#jot-mem-add-city' ).val("");
			$('#jot-mem-add-state').val("");
			$('#jot-mem-add-zip').val("");
			
			//  Set field size
			var size = 18;
			
			$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
			var row = "";
			var addid = "jot-added-" + formdata['jot_grpid'];
			row += "<tr class='jot-member-list' id='" + addid +  "'>";
			row += "<td style='width:50px;' class='jot-td-c'>";
			row += "<input id='jot-mem-select-" + resp.lastid + "' name='jot-mem-select-" + resp.lastid +  "' type='checkbox' value='true' />";
			row += "</td>";	
			row += "<td class='jot-td-l'>";
			rownameid = "jot-mem-upd-name-" + formdata['jot_grpid'] + "-" + resp.lastid;
			row += "<input id='" + rownameid + "' name='" + rownameid + "' maxlength='40' size='" + size + "' type='text' value='" + formdata['jot_grpmemname'] + "'/>"
			row += "</td>";
			row += "<td class='jot-td-r'>";
			rownumid = "jot-mem-upd-num-" + formdata['jot_grpid'] + "-" + resp.lastid;
			row += "<input id='" + rownumid + "' name='" + rownumid + "' maxlength='40' size='" + size + "' type='text' value='" + resp.verified_number + "'/>"
			row += "</td>";
			
			// Is show extended member info checked?
			if ($("#jot-plugin-group-list\\[jot-mem-extfields\\]").is(':checked')) {
				style =  "";
			} else {
				style =  " style='display:none'";
			}
			row += "<td class='jot-td-r' " + style + ">";
			rownumid = "jot-mem-upd-email-" + formdata['jot_grpid'] + "-" + resp.lastid;
			row += "<input id='" + rownumid + "' name='" + rownumid + "' maxlength='90' size='" + size + "' type='text' value='" + formdata['jot_grpmememail'].replace(/\s/g, '') + "'/>"
			row += "</td>";
			row += "<td class='jot-td-r' " + style + ">";
			rownumid = "jot-mem-upd-addr-" + formdata['jot_grpid'] + "-" + resp.lastid;
			row += "<input id='" + rownumid + "' name='" + rownumid + "' maxlength='240' size='" + size + "' type='text' value='" + formdata['jot_grpmemaddress'] + "'/>"
			row += "</td>";
			row += "<td class='jot-td-r' " + style + ">";
			rownumid = "jot-mem-upd-city-" + formdata['jot_grpid'] + "-" + resp.lastid;
			row += "<input id='" + rownumid + "' name='" + rownumid + "' maxlength='40' size='" + size + "' type='text' value='" + formdata['jot_grpmemcity'] + "'/>"
			row += "</td>";
			row += "<td class='jot-td-r' " + style + ">";
			rownumid = "jot-mem-upd-state-" + formdata['jot_grpid'] + "-" + resp.lastid;
			row += "<input id='" + rownumid + "' name='" + rownumid + "' maxlength='40' size='" + size + "' type='text' value='" + formdata['jot_grpmemstate'] + "'/>"
			row += "</td>";
			row += "<td class='jot-td-r' " + style + ">";
			rownumid = "jot-mem-upd-zip-" + formdata['jot_grpid'] + "-" + resp.lastid;
			row += "<input id='" + rownumid + "' name='" + rownumid + "' maxlength='20' size='" + size + "' type='text' value='" + formdata['jot_grpmemzip'] + "'/>"
			row += "</td>";
			
			row += "<td class='jot-td-l'><div class='divider'></div><a href='#' id='jot-mem-save-" + formdata['jot_grpid'] + '-' + resp.lastid + "'><img src='" + jot_images.saveimg +  "' title='Save'></a><div class='divider'></div><a href='#' id='jot-mem-delete-" + formdata['jot_grpid']  +  '-' + resp.lastid + "'><img src='" + jot_images.delimg + "' title='Delete'></a><div class='divider'></div><a href='#' id='jot-mem-deleteall-" + formdata['jot_grpid']  +  '-' + resp.lastid + "'><img src='" + jot_images.trashimg + "' title='Delete member from ALL groups'></a>" + "</td>";          
			row += "</tr>\n";
			
			$('.jot-member-add').closest('tr').after(row);
			$("#" + addid).hide().fadeIn('slow');
			
			// Add one to the member count
			var memcount = $('#' + grpid).find('td').eq(4).text();
			var updmemcount = parseInt(memcount) + 1;
			$('#' + grpid).find('td').eq(4).hide().html(updmemcount).fadeIn('slow') ;
		    }
		});
		    
		
	    });
	});
	
	
	// RefreshShow memberlist for group
	$(document).ready(function(){
		    $('[id^=jot-mem-refresh]').click(function(event) {	
			event.preventDefault();
			jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\">" + jot_strings.refreshing +" <img src='" + jot_images.spinner +  "'></div>");
			var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=group-list&lastid=' + $(this).closest('tr').attr('id') + '&paged=' + $('#jot_grppage').val() + "&subtab=jottabgroupmembers";			
			$(location).attr('href',joturl);
			
		    });
	});
	
	
	// Save existing member's details on admin screen
	$(document).ready(function(){
	    $( "#jot-groupmem-tab" ).on( "click", "a[id^=jot-mem-save]", function( event ) {
	    	event.preventDefault();
		jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\"><img src='" + jot_images.spinner +  "'></div>");
				
		    // jot-mem-upd-<type>-<groupid>-<groupmemid>
		       
		    var valarr = $(this).attr('id').split('-');
		    
		    var formdata =  {   'jot_grpid' : valarr[3],
					'jot_grpmemid' : valarr[4],
					'jot_grpmemname' : $('#jot-mem-upd-name-' + valarr[3] + '-' + valarr[4]).val(),
					'jot_grpmemnum'  : $('#jot-mem-upd-num-'  + valarr[3] + '-' + valarr[4]).val(),
					'jot_grpmememail'   : $('#jot-mem-upd-email-'  + valarr[3] + '-' + valarr[4]).val(),
					'jot_grpmemaddress' : $('#jot-mem-upd-addr-'  + valarr[3] + '-' + valarr[4]).val(),
					'jot_grpmemcity'    : $('#jot-mem-upd-city-'  + valarr[3] + '-' + valarr[4]).val(),
					'jot_grpmemstate'   : $('#jot-mem-upd-state-'  + valarr[3] + '-' + valarr[4]).val(),
					'jot_grpmemzip'     : $('#jot-mem-upd-zip-'  + valarr[3] + '-' + valarr[4]).val(),
					'jot_namefield_id' : $('#jot-mem-upd-name-' + valarr[3] + '-' + valarr[4]).attr('id'),
					'jot_numfield_id'  : $('#jot-mem-upd-num-' + valarr[3] + '-' + valarr[4]).attr('id')
		    };
		    
		    var data = {
		        'action': 'process_savemem',
		        'formdata':  formdata
		    };
		   
		    jQuery.post(ajax_object.ajax_url, data, function(response) {				    
			var resp = JSON.parse(response);
			  
			if (resp.errorcode != '0'){			
			    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
			    $("#" + resp.errorfield).focus();			    
			} else {
			    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
			}
		    });
		    
		
	    });
	});
	
	// Remove a member from group (admin screen)
	$(document).ready(function(){
	    $( "#jot-groupmem-tab" ).on( "click", "a[id^=jot-mem-delete-]", function( event ) {
	    
		event.preventDefault();
		jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\"></div>");
			
		    var $tr = $(this).closest('tr');
	
		    // jot-mem-delete-<groupid>-<groupmemid>
		    
		    var valarr = $(this).attr('id').split('-');
		    var grpid = valarr[3];
		    var formdata =  {   'jot_grpid' : valarr[3],
					'jot_grpmemid' : valarr[4]					
		    };
		    
		   
		    var data = {
		        'action': 'process_deletemem',
		        'formdata':  formdata
		    };
		    if (confirm(jot_strings.confirmmemrem)) {
			jQuery.post(ajax_object.ajax_url, data, function(response) {				    
			    var resp = JSON.parse(response);
			      
			    if (resp.errorcode != '0'){			
				$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg  + " </div>" );			   		    
			    } else {
				$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg  + " </div>" );
				$tr.find('td').fadeOut('slow',function(){ 
				   $tr.remove();                    
			        });
				
				// Subtract one from the member count
				var memcount = $('#' + grpid).find('td').eq(4).text();
			        var updmemcount = parseInt(memcount) - 1;
			        $('#' + grpid).find('td').eq(4).hide().html(updmemcount).fadeIn('slow') ;
				
			    }
			});	
		    }
	    });
	});
	
	// Remove a member from ALL groups (admin screen)
	$(document).ready(function(){
	    $( "#jot-groupmem-tab" ).on( "click", "a[id^=jot-mem-deleteall]", function( event ) {
	    
		event.preventDefault();
		jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\"></div>");			
		    	
		    // jot-mem-deleteall-<groupid>-<groupmemid>
		    
		    var valarr = $(this).attr('id').split('-');
		    var grpid = valarr[3];
		    var formdata =  {   'jot_grpid' : valarr[3],
					'jot_grpmemid' : valarr[4]					
		    };
		    
		   
		    var data = {
		        'action': 'process_deleteallmem',
		        'formdata':  formdata
		    };
		    if (confirm(jot_strings.confirmmemalldel)) {
			jQuery.post(ajax_object.ajax_url, data, function(response) {				    
			    var resp = JSON.parse(response);
			      
			    if (resp.errorcode != '0'){			
				$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg  + " </div>" );			   		    
			    } else {
				$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg  + " </div>" );
				
				// Refresh screen
				jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\">" + jot_strings.refreshing +" <img src='" + jot_images.spinner +  "'></div>");
				var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=group-list&lastid=' + grpid + '&paged=' + $('#jot_grppage').val() + "&subtab=jottabgroupmembers";			
				$(location).attr('href',joturl);	
			    }
			});	
		    }
	    });
	});
	
	// Process bulk adds
	$(document).ready(function(){
		$("#jot-membulkadd").click(function(event){		
			event.preventDefault();
		
			var jot_grpid = $("#jot_grpid").val();
		
			var tabhtml = "<table id=\"jot-bulkaddstatustab\">";
			tabhtml += "<tr><th class=\"jot-td-c\">" + jot_strings.status + "</th></tr>";
			tabhtml += "</table>";
			$('#jot-bulkaddstatus-div').html(tabhtml);
			
			allmembers = cleanArray($("#jot-plugin-group-list\\[jot_bulkadd\\]").val().split('\n'));			
			
			memmax = allmembers.length;
			memcount = 0;
			membatchsize = 20;
			
			$("#jot-bulk-status").html("<div class=\"jot-messageblack\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>Processing.....</h4></div>");
			membatch = allmembers.slice(0,membatchsize);			
			process_bulkbatch(jot_grpid,membatch);				       
					
		});
	     
	     function process_bulkbatch(jot_grpid, membatch) {
		
		// Process bulk additions		 
		var formdata =  {   'jot-grpid' : jot_grpid,
				     'jot-bulkaddlist' : JSON.stringify(membatch)
		    };
		var data = {
		    'action': 'process_bulkadds',
		    'formdata':  formdata
		};
		
		$.post(ajax_object.ajax_url, data, function(response) {				    
			
			
			if (response) {
				// All ok
				var resp = JSON.parse(response);
			        allerrors = resp.bulkerrors;
				//console.log(resp);
			} else {
				console.log("An ajax error occurred - restarting");
				process_bulkbatch(jot_grpid, membatch);
			}
			$.each(allerrors, function(index, row) {
				if (row.errorcode == 0){
					tabhtml = "<tr><td class=\"jot-messagegreen\">" + row.errormsg + "</td></tr>";						
				} else {
					tabhtml = "<tr><td class=\"jot-messagered\">" + row.errormsg + " (Error : " + row.errorcode + ")</td></tr>";
				}
				$('#jot-bulkaddstatustab tr:last').after(tabhtml);
				memcount++;
			});
			
			if (memcount < memmax) {
				//Process next batch of new members
				$("#jot-bulk-status").html("<div class=\"jot-messageblack\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>Processing....." + memcount + " of " + memmax + "</h4></div>");
				membatch = allmembers.slice(memcount,memcount + membatchsize);
				// Half second pause before next batch
				//setTimeout(function() {
		                   process_bulkbatch(jot_grpid, membatch);
		                //}, 500);
			} else {
				$("#jot-bulk-status").html("<div class=\"jot-messageblack\"><h4>Processing complete.</h4></div>");				
			}
			
				    
		});
	     }
	     
	     
	    function cleanArray(actual) {
		
		    var newArray = new Array();
		    for(var i = 0; i<actual.length; i++)
		    {
			if (actual[i])
			{
			    newArray.push(actual[i]);
			}
		    }
		    return newArray;
	     }
	     
	});
	
	function get_current_time() {
		var currentdate = new Date(); 
		var datetime =  currentdate.getDate() + "/"
                + (currentdate.getMonth()+1)  + "/" 
                + currentdate.getFullYear() + " @ "  
                + currentdate.getHours() + ":"  
                + currentdate.getMinutes() + ":" 
                + currentdate.getSeconds();
		return datetime;
		
	}
	
	// Make Bulk Add group name field read only
        //$(document).ready(function(){
	//        if ($("#jot-plugin-group-list\\[jot_bulkaddgrp\\]").length > 0 ) {
	//		$("#jot-plugin-group-list\\[jot_bulkaddgrp\\]").attr('readonly', 'readonly');			
	//        }
	//})
	
	// Cancel out of Bulk member add panel
	$(document).ready(function(){
	    $("#jot-membulkaddcancel").click(function(event){
		event.preventDefault();		
		var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=group-list&subtab=jottabgroupmembers&lastid=' + $('#jot_grpid').val() + '&paged=' + $('#jot_grppage').val();
		$(location).attr('href',joturl);
	    });
	});
	
	// Open group details
	$(document).ready(function(){
	    $('#jot-group-list-tab td:not(:last-child)').click(function(){
		var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=group-list&lastid=' + $(this).closest('tr').attr('id') + '&paged=' + $('#jot_grppage').val() + '&t=' + + new Date().getTime();
		//$(location).attr('href',joturl);
		window.location.href = joturl;
	    });
	});
	
	  
       // Save invite form on intial load
       $(document).ready(function(){
	    if ( $("#jot-group-invite-form").length > 0 ) {
				
		var data = {
		    'action': 'process_forms',
		    'formdata': $("#jot-group-invite-form").serialize()     
		};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {				    
		    //
		});	
		
	    };
	});
       
       // Save invite form
       $(document).ready(function(){
	    $("#jot-saveinvite").click(function(){
		jQuery("#jot-invite-message").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4>" + jot_strings.saveinv + "</h4></div>");
		var data = {
		    'action': 'process_forms',
		    'formdata': $("#jot-group-invite-form").serialize()     
		};
		
		
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		    var resp = JSON.parse(response);
			      
		    if (resp.errorcode != '0'){			
			$("#jot-invite-message").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + resp.errormsg + " " + resp.sqlerr + "</h4></div>" );			   		    
		    } else {
			$("#jot-invite-message").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\"<h4>" + resp.errormsg + " " + resp.sqlerr + "</h4></div>" );
		    }			   
		});	
		
	    });
	});
       
        // Save group details form
       $(document).ready(function(){
	    $("#jot-savegrpdetails").click(function(){
		jQuery("#jot-grpdetails-message").html("<h4>" + jot_strings.savegrp + "</h4>");
		var origname = $('#jot-plugin-group-list\\[jot_groupnameupd\\]').val();
		var origdesc = $('#jot-plugin-group-list\\[jot_groupdescupd\\]').val();
		var origoptk = $('#jot-plugin-group-list\\[jot_groupoptout\\]').val();
		
		var autotext = "";
		var grpid = $('#jot_grpid').val();
		
		var data = {
		    'action': 'process_forms',
		    'formdata': $("#jot-group-details-form").serialize()     
		};
				
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		    var resp = JSON.parse(response);
			      
		    if (resp.errorcode != '0'){			
			$("#jot-grpdetails-message").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + resp.errormsg + " " + resp.sqlerr + "</h4></div>" );			   		    
		    } else {
			$("#jot-grpdetails-message").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\"<h4>" + resp.errormsg + " " + resp.sqlerr + "</h4></div>" );
		        // Change name/desc on Group Manager panel
			if (origoptk == "") {
			   origoptk = "all";
			} else {
			   origoptk += ",all";
			}
		
			if ($('#jot-plugin-group-list\\[jot_groupautosub\\]').is(':checked')) {
			   autotext = 'Yes';
			}
			
			$('#' + grpid).find('td').eq(0).hide().html(origname).fadeIn('slow') ;
			$('#' + grpid).find('td').eq(1).hide().html(origdesc).fadeIn('slow') ;
			$('#' + grpid).find('td').eq(2).hide().html(autotext).fadeIn('slow') ;
			$('#' + grpid).find('td').eq(3).hide().html(origoptk).fadeIn('slow') ;
			$('#jot_grptitle').hide().html(origname).fadeIn('slow') ;			
		    }		    
		});	
		
	    });
	});
              
        // Subscribe to a group
        $(document).ready(function(){
	    $("[id^=jot-subscribegroup]").click(function(){		
		
		var parentform = $(this).parents('form:first');
		if ($('#jot-confirm-groupid').val() != "") {
			var statusmsg = jot_strings.confcode;
		} else  {
			var statusmsg = jot_strings.grpsub;
		}
		jQuery($(parentform).find('div[id^=jot-subscribemessage]')).html("<div id=\"jot-messagestatus\" class=\"jot_info\">" + statusmsg + "</div>");
		
		var data = {
		    'action': 'process_subscriber_form',
		    'formdata': $(this).parents('form:first').serialize()     
		};
			
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		   
		    var resp = JSON.parse(response);
			      
		    if (resp.errorcode != '0'){			
			jQuery($(parentform).find('div[id^=jot-subscribemessage]')).html("<div id=\"jot-messagestatus\" class=\"jot_error\">" + resp.errormsg + " " + resp.sqlerr + "</div>" );			   		    
		    } else {
			if (resp.url != "") {
			   $(location).attr('href',resp.url);	
			} else {
			  jQuery($(parentform).find('div[id^=jot-subscribemessage]')).html("<div id=\"jot-messagestatus\" class=\"jot_success\">" + resp.errormsg + " " + resp.sqlerr + "</div>" );
			  $('#jot-verified-number').val(resp.number);
			  $('#jot-confirm-groupid').val(resp.confirmgroup);
			}
						
		    }		
		    
		});
		
	    });
	});
       
       // Subscribe to a group after entering confirmation code
       $(document).ready(function(){
	    $("[id^=jot-confirm]").click(function(){		
		
		var parentform = $(this).parents('form:first');
		jQuery($(parentform).find('div[id^=jot-subscribemessage]')).html("<div id=\"jot-messagestatus\" class=\"jot_info\">" + jot_strings.grpsub + "</div>");
		
		var data = {
		    'action': 'process_confirmed_subscriber_form',
		    'formdata': $(this).parents('form:first').serialize()     
		};
			
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		   
		    var resp = JSON.parse(response);
			      
		    if (resp.errorcode != '0'){			
			jQuery($(parentform).find('div[id^=jot-subscribemessage]')).html("<div id=\"jot-messagestatus\" class=\"jot_error\">" + resp.errormsg + " " + resp.sqlerr + "</div>" );			   		    
		    } else {
			if (resp.url != "") {
			   $(location).attr('href',resp.url);	
			} else {
			  jQuery($(parentform).find('div[id^=jot-subscribemessage]')).html("<div id=\"jot-messagestatus\" class=\"jot_success\">" + resp.errormsg + " " + resp.sqlerr + "</div>" );
			}
						
		    }		
		    
		});
		
	    });
	});
       
    
       
       function getUrlParameter(sParam)	{
	    var sPageURL = window.location.search.substring(1);
	    var sURLVariables = sPageURL.split('&');
	    var retval = "";
	    for (var i = 0; i < sURLVariables.length; i++) 
	    {
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam) 
		{
		    retval = sParameterName[1];
		}
	    }
	    return retval;
	}
	
      
       
       // Send a message
       $(document).ready(function(){
	
	   
	    $("#jot-sendmessage").click(function(event){
		event.preventDefault();
		$('#jot-sendstatus-div').hide();
		$('#jot-restartbatch-div').hide();
		
		//console.log($('#jot-plugin-messages\\[jot-message-sendmethod\\]').val());
		
		if ($('#jot-plugin-messages\\[jot-message-sendmethod\\]').val() == 'jottabquicksend') {
			quick_send();
		} else {
			group_send();
		}
		
		
	    });
	});
       
        function quick_send() {
	   	    
	    if ($('#jot-plugin-messages\\[jot-message-quicksend-number\\]').val() != "") {
		     
		    var tabhtml = "<table id=\"jot-sendstatustab\">";
		    tabhtml += "<tr><th class=\"jot-td-c\">" + jot_strings.number + "</th><th class=\"jot-td-c\">" + jot_strings.status + "</th></tr>";
		    tabhtml += "</table>";
		    
		    $('#jot-sendstatus-div').html(tabhtml);
		    		    
		    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + jot_strings.queuemsg  +"</h4></div>");
		    $("#jot-messagewarningstatus").html("<div id=\"jot-messagewarningstatus\"></div>");
		    
		    var allform = $("#jot-message-field-form").serialize();
		    
		    var formdata =  {   'jot-message-numberlist' :  $('#jot-plugin-messages\\[jot-message-quicksend-number\\]').val(),
					'jot-allform' : allform
				
				    };
		    var data = {
			   'action': 'process_quicksend',
			   'formdata' : formdata
			   
		    };	
		   
		    jQuery.post(ajax_object.ajax_url, data, function(response) {
	                var resp = JSON.parse(response);
			var errmsg = "";
			var warnmsg = "";
			
			if (resp.errorcode != '0'){
				var errhtml = "";				
				
			        if (resp.rejected_numbers != "") {
					errmsg = resp.errormsg;
					warnmsg = "<span class=\"jot-messageamber\">" + jot_strings.rejectednumbers + resp.rejected_numbers + "</span>";
				} else {
				        errmsg = resp.errormsg;
					warnmsg = "";
						
				}
				$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + errmsg + "</h4></div>");			  						    		   		    
				$("#jot-messagewarningstatus").html("<div id=\"jot-messagewarningstatus\" class=\"jot-messageamber\"><h4>" + warnmsg + "</h4></div>");				
			
			} else {
				if (resp.scheduled == false) {
				        // Process Queue
					counter = 0;
					
					// Only allow one polling process if using sqlite.
					if (jot_db.usingsqlite == 'true') {
					    engines = 1;				    
					} else {    
					    engines = 1; // Changed to 1. Not much difference in performance compared with multiple engines			   
					}
					
					if (resp.rejected_numbers != "") {
						errmsg = resp.errormsg;
						warnmsg = "<span class=\"jot-messageamber\">" + jot_strings.rejectednumbers + resp.rejected_numbers + "</span>";
					} else {
						errmsg = resp.errormsg;
						warnmsg = "";
						
					}
					
					// Keep batch id and size for this current batch.
					$('#jot-plugin-messages\\[jot-message-batchid\\]').val(resp.batchid);
					$('#jot-plugin-messages\\[jot-message-fullbatchsize\\]').val(resp.fullbatchsize);
																			
					$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + errmsg + "</h4></div>");
					$("#jot-messagewarningstatus").html("<div id=\"jot-messagewarningstatus\" class=\"jot-messageamber\"><h4>" + warnmsg + "</h4></div>");
					
					$('#jot-sendstatus-div').show();
					$('#jot-restartbatch-div').show();
					
					for (i=1;i<=engines;i++) {					 
					        poll(i,resp.batchid,resp.fullbatchsize,engines);
					}				
				} else {
					scheddate = $('#jot-scheddate').val();
					schedtime = $('#jot-schedtime').val();
				        $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\"><h4>" + jot_strings.scheduled + " (" + scheddate + " @ " + schedtime + ")" + "</h4></div>");
				}
			}
		    });
		   
		} else {
		    $("#jot-messagewarningstatus").html("<div id=\"jot-messagewarningstatus\"></div>");
		    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + jot_strings.enterrecip + "</h4></div>");
		}
	    
	} // Quick Send
       
        function group_send() {
			
	    var selected_numbers = [];
		
		$('[id^=jot-recip-mem-select]:checked').each(function () {
			selected_numbers.push($(this).prop('value'));
		});				
		
		if (selected_numbers.length > 0) {
		     
		    var tabhtml = "<table id=\"jot-sendstatustab\">";
		    tabhtml += "<tr><th class=\"jot-td-c\">" + jot_strings.number + "</th><th class=\"jot-td-c\">" + jot_strings.status + "</th></tr>";
		    tabhtml += "</table>";
		    
		    $('#jot-sendstatus-div').html(tabhtml);
		    		    
		    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + jot_strings.queuemsg  +"</h4></div>");
		    $("#jot-messagewarningstatus").html("<div id=\"jot-messagewarningstatus\"></div>");
		    var allform = $("#jot-message-field-form").serialize();
		    
		    var formdata =  {   'jot-message-grouplist' :  JSON.stringify(selected_numbers),
					'jot-allform' : allform
				
				    };
		    var data = {
			   'action': 'queue_message',
			   'formdata' : formdata
			   
		    };	
		   
		    jQuery.post(ajax_object.ajax_url, data, function(response) {
	                var resp = JSON.parse(response);
				      
			if (resp.errorcode != '0'){
				var errhtml = "";
				$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + resp.errormsg + "</h4></div>");			  						    		   		    
			} else {
				if (resp.scheduled == false) {
				        // Process Queue
					counter = 0;
					
					// Only allow one polling process if using sqlite.
					if (jot_db.usingsqlite == 'true') {
					    engines = 1;				    
					} else {    
					    engines = 1; // Changed to 1. Not much difference in performance compared with multiple engines			   
					}
					
					// Keep batch id and size for this current batch.
					$('#jot-plugin-messages\\[jot-message-batchid\\]').val(resp.batchid);
					$('#jot-plugin-messages\\[jot-message-fullbatchsize\\]').val(resp.fullbatchsize);
					
					$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + resp.errormsg + "</h4></div>");
					$('#jot-sendstatus-div').show();
					$('#jot-restartbatch-div').show();
					
					for (i=1;i<=engines;i++) {
						//console.log("1 Calling poll " + i + " >" + resp.batchid + " >" + resp.fullbatchsize + " >" + engines );
						poll(i,resp.batchid,resp.fullbatchsize,engines);
					}				
				} else {
					scheddate = $('#jot-scheddate').val();
					schedtime = $('#jot-schedtime').val();
				        $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\"><h4>" + jot_strings.scheduled + " (" + scheddate + " @ " + schedtime + ")" + "</h4></div>");
				}
			}
		    });
		   
		} else {
		    $("#jot-messagewarningstatus").html("<div id=\"jot-messagewarningstatus\"></div>");
		    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + jot_strings.selectrecip + "</h4></div>");
		}
	
	
        } // End group_send
       
        //
	// Poll - Process message queues.
	//
	function poll(id, batchid,fullbatchsize,engines) {
						    
	    var data = {
		        'id' : id,
			'action': 'process_queue',
			'jot_batchid': batchid,
			'jot_fullbatchsize' : fullbatchsize,
			'jot_engines' : engines
	    };
	    
	    $.ajax({
		type:"POST",
		url:ajax_object.ajax_url,
		timeout:30000,
		error: function(x, t, m) {
			if(t==="timeout") {
			    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>A timeout occurred, attempting to restart processing.</h4></div>");
			    console.log("Message processing timed out.");
			    if (confirm("A timeout has occurred. Press OK to restart")) {
				poll(id, batchid,fullbatchsize,engines);
			    }
			    
			} else {
			    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>A server error occurred.</h4></div>");
			    console.log("An error has occurred : " + t );			    
			}
		    },
		data:data,
		success:function(queueresp) {	
				
			var qresp = JSON.parse(queueresp);
			
			var allerrors = '';
			
			if (qresp.remaining_messages == -1 || qresp.remaining_messages == "" ) {
			   counter_status = "A problem occurred. Trying to restart processing. ";
			} else {			   
			   counter = fullbatchsize - qresp.remaining_messages;
			   counter_status =  counter +  "/" + fullbatchsize;
			}
					
			$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>Messages sent : " + counter_status + " - do not leave this page until all messages are sent.</h4></div>");
			if (qresp.batcherrors != null) {		    
			    allerrors = qresp.batcherrors;
			    $.each(allerrors, function(index, row) {
				if (row.send_message_errorcode != 0) {
				    $('#jot-sendstatustab tr:last').after("<tr class=\"jot-messagered\"><td class=\"jot-td-c\">" + row.send_message_number + "</td><td> " + row.send_message_msg + " (Error code : <a href='https://www.twilio.com/docs/errors/" + row.send_message_errorcode + "' target='_blank'>" + row.send_message_errorcode + "</a>) </td></tr>");	
				} else {
				    $('#jot-sendstatustab tr:last').after("<tr class=\"jot-messagegreen\"><td class=\"jot-td-c\">" + row.send_message_number + "</td><td> " + row.send_message_msg  + "</td></tr>");	
				}
			    });		    
			}
			
			if (qresp.remaining_messages == 0) {
				var finished_msg = jot_strings.sentallmsg;
				
				if ($("#jot-groupsend-status").length > 0) {
				     // Display log link - used on jotgroupsend shortcode
				     loglink = '<a href="#jot-groupsend-log-link" id="jot-groupsend-log-link">View Log</a>';			    
				     finished_msg = jot_strings.sentallmsg + " " + loglink;
				} 
				$("#jot-messagestatus").html("<h4>" + finished_msg  + "</h4>");				
			}
			if (qresp.remaining_messages > 0) {
			  	//console.log("2 Calling poll " + id + " >" + batchid + " >" + fullbatchsize + " >" +engines + " >" + qresp.remaining_messages);
				poll(id, batchid,fullbatchsize,engines);
			}
		
		} // success function
	    })		    
	    
	}; // Poll function
        
       
       // Restart message send if hung
	$(document).ready(function(){
	    $( "#jot-restartbatch-div" ).on( "click", "a[id=jot-restartbatch]", function( event ) {	
		event.preventDefault();
		$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + "Restarting...." + "</h4></div>");
					
		batchid = $('#jot-plugin-messages\\[jot-message-batchid\\]').val();
		fullbatchsize = $('#jot-plugin-messages\\[jot-message-fullbatchsize\\]').val();
		console.log("Restarting batch : " + batchid);
		poll(1, batchid,fullbatchsize,1);
	    });
	});
       
	// Add a new group
	$(document).ready(function(){
	    $("#jot-addgroup").click(function(){
		jQuery("#jot-messagestatus").html("<h4>" + jot_strings.addgrp + "</h4>");
		var data = {
		    'action': 'process_forms',
		    'formdata': $("#jot-group-add-fields-form").serialize()     
		};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		    var resp = JSON.parse(response);
		   
		    if (resp.errorcode != '0'){			
			jQuery("#jot-messagestatus").html("<h4>" + resp.errormsg + " " + resp.sqlerr + "</h4>");
		    } else {			
			window.location.replace(resp.url);
		    }
		});
	    });
	});
	
	// Cancel out of Add Group panel
	$(document).ready(function(){
	    $("#jot-addgroupcancel").click(function(){
		window.location.replace(jot_plugin.referrer);
	    });
	});
	
		
	// Generate HTML for invite form	
	$(document).ready(function(){
	    $("#jot-generate-invite-html").click(function(event){
		event.preventDefault();
		
		$("#jot-plugin-group-list\\[jot_grpinvformtxt\\]").val("");
	
		// Get chat history
		var formdata =  { 'jot_groupid'         : $('#jot-plugin-group-list\\[jot_grpid\\]').val(),
				  'jot_grpinvdesc'      : $('#jot-plugin-group-list\\[jot_grpinvdesc\\]').val(),
				  'jot_grpinvnametxt'   : $('#jot-plugin-group-list\\[jot_grpinvnametxt\\]').val(),
				  'jot_grpinvnumtxt'    : $('#jot-plugin-group-list\\[jot_grpinvnumtxt\\]').val()
				 };
		
		var data = {
		    'action': 'process_generate_invite_html',
		    'formdata':  formdata
		};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {				    
		    var resp = JSON.parse(response);
		    $("#jot-plugin-group-list\\[jot_grpinvformtxt\\]").val(resp.html);
		});
		
	    });
	});
	
	// Count message length
	function get_messagecount (panel) {
		
		var maxlen = 0;
		var cs = 0;
		
		// Messages tab
		if (panel == 'message') {
		    cs = $('#jot-plugin-messages\\[jot-message\\]').val().length + $('#jot-plugin-messages\\[jot-message-suffix\\]').val().length;
		    maxlen = $('#jot-plugin-messages\\[jot-message\\]').attr('maxlength');
		}
		// Group Details tab
		if (panel == 'optout') {
		    cs = $('#jot-plugin-group-list\\[jot_groupopttxt\\]').val().length;
		    maxlen = $('#jot-plugin-group-list\\[jot_groupopttxt\\]').attr('maxlength');
		}
		// Group Invite tab
		if (panel == 'welcome') {
		    cs = $('#jot-plugin-group-list\\[jot_grpinvrettxt\\]').val().length;
		    maxlen = $('#jot-plugin-group-list\\[jot_grpinvrettxt\\]').attr('maxlength');
		}
		// Group Invite tab
		if (panel == 'already-subbed') {
		    cs = $('#jot-plugin-group-list\\[jot_grpinvalreadysub\\]').val().length;
		    maxlen = $('#jot-plugin-group-list\\[jot_grpinvalreadysub\\]').attr('maxlength');
		}
		// Settings - Unsub Message
		if (panel == 'unsub-notification') {
		    cs = $('#jot-plugin-smsprovider\\[jot-inbunsubmsg\\]').val().length;
		    maxlen = $('#jot-plugin-smsprovider\\[jot-inbunsubmsg\\]').attr('maxlength');
		}
		// Settings - Notification Message
		if (panel == 'inbound-notification') {
		    cs = $('#jot-plugin-smsprovider\\[jot-inbsmsrtmsg\\]').val().length;
		    maxlen = $('#jot-plugin-smsprovider\\[jot-inbsmsrtmsg\\]').attr('maxlength');
		}		
		// Settings - Auto-add reply
		if (panel == 'inbreply') {
		    cs = $('#jot-plugin-smsprovider\\[jot-inbreply\\]').val().length;
		    maxlen = $('#jot-plugin-smsprovider\\[jot-inbreply\\]').attr('maxlength');
		}
	
		$('#jot-message-count-' + panel).text(cs + "/" + maxlen );	
	};
	
	$(document).ready(function(){
	    // Messages tab
	    $(document).on('keyup', '#jot-plugin-messages\\[jot-message\\], #jot-plugin-messages\\[jot-message-suffix\\]', function(event) { get_messagecount('message')} );
	   
	   // Group Details tab (opt-out reply)	   
	    $(document).on('keyup', '#jot-plugin-group-list\\[jot_groupopttxt\\]', function(event) { get_messagecount('optout')});
			   
	    // Group Invite tab	   
	    $(document).on('keyup', '#jot-plugin-group-list\\[jot_grpinvrettxt\\]', function(event) { get_messagecount('welcome')});
	    
	    // Group Invite  - already subscribed	 
	    $(document).on('keyup', '#jot-plugin-group-list\\[jot_grpinvalreadysub\\]', function(event) { get_messagecount('already-subbed')});
	    
	    // Settings unsubscription message
	    $(document).on('keyup', '#jot-plugin-smsprovider\\[jot-inbunsubmsg\\]', function(event) { get_messagecount('unsub-notification')});	 
	    
	    // Settings Notification message
	    $(document).on('keyup', '#jot-plugin-smsprovider\\[jot-inbsmsrtmsg\\]', function(event) { get_messagecount('inbound-notification')});	     
	
	    // Settings Inbound Auto-add
	    $(document).on('keyup', '#jot-plugin-smsprovider\\[jot-inbreply\\]', function(event) { get_messagecount('inbreply')});
	
	       
	    
	});
	
	
	
	// Populate message character count on intial load
       $(document).ready(function(){
	    if ( $("#jot-message-count-message").length > 0 ) {
		get_messagecount('message');		
	    };
	    if ( $("#jot-message-count-optout").length > 0 ) {
		get_messagecount('optout');		
	    };
	    if ( $("#jot-message-count-welcome").length > 0 ) {
		get_messagecount('welcome');		
	    };
	    if ( $("#jot-message-count-inbound-notification").length > 0 ) {
		get_messagecount('inbound-notification');		
	    };
	    if ( $("#jot-message-count-inbreply").length > 0 ) {
		get_messagecount('inbreply');		
	    };
	    if ( $("#jot-message-count-unsub-notification").length > 0 ) {
		get_messagecount('unsub-notification');		
	    };
	    if ( $("#jot-message-count-already-subbed").length > 0 ) {
		get_messagecount('already-subbed');
	    }
	});
	
	
	
	// Submit SMS provider form on select change 
	$(document).ready(function(){
	    $('#jot-plugin-smsprovider\\[jot-smsproviders\\]').change(function(){
		  var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=smsprovider&smsprovider=' + $(this).val();
		  $(location).attr('href',joturl);
		
	    });
	});
	
	

	// Validate SMS routing number
	/*
	$(document).ready(function(){
		$('#smsprovider-fields-form').on('submit',function(){
		    if($("#jot-plugin-smsprovider\\[jot-inbsmschk\\]").prop('checked')) {			
			// if everything is ok return true else return false and show errors
			var smsnumber = $("#jot-plugin-smsprovider\\[jot-inbsmsnum\\]").val().replace(/ /g,'');
	                if (smsnumber != "") {			
				if (isNumber(smsnumber)) {
					$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"></div>");
					$("#jot-plugin-smsprovider\\[jot-inbsmsnum\\]").val(smsnumber);
					return true;
				} else {
					$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + jot_strings.addroutenum + "</h4></div>");
					$("#jot-plugin-smsprovider\\[jot-inbsmsnum\\]").focus();
					return false;
				}
			} else {
			   return true;
			}
		    } else {
			return true;
		    }
		});
	})
	*/
	
	function isNumber(n) {
	  return !isNaN(parseFloat(n)) && isFinite(n);
	}
        
	/*
	// Disable routing SMS number if checkbox isn't checked
        $(document).ready(function(){
	        if ($("#jot-plugin-smsprovider\\[jot-inbsmschk\\]").length > 0) {
			
			if ($("#jot-plugin-smsprovider\\[jot-inbsmschk\\]").is(':checked')) {			
	                  $("#jot-plugin-smsprovider\\[jot-inbnotgroup\\]").removeAttr('disabled');
			  $("#jot-plugin-smsprovider\\[jot-inbemail\\]").removeAttr('disabled');
			} else if ($("#jot-plugin-smsprovider\\[jot-inbsubchk\\]").is(':checked')) {
	                  $("#jot-plugin-smsprovider\\[jot-inbnotgroup\\]").removeAttr('disabled');
			  $("#jot-plugin-smsprovider\\[jot-inbemail\\]").removeAttr('disabled');
			} else {
			  $("#jot-plugin-smsprovider\\[jot-inbnotgroup\\]").attr('disabled', 'disabled');
			  $("#jot-plugin-smsprovider\\[jot-inbemail\\]").attr('disabled', 'disabled');
			}
			
	        }
	})
	
	// Enable notifications if SMS checkbox clicked.
	$(document).ready(function(){
	    $("#jot-plugin-smsprovider\\[jot-inbsmschk\\]").click(function(){
		if ($("#jot-plugin-smsprovider\\[jot-inbsubchk\\]").is(':checked')) {
			// If subchk is checked then don't disable
		} else {
			$("#jot-plugin-smsprovider\\[jot-inbnotgroup\\]").attr('disabled', !$(this).attr('checked'));
			$("#jot-plugin-smsprovider\\[jot-inbemail\\]").attr('disabled', !$(this).attr('checked'));
			$("#jot-plugin-smsprovider\\[jot-inbnotgroup\\]").focus();
		}
	    });
	});
	
	// Enable notifications if email checkbox clicked.
	$(document).ready(function(){
	    $("#jot-plugin-smsprovider\\[jot-inbsubchk\\]").click(function(){
		if ($("#jot-plugin-smsprovider\\[jot-inbsmschk\\]").is(':checked')) {
			// If smschk is checked then don't disable
		} else {
			$("#jot-plugin-smsprovider\\[jot-inbsmsnum\\]").attr('disabled', !$(this).attr('checked'));
			$("#jot-plugin-smsprovider\\[jot-inbemail\\]").attr('disabled', !$(this).attr('checked'));
			$("#jot-plugin-smsprovider\\[jot-inbsmsnum\\]").focus();
		}
	    });
	});
	*/
	
	
	// ON CLICK Enable the options depending on the message type selected
	$(document).ready(function(){
		
	    $("#container-jot-plugin-messages\\[jot-message-type\\] input[type='radio']").click(function(){
			 
		var messtype = $(this).val();
		
		
		// 5=senderid, 6=audio select, 7=choose mms, 8=selected mms
		if (messtype == 'jot-sms') {
			$("#jot-plugin-messages\\[jot-message-senderid\\]").parent("td").parent("tr").show();
			$("#jot-plugin-messages\\[jot-message-audioid\\]").parent("td").parent("tr").hide();
			$("#jot-upload-btn").parent("td").parent("tr").hide();	
			$("#jot-image-selected").parent("td").parent("tr").hide();			
		}
		if (messtype == 'jot-call') {				   
			$("#jot-plugin-messages\\[jot-message-senderid\\]").parent("td").parent("tr").hide();
			$("#jot-plugin-messages\\[jot-message-audioid\\]").parent("td").parent("tr").show();
			$("#jot-upload-btn").parent("td").parent("tr").hide();
			$("#jot-image-selected").parent("td").parent("tr").hide();
		}
		if (messtype == 'jot-mms') {				   
			$("#jot-plugin-messages\\[jot-message-senderid\\]").parent("td").parent("tr").show();
			$("#jot-plugin-messages\\[jot-message-audioid\\]").parent("td").parent("tr").hide();
			$("#jot-upload-btn").parent("td").parent("tr").show();
			$("#jot-image-selected").parent("td").parent("tr").show();
		}
		
	    });
	});
	
	
	// ON LOAD Enable the options depending on the message type selected
	$(document).ready(function(){
	        if ($("#jot-plugin-messages\\[jot-message-audioid\\]").length > 0 ) {
			if ($("#container-jot-plugin-messages\\[jot-message-type\\] input[type='radio']").is(':checked')) {
				var messtype = $("#container-jot-plugin-messages\\[jot-message-type\\] input[type='radio']:checked").val();
				
				
				if (messtype == 'jot-sms') {					
					$("#jot-plugin-messages\\[jot-message-senderid\\]").parent("td").parent("tr").show();
					$("#jot-plugin-messages\\[jot-message-audioid\\]").parent("td").parent("tr").hide();
					$("#jot-upload-btn").parent("td").parent("tr").hide();	
					$("#jot-image-selected").parent("td").parent("tr").hide();			
				}
				if (messtype == 'jot-call') {	
					$("#jot-plugin-messages\\[jot-message-senderid\\]").parent("td").parent("tr").hide();
					$("#jot-plugin-messages\\[jot-message-audioid\\]").parent("td").parent("tr").show();
					$("#jot-upload-btn").parent("td").parent("tr").hide();
					$("#jot-image-selected").parent("td").parent("tr").hide();
				}
				if (messtype == 'jot-mms') {	
					$("#jot-plugin-messages\\[jot-message-senderid\\]").parent("td").parent("tr").show();
					$("#jot-plugin-messages\\[jot-message-audioid\\]").parent("td").parent("tr").hide();
					$("#jot-upload-btn").parent("td").parent("tr").show();
					$("#jot-image-selected").parent("td").parent("tr").show();
				}		
			} 
	        }
	})
	
	
	// Group Invite Welcome Message
	// ON CLICK Enable the options depending on the message type selected
	$(document).ready(function(){
		
	    $("#container-jot-plugin-group-list\\[jot_grpinvmesstype\\] input[type='radio']").click(function(){
			 
		var messtype = $(this).val();
			
		if (messtype == 'jot-sms') {			
			$("#jot-plugin-group-list\\[jot_grpinvaudioid\\]").parent("td").parent("tr").hide();
			$("#jot-upload-btn").parent("td").parent("tr").hide();
			$("#jot-image-selected").parent("td").parent("tr").hide();
			
		}		
		if (messtype == 'jot-call') {
			$("#jot-plugin-group-list\\[jot_grpinvaudioid\\]").parent("td").parent("tr").show();
			$("#jot-upload-btn").parent("td").parent("tr").hide();
			$("#jot-image-selected").parent("td").parent("tr").hide();
		}
		if (messtype == 'jot-mms') {			
			$("#jot-plugin-group-list\\[jot_grpinvaudioid\\]").parent("td").parent("tr").hide();
			$("#jot-upload-btn").parent("td").parent("tr").show();
			$("#jot-image-selected").parent("td").parent("tr").show();
		}
		
	    });
	});
	
	
	// Group Invite Welcome Message
	// ON LOAD Enable the options depending on the message type selected
	$(document).ready(function(){
	        if ($("#jot-plugin-group-list\\[jot_grpinvaudioid\\]").length > 0 ) {
			if ($("#container-jot-plugin-group-list\\[jot_grpinvmesstype\\] input[type='radio']").is(':checked')) {
				var messtype = $("#container-jot-plugin-group-list\\[jot_grpinvmesstype\\] input[type='radio']:checked").val();
				
				if (messtype == 'jot-sms') {			
					$("#jot-plugin-group-list\\[jot_grpinvaudioid\\]").parent("td").parent("tr").hide();
					$("#jot-upload-btn").parent("td").parent("tr").hide();
					$("#jot-image-selected").parent("td").parent("tr").hide();
					
				}		
				if (messtype == 'jot-call') {
					$("#jot-plugin-group-list\\[jot_grpinvaudioid\\]").parent("td").parent("tr").show();
					$("#jot-upload-btn").parent("td").parent("tr").hide();
					$("#jot-image-selected").parent("td").parent("tr").hide();
				}
				if (messtype == 'jot-mms') {			
					$("#jot-plugin-group-list\\[jot_grpinvaudioid\\]").parent("td").parent("tr").hide();
					$("#jot-upload-btn").parent("td").parent("tr").show();
					$("#jot-image-selected").parent("td").parent("tr").show();
				}
						
					} 
				}
	})
	
		
	// Delete history item
	$(document).ready(function(){
	    $( "#jot-hist-list-tab" ).on( "click", "a[id^=jot-hist-delete-]", function( event ) {
	    	event.preventDefault();
		
		var $tr = $(this).closest('tr');
		var valarr = $(this).attr('id').split('-');
		
		var formdata =  { 'jot_histid' : valarr[3] };
		var data = {
		    'action': 'process_deletehistitem',
		    'formdata':  formdata
		};
		
		if (confirm(jot_strings.histitemdelete)) {
		    jQuery.post(ajax_object.ajax_url, data, function(response) {				    
			var resp = JSON.parse(response);
			  
			if (resp.errorcode != '0'){			
			    //Error deleting history item			   		    
			} else {
			    //Item deleted ok
			    $tr.find('td').fadeOut('slow',function(){ 
			       $tr.remove();                    
			    }); 
			    var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=message-history&paged=' + $('#jot_histpage').val();
			    $(location).attr('href',joturl);
			    
			}
		    });	
		}
	    });
	});
	
	// Open history chat dialog 
	$(document).ready(function(){
	    $( "#jot-hist-list-tab" ).on( "click", "tr:not(.jot-ignore) td:not(:last-child,:nth-child(4), :nth-child(6))", function( event ) {
		
		event.preventDefault();
		var clickedrow = $(this);
		
		//Play sound when dialog open
		var open_dialog_sound = new Audio(jot_sounds.open);
                open_dialog_sound.play();		
		
		$("#jot-chathist").dialog(
		   {
			height: 610,
			width: 400,
			dialogClass: 'jot-chathist'
		    }
		);
				   
		$('#jot-chat-hist-div').html("Refreshing...<img src='" + jot_images.spinner +  "'>");
		
		var jot_histfrom      = $(this).parents("tr").children("td:first").attr('id');
		var jot_histto        = $(this).parents("tr").find('td:eq(1)').attr('id');
		var jot_histfrom_name = $(this).parents("tr").children("td:first").html();
		var jot_histto_name   = $(this).parents("tr").find('td:eq(1)').html();
		var jot_histid        = $(this).parents("tr").attr("data-histid");
		var jot_histtype      = $(this).parents("tr").attr("data-histtype");
		var jot_histstatus    = $(this).parents("tr").attr("data-histstatus");		
		
						
		// Get chat history
		var formdata =  { 'jot_histid'     : jot_histid,
				  'jot_histtype'   : jot_histtype,
				  'jot_histstatus' : jot_histstatus
				};
		
		var data = {
		    'action': 'process_getchathistory',
		    'formdata':  formdata
		};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {				    
		    var resp = JSON.parse(response);
		     buildChat(resp, jot_histfrom, jot_histto,jot_histfrom_name,jot_histto_name);        
		});
		
		
	    });
	});
	
	// Build history chat dialog box
	function buildChat(resp,jot_histfrom, jot_histto, jot_histfrom_name,jot_histto_name) {
		var html = "";
		var cssclass = "";
		var displaydate="";
		var lasthistid = 0;
		
		//console.log(resp);
		if (resp.ournumber) {
			var ournumber = resp.ournumber;
		} else {
			var ournumber = 'Not found.';
		}
		if (resp.theirnumber) {
			var theirnumber = resp.theirnumber;
		} else {
			var theirnumber = 'Not found.';
		}
		if (resp.theirname) {
			var theirname = resp.theirname;
		} else {
			var theirname = "";
		}
		
		
		html += "<form id='jot-chat-hist-form'>";
		html += "<input type='hidden' id='jot-chat-theirnumber' value='" + theirnumber + "'>";
		html += "<input type='hidden' id='jot-chat-ournumber' value='" + ournumber + "'>";
 		html += "<table id='jot-chat-hist-table' class='jot-chat-hist-table'>";
				
		
		$.each(resp.histlist, function(index, row) {
		    if (row.jot_date != displaydate) {
			displaydate = row.jot_date;
		        html += "<tr><td class='jot-chat-date'>" + displaydate + "</td></tr>";
		    }
		    
		    if (row.jot_histfrom == ournumber ) {
			tdcssclass= "jot-chat-td-right";
			if (row.jot_histmesstype == 'c') {
			   divcssclass = "jot-chat-call-right";
			} else {
			   divcssclass = "jot-chat-sms-right";
			}
			html += "<tr><td class='" + tdcssclass + "'>" + row.jot_time + "<div class='" + divcssclass +  "'>" + row.jot_histmesscontent.replace("\\", "") + "</div></td></tr>";  
		    } else {
			tdcssclass= "jot-chat-td-left";			
			divcssclass = "jot-chat-sms-left";						
			html += "<tr><td class='" + tdcssclass + "'><div class='" + divcssclass +  "'>" + row.jot_histmesscontent.replace("\\", "") + "</div>" + row.jot_time + " </td></tr>";  
		    }
		    lasthistid = row.jot_histid;
                });
		
		html += "</table>";
		html += "<input type='hidden' id='jot-chat-lasthistid' value='" + lasthistid + "'>";
		html += "</form>";
		
		$('#jot-chat-hist-div').html(html);
		if (theirnumber == '') {
		    theirnumber = jot_strings.numbernotavailable;
		}
		
		
		
		$("#jot-chathist").dialog('option', 'title', ournumber + " " + theirnumber + " (" + theirname + ") " );		
		$("#jot-chathist").dialog('open');
		$("#jot-chat-hist-div").animate({ scrollTop: $("#jot-chat-hist-table")[0].scrollHeight}, 1000);
		$('#jot-chathist').css('overflow', 'hidden');
		$('#jot-chat-send-input').focus();
		
		// Setup polling
		// Poll for new messages sent to this number whilst chat dialog is open
		var poller = setInterval(function(){
				if ( $("#jot-chathist").is(":visible") ) {	
				    // Send ajax request to database
				    var new_message_sound = new Audio(jot_sounds.ting);
				    var theirnumber = $('#jot-chat-theirnumber').val();
				    var ournumber   = $('#jot-chat-ournumber').val();
				    var lasthistid  = $('#jot-chat-lasthistid').val();
				    var formdata =  {'jot_theirnumber' : theirnumber ,
				                     'jot_ournumber' : ournumber,
					    	     'jot_lasthistid' : lasthistid
					}
				    var data = {
					'action': 'process_getnewchatmessages',
					'formdata':  formdata
				    };
				  
				    if (theirnumber != '') {
					jQuery.post(ajax_object.ajax_url, data, function(response) {				    
						var resp = JSON.parse(response);
						  
						if (resp){
							var html = '';
							$.each(resp, function(index, row) {
								divcssclass = "jot-chat-sms-left";
								tdcssclass= "jot-chat-td-left";
								html += "<tr><td class='" + tdcssclass + "'><div class='" + divcssclass +  "'>" + row.jot_histmesscontent.replace("\\", "") + "</div>" + row.jot_time + " </td></tr>";  
								lasthistid = row.jot_histid;
								new_message_sound.play();
						        });
							$('#jot-chat-lasthistid').val(lasthistid);
						        $('#jot-chat-hist-table tr:last').after(html).fadeIn('slow');;
							$("#jot-chat-hist-div").animate({ scrollTop: $("#jot-chat-hist-table")[0].scrollHeight}, 1000);							
						}
					});	
					
				    } // if theirnumber
				    
				} else {
				    clearInterval(poller);
				}			    
			}, 20000);
	}
	
	function get_theirnumber(from, to) {
		
		if (from == jot_number.number ) {
			return to;
		} else {
		        return from;	
		}		
	}
	
	// Get all names associated with the number
	// there may be more than one name
	function get_names(theirnumber) {
		
	
	}
	
	// Send a message from the Chat History dialog
	$(document).ready(function(){
	    $("#jot-chat-send-msg").click(function(event){
		event.preventDefault();
		
		var theirnumber = $('#jot-chat-theirnumber').val();
		var chatmessage = $("<p>").html($('#jot-chat-send-input').val()).text();
		var formdata =  {   'jot_theirnumber' : theirnumber ,
		                    'jot_chatmessage' : chatmessage,
				    'jot_grpmemid' : 8888888
		}
		var data = {
		    'action': 'process_sendchatmessage',
		    'formdata': formdata    
		};
				
		$('#jot-chat-send-input').val("");
		var tdcssclass= "jot-chat-td-right";
		var currentdate = new Date();
		var currenttime = ((currentdate.getDate() < 10)?"0":"") + currentdate.getDate() + ":" + ((currentdate.getMinutes() < 10)?"0":"") + currentdate.getMinutes();
		
		// Play sound on send
		var send_message_sound = new Audio(jot_sounds.send);
                send_message_sound.play();
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {
				
		    var resp = JSON.parse(response);
		   
		    if (resp.send_message_errorcode == '0'){
			divcssclass = "jot-chat-sms-right";
			html = "<tr><td class='" + tdcssclass + "'>" + currenttime + "<div class='" + divcssclass +  "'>" + chatmessage + "</div></td></tr>";  
			$('#jot-chat-hist-table tr:last').after(html).fadeIn('slow');;
		    } else {
			divcssclass = "jot-chat-fail-right";
			html = "<tr><td class='" + tdcssclass + "'>" + currenttime + "<div class='" + divcssclass +  "'>" + chatmessage + " - " + resp.send_message_msg + "</div></td></tr>";  
			$('#jot-chat-hist-table tr:last').after(html).fadeIn('slow');;
		    }
		    $("#jot-chat-hist-div").animate({ scrollTop: $("#jot-chat-hist-table")[0].scrollHeight}, 1000);
		});
	    });
	});
	
	// Apply filters on Message History tab
       $(document).ready(function(){
	    
	    var timer;
	    
	    $("[id*='jot-filter']").keyup(function(){		
		filterhistory();		
	    });
	    
	    $('#jot-plugin-message-history\\[jot-filter-type\\]').change(function(){
		filterhistory();
	    });
	    
	    function filterhistory() {
	           
		clearTimeout(timer);
		var formdata =  {  'jot-filter-from' : $('#jot-plugin-message-history\\[jot-filter-from\\]').val(),
		                   'jot-filter-to' : $('#jot-plugin-message-history\\[jot-filter-to\\]').val(),
				   'jot-filter-message' : $('#jot-plugin-message-history\\[jot-filter-message\\]').val(),
				   'jot-filter-type' : $('#jot-plugin-message-history\\[jot-filter-type\\]').val(),
				   'jot-filter-status' : $('#jot-plugin-message-history\\[jot-filter-status\\]').val()
				}
		
		var ms = 500; // milliseconds
		var data = {
		    'action': 'process_filter_history',		    
		    'formdata': formdata    
		};
		
		timer = setTimeout(function() {
			$("#jot-applyfilters-status").html("<div class=\"jot-messageblack\"><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + jot_strings.refreshing + "</div>");
			
			jQuery.post(ajax_object.ajax_url, data, function(response) {
		            var resp = JSON.parse(response);
			    
			    if (resp != null) {				
				var html = "";
				render = setTimeout(function() {
					$("#jot-hist-list-tab-body").html(resp);
					$("#jot-hist-list-tab-pagination").html(resp.pagination);
					$("#jot-applyfilters-status").html("");
				}, 200);
			    }
		        });
		}, ms)
	    }
	    
	    function get_timestamp () {
	    	var currentdate = new Date(); 
		var datetime =  currentdate.getDate() + "/"
				+ (currentdate.getMonth()+1)  + "/" 
				+ currentdate.getFullYear() + " @ "  
				+ currentdate.getHours() + ":"  
				+ currentdate.getMinutes() + ":" 
				+ currentdate.getSeconds();
		return datetime;
	    }
	});
	
	
	// Clear history tab filters
	$(document).ready(function(){
	    $("#jot-filter-clear").click(function(event){
		event.preventDefault();
		$("#jot-applyfilters-status").html("<div class=\"jot-messageblack\"><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + jot_strings.refreshing + "</div>");
			
		
		var formdata =  {  'jot_histpage' : '',
				   'jot-filter-from' : '',
		                   'jot-filter-to' : '',
				   'jot-filter-message' : '',
				   'jot-filter-type' : '',
				   'jot-filter-status' : ''
				}
		var data = {
		    'action': 'process_reset_filters',		    
		    'formdata': formdata    
		};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		        var resp = JSON.parse(response);
			
			var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=message-history';
			$(location).attr('href',joturl);
		        
		})
		
		
				
	    });
	});
	
	// Select History delete date 
	$(document).ready(function(){
	    $('#jot-plugin-message-history\\[jot-memhistdelete\\]').change(function(){
		
		$("#jot-applyfilters-status").html("<div class=\"jot-messageblack\"><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + jot_strings.refreshing + "</div>");
				
		if ($(this).val() == 99999999) {
		    var confstring = jot_strings.confirmhistkeep;
		} else {
		    var confstring = jot_strings.confirmhistdel.replace("xxx", $(this).val())
		}
		if (confirm(confstring)) {
			var formdata =  {  'jot_histdelete' : $(this).val()				   
					}
			var data = {
			    'action': 'process_history_deletions',		    
			    'formdata': formdata    
			};
			
			jQuery.post(ajax_object.ajax_url, data, function(response) {
				var resp = JSON.parse(response);
				
				var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=message-history';
				$(location).attr('href',joturl);
				
			})
		} else {
		  $("#jot-applyfilters-status").html("");
		}
	    });
	});
	
	// Radio button for voice options
	$(document).ready(function(){
	     $("#container-jot-plugin-smsprovider\\[jot-voice-gender\\] input[type='radio']").click(function(){
		var voice = $(this).val();
						
		var formdata =  {  'jot_voice_gender' : voice			   
					}
		var data = {
			'action': 'process_refresh_languages',		    
			'formdata': formdata    
			};
		//$("#jot-plugin-smsprovider\\[jot-voice-accent\\]").hide();
		jQuery.post(ajax_object.ajax_url, data, function(response) {		   
		    var resp = JSON.parse(response);			      
		    		    
		    html = '<select id="jot-plugin-smsprovider[jot-voice-accent]" name="jot-plugin-smsprovider[jot-voice-accent]">';
		    
		    $.each(resp, function(index, row) {			
			html += '<option value="' + index  + '">' +  row + '</option>';
		    });
		    html += '</select>';
		    $("#jot-plugin-smsprovider\\[jot-voice-accent\\]").html(html);
		    $('#jot-plugin-smsprovider\\[jot-voice-accent\\] option[value="en-GB"]').prop('selected', true);
		});
		
	    });
	});
	
	// Open Media Library
	$(document).ready(function(){
		$('#jot-upload-btn').click(function(e) {							
			e.preventDefault();
			var image = wp.media({ 
				title: 'Select MMS media file',
				
				multiple: false
			}).open()
			.on('select', function(e){
				
				var uploaded_image = image.state().get('selection').first();
				
				
				if (uploaded_image.attributes.type != 'image') {
					//None Image
					$('#jot-image-selected').hide();
					$('#jot-image-selected-status').hide();
					$('#jot-media-selected').show();
				} else {
					// Image
					if (uploaded_image.attributes.sizes.thumbnail != undefined) {
					    var image_url = uploaded_image.attributes.sizes.thumbnail.url;
					} else {
					   var image_url = uploaded_image.attributes.sizes.full.url;
					}
					$('#jot-image-selected').attr('src',image_url);
					$('#jot-image-selected').show();
					$('#jot-media-selected').hide();
					$('#jot-image-selected-status').hide();
				}
				
				if ($('#jot-plugin-messages\\[jot-message-mms-image\\]').length > 0) {
				    $('#jot-plugin-messages\\[jot-message-mms-image\\]').val(uploaded_image.id);
				}
				if ($('#jot-plugin-group-list\\[jot-message-mms-image\\]').length > 0) {
				    $('#jot-plugin-group-list\\[jot-message-mms-image\\]').val(uploaded_image.id);
				}
				
				$('#jot-media-selected').val(uploaded_image.attributes.filename);
				
			});
		});
	});
	
        // Add history image and audio to a tooltip
	$(document).ready(function(){

		$( "[class^=jot-histimage]" ).tooltip({
		    tooltipClass: "jot-tooltip",
		    content: function(){
			return "<img src='" + $(this).attr('href') + "' />";
		    }		    
		});
		$('[class^=jot-histimage]').click(function (e) {
		    e.preventDefault();		    
		})
		
		$('[class^=jot-histimage]').click(function (e) {
		    e.preventDefault();	
		                        
		    var html =  "<html><center><img width='150' height='150' src='" + $(this).attr('href') + "' /></center></html>";
                    
                    var newDiv = $(document.createElement('div')); 
		    newDiv.html(html);
		    newDiv.dialog(newDiv.dialog());
		    
		})
		
		
		$('[class^=jot-histaudio]').click(function (e) {
		    e.preventDefault();	
		                       
		    var html =  "<center>" +
				" <audio id='jot-audio-tag' controls autoplay> " +
				" <source src='" + $(this).prop('href')  + "'> " + 
				"  Your browser does not support the audio tag " +
				" </audio>" +
				"</center>";
                    var newDiv = $(document.createElement('div')); 
		    newDiv.html(html);
		    newDiv.dialog({
			close: function(ev, ui) { 
						  audio = $('audio');
						  $.each(audio, function(obj, val) {
							val.pause();
							val.currentTime = 0;
						  });	
						}				  
		    });
                    	    
		})
    
	});
	
	// Reset message tab fields
	$(document).ready(function(){
	     $("#jot-sendreset").click(function(event){
		event.preventDefault();
		
		var data = {
			'action': 'process_messagefields_reset'    
			};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {		   
		    var resp = JSON.parse(response);			      
		    
		   
		    var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=messages';
		    $(location).attr('href',joturl);
		    
		});
		
	    });
	});
	
	// Show/hide extended member info
	$(document).ready(function(){
	     $("#jot-plugin-group-list\\[jot-mem-extfields\\]").click(function(event){
		if ($("#jot-plugin-group-list\\[jot-mem-extfields\\]").is(':checked')) {
			
			// Member list fields
	              	$('#jot-groupmem-tab td:nth-child(4),#jot-groupmem-tab th:nth-child(4)').show();
			$('#jot-groupmem-tab td:nth-child(5),#jot-groupmem-tab th:nth-child(5)').show();
			$('#jot-groupmem-tab td:nth-child(6),#jot-groupmem-tab th:nth-child(6)').show();
			$('#jot-groupmem-tab td:nth-child(7),#jot-groupmem-tab th:nth-child(7)').show();
			$('#jot-groupmem-tab td:nth-child(8),#jot-groupmem-tab th:nth-child(8)').show();
			
			// Message panel fields
			$(".jot-showextended").show();
			$(".jot-filler").hide();
			
			var checked = 'true';
		} else {
			
			// Member list fields
			$('#jot-groupmem-tab td:nth-child(4), #jot-groupmem-tab th:nth-child(4)').hide();
			$('#jot-groupmem-tab td:nth-child(5), #jot-groupmem-tab th:nth-child(5)').hide();
			$('#jot-groupmem-tab td:nth-child(6), #jot-groupmem-tab th:nth-child(6)').hide();
			$('#jot-groupmem-tab td:nth-child(7), #jot-groupmem-tab th:nth-child(7)').hide();
			$('#jot-groupmem-tab td:nth-child(8), #jot-groupmem-tab th:nth-child(8)').hide();
			
			// Message panel fields
			$(".jot-showextended").hide();
			$(".jot-filler").show();
			
			var checked = 'false';
		}
		
		// Toggle fields on Messages page
		//$(".jot-showextended").toggle();
		//$(".jot-filler").toggle();
		
		var formdata =  {  'jot-mem-extfields' : checked
				}
		
		var data = {
			'action': 'process_set_extendmemfields',		    
				   'formdata': formdata    		       
			};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {		   
		    var resp = JSON.parse(response);
		    
		});
		
	    });
	});

        // Abridge the messages in Message history
	//jot-plugin-message-history[jot-memhistlatchk]
	$(document).ready(function(){
	     $("#jot-plugin-message-history\\[jot-hist-abridge\\]").click(function(event){
		
		$("#jot-applyfilters-status").html("<div class=\"jot-messageblack\"><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + jot_strings.refreshing + "</div>");
			
		if ($("#jot-plugin-message-history\\[jot-hist-abridge\\]").is(':checked')) {
	              	var checked = 'true';
		} else {					
			var checked = 'false';
		}
		
		var formdata =  {  'jot-hist-abridge' : checked
				}
		
		var data = {
			'action': 'process_set_abridgehistory',		    
				   'formdata': formdata    		       
			};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {		   
		    var resp = JSON.parse(response);
		    var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=message-history';
		    $(location).attr('href',joturl);
		    
		});
		
	    });
	});
	
	
	// Recipients search/select functions
	$(document).ready(function(){
		
		// Collapse selected group
		$('.jot-recip-group-header').click(function(){
			$(this).nextUntil('tr.jot-recip-group-header').not(".hiddendupe").slideToggle(200);
		        $('#jot-recip-collapseall').attr("checked",false);			
		});
	
	        // Collapse all groups
		$('#jot-recip-collapseall').click(function(){
			
			$('#jot-recip-tab tr').not('.jot-recip-group-header').each(function () {
			     if ($("#jot-recip-collapseall").is(':checked')) {
			        $(this).show();
				update_recip_count();				
			     } else {
				$(this).hide();
			     }			   
			});	
				
		});
		
		
		function collapse_groups() {
			$('#jot-recip-tab tr').not('.jot-recip-group-header').each(function () {			   
			        $(this).hide();			     
			});	
		}
		
	
		// Select all members of the group
		$('[id^=jot-recip-group-select]').click(function(e){
			e.stopPropagation();
			
			$(this).parent().parent().nextUntil('tr.jot-recip-group-header')
			    .find('input[type="checkbox"]')
			    .prop("checked", this.checked)  ;
			
			var numchecked = $("#jot-recip-tab .jot-member input[type='checkbox']:checked").length  ;
		        $("#jot-recip-numselected").html("Selected : " + numchecked);
			
			if ($("#jot-recip-selectall").is(':checked')) {
			    $('#jot-recip-selectall').attr("checked",false);
			}
			
		}); 
		
		// Filter the recipients box
		$("#jot-recip-search").keyup(function () {
			var searchval = this.value.toLowerCase().trim();
			do_recipient_search(searchval,'jot-recip-tab');
		});
		
		$("#jot-groupsend-recip-search").keyup(function () {
			var searchval = this.value.toLowerCase().trim();
			do_recipient_search(searchval,'jot-groupsend-recip-tab');
		});
		
		function do_recipient_search(value,recip_table) {
			//var value = this.value.toLowerCase().trim();
			var lastheader = "";
			var lastgrpheader="";
			
			// Uncheck all first before search
			$("#" + recip_table + " input[type='checkbox']").attr("checked", false )  ;
			$("#jot-recip-selectall").attr("checked", false )  ;	
						
			// Find search term in each row			
			$("#" + recip_table + " tr").each(function () {			    
			    if ($(this).hasClass("jot-member")) {
				
				//Ignore if row already hidden (dupe)
				if (!$(this).hasClass('hiddendupe')) {
					//if ($(this).hasClass('hiddendupe')) {
					//   console.log($(this));
				        //}
					$(this).find("td").each(function () {					
										    
						var id = $(this).text().toLowerCase().trim();
						var not_found = (id.indexOf(value) == -1);
						
						// Ignore column if hidden
						if ($(this).is(".jot-showextended") ) {						   
						    if ( $("#jot-plugin-group-list\\[jot-mem-extfields\\]").is(':checked') ) {
							// Ignore
						    } else {
							not_found = true;
						    }						   
						}
						
						if (!not_found) {
						    // Show
						    $(this).closest("tr").toggle(true);
						    if (value != "") {
							$(this).closest("tr").find("input[type='checkbox']").attr("checked", true );
						    }
						    $(lastgrpheader).show();
						    $(lastheader).show();
						} else {
						    // Hide
						    $(this).closest("tr").toggle(false);
						    
						}
						
						return not_found;		
					});
				}
			   } else {				
				if ($(this).is(".jot-recip-group-header") ) {
				   lastgrpheader = $(this);
				   $(lastgrpheader).hide();	
				}
				if ($(this).is(".jot-mem-table-headers") ) {
				   lastheader = $(this);
				   $(lastheader).hide();	
				}
				
			   }
		           update_recip_count();
			});
		}
		
		// Select all members
		$('#jot-recip-selectall').click(function(){			
		        $("#jot-recip-tab input[type='checkbox']").attr("checked", this.checked )  ;
			
			update_recip_count();
		});
		
		// Update selected recipients count when member selected
		$('[id^=jot-recip-mem-select]').click(function(e){				
			update_recip_count();
			
			var grpid = $(this).attr("value").split('-');
			var grpheader = $("#jot-recip-group-select-" + grpid[0]) ;
			$(grpheader).attr("checked",false);
			
			$('#jot-recip-selectall').attr("checked");
			if ($("#jot-recip-selectall").is(':checked')) {
			    $('#jot-recip-selectall').attr("checked",false);
			}			
		});
		
		// Deselect all, collapse groups and update selected recipients count on load
		if ($("#jot-recip-numselected").length > 0) {
		      
		      $("#jot-recip-tab input[type='checkbox']").attr("checked", false ) ;
		      $("#jot-recip-selectall").attr("checked", false ) ;
		      //collapse_groups();
		      update_recip_count();
		     
		}
		
		function update_recip_count() {
		      //var numchecked = $("#jot-recip-tab .jot-member input[type='checkbox']:checked").not(':hidden').length  ;
		      var numchecked = $("#jot-recip-tab .jot-member input[type='checkbox']:checked").length  ;		      
		      $("#jot-recip-numselected").html("Selected : " + numchecked);
		      
		      // Add warning for large numbers of recipients
		      if (numchecked >= 250) {
			 $("#jot-messagewarningstatus").html("<div id=\"jot-messagewarningstatus\" class=\"jot-messageamber\">" + jot_strings.messagelimitwarning + "</div>");
		      } else if (numchecked < 250) {
			$("#jot-messagewarningstatus").html("");		      
		      }
		      
		}
		
		$('[id^=jot-recip-mem-delete-]').click(function(event){			
			event.preventDefault();
				    
			// jot-recip-mem-delete-<groupid>-<groupmemid>
			
			var valarr = $(this).attr('id').split('-');
			var grpid = valarr[3];
			var formdata =  {   'jot_grpid' : valarr[4],
					    'jot_grpmemid' : valarr[5]					
			};
			
		       
			var data = {
			    'action': 'process_deletemem',
			    'formdata':  formdata
			};
			
						
			if (confirm(jot_strings.confirmmemdel)) {
			    jQuery.post(ajax_object.ajax_url, data, function(response) {				    
				var resp = JSON.parse(response);
				  
				if (resp.errorcode != '0'){			
				    //Error			   		    
				} else {
				    // Refresh screen
				    var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=messages';
				    $(location).attr('href',joturl);					    
				}
			    });	
			}
			
				
		});
		
		$('[id^=jot-recip-mem-deleteall-]').click(function(event){	
			
			event.preventDefault();
				    
			// jot-recip-mem-deleteall-<groupid>-<groupmemid>
			
			var valarr = $(this).attr('id').split('-');
			var grpid = valarr[3];
			var formdata =  {   'jot_grpid' : valarr[4],
					    'jot_grpmemid' : valarr[5]					
			};
			
		       
			var data = {
			    'action': 'process_deleteallmem',
			    'formdata':  formdata
			};
			
			
			if (confirm(jot_strings.confirmmemalldel)) {
			    jQuery.post(ajax_object.ajax_url, data, function(response) {				    
				var resp = JSON.parse(response);
				  
				if (resp.errorcode != '0'){			
				    // Error		   		    
				} else {
				    var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=messages';
				    $(location).attr('href',joturl);					    
				}
			    });	
			}			
			
		});
		
	});
	
	// Activate licences
	$('#jot-edd-licence-activate').click(function(event){
		event.preventDefault();
		
		var formdata =  {  'jot-eddlicence' : $('#jot-plugin-smsprovider\\[jot-eddlicence\\]').val(),
		                   'jot-eddproduct' : jot_product.item
				}
		
		var data = {			
			'action'   : 'process_jot_edd_activate_license',
			'formdata' : formdata
			};
		
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {		   
		    var resp = JSON.parse(response); 
		    var status = "";
		    
		    status = resp.activationstatus;
		    
		    $("#jot-plugin-smsprovider\\[jot-eddlicencestatus\\]").val(status);
		    
		    
		});
		
	});
	
		
	        
        // Woocommerce sync
	$(document).ready(function(){
	   
	    $("#jot-plugin-woo-manager\\[jot-woo-sync\\]").click(function () {

		if ($("#jot-plugin-woo-manager\\[jot-woo-merge\\]").is(':checked')) {			
			woosync();
		} else {			
			if (confirm(jot_strings.woodelconfirm)) {
				woosync();	
			}
		}
		
	        
	    });
	    
	    function woosync(){
		$("#jot-woo-syncstatus-div").html("<img src='" + jot_images.spinner +  "'>");
		$("#jot-woo-syncstatus-textarea").html("");
		
		var data = {
		    'action': 'process_sync_woo_jot',
		    'formdata': $("#woo-fields-form").serialize()
		};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		    var resp = JSON.parse(response);	  
		  
		    $("#jot-woo-syncstatus-div").html("");
		    //$("#jot-woo-syncstatus-textarea").load(jot_woo.logfile + "?t=" + Date.now());
		    $("#jot-woo-syncstatus-textarea").html(resp.log);
			    
		});
	    }
	    
	    //if ($("#jot-woo-syncstatus-textarea").length > 0) {
		//$("#jot-woo-syncstatus-textarea").html("");
	    //}

	});
	
	// Gravity Forms Integration
	$(document).ready(function(){
		
	$('#jot-savegrpgravity').click(function(event){
		event.preventDefault();
		$("#jot-grpgravity-message").html("<div class=\"jot-messageblack\">" +  jot_strings.savegrp + " </div>" );
		
		var data = {			
			'action'   : 'process_save_gf_fields',
			'formdata' : $('#jot-group-gravity-form').serialize()
			};
		
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {		   
		    var resp = JSON.parse(response);
		    
		    if (resp.errorcode != 0){			
			$("#jot-grpgravity-message").html("<div class=\"jot-messagered\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
		    } else {
			$("#jot-grpgravity-message").html("<div class=\"jot-messagegreen\">" +  resp.errormsg + " </div>" );
		    }
		});
		
		
	});
	
	$("#jot-plugin-group-list\\[jot-gravityforms\\]").change(function () {		
		
		if ($("#jot-plugin-group-list\\[jot-gravityforms\\]").val() != 99999999) {
		
			var formdata =  {  'jot_tab'    : 	$("#jot_tab").val(),
					   'jot_gfformid' : 	$("#jot-plugin-group-list\\[jot-gravityforms\\]").val(),
					   'jot_grpid'   :      $("#jot_grpid").val()
					}
			var data = {
			    'action': 'process_get_gf_fields',
			    'formdata': formdata     
			};
			
			jQuery.post(ajax_object.ajax_url, data, function(response) {
			     $('#jotgravityfieldsmap').html(response);
			     $('#jotgravityfieldsmap').show();
			   
			});
		} else {
			$('#jotgravityfieldsmap').hide();	
		}
		
		
	    });
	});
	
	// Member list bulk actions
	$(document).ready(function(){
            
	    $('#jot-memberlist-bulkapply').click(function (event) {
		
		       var selected_members = [];
		
			$('[id^=jot-mem-select]:checked').each(function () {
				var valarr = $(this).attr('id').split('-');
				selected_members.push(valarr[3]);
			});	
	               
		      
		       var formdata =  {  'jot_action' : $("#jot-plugin-group-list\\[jot-bulk-action\\]").val(),
					  'jot_source_grpid'  : $("#jot_grpid").val(),
					  'jot_target_grpid'  : $("#jot-plugin-group-list\\[jot-target-grpid\\]").val(),
					  'jot_memberlist'    : JSON.stringify(selected_members)
					}
			var data = {
			    'action': 'process_memberlist_bulk_actions',
			    'formdata': formdata     
			};
			
			jQuery.post(ajax_object.ajax_url, data, function(response) {
				var resp = JSON.parse(response);
		   
				if (resp.errorcode > 900){
					//No page refresh
					$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg  + " </div>" );			   		    
				} else {
					$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg  + " </div>" );			
				        var memberlist_url = wp_vars.wp_admin_url + 'admin.php?' + insertParam("subtab","jottabgroupmembers");
					window.location.replace(memberlist_url);
				}
				
			});
	    });
	    
	    
	    // Select all members
	    $('#jot-mem-select-all').click(function(){		       
		        $("input:checkbox[id^=jot-mem-select]").attr("checked", this.checked )  ;			
	    });
	    
	    $("#jot-plugin-group-list\\[jot-bulk-action\\]").on('change', function() {
		 var action = $(this).val();
		 		 
		 // Grey out Group Select if delete action deleted
		 if (action  == "delete") {			
			$("#jot-plugin-group-list\\[jot-target-grpid\\]").attr("disabled", true);
		 } else {
			$("#jot-plugin-group-list\\[jot-target-grpid\\]").removeAttr('disabled');
		 }
		 
	    });
	    
	    
	    // Add key and value to the current page URL
	    function insertParam(key, value)	{
		    key = encodeURI(key); value = encodeURI(value);
		
		    var kvp = document.location.search.substr(1).split('&');
		
		    var i=kvp.length; var x; while(i--) 
		    {
			x = kvp[i].split('=');
		
			if (x[0]==key)
			{
			    x[1] = value;
			    kvp[i] = x.join('=');
			    break;
			}
		    }
		
		    if(i<0) {kvp[kvp.length] = [key,value].join('=');}
		
		    //this will reload the page, it's likely better to store this until finished
		    return kvp.join('&'); 
	   }
	    
	});
	
	// jottextus shortcode handling
	$(document).ready(function() {
	    $("#jot-textus-dialog").dialog({
		autoOpen: false,		
		dialogClass: 'jottextus',		
                width: 'auto',		
                create: function( event, ui ) {
			// Set maxWidth
			$(this).css("maxWidth", "600px");
		}
	    });
	    
	    $('#jot-textus-opener').click(function() {
		$("#jot-textus-dialog").dialog('open');
		return false;
	    });
	    
	  
	    
	    $('#jot-textus-send').click(function() {
		var data = {			
			'action'   : 'process_textus_send',
			'formdata' : $('#jot-textus-form').serialize()
		};
			
		jQuery.post(ajax_object.ajax_url, data, function(response) {		   
		    var resp = JSON.parse(response);
		    
		    if (resp.errorcode != 0){			
			$("#jot-textusmessage").html("<div class=\"jot-messagered\">" + resp.errormsg + " </div>" );
		    } else {
			$("#jot-textusmessage").html("<div class=\"jot-messagegreen\">" +  resp.errormsg + " </div>" );
		    }
		});
		
	    });
	    
	    $('#jot-textus-close').click(function() {
	        $("#jot-textus-dialog").dialog('close');
		
	    });
	    
	});
	
	// jotsendgroup short code handling
	$(document).ready(function() {	       
	  
	    
	    $('#jot-groupsend-send').click(function() {
		
		var selected_numbers = [];
		
		$('[id^=jot-groupsend-recip-mem-select]:checked').each(function () {			
			selected_numbers.push($(this).prop('value'));
		});
		
		$("#jot-groupsend-status").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + jot_strings.queuemsg  +"</div>" );
				
		var data = {			
			'action'         : 'process_jotgroupsend',
			'jot-recipients' :  JSON.stringify(selected_numbers),
			'formdata'       : $('#jot-groupsend-form').serialize()
		};
		
		var jot_groupsend_sendtype = $('#jot-groupsend-sendtype').val();
		if (jot_groupsend_sendtype == null) {
		    jot_groupsend_sendtype = "immediate";
		}
			
		jQuery.post(ajax_object.ajax_url, data, function(response) {		   
		    var resp = JSON.parse(response);
		    
		    
		    if (resp.errorcode != 0){			
			$("#jot-groupsend-status").html("<div class=\"jot-messagered\">" + resp.errormsg  + " </div>" );			
		    } else {
			if (jot_groupsend_sendtype == "immediate") {	
				var tabhtml = "<table id=\"jot-sendstatustab\">";
				tabhtml += "<tr><th class=\"jot-td-c\">" + jot_strings.number + "</th><th class=\"jot-td-c\">" + jot_strings.status + "</th></tr>";
				tabhtml += "</table>";
				$('#jot-sendstatus-div').html(tabhtml);
				
				// Send messages
				var engines = 1;
				
				// Process Queue
				counter = 0;
				poll(1,resp.batchid,resp.fullbatchsize,engines);				
			} else {
				// Dripfeed events instead.
				$("#jot-groupsend-status").html("<div class=\"jot-messagegreen\">" + jot_strings.msgqueued + " " + jot_strings.msgsentshortly  + " </div>" );
			}
		    }
		    
		});
		
	    });
	    	   
	    $(document).on('click', '#jot-groupsend-log-link', function(event) {
		        event.preventDefault();			
			$('#jot-sendstatus-div').toggle();
	    });
	    
	});
	
	
	// jotoptout code handling
	$(document).ready(function() {	      
		$(document).on('click', '#jot-groupout-get-groups-button', function(event) {
			event.preventDefault();
			$("#jot-groupoptout-groups-display").hide();
			$("#jot-groupoptout-spinner").css('visibility', 'visible');
			$("#jot-groupoptout-spinner").show();
			
			var data = {			
				'action'   : 'process_jotoptout_getgroups',
				'formdata' : $('#jot-groupoptout-form').serialize()
			};			
			
			jQuery.post(ajax_object.ajax_url, data, function(response) {		   
			    var resp = JSON.parse(response);			    
			    
			    if (resp.errorcode != 0){
				$("#jot-groupoptout-groups").html("<span class=\"jot-messagered\">" + resp.errormsg + "</span>");
				$("#jot-groupout-get-unsubscribe-button").hide();				
				$("#jot-groupoptout-groups-display").show();
				$("#jot-groupoptout-spinner").css('visibility', 'hidden');
				$("#jot-groupoptout-spinner").hide();
			    } else {
				$("#jot-groupoptout-spinner").css('visibility', 'hidden');
				$("#jot-groupoptout-spinner").hide();				
				var allgroups = resp.allgroups;				
				var html= "";
				html += '<input type="hidden"  name="jot-groupoptout-verifiednum"  id="jot-groupoptout-verifiednum" value="' + resp.verified_number + '">';
				html += '<p>';
				$.each(allgroups, function(index, row) {				  
				  html += '<label for="jot-groupoptout-groups-' +  allgroups[index].jot_groupid + '">';
				  html += '<input id="jot-groupoptout-groups-' + allgroups[index].jot_groupid + '" name="jot-groupoptout-groups['  + allgroups[index].jot_groupid +  ']" type="checkbox" value="true" />' + "\n";
                                  html += allgroups[index].jot_groupname + "<br>";
				});
				html += '<p>';
				$("#jot-groupoptout-groups").html(html);
				$('#jot-groupoptout-groups-heading').show();
				$("#jot-groupout-get-unsubscribe-button").show();
				$("#jot-groupoptout-groups-display").show();
			    }
			});
		});
		
		$(document).on('click', '#jot-groupout-get-unsubscribe-button', function(event) {
			event.preventDefault();		
			
			var data = {			
				'action'   : 'process_jotoptout_unsubscribe',
				'formdata' : $('#jot-groupoptout-form').serialize()
			};			
			
			jQuery.post(ajax_object.ajax_url, data, function(response) {		   
			    var resp = JSON.parse(response);			    
			    
			    if (resp.errorcode != 0){
				$("#jot-groupout-get-unsubscribe-button").hide();
				$("#jot-groupoptout-groups").html("<span class=\"jot-messagered\">" + resp.errormsg + "</span>");
			    } else {
				$('#jot-groupoptout-groups-heading').hide();
				$("#jot-groupout-get-unsubscribe-button").hide();
				$("#jot-groupoptout-groups").html("<span class=\"jot-messagegreen\">" + resp.errormsg + "</span>");
				
			    }
			});
		});
		
	});
	
	// Configure SMS url
	$(document).ready(function(){
	
		$(document).on('click', '#jot-config-sms-url', function(event) {
			event.preventDefault();
			
			$("#jot-config-sms-url-messages").html("<span class='jot-messageblack'>" + jot_strings.configuring + "</span>");
						
			var data = {			
				'action'   : 'process_configure_smsurl'
			};			
			
			jQuery.post(ajax_object.ajax_url, data, function(response) {		   
			    var resp = JSON.parse(response);			    
			    
			    if (resp.errorcode == 0){
				$("#jot-config-sms-url-messages").html("<span class='jot-messageblack'>" + jot_strings.refreshing + "</span>");
				var joturl = wp_vars.wp_admin_url + 'admin.php?page=jot-plugin&tab=smsprovider&section=twiliosettings';
				$(location).attr('href',joturl);
			    } else {				
				$("#jot-config-sms-url-messages").html("<span class='jot-messagered'>" + resp.errormsg + "</span>");
			    }
			});
		});
	});
	
	
	
	//
	// Refresh inbox for jotinbox shortcode
	//
	$( "body" ).on( "click", "a[id=jot-refresh-inbox]", function( event ) {
					
		event.preventDefault();
	
		var data = {
		    'action': 'process_refresh_inbox'
		};
		
		$('#jot-refresh-inbox').text("Refreshing....");
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {				    
		    var resp = JSON.parse(response);
		      
		    //$('#jot-groupinbox-div').replaceWith(resp.html);
		    $('#jot-tab-2').html(resp.html);
		});	
			
	})
	
	//
	// Filter jotinbox
	//
	$("#jot-groupinbox-search").keyup(function() {
		var rows = $("#jot-groupinbox-tab").find("tr").hide();
		var data = this.value.split(" ");
		
		$.each(data, function(i, v) {
			rows.filter(":contains('" + v + "')").show();
		});
	});
	
	
	//
	// Jump to group 
	//	
	$(document).ready(function(){
	    $('#jot-plugin-group-list\\[jot-jumptogroup\\]').change(function(){
		var jot_groupid = $(this).val();
		
		if (jot_groupid == 999999) {
		    var joturl = wp_vars.wp_admin_url +  'admin.php?page=jot-plugin&tab=group-list';	
		} else {
		    var joturl = wp_vars.wp_admin_url +  'admin.php?page=jot-plugin&tab=group-list&lastid=' + jot_groupid;
		}
		$(location).attr('href',joturl);		  
		
	    });
	});
	
	
}(jQuery));