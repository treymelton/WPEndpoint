<?php
/**
* @class: FD_PluginInstall
* @brief: plugin installation functions
* @requires - FD_BaseClass.php
* @requires - FD_Database.php
* @author: Trey Melton ( treymelton@gmail.com )
*/

  class FD_PluginInstall extends FD_BaseClass{

    public static function Get(){
	  //==== instantiate or retrieve singleton ====
	  static $inst = NULL;
	  if( $inst == NULL )
		$inst = new FD_PluginInstall();
	  return( $inst );
    }

    public function __construct() {
    }
    // end __construct()

    /**
    * check to see if PC plugin has been installed
    * @return bool
    */
    public function CheckForInstall(){
      return get_option('FDPlugin_Activation');
    }

    /**
    * @brief: activate the plugin
    */
    public static function FD_ActivatePlugin(){
      FD_Logger::Get()->FD_LogMessage('Activating Plugin....',__METHOD__,__LINE__,1);
      $boolFreshInstall = FALSE;
      if(!get_option('FDPlugin_Activation')){
        add_option( 'FDPlugin_Activation', time(),NULL,'yes' );
        $boolFreshInstall = TRUE;
      }
      //mark deactivation so we know we've been here if they change their mind
      if(get_option('FDPlugin_Deactivation')){
          delete_option('FDPlugin_Deactivation');
          $boolFreshInstall = FALSE;//don't reinstall core packages
      }
      if ( is_admin() ){
      //figure out if we're updating or installing
        if($boolFreshInstall){
          FD_Logger::Get()->FD_LogMessage('Installing core components....',__METHOD__,__LINE__,1);
          if(!FD_PluginInstall::Get()->FD_InstallCoreFeatures()){
              delete_option('FDPlugin_Activation');
              FD_Logger::Get()->FD_LogMessage('Cannot install core features. Exiting.',__METHOD__,__LINE__,1);
              return FALSE;
          }
        }
        //we have our initial install
        FD_Logger::Get()->FD_LogMessage('Plugin activation complete....',__METHOD__,__LINE__,1);
        return TRUE;
      }
      return FALSE;
    }

    /**
    * deactivate the plugin
    */
    function FD_DeActivatePlugin(){
      FD_Logger::Get()->FD_LogMessage('Deactivating Plugin....',__METHOD__,__LINE__,1);
      delete_option('FDPlugin_Activation');
      add_option( 'FDPlugin_Deactivation', time(),NULL,'yes' );
      return TRUE;
    }

    /**
    * deactivate the plugin
    */
    function FD_UninstallPlugin(){
      delete_option('FDPlugin_Deactivation');
      FD_PluginInstall::Get()->FD_UninstallFDPlugin();
      return TRUE;
    }

    /**
    * @brief: in the event we have more than one table to create or other dependencies
    * @return bool
    */
    function FD_InstallCoreFeatures(){
      if(!FD_PluginInstall::Get()->FD_MakeRequestTable())
        return FALSE;
      return TRUE;
    }


    /**
    * @brief: in the event we have more than one table to remove or other dependencies
    * @return bool
    */
    function FD_UninstallFDPlugin(){
      if(FD_Database::Get()->CheckForTable('dataRequest')){
        if(!FD_Database::Get()->DropTable('dataRequest'))
          return FALSE;
      }
      return TRUE;
    }


    /**
    * @brief: make our request storage table. We do this instead of using
    * session to store a key in the browser cookies for persistent timing controls
    * @return bool
    */
    function FD_MakeRequestTable(){
      if(!FD_Database::Get()->CheckForTable('requests')){
        $strQuery = "CREATE TABLE fd_requests (
                      requestid int(11) NOT NULL AUTO_INCREMENT,
                      requestparams varchar(150) NULL,
                      requestresult text,
                      entrydate int(12) NOT NULL,
                      expiredate int(12) NOT NULL,
                      PRIMARY KEY (requestid)
                      ) %COLLATE%;";
        return  FD_Database::Get()->CreateTable($strQuery);
      }
      return TRUE;
    }

  }
?>