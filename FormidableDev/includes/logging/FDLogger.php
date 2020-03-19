<?php
/*error_reporting (E_ALL ^ E_WARNING ^ E_PARSE ^ E_COMPILE_ERROR ^ E_NOTICE);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . DIRECTORY_SEPARATOR.'..'. DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Logs'.DIRECTORY_SEPARATOR.'error.log');*/

/**
* @class:FD_Logger
* @brief: Log internal dev messages. This will most likely be used when failures occur for debugging
* @author: Trey Melton ( treymelton@gmail.com )
*/

  class FD_Logger extends FD_Utility{

    public $arrLogType = array();//hold the log types for display puposes
    public $arrMessages = array();
    public $arrFailures = array();


    public static function Get(){
	  //==== instantiate or retrieve singleton ====
	  static $inst = NULL;
	  if( $inst == NULL )
		$inst = new FD_Logger();
	  return( $inst );
    }

    public function __construct() {
      //load our log types array
      $this->arrLogType[1] = 'Info';
      $this->arrLogType[2] = 'Success';
      $this->arrLogType[3] = 'Error';
      $this->arrLogType[4] = 'Warning';
    }
    // end __construct()

    /**
    * given a user message deliver it to the admin console
    * @param $strMessage
    * @param $intType - type of message
    */
    public static function FD_AdminMessage($strMessage,$intType=1){
      if(trim($strMessage) != ''){
        return '<div class="notice notice-'. strtolower(FD_Logger::Get()->arrLogType[$intType]) .' is-dismissible"><p>'. $strMessage.'</p></div>';
      }
      return TRUE;
    }

    /**
    * @brief: form the backtrace for addition in the log
    * @return string
    */
    public static function FD_FormBackTrace(){
      $arrBackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
      $strBreak = "\r\n";
      $strArguments = '';
      $strBackTrace = '';
      foreach($arrBackTrace as $ka=>$va){
        $strBackTraceFile = 'File ['.$va['file'].']'.$strBreak;
        if(array_key_exists('args',$va) && is_array($va['args'])){
          foreach($va['args'] as $kb=>$vb){
            if(!is_object($vb))
              $strArguments .= '<pre>'.$vb.'</pre>,';
            else{
              $strObjectVariables = var_export($vb,TRUE);
              $strArguments .= '[OBJECT]'.$strBreak.'<pre>'.$strObjectVariables.'</pre>,'.$strBreak;
            }
          }
        }
        $strBackTrace .= 'Line ['.$va['line'].'] '.$va['class'].'->'.$va['function'].'('.$strArguments.')'.$strBreak;
      }
      return $strBackTraceFile.$strBackTrace;

    }//end FD_FormBackTrace


    /**
     * @brief:Given an error string  attempt to open the debug log and
     * append the error string with a timestamp.
     *
     * @access public
     * @param- $strError - Error Message
     * @return bool
     */
    public function FD_LogMessage($strError, $strMethod, $intLine, $intType=1, $boolBacktrace=FALSE){
      //our log folder location
      $strLogFolder = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Logs'.DIRECTORY_SEPARATOR;
      if(!FD_Utility::FD_VerifyFolder($strLogFolder, TRUE)){
        //can't log the message and the message may be sensitive so it cannot be printed on the screen
        return FALSE;
      }
      //get our log file
      $strLogHandle = ($strLogFolder.DIRECTORY_SEPARATOR.'LOG_'.date('Y_m_d',time()).'.txt');
      $arrLastError = error_get_last();
      $strLastError = '';
      if(is_array($arrLastError) && sizeof($arrLastError) > 0)
        $strLastError = var_export($arrLastError,TRUE);
      if(! ( $objLogHandle = fopen($strLogHandle,'a+') ) ){

      }
      else{
        //write our message
        fwrite($objLogHandle,"\r\n----------------------[".date('r')."]:----------------------\r\n ".
                   "[Script]: " .$_SERVER['SCRIPT_NAME'].
                   "[Method]: " .  $strMethod . "\r\n".
                   "[Line]: " .  $intLine . "\r\n".
                   "[".$this->arrLogType[$intType]."]:".$strError . "\r\n".
                   "[LastError]: ".$strLastError. " \r\n");
        if($boolBacktrace){
          fwrite($objLogHandle,"\r\n___________________[Start Backtrace]___________________\r\n ".
                   "[" . self::FD_FormBackTrace()."]: " .
                   "\r\n~~~~~~~~~~~~~~~~~~~~~~[END Backtrace]~~~~~~~~~~~~~~~~~~~~~~\r\n ");
        }
        fwrite($objLogHandle,"\r\n###################[End Log Entry]###################\r\n ");
        fclose($objLogHandle);
      }
      return TRUE;
    } // end Debug_log()
  }
?>