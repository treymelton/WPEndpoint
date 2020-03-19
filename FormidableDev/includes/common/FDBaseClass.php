<?php
/**
* @class: FD_BaseClass
* @brief: standard unversal plugin functions
* @author: Trey Melton ( treymelton@gmail.com )
*/
  class FD_BaseClass{

    public function __construct() {
      //
    }
    // end __construct()

    /**
    * @brief verify a function is within the users capability
    * @param $boolDie - terminate immediately or just return boo: default FALSE
    * @return bool | DIE;
    */
    public static function FD_CanManage($boolDie=FALSE){
      if ( !current_user_can( 'manage_options' ) )  {
        if($boolDie)
          wp_die( __( 'You do not have sufficient permissions to access this feature or page.' ) );
        return FALSE;
      }
      return TRUE;
    }
  }
?>