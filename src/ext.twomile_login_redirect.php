<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * =============================================================
 * For allowing customization of system behavior after
 * login and logout, by skipping the confirmation page and
 * taking the user to a specific location. 
 * 
 * Version:     2.0.0 (beta)
 * Authors:	Corey Snipes, Kevin Major, Noah Kuhn
 * Written:	3/31/2010
 * =============================================================
 */

class Twomile_login_redirect_ext
{
	
	// ----------------------------------
	// Class params
	// ----------------------------------
	
	var $settings		= array();
	var $ext_class		= "Twomile_login_redirect_ext";
	var $name		= "Twomile Login Redirect";
	var $version		= "2.1.1";
	var $description	= "Gives control over user destination after login or logout.";
	var $settings_exist	= "y";
	var $docs_url		= "";


	/**
	 * ----------------------------------------
	 * Constructor.
	 * 
	 * @author	Corey Snipes
	 *          <corey.snipes@twomile.com>
	 *          12/23/2007
	 * ----------------------------------------
	 */
	function Twomile_login_redirect_ext ($settings = "")
	{
		$this->EE = & get_instance();
		$this->settings = $settings;
	}
	// End Twomile_login_redirect_ext()


	/**
	 * ----------------------------------------
	 * For activating the extension.
	 * 
	 * @author	Corey Snipes
	 *          <corey.snipes@twomile.com>
	 *          12/23/2007
	 * ----------------------------------------
	 */
	function activate_extension ()
	{


		// ----------------------------------
		//  Default settings
		// ----------------------------------

		$default_settings = serialize(array(
			"display_confirmation_after_login"	=> "no"
			,"display_confirmation_after_logout"	=> "no"
			,"lastpage_destination"			=> "no"
			,"logout_lastpage_destination"		=> "no"
			,"login_page_url"			=> "/member/login"
			,"login_destination"			=> ""
			,"logout_destination"			=> ""
			)
		);

		// ----------------------------------
		//  Add custom processing to member_member_login_single
		// ----------------------------------
		
		$this->EE->db->query(
			$this->EE->db->insert_string(
				"exp_extensions", array(
				"extension_id" => "",
				"class"        => get_class($this),
				"method"       => "process_login",
				"hook"         => "member_member_login_single",
				"settings"     => $default_settings,
				"priority"     => 7,
				"version"      => $this->version,
				"enabled"      => "y"
				)
			)
		);
		
		// ----------------------------------
		//  Add custom processing to member_member_logout
		// ----------------------------------
		
		$this->EE->db->query(
			$this->EE->db->insert_string(
				"exp_extensions", array("extension_id" => "",
				"class"        => get_class($this),
				"method"       => "process_logout",
				"hook"         => "member_member_logout",
				"settings"     => $default_settings,
				"priority"     => 7,
				"version"      => $this->version,
				"enabled"      => "y"
				)
			)
		);

	}
	// End activate_extension()

	
	/**
	 * ----------------------------------------
	 * For upgrading the extension from a 
	 * prior version.
	 * 
	 * @author	Corey Snipes
	 *          <corey.snipes@twomile.com>
	 *          12/23/2007
	 * ----------------------------------------
	 */
	function update_extension ($current = "")
	{

		// ----------------------------------
		//  No adjustments needed
		// ----------------------------------

		return FALSE;
	}
	// End update_extension()


	/**
	 * ----------------------------------------
	 * For sending the user to a custom
	 * destination after login, and determining
	 * whether the confirmation page should
	 * be displayed.
	 * 
	 * @author	Corey Snipes
	 *          <corey.snipes@twomile.com>
	 *          12/23/2007
	 * ----------------------------------------
	 */   
	function process_login()
	{
		$url = NULL;
		
		//Set variables to stop it from throwing variable not set errors		
		if (empty($this->EE->session->tracker['1'])) $this->EE->session->tracker['1']="/";
		if (empty($this->EE->session->tracker['2'])) $this->EE->session->tracker['2']="/";

		// -------------------------------------------
		//  Set destination URL
		// -------------------------------------------

		// Check if 'last page' is set to yes
		if ($this->settings['lastpage_destination'] == "yes")
		{		
			//Check if login page url is defined and if it is also in the previous page url - if so go back 2 pages, else go back 1			
			if ($this->settings['login_page_url'] != ""){
				if ((stristr($this->EE->session->tracker['1'], $this->settings['login_page_url'])))
				{
					$url = $this->EE->session->tracker['2'];
					if ($url == 'index') { $url = '/'; }
				}
				else
				{
					$url = $this->EE->session->tracker['1'];
				}
			}
			else
			{
				$url = $this->EE->session->tracker['1'];
			}
		}
		else
		{
			$url = $this->settings["login_destination"];
		}
		if (strlen($url) < 1) $url = "/";
		
		// -------------------------------------------
		//  If skipping confirmation page, redirect here
		// -------------------------------------------

		if ($this->settings["display_confirmation_after_login"] != "yes")
		{
			$this->EE->functions->redirect($url);
		}
		
		// -------------------------------------------
		//  Otherwise, build the display output 
		// -------------------------------------------
		
		$data = array(	
			'title'		=> $this->EE->lang->line('mbr_login'),
			'heading'	=> $this->EE->lang->line('thank_you'),
			'content'	=> $this->EE->lang->line('mbr_you_are_logged_in'),
			'redirect'	=> $url,
			'link'		=> array($url, "")
		);
		$this->EE->output->show_message($data);
				
		// -------------------------------------------
		//  Return 
		// -------------------------------------------
		
		return FALSE;		
		
	}
	// End process_login()

	
	/**
	 * ----------------------------------------
	 * For sending the user to a custom
	 * destination after logout, and determining
	 * whether the confirmation page should
	 * be displayed.
	 * 
	 * @author	Corey Snipes
	 *          <corey.snipes@twomile.com>
	 *          12/23/2007
	 * ----------------------------------------
	 */
	function process_logout()
	{

		$url = NULL;
		
		// -------------------------------------------
		//  Set destination URL
		// -------------------------------------------

		// -------------------------------------------
		//  Logout destination handling
		//  Contributed by: Noah Kuhn - 1/13/2010
		//  http://noahkuhn.com/
		// -------------------------------------------
		
		// Check if 'logout last page' is set to yes
		if ($this->settings['logout_lastpage_destination'] == "yes")
		{
			$url = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			$url = $this->settings["logout_destination"];
		}


		if (strlen($url) < 1) $url = "/";
		
		// -------------------------------------------
		//  If skipping confirmation page, redirect here
		// -------------------------------------------

		if ($this->settings["display_confirmation_after_logout"] != "yes")
		{
			$this->EE->functions->redirect($url);
		}
		
		// -------------------------------------------
		//  Otherwise, build the display output 
		// -------------------------------------------
		
		$data = array(
			'title' 	=> $this->EE->lang->line('mbr_login'),
			'heading'	=> $this->EE->lang->line('thank_you'),
			'content'	=> $this->EE->lang->line('mbr_you_are_logged_out'),
			'redirect'	=> $url,
			'link'		=> array($url, "")
		);
		$this->EE->output->show_message($data);
				
		// -------------------------------------------
		//  Return 
		// -------------------------------------------
		
		return FALSE;
				
	}
	// End process_logout()


	/**
	 * ----------------------------------------
	 * For handling the settings for this 
	 * extension.
	 * 
	 * @author	Corey Snipes
	 *          <corey.snipes@twomile.com>
	 *          12/23/2007
	 * ----------------------------------------
	 */
	function settings()
	{
		$settings = array();
		$settings["display_confirmation_after_logout"] = array( "s", array( "no" => "no", "yes" => "yes" ), "yes" );
		$settings["logout_lastpage_destination"] = array( "s", array( "no" => "Specific Page (below)", "yes" => "Last Page Visited" ), "yes" );
		$settings["logout_destination"] = "";
		$settings["display_confirmation_after_login"] = array( "s", array( "no" => "no", "yes" => "yes" ), "yes" );
		$settings["lastpage_destination"] = array( "s", array( "no" => "Specific Page (below)", "yes" => "Last Page Visited" ), "yes" );
		$settings["login_destination"] = "";
		$settings["login_page_url"] = "";
		return $settings;
	}
	// End settings()

}
?>