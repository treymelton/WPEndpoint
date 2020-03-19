<?php
/*/
 * @link              https://progressivecoding.net
 * @since             1.1.1
 * @package           FormidableDev
 * @wordpress-plugin
 * Plugin Name:       FormidableDev
 * Plugin URI:        https://progressivecoding.net
 * Description:       A simple Ajax handler and plugin manager
 * License:           GPL-2.0+
 * Version:           1.1.1
 * Author:            Trey Melton
 * Author URI:        https://progressivecoding.net
 * Text Domain:       progressivecoding
 * Domain Path:       /FormidableDev
 /*/
  // If this file is called directly, abort.
  if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
  	die;
                                             
  //include our required libraries
  require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'FDRequires.php');
  register_activation_hook( __FILE__ , array('FD_PluginInstall','FD_ActivatePlugin'));
  register_deactivation_hook( __FILE__, array('FD_PluginInstall','FD_DeActivatePlugin'));
  register_uninstall_hook( __FILE__, array('FD_PluginInstall','FD_UninstallPlugin'));
  //if the plugin is activated register short codes
  if(FD_PluginInstall::Get()->CheckForInstall()){
    RegisterShortCodes();
  }

  /**
  * register the shortcodes
  * @return bool
  */
  function RegisterShortCodes(){
    //register our hooks
    FD_PluginCore::Get()->FD_SpecialHookRegister();
    //===========================================================================
    //=========              shortcode functions                         ========
    //=========          add the shortcodes for interfacing              ========
    //===========================================================================
    add_shortcode( 'FD_MakeRequest', 'FD_MakeRequest' );
    return TRUE;
  }

  /**
  * make the shortcode form for search data
  * @return void
  */
  function FD_MakeRequest(){
    FD_PluginCore::Get()->FD_MakeEndpointForm();
  }

?>