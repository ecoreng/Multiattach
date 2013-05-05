// Deletes an attachment locating it by its node_id and id
function deleteAttachment(attachmentID) {
	var url=Croogo.basePath + 'admin/Multiattach/AjaxKillAttachmentJson/'+attachmentID+'/'+Croogo.params.node_id;
	$.getJSON(url, function(data, textStatus) {
			$("#multiattachstatus").html(Croogo.params.multiattach.deleteSt['s'+data.status]).slideDown('fast',function(){setTimeout('$("#multiattachstatus").slideUp();',5000)});
			reloadAttachmentTable();
		});
}
// Returns a string with the row of the attachment
function tabulate(attachment){
	accion='<a href="javascript:void(0);" onclick="deleteAttachment('+attachment.id+')">'+Croogo.params.multiattach.actionlnkLbl+'</a>';
	if (attachment.comment=="") {
			attachment.comment=Croogo.params.multiattach.noCommentLbl;
	}
	return '<tr><td>'+(attachment.display)+'</td><td class="mltaComment">'+attachment.comment+'</td><td>'+accion+'</td></tr>';
}
function reloadAttachmentTable(){
		if (Croogo.params.controller=='nodes') {
		var url=Croogo.basePath + 'admin/Multiattach/AjaxGetAttachmentJson/'+Croogo.params.node_id;
		$.getJSON(url, function(data, textStatus) {
			cuantos=data.length;
			$("form#NodeAdminEditForm .nav-tabs li a[href=#node-attachments]").html(Croogo.params.multiattach.tabName+" ("+cuantos+")");
			tableHTML='<table class="table table-stripped">';
			tableHTML+='<tbody>';
			tableHead='<tr><th>'+Croogo.params.multiattach.fileLbl+'</th><th>'+Croogo.params.multiattach.commentLbl+'</th><th>'+Croogo.params.multiattach.actionsLbl+'</th></tr>';
			tableHTML+=tableHead;
			$.each(data, function(key, val) {
					tableHTML+=tabulate(val.Multiattach);
				});
			tableHTML+=tableHead;
			tableHTML+='</tbody>';
			tableHTML+='</table>';
			$("#multiattachments").html(tableHTML);
		});
	}
	
}
// Generates a table with the attachments for this Node, and sets the number of attachments up in the tab
$(document).ready(function() {
	reloadAttachmentTable();
});