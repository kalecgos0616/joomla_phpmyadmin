/* 
 * Name   : association.js
 * Author : Bernard Saulm�
 * (C) Bernard Saulm�
 */

function jSelectMyjsp_jform_associations(id, title, lang) {
	var_id = "jform_associations_"+lang+"_id";
	var_name = "jform_associations_"+lang+"_name";
	document.id(var_id).value = id;
	document.id(var_name).value = title;
	SqueezeBox.close();
}
			