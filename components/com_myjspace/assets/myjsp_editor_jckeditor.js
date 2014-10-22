/*
 * myjsp_editor_jckeditor.js
 * Author : Bernard Saulmé
 */
 
function ClosePluginPopup(strReturnURL) {
	PluginReturnUrl(strReturnURL);
	// Close popup window
	if (window.opener.CKEDITOR)
		window.close();
}

function PluginReturnUrl(strReturnURL) { // Return the file url
	if (window.opener.CKEDITOR)
		window.opener.CKEDITOR.tools.callFunction(2, strReturnURL);

}
