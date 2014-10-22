/*
 * myjsp_editor_myjsp.js
 * Author : Bernard Saulmé
 */

 function ClosePluginPopup(strReturnURL) {
	PluginReturnUrl(strReturnURL);
	// Close popup window
	if (top.tinymce)
		top.tinymce.activeEditor.windowManager.close();
}

function PluginReturnUrl(strReturnURL) { // Return the file url
	if (top.tinymce)
		top.tinymce.activeEditor.windowManager.getParams().setUrl(strReturnURL);
}
