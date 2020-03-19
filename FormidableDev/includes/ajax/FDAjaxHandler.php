<?php
/**
* @class: FD_AjaxHandler
* @brief: Handle ajax proxy functions
* @requires - FD_Utility.php
* @requires - FD_Database.php
* @author: Trey Melton ( treymelton@gmail.com )
*/
  define('FD_ENDPOINT','http://api.strategy11.com/wp-json/challenge/v1/1');
  class FD_AjaxHandler extends FD_Utility{

    public $objRequestResult = FALSE;
    public $arrFDPayLoad = array();

    public static function Get(){
	  //==== instantiate or retrieve singleton ====
	  static $inst = NULL;
	  if( $inst == NULL )
		$inst = new FD_AjaxHandler();
	  return( $inst );
    }

    public function __construct() {
    //
    }
    // end __construct()

    /**
    * @brief: given a request from the admin side, determine the purpose and execute
    * @param - NONE
    * @return JsonResult
    */
    public static function FD_AdminAjaxHandler(){
      //load incoming payload
      //no params at this point, but that may change, so we're sanitizing
      FD_AjaxHandler::Get()->arrFDPayLoad = FD_AjaxHandler::Get()->SanitizePayload();
      if(FD_AjaxHandler::Get()->LoadRequestResults()){
        FD_AjaxHandler::Get()->PackReturnPayload();
      }
      else{
        $strMessage = 'Something went wrong. Please try again later ['.__LINE__.']';
        echo json_encode(array('messageslug'=>FD_Logger::Get()->FD_AdminMessage($strMessage,3)));
        wp_die();
      }
    }

    /**
    * @brief: given a request from a user, determine the purpose and execute
    * @param - NONE
    * @return JsonResult
    */
    public static function FD_UserAjax(){
      //load incoming payload
      //no params at this point, but that may change, so we're sanitizing
      FD_AjaxHandler::Get()->arrFDPayLoad = FD_AjaxHandler::Get()->SanitizePayload();
      if(FD_AjaxHandler::Get()->LoadRequestResults()){
        FD_AjaxHandler::Get()->PackReturnPayload();
      }
      else{
        $strMessage = 'Something went wrong. Please try again later ['.__LINE__.']';
        echo json_encode(array('messageslug'=>FD_Logger::Get()->FD_AdminMessage($strMessage,3)));
        wp_die();
      }
    }

    /**
    * @brief: given a request from a guest, determine the purpose and execute
    * There may be a need for further restrictions in the data return or in what
    *  is allowed, so we'll make this call seperate
    * @param - NONE
    * @return JsonResult
    */
    public static function FD_GuestAjax(){
      //load incoming payload
      //no params at this point, but that may change, so we're sanitizing
      FD_AjaxHandler::Get()->arrFDPayLoad = FD_AjaxHandler::Get()->SanitizePayload();
      if(FD_AjaxHandler::Get()->LoadRequestResults()){
        FD_AjaxHandler::Get()->PackReturnPayload();
      }
      else{
        $strMessage = 'Something went wrong. Please try again later ['.__LINE__.']';
        echo json_encode(array('messageslug'=>FD_Logger::Get()->FD_AdminMessage($strMessage,3)));
        wp_die();
      }
    }

    /**
    * @brief: get the last request results
    * @return string ( Json ) || FALSE
    */
    function GetLastRequestResults(){
      if(!FD_AjaxHandler::Get()->VerifyRequestTable()){
        return FALSE;
      }
      //standard user can only see once an hour reporting
      //Admins can refesh and ALWAYS get fresh reporting
      if(($arrResults = FD_Database::Get()->GetActiveRequestResults()) && !FD_Database::Get()->FD_CanManage()){
        FD_DataRequest::Get()->LoadObjectWithArray($arrResults[0]);
        FD_AjaxHandler::Get()->objRequestResult = FD_DataRequest::Get();
        return TRUE;
      }
      else{
        //no results that have not expired, look for ANY in history
        if($arrResults = FD_Database::Get()->GetLastRequest()){
          FD_DataRequest::Get()->LoadObjectWithArray($arrResults[0]);
          FD_AjaxHandler::Get()->objRequestResult = FD_DataRequest::Get();
          return FALSE;
        }
        else{
          //none exists
          FD_AjaxHandler::Get()->objRequestResult = 0;
          return FALSE;
        }
      }
    }

    /**
    * load the request results
    * @return bool
    */
    function LoadRequestResults(){
      if(!FD_AjaxHandler::Get()->GetLastRequestResults()){
        if(FD_AjaxHandler::Get()->objRequestResult === FALSE){//table not created. We cannot control requests
          echo 'false ['.FD_AjaxHandler::Get()->objRequestResult.']';
          wp_die();
        }
        else{
          $arrResults = FD_AjaxHandler::Get()->MakeQuickCURL();
          if(FD_AjaxHandler::Get()->objRequestResult === 0){
            //We do not yet have a table entry
            FD_AjaxHandler::Get()->objRequestResult = new FD_DataRequest();
          }
          return FD_AjaxHandler::Get()->LoadNewFD_DataRequest($arrResults['result']);
        }
      }
      return TRUE;
    }

    /**
    * @brief: load our new request table object with results
    * @param:$strResults
    * @return bool
    */
    function LoadNewFD_DataRequest($strResults){
     //load our object
     FD_AjaxHandler::Get()->objRequestResult->strRequestParams = json_encode(FD_AjaxHandler::Get()->arrFDPayLoad);
     FD_AjaxHandler::Get()->objRequestResult->strRequestResult = $strResults;
     FD_AjaxHandler::Get()->objRequestResult->intEntryDate = time();
     FD_AjaxHandler::Get()->objRequestResult->intExpireDate = (time()+3600);
     if((int)FD_AjaxHandler::Get()->objRequestResult->intRequestId < 1)
        return FD_Database::Get()->InsertRequestResult(FD_AjaxHandler::Get()->objRequestResult);
     else
        return FD_Database::Get()->UpdateRequestResult(FD_AjaxHandler::Get()->objRequestResult);
    }

    /**
    * @brief: sanitize our payload and parse it into an array
    * @param $strPayload
    * @return array || FALSE
    */
    function SanitizePayload(){
      $arrValues = filter_var_array($_POST,FILTER_SANITIZE_STRING);
      $arrPayload = filter_var_array($arrValues['FD_payload'],FILTER_SANITIZE_STRING);
      return $arrPayload;
    }

    /**
    * @brief package our return into a JSON object for return to the client
    * @param $arrPayload
    * @return sting ( JSON )
    */
    function PackReturnPayload(){
      if(FD_Database::Get()->FD_CanManage()){
        $arrResponse = json_decode(FD_AjaxHandler::Get()->objRequestResult->strRequestResult);
        $arrResponse->dateslug = 'Last updated on '.date('m-d-y h:i:s',FD_AjaxHandler::Get()->objRequestResult->intEntryDate);
        $arrResponse->dateslug .= '<br />  Expires at '.date('m-d-y h:i:s',FD_AjaxHandler::Get()->objRequestResult->intExpireDate);
        //repackage
        FD_AjaxHandler::Get()->objRequestResult->strRequestResult = json_encode($arrResponse);
      }
      echo FD_AjaxHandler::Get()->objRequestResult->strRequestResult;//return our payload
      wp_die();
    }

    /**
    * @brief: check the session for requests made recently before posting to our endpoint
    * @return bool
    */
    function VerifyRequestTable(){
      return FD_Database::Get()->CheckForTable('requests');
    }

    /**
    * given a search request, send it via CURL to our endpoint
    * @return array ( $varResponse, $arrHeaders)
    */
    function MakeQuickCURL(){
      $arrResponse = array();
      $strPayload = json_encode(FD_AjaxHandler::Get()->arrFDPayLoad);
      $objCURL = curl_init();
      curl_setopt($objCURL, CURLOPT_URL, FD_ENDPOINT);
      curl_setopt($objCURL, CURLOPT_TIMEOUT, 30);
      curl_setopt($objCURL, CURLOPT_RETURNTRANSFER,1);
      curl_setopt($objCURL, CURLOPT_POSTFIELDS, $strPayload);
      curl_setopt($objCURL, CURLOPT_CUSTOMREQUEST, "GET");
      curl_setopt($objCURL, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($strPayload))
      );
      $arrResponse['result'] = curl_exec ($objCURL);
      $arrResponse['headers'] = curl_getinfo($objCURL);
      curl_close ($objCURL);
      return $arrResponse;
    }

  }
?>