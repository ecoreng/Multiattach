// Deletes an attachment locating it by its node_id and id
function deleteAttachment(attachmentID) {
	var url = Croogo.basePath + 'admin/Multiattach/AjaxKillAttachmentJson/'+attachmentID+'/'+Croogo.params.node_id;
	var params = {rand:Math.random()};
	$.getJSON(url,params, function(data, textStatus) {
			$("#multiattachstatus").html(Croogo.params.multiattach.deleteSt['s'+data.status]).slideDown('fast',function(){setTimeout('$("#multiattachstatus").slideUp();',5000)});
			reloadAttachmentTable();
		});
}
// Returns a string with the row of the attachment
function tabulate(attachment){
	var action = new Array();
	
	// Erase attachment button
	action[1] = '<button type="button" class="close" onclick="deleteAttachment('+attachment.id+')">&times;</button>';
	
	// Use the tag set for the empty comment
	if (attachment.comment == "" || attachment.comment === undefined) {
			attachment.comment = Croogo.params.multiattach.noCommentLbl;
	}
	// Erase meta variable if nothing is in there
	if (attachment.metaDisplay == "" || attachment.metaDisplay === undefined) {
			attachment.metaDisplay='';
	}
	// replace carriage returns with <br /> for inline editor
	attachment.metaDisplay = attachment.metaDisplay.replace(/[\n\r]/g,"<br />");
	// Meta content
	action[2] = '<a class="editable" data-emptytext="['+Croogo.params.multiattach.noMetaLbl+']" href="#" data-name="meta" data-url="'+Croogo.params.multiattach.proCoUrl+'?rand='+Math.random()+'" data-type="textarea" data-pk="'+attachment.id+'">'+attachment.metaDisplay+'</a>';
	// Comments
	action[3] = '<a class="editable" href="#" data-name="comment" data-url="'+Croogo.params.multiattach.proCoUrl+'?rand='+Math.random()+'" data-type="text" data-pk="'+attachment.id+'">'+attachment.comment+'</a>';
	
	// drag icon
	action[4] = '<span class="dHandle"><i class="icon-screenshot"></i></span>';
	
	// Format element
	retTxt = '<li class="span3" id="att_' + attachment.id + '">';
	retTxt += '	<div class="thumbnail">';
	retTxt += action[1];
	retTxt += action[4];
	retTxt += (attachment.display);
	retTxt += '		<div class="caption">';
	retTxt += action[3];
	retTxt += '		</div>';
	retTxt += '		<div class="well well-small">';
	retTxt += action[2];
	retTxt += '		</div>';
	retTxt += '	</div>';
	retTxt += '</li>';
	// return assembled element
	return retTxt;
}
function sortedArray(elements){
	elements = elements[0];
	var a = [];
	for (var i = 0; i < elements.length; i++) {
		attID = elements[i].id.split("_");
		a.push(attID[1]);
	}
	if (a.length > 0) {
		serialText = '';
		$(a).each(function(i,e){
			serialText += '&s[' + i + ']=' + e;
		});
		var url = Croogo.basePath + 'admin/Multiattach/AjaxOrderAttachmentJson/'+Croogo.params.node_id + '/?' + serialText;
		$.getJSON(url, function(data) {
			
		});
	}
	// guarda orden de arreglo e id con ajax
	// done
}
function reloadAttachmentTable(){
		if (Croogo.params.controller == 'nodes') {
			var url = Croogo.basePath + 'admin/Multiattach/AjaxGetAttachmentJson/'+Croogo.params.node_id;
			var params = {rand:Math.random()};
			$.getJSON(url,params, function(data, textStatus) {
				cuantos = data.length;

				// Change the name of the tab, add the # of attachments
				$("form#NodeAdminEditForm .nav-tabs li a[href=#node-attachments]").html(Croogo.params.multiattach.tabName+" ("+cuantos+")");

				// Display the attachment list
				tableHTML = '<ul class="thumbnails">';			
				$.each(data, function(key, val) {
						tableHTML += tabulate(val.Multiattach);
					});
				tableHTML += '</ul>';
				$("#multiattachments").html('');
				myDiv  = document.createElement('div');
				myDiv.id = 'divAttachments';
				myDiv.innerHTML = tableHTML;
				$("#multiattachments")[0].appendChild(myDiv);

				// Make it sortable
				$("ul.thumbnails").sortable({
					vertical: false,
					distance: 5,
					handle: '.dHandle',
					itemSelector: ".span3",
					onDrop: function (item, container, _super) {
						sortedArray($("ul.thumbnails").sortable("serialize"));
						 _super(item, container)
					  }
				});
				// Activate in line editor
				$('.editable').editable(
					{
						ajaxOptions: {
							type: 'get',
						},
						success: function(response, newValue) {
							response=JSON.parse(response);
							if(!response.status)
								return response.msg;
							else
								return {newValue:response.newValue};
						}
					}
				);
				// Do more stuff required by the newly generated list
			});
	}
	
}
// Generates a table with the attachments for this Node, and sets the number of attachments up in the tab
$(document).ready(function() {
	reloadAttachmentTable();
	$.fn.editable.defaults.mode = 'popup';
});
