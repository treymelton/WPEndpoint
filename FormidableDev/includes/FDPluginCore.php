<?php
/**
* @class: FD_PluginCore
* @brief: Central codebehind plugin functionality
* @requires - FDBaseClass.php
* @requires - FDLogger.php
* @requires - FDAjaxHandler.php
* @author: Trey Melton ( treymelton@gmail.com )
*/

  class FD_PluginCore extends FD_BaseClass{


    public static function Get(){
	  //==== instantiate or retrieve singleton ====
	  static $inst = NULL;
	  if( $inst == NULL )
		$inst = new FD_PluginCore();
	  return( $inst );
    }

    public function __construct() {
      //Do nothing
    }
    // end __construct()


    /**
    * since we have a bevy of initial hook wordpress loves to use, we need to
    * initiate and execute first run actions now. functions.php CANNOT be trusted.
    */
    public function FD_SpecialHookRegister(){
      //============ Add Actions ===============//
      add_action( 'admin_menu', array(&$this,'FD_MakeAdminMenuOption'),1 );
      add_action( 'user_admin_menu', array(&$this,'FD_MakeAdminMenuOption'),1 );
      add_action( 'wp_enqueue_scripts', array(&$this,'FD_EnqueueScripts'),99 );  //
      add_action( 'admin_enqueue_scripts', array(&$this,'FD_EnqueueScripts'),99 );  //
      add_action( 'admin_notices', array('FD_Logger','FD_AdminMessage') );
      //add ajax hooks
      if ( is_admin() ) {
        add_action( 'wp_ajax_FD_AjaxHandler', array('FD_AjaxHandler','FD_AdminAjaxHandler') );
        add_action( 'admin_footer', array(&$this,'FD_MakeAdminRequest') );
      }
      else{
        add_action( 'wp_ajax_FD_AjaxHandler', array('FD_AjaxHandler','FD_UserAjax') );
      }
      //no priv is different
      add_action( 'wp_ajax_nopriv_FD_AjaxHandler', array('FD_AjaxHandler','FD_GuestAjax') );
      return TRUE;
    }

    /**
    * @brief: load this in the footer for admins
    */
    function FD_MakeAdminRequest(){  ?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) {
        MakeUserRequest('FD_endpointresults');
	});
	</script> <?php
    }

    /**
    * enqueue our styles and scripts
    * @return bool
    */
    function FD_EnqueueScripts(){
      //enque styles
      wp_enqueue_style( 'FD_css', plugin_dir_url( __FILE__ ).'../assets/css/FDcss.css');
      wp_enqueue_style( 'bootstrap4.0.0', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',array(),'4.0.0');

      //enque JS
      wp_enqueue_script('popper1.12.9','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js',array('jquery'),'1.12.9');
      wp_enqueue_script('bootstrap4.0.0','https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js',array('jquery'),'4.0.0');
      wp_enqueue_script('FD_jscore',plugin_dir_url( __FILE__ ).'../assets/js/FDJSCore.js',array(),'');
      wp_enqueue_script('FD_ajaxcore',plugin_dir_url( __FILE__ ).'../assets/js/FDAjaxCore.js',array(),'');
      if ( !is_admin() ) {
        wp_enqueue_script('FD_getrequest',plugin_dir_url( __FILE__ ).'../assets/js/FDGetRequests.js',array(), false, true);
      }
      return TRUE;
    }


    /**
    * @brief: To access the options we'll ad a menu option
    * @return bool
    */
    function FD_MakeAdminMenuOption(){
      //load our Admin menu option
      add_menu_page( 'FormiddableDev', 'FormiddableDev', 'manage_options', 'FDPluginAdmin', array(&$this, 'FD_MakeAdminOptions'), 'dashicons-carrot', 20 );
      return TRUE;
    }

    /**
    * @brief make the admin page
    * @return string - options
    */
    public function FD_MakeAdminOptions(){
      $strDiv = '<div class="wrap col-md-12">';
      $strDiv .= '<h1>Formiddable Dev Admin Page</h1>';
      $strDiv .= '<hr />';
      $strDiv .= '<div id="FD_endpointresults"></div>';
      $strDiv .= '    <button onclick="MakeUserRequest(\'FD_endpointresults\')"';
      $strDiv .= '        class="btn btn-success">Refresh</button>';
      $strDiv .= '</div>';
      echo $strDiv;
    }


    /**
    * @brief make the admin page
    * @return string - options
    */
    public function FD_MakeEndpointForm(){
      $strDiv = '<div class="wrap col-md-12">';
      $strDiv .= '<h1>Formiddable Dev End point results</h1>';
      $strDiv .= '<hr />';
      $strDiv .= '<div id="FD_endpointresults"></div>';
      $strDiv .= '</div>';
      echo $strDiv;
    }

  }
?>