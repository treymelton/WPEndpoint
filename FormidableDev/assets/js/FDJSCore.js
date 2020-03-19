/**
* @brief: handle all FDPlugin JS/JQuery requests
* @author: Trey Melton ( treymelton@gmailcom )
*/


/**
* @brief: given a search term, request data from the server
* @param: strElement - element to be updated after the request is complete
*/
function MakeUserRequest(strElement){
  if(typeof(window['FD_AjaxCore']) != "undefined"){
    var strPurpose = 'strPurpose';
    FD_AjaxCore.arrFDPayLoad[strPurpose] = 'endpointresult';
    var strResponse;
    if(strResponse = FD_AjaxCore.SendAjaxRequest(strElement)){
      FD_ShowSearchResults(strResponse,strElement);
    }
  }
  return true;
}

/**
* @brief: given return data, display it in a table
* @param - objResponse
* @param - strElementID - id of the element to build the table in
* @return void
*/
function FD_ShowSearchResults(objResponse,strElementID){
  var container = jQuery('#'+strElementID);
  container.html('');//reset the HTML
  if(objResponse.hasOwnProperty('title')){
    //make our title
    var strTitleDiv = ('<div class="table-responsive small"><h3 class="FD-title">'+objResponse.title+'</h3></div>');
    container.append(strTitleDiv);
    //start our table
    var objTable = jQuery('<table class="table table-striped table-bordered table-hover table-condensed">');
    //make our headings
    var objHeader = jQuery('<thead>');
    //headings row
    var objHeadingRow = jQuery('<tr>');
    var strTh;
    objResponse.data.headers.forEach(function(strHeaderValue){
      strTh += '<th>'+strHeaderValue+'</th>';
    });
    //append our headings
    objHeadingRow.append(strTh);
    objHeader.append(objHeadingRow);
    objTable.append(objHeader);
    //add our values
    var objTableBody = jQuery('<tbody>');
    var i = 1;
    for(i in objResponse.data.rows){
      var objTr = jQuery('<tr>');
      for (var key in objResponse.data.rows[i]) {
        if(key == 'date'){
          var date = new Date(objResponse.data.rows[i][key]*1000);
          objResponse.data.rows[i][key] = date.toDateString();
        }
        objTr.append('<td>' + objResponse.data.rows[i][key] + '</td>');
      }
      objTableBody.append(objTr);
    }
    //add the body to the table
    objTable.append(objTableBody);
    //ad the table
    container.append(objTable);
  }
  if(objResponse.hasOwnProperty('dateslug')){
    var strDateSlug = '<i>'+objResponse.dateslug+'</i>';
    container.append(strDateSlug);
  }
  if(objResponse.hasOwnProperty('messageslug')){
    var strMessageSlug = '<i>'+objResponse.messageslug+'</i>';
    container.append(strMessageSlug);
  }
  return true;
}
