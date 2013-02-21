<?php
/*
*	MobileESP
* 	The MobileESP Project is Copyright 2010-2012, Anthony Hand
*
*	Plugin Author:		Robert Gerald Porter <rob@weeverapps.com>
*	Library Author:		Anthony Hand <http://code.google.com/p/mobileesp/>		
*	Version: 			1.4
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

class plgSystemMobileESPInstallerScript
{ 

	public function install( $parent ) 
	{ 

		$db 				= JFactory::getDbo();
		$tableExtensions 	= $db->nameQuote("#__extensions");
		$columnElement   	= $db->nameQuote("element");
		$columnType      	= $db->nameQuote("type");
		$columnEnabled   	= $db->nameQuote("enabled");
		 
		$db->setQuery("UPDATE $tableExtensions SET $columnEnabled=1 WHERE $columnElement='mobileesp' AND $columnType='plugin'");
		$db->query();
		
		echo "TEST";
		  
	} 
  
}
?>