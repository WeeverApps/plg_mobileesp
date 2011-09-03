<?php
/*
*	MobileESP
* 	The MobileESP Project is Copyright 2010-2011, Anthony Hand
*
*	Plugin Author:		Robert Gerald Porter <rob@weeverapps.com>
*	Library Author:		Anthony Hand <http://code.google.com/p/mobileesp/>		
*	Version: 			0.9.3
*	License: 			GPL v3.0
*
*	This extension is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This extension is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details <http://www.gnu.org/licenses/>.
* 
*/

defined('_JEXEC') or die();

jimport('joomla.plugin.plugin');

require_once JPATH_PLUGINS.DS.'system'.DS.'mobileesp'.DS.'mdetect.php';


class plgSystemMobileESP extends JPlugin
{
	
	public function plgSystemMobileESP(& $subject, $config)
	{
	
		parent::__construct($subject, $config);
	
	}
	
	public function onAfterInitialise()
	{
	
		$session =& JFactory::getSession();
	
		// none of this if it's an RSS request, specific template, or 
		// component-only setting to play nice with Joomla devs & Weever
		if(JRequest::getVar('template') || JRequest::getVar('format') || JRequest::getVar('tmpl'))
			return;
			
		// kill the ignore_mobile session var if full=0 added to query
		if (JRequest::getVar('full') == '0')
		{
			$session->set( 'ignore_mobile', '' );
		}
	
		// if requesting the full site, ignore all this
		if(JRequest::getVar('full') > 0 || $session->get( 'ignore_mobile', '' ) == '1')
		{
			$session->set( 'ignore_mobile', '1' );
			return;
		} 
	
		switch($this->params->get('appService'))
		{
		
			case "weever":
			
				// no sense in doing anything if the component isn't installed
				if(!JComponentHelper::isEnabled('com_weever'))
					return;
					
				$settings = mobileESPWeeverHelper::getWeeverSettingsDB();
				
				if(mobileESPWeeverHelper::getAppEnabled($settings) == "0")
					return;

				$devices = mobileESPWeeverHelper::getDevices($settings);
				
				if(strstr($devices,","))
					$deviceList = explode(",",$devices);
				else
					$deviceList[] = $devices;
				
				$uagent_obj = new uagent_info();

				if(!$uagent_obj->DetectWebkit())
				{
					$session->set( 'ignore_mobile', '1' );
					return;
				}
				
				$weeverApp = false;
				
				foreach((array)$deviceList as $v)
				{
					if($v)
					{
						if($uagent_obj->$v())
							$weeverApp = true;	
					}
				}
				
				if($weeverApp == false)
				{
					$session->set( 'ignore_mobile', '1' );
					return;
				}
				
				$request_uri = $_SERVER['REQUEST_URI'];
				
				$request_uri = str_replace("?full=0","",$request_uri);
				$request_uri = str_replace("&full=0","",$request_uri);		
		
				if($request_uri && $request_uri != 'index.php' && $request_uri != '/'  )
					$exturl = '?exturl='.$request_uri;
				else
					$exturl = "";
					
				// requires version 0.9.2+ of com_weever	
				if($customAppDomain = mobileESPWeeverHelper::getCustomAppDomain($settings))
				{
				
					header('Location: http://'.$customAppDomain.$exturl);
					jexit();
				
				}
				else 
				{
											
					$siteDomain = mobileESPWeeverHelper::getPrimaryDomain($settings);
					header('Location: http://weeverapp.com/app/'.$siteDomain.$exturl);
					jexit();
					
				}
			
				break;
			
			default:
			
				if(!$this->params->get('forwardingEnabled', 0))
					return;
					
				$uagent_obj = new uagent_info();
					
				if(!$this->params->get('webkitOnly', 0) && (!$uagent_obj->DetectWebkit()))
				{
					$session->set( 'ignore_mobile', '1' );
					return;
				}
					
				$devices = $this->params->get('devicesForwarded', '');
				
				if(!$devices)
					return;
				
				$deviceList = explode(",",$devices);
				
				$forwardApp = false;
				
				foreach((array)$deviceList as $v)
				{
					if($uagent_obj->$v())
						$forwardApp = true;	
				}
				
				// devices on list not detected, let's not have it check again in this session
				
				// only send once, in case the redirect is to a landing page
				$session->set( 'ignore_mobile', '1' );
				
				if($forwardApp == false)
				{
					return;
				}
				
				header('Location: '.$this->params->get('forwardingUrl', ''));
			
				break;
		
		}
	
	
	}

}


class mobileESPWeeverHelper {

	static function getWeeverSettingsDB()
	{
	
		$db = &JFactory::getDBO();
			
		$query = 	"	SELECT	* ".
					"	FROM	#__weever_config ";
				
		$db->setQuery($query);
		$result = $db->loadObjectList();
		
		return $result;
	
	}
	
	// these functions need refactoring when I get the chance
	
	static function getPrimaryDomain($result)
	{
	
		foreach((array)$result as $k=>$v)
		{
			if($v->option == "primary_domain")
				return $v->setting;
		}
		
		return null;
	
	}
	
	static function getDevices($result)
	{
	
		foreach((array)$result as $k=>$v)
		{
			if($v->option == "devices")
				return $v->setting;
		}
		
		return null;
	
	}
	
	static function getAppEnabled($result)
	{
	
		foreach((array)$result as $k=>$v)
		{
			if($v->option == "app_enabled")
				return $v->setting;
		}
	
		return null;
	}
	
	static function getCustomAppDomain($result)
	{
	
		foreach((array)$result as $k=>$v)
		{
			if($v->option == "domain")
				return $v->setting;
		}
	
		return null;
	}

}