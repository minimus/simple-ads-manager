tinyMCEPopup.requireLangPack();

function init() {
	tinyMCEPopup.resizeToInnerSize();
	
	TinyMCE_EditableSelects.init();
}

function insertSAMCode() {
	
	var samCode;
	
	var f = document.forms[0];
	var radio = f.elements.sam_item;
	
	var samItem = 0;
	if(radio[1].checked) samItem = 1;
  var samId = f.elements.sam_id.value;
  var samIdObj = f.elements.sam_id;
	var samBA = f.elements.sam_codes.checked;
	
	samCode = ' [sam id="' + samId + '"';
  if(samItem == 1) samCode += ' name="' + samIdObj.options[samIdObj.selectedIndex].text + '"';
  if(samBA == 1) samCode += ' codes="true"]';
  else samCode += ']';

  window.tinyMCE.activeEditor.execCommand('mceInsertContent', false, samCode);
	tinyMCEPopup.editor.execCommand('mceRepaint');
	tinyMCEPopup.close();
	return;
}

function insertSAMZoneCode() {
  
  var samCode;
  
  var f = document.forms[0];
  var radio = f.elements.sam_item;
  
  var samItem = 0;
  if(radio[1].checked) samItem = 1;
  var samId = f.elements.sam_id.value;
  var samIdObj = f.elements.sam_id;
  var samBA = f.elements.sam_codes.checked;
  
  samCode = ' [sam_zone id="' + samId + '"';
  if(samItem == 1) samCode += ' name="' + samIdObj.options[samIdObj.selectedIndex].text + '"';
  if(samBA == 1) samCode += ' codes="true"]';
  else samCode += ']';

  window.tinyMCE.activeEditor.execCommand('mceInsertContent', false, samCode);
  tinyMCEPopup.editor.execCommand('mceRepaint');
  tinyMCEPopup.close();
  return;
}

function insertSAMAdCode() {
  
  var samCode;
  
  var f = document.forms[0];
  var radio = f.elements.sam_item;
  
  var samItem = 0;
  if(radio[1].checked) samItem = 1;
  var samId = f.elements.sam_id.value;
  var samIdObj = f.elements.sam_id;
  var samBA = f.elements.sam_codes.checked;
  
  samCode = ' [sam_ad id="' + samId + '"';
  if(samItem == 1) samCode += ' name="' + samIdObj.options[samIdObj.selectedIndex].text + '"';
  if(samBA == 1) samCode += ' codes="true"]';
  else samCode += ']';

  window.tinyMCE.activeEditor.execCommand('mceInsertContent', false, samCode);
  tinyMCEPopup.editor.execCommand('mceRepaint');
  tinyMCEPopup.close();
  return;
}

function insertSAMBlockCode() {
  
  var samCode;
  
  var f = document.forms[0];
  var radio = f.elements.sam_item;
  
  var samItem = 0;
  if(radio[1].checked) samItem = 1;
  var samId = f.elements.sam_id.value;
  var samIdObj = f.elements.sam_id;
  
  samCode = ' [sam_block id="' + samId + '"';
  if(samItem == 1) samCode += ' name="' + samIdObj.options[samIdObj.selectedIndex].text + '"';
  samCode += ']';
  
  window.tinyMCE.activeEditor.execCommand('mceInsertContent', false, samCode);
  tinyMCEPopup.editor.execCommand('mceRepaint');
  tinyMCEPopup.close();
  return;
}

tinyMCEPopup.onInit.add(init);