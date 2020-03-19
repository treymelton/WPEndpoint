<?php
/**************************************************
* Class :FD_DataRequest
* @brief Well-formed object declaration of the 'fd_dataRequest' database table.
***************************************************/
class FD_DataRequest  extends FD_BaseClass{
    public $intRequestId;// int(11) NOT NULL AUTO_INCREMENT
    public $strRequestParams;// varchar(150) NULL
    public $strRequestResult;// text
    public $intEntryDate;// int(12) NOT NULL
    public $intExpireDate;// int(12) NOT NULL

  function __construct(){
  //construct
  }

  public static function Get(){
    //==== instantiate or retrieve singleton ====
    static $inst = NULL;
    if( $inst == NULL )
      $inst = new FD_DataRequest();
    return( $inst );
  }

  /**
  * @brief: load our object with results from the query
  * @return bool
  */
  public function LoadObjectWithArray($arrArray){
    //we need to make this fit for the locaor logic
     $this->intRequestId = (int)$arrArray['requestid'];
     $this->strRequestParams = (string)stripslashes($arrArray['requestparams']);
     $this->strRequestResult = (string)stripslashes($arrArray['requestresult']);
     $this->intEntryDate = (int)stripslashes($arrArray['entrydate']);
     $this->intExpireDate = (int)stripslashes($arrArray['expiredate']);
     return TRUE;
  }

}//end class FD_DataRequest
?>