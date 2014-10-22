/*
 * myjsp_editor.js
 * Author : Bernard Saulmé
 */

function basename(path) {
	path = path.replace(/\\/g, '/'); // to cleanup for IE
	return path.split('/').reverse()[0];
}
