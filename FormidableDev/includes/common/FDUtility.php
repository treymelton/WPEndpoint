<?php
/**
* class: FD_Utility
* @brief: Abstract functions for all modules to share
* @requires - FD_BaseClass.php
* @author: Trey Melton ( treymelton@gmail.com )
*/

  class FD_Utility extends FD_BaseClass{

    public static function Get(){
	  //==== instantiate or retrieve singleton ====
	  static $inst = NULL;
	  if( $inst == NULL )
		$inst = new FD_Utility();
	  return( $inst );
    }

    public function __construct() {
      //
    }
    // end __construct()

    /**
    * @brief: write file content for logging or plugin file creation
    * @return bool
    */
    public static function FD_WriteData($strFile,$strContent){
      if($objFileHandle = fopen($strFile,"a+")){
        fwrite($objFileHandle,$strContent);
        fclose($objFileHandle);
        return TRUE;
      }
      return FALSE;
    }


    /**
    * @brief: verify a folder exists
    * @param - $strFolder
    * @param - $boolMakeDir - if it dos not already exist
    * @return bool
    */
    public static function FD_VerifyFolder($strFolder, $boolMakeDir=FALSE){
      if(!is_dir($strFolder)){
        if($boolMakeDir){
          return mkdir($strFolder);
        }
        return FALSE;//dir not exist
      }
      return TRUE;
    }
    
  }//end class

?>