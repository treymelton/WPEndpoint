<?php
/**
* @brief: FD_Database is a simple wrapper interface for $WPDB calls
* @requires - FDBaseClass.php
* @author: Trey Melton ( treymelton@gmail.com )
*/

  class FD_Database extends FD_BaseClass{

    public $WPDB;//hold the wpdb object

    public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new FD_Database();
		return( $inst );
    }

    public function __construct() {
      global $wpdb;
      //load wpdb locally for simplicity
      $this->WPDB = $wpdb;
    }
    // end __construct()


    /**
     * Given a string, prepare it for entry into a database and return db-safe
     * string.
     *
     * Currently calls mysql_real_escape_string on the htmlentities()'d string.
     * NOTE: any data which requires entities to be decoded will have to be
     * decoded in the data's containing class.
     *
     * @param $strvar
     * @param $boolEncode - encode HTML special chars
     * @param $boolNl2br - use nl2br filtering in text
     * @return string
     */
    function safe($strvar, $boolEncode = false, $boolNl2br = false)
    {
      // needs a flag because we don't want to do this for all strings
      if($boolNl2br === true)
        $strvar = nl2br($strvar);

      if($boolEncode === true)
      {
        $strvar = htmlspecialchars($strvar, ENT_COMPAT, 'UTF-8', false);
      }
      if(!get_magic_quotes_gpc())
      {
        $strvar = addslashes($strvar);
      }
      else
      {
        $strvar = addslashes($strvar);
      }
      if(trim($strvar) == '')
          $strvar = NULL;
      return $strvar;
    } // end function safe()

    /**
    * @brief: check to see if a table exists
    * @param: $strTable
    * @return bool
    */
    function CheckForTable($strTable){
      if($strTable == '')
          return TRUE;//DO NOT OVERWRITE TABLES
      if($this->WPDB->get_var("SHOW TABLES LIKE 'fd_".$strTable."'") != 'fd_'.$strTable)
       return FALSE;
      return TRUE;
    }

    /**
    * @brief: Drop a table
    * @param: $strTable
    * @return bool
    */
    function DropTable($strTable){
      if($strTable == '')
          return TRUE;//DO NOT OVERWRITE TABLES
      if($this->WPDB->query("DROP TABLE fd_".$strTable))
       return TRUE;
      return FALSE;
    }



    /**
    * @brief:create a table
    * @return bool
    */
    function CreateTable($strQuery){
      $charset_collate = $this->WPDB->get_charset_collate();
      $strQuery = str_replace('%TABLE%',$this->WPDB->prefix,$strQuery);
      $strQuery = str_replace('%COLLATE%',$charset_collate,$strQuery);
      require_once( ABSPATH . 'wp-admin'.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'upgrade.php' );
      return dbDelta($strQuery);
    }

    /**
    * @brief: get the last insert ID for queries
    * @return last insert id
    */
    function GetLastInsertId(){
      return $this->WPDB->insert_id;
    }

    /**
    * @brief: given a request result insert it
    * @param: $objRequestResult
    * @return int ( last insert ID )
    */
    function InsertRequestResult($objRequestResult){
      $strQuery = 'INSERT INTO fd_requests(requestparams,'.
                                    'requestresult,'.
                                    'entrydate,'.
                                    'expiredate) VALUES("'.
                                                       $this->safe($objRequestResult->strRequestParams).'","'.
                                                       $this->safe($objRequestResult->strRequestResult).'","'.
                                                       $this->safe((int)$objRequestResult->intEntryDate).'","'.
                                                       $this->safe((int)$objRequestResult->intExpireDate).'")';
         if($this->WPDB->query($strQuery)){
             return $this->WPDB->insert_id;
         }
         else{
             FD_Logger::Get()->FD_LogMessage('Cannot insert ['.$strQuery.']',__METHOD__,__LINE__,3);
             return FALSE;
         }
    }


    /**
    * @brief: update a request record for returning users with an ID
    * @param - $objRequestResult
    * @return bool
    */
    function UpdateRequestResult($objRequestResult){
       $strQuery = 'UPDATE fd_requests SET requestparams = "'.$this->safe($objRequestResult->strRequestParams).'",'.
                                   'requestresult = "'.$this->safe($objRequestResult->strRequestResult).'",'.
                                   'entrydate = "'.$this->safe($objRequestResult->intEntryDate).'",'.
                                   'expiredate = "'.$this->safe($objRequestResult->intExpireDate).'"'.
                                   ' WHERE requestid = '.$objRequestResult->intRequestId;
         if($results = $this->WPDB->query($strQuery)){
             return TRUE;
         }
         else{
             FD_Logger::Get()->FD_LogMessage('Cannot update ['.$strQuery.']',__METHOD__,__LINE__,3);
             return FALSE;
         }
    }

    /**
    * @brief: get the last end point request
    * @return result || FALSE
    */
    function GetLastRequest(){
      $strQuery = 'SELECT * FROM fd_requests ORDER BY expiredate DESC';
      $strQuery .= ' LIMIT 1';
      if($results = $this->WPDB->get_results($strQuery,ARRAY_A)){
         return $results;
      }
      else{
         return FALSE;
      }
    }

    /**
    * @brief: get all active requests for reporting purposes
    * @param $strArguments - optional
    * @return result || FALSE
    */
    function GetActiveRequestResults($strArguments=''){
      $strQuery = 'SELECT * FROM fd_requests WHERE expiredate < NOW()';
      if(trim($strArguments) != '')
        $strQuery .= ' AND requestparams = "'.$this->safe($strArguments).'"';
      $strQuery .= ' LIMIT 1';
      if($results = $this->WPDB->get_results($strQuery,ARRAY_A)){
         return $results;
      }
      else{
         return FALSE;
      }
    }
  }
?>