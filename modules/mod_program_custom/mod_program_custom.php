<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_custom
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once ( JPATH_ROOT.'/api/utils.php' );

if(strpos($module->content, "<?php") !== false)
{
	eval("?>".$module->content."<?'php';");
}

require JModuleHelper::getLayoutPath('mod_program_custom', $params->get('layout', 'default'));
