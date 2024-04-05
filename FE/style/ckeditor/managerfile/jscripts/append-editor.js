function selectFile( url ){
	var selectedFileRowNum = $('#selectedFileRowNum').val();
  if(selectedFileRowNum != '' && $('#row' + selectedFileRowNum)){
	  // insert information now
	  //var url = $('#fileUrl'+selectedFileRowNum).val();  	
		//alert(url)
		//i want to get relative url
		if( url.match(/https?:\/\/[^\/]*\/(.*)$/i) ){
			url = "/"+RegExp.$1;
		}	
		window.opener.document.getElementById("txtUrl").value = url;
		window.close() ;
	}else{
  	alert(noFileSelected);
  }
}



function cancelSelectFile(){
  window.close() ;
}