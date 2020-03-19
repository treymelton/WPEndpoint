/**
* @brief: handle all FDPlugin Ajax requests
* @author: Trey Melton ( treymelton@gmailcom )
*/


/**
* @brief handle ajax calls from the front end
* @param strSearchValue
* @return bool
*/
var FD_AjaxCore = {
    strElementUpdateId:'',
    arrFDPayLoad:{},
    varAjaxReturn:'',
    objResult:null,//this will be the element we update
    /**
    *   make an ajax call
    *   @param arrFDPayLoad - Simple package of variables to search for
    *       -string curl formed string
    *       -form data direct POST/GET
    *   @return bool
    */
    SendAjaxRequest:function(strElementUpdateId){
      //can't make empty requests
      if( this.arrFDPayLoad.length < 1 ){
        console.log( 'Ajax request failed.');
        return false;
      }
      //update our local variable for scope purposes
      this.strElementUpdateId = strElementUpdateId;
      //send our content
    	this.objResult = jQuery.ajax({
    		type: "post",
            url: "/wp-admin/admin-ajax.php",
            data: { action: 'FD_AjaxHandler', FD_payload: this.arrFDPayLoad},
            async: false
    	}); //close jQuery.ajax(
      return jQuery.parseJSON(this.objResult.responseText);
    }
};