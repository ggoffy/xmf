<?php

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Permissions handles a permission map with structured methods.
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU private license
 * @package         Xmf_Mvc
 * @since           1.0
 * @author          Richard Griffith
 */


/**
 * PermissionMap handles a permission map with structured methods.
 * The Permissions object can:
 * - build a permission map with structured methods
 * - store a map in the current Config
 *
 * Applications should not rely on the internal map format, and only
 * rely on the provided interfaces.
 *
 * At present the map is a simple array following this format:
 * @code
 * array(
 * 	'Namespace1' => array(
 *  	'title'=> '(language constant - display title for permission form)',
 * 		'desc'=>  '(language constant - description for permission form)',
 * 		'items'=> array(
 * 			 'Name1'=>array('id'=>(unique id), 'name'=>'(language constant - permission label)')
 * 			,'Name2'=>array('id'=>(unique id), 'name'=>'(language constant - permission label)')
 * 		)
 * 	)
 *  ,'Namespace2' => array(
 *  	'title'=> '(language constant - display title for permission form)',
 * 		'desc'=>  '(language constant - description for permission form)',
 * 		'items'=> array(
 * 			 'Name1'=>array('id'=>(unique id), 'name'=>'(language constant - permission label)')
 * 			,'Name2'=>array('id'=>(unique id), 'name'=>'(language constant - permission label)')
 * 			,'Name3'=>array('id'=>(unique id), 'name'=>'(language constant - permission label)')
 * 		)
 * 	)
 * );
 * @endcode
 */
class Xmf_Mvc_Lib_PermissionMap
{

	static $map=array();

	/*
	 * loadConfig loads config.php from current module directory
	 */
	static protected function initMap($namespace)
	{
		if(empty(self::$map[$namespace])) {
			self::$map[$namespace]=array(
					'title'=> 'Permission Form'
				,	'desc'=>  ''
				,	'items'=> array());
		}
	}

	/*
	 * Add an item to the permission map
	 * 	'Name1'=>array('id'=>(unique numeric id), 'name'=>'(language constant - label for permission form)')
	 *
	 * @param string $namespace  the namespace for the permission
	 * @param string $name       the symbolic name of the permission
	 * @param int    $id         a unique (in the namespace) integer id
	 * @param string $lang_label a languge constant to be use as a
	 *                           label for this permission on a form
	 *
	 * @return bool true if item added, false if error encountered
	 */
	static public function addItem($namespace,$name,$id,$lang_label)
	{

		self::initMap($namespace);

		foreach(self::$map[$namespace]['items'] as $items) {
			if($items['id']==$id) {
				trigger_error('Duplicate permission id: '.$id.':'.$namespace.':'.$name);
				return false;
			}
		}
		self::$map[$namespace]['items'][$name]['id']=$id;
		self::$map[$namespace]['items'][$name]['name']=$lang_label;
		return true;
	}

	/*
	 * Add title and description for a namespace to the permission map
	 *
	 * @param string $namespace        the namespace being defined
	 * @param string $lang_title       language constant for permission form title
	 * @param string $lang_description a languge constant for form description
	 *
	 * @return bool true if action completed without error
	 */
	static public function addNamespace($namespace,$lang_title,$lang_description)
	{
		self::initMap($namespace);
		self::$map[$namespace]['title']=$lang_title;
		self::$map[$namespace]['desc'] =$lang_description;
		return true;
	}

	/*
	 * save the map in Config
	 *
	 * @return bool true if action completed without error
	 */
	static function save()
	{
		Xmf_Mvc_Config::set('PermissionMap',self::$map);
		return true;
	}

	/*
	 * loadConfig loads config.php from current module directory
	 */
	static protected function loadConfig()
	{
		$_dirname=null;
		$modhelper=null;
		$_dirname = $GLOBALS['xoopsModule']->getVar('dirname');
		$modhelper = Xmf_Module_Helper::getInstance($_dirname);
		$pathname=XOOPS_ROOT_PATH .'/modules/'.$_dirname.'/';
		// this will quietly ignore a missing config file
		$configfile=$pathname.'/config.php';
		Xmf_Loader::loadFile($configfile, true);
	}

	/*
	 * renderPermissionForm renders a permission form from a permission map
	 *
	 * @param array $map a permission map. If null, uses current Config map
	 *
	 * @return string an HTML string containing group permission form(s)
	 */
	static public function renderPermissionForm($map=null)
	{
		require_once XOOPS_ROOT_PATH.'/class/xoopsform/grouppermform.php';

		global $xoopsModule;

		$module_id = $xoopsModule->getVar('mid');

		$forms=array();
		$rendered=array();

		if($map) { // if we passed a map in, use it
			self::$map=$map;
		}
		else {
			if(empty(self::$map)) {	// if map is not set, load config
				self::loadConfig();
//				$mvc_permissions=Xmf_Mvc_Config::get('PermissionMap',array());
			}
		}
		$mvc_permissions = self::$map; // use the map we already have
		foreach ($mvc_permissions as $key=>$perm) {
			$title_of_form = defined($perm['title']) ? constant($perm['title']) : $perm['title'];
			$perm_name = $key;
			$perm_desc = defined($perm['desc']) ? constant($perm['desc']) : $perm['desc'];

			$forms[$key] = new XoopsGroupPermForm($title_of_form, $module_id, $perm_name, $perm_desc, '', false);
			foreach ($perm['items'] as $item) {
				$forms[$key]->addItem($item['id'],
					defined($item['name']) ? constant($item['name']) : $item['name']);
			}

			$rendered[$key]=$forms[$key]->render();
		}

		$return=implode("\n<br /><br />",$rendered);
		return $return;
	}

}

?>
