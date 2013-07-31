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
 * XoopsSmartRenderer renders using XOOPS Smarty templates
 *
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU private license
 * @package         Xmf_Mvc
 * @since           1.0
 * @author          Richard Griffith
 */

/**
 * The XoopsSmartyRenderer is a XOOPS specific renderer that uses XOOPS
 * Smarty templates and the standard $xoopsTpl mechanisms for page
 * rendering. Renderer attributes become Smarty assigned variables,
 * and the actual display is handled by the normal XOOPS cycle.
 */
class Xmf_Mvc_XoopsSmartyRenderer extends Xmf_Mvc_Renderer
{

	/** signal that we used a default template, just dump attributes */
	private $dumpmode;

	/**
	 * Create a new Renderer instance.
	 *
	 * @since  1.0
	 */
	public function __construct ()
	{

		parent::__construct();
		$this->dumpmode   = false;

	}

	/**
	 * Render the view.
	 *
	 * We actually just
	 * - make sure that a template is set
	 * - assign attributes to smarty variables
	 *
	 * @since  1.0
	 */
	public function execute ()
	{
		global $xoopsTpl, $xoopsOption;
		if ($this->template == NULL)
		{
			if(empty($xoopsOption['template_main'])) {
				$this->template = 'db:system_dummy.html';
				$this->dumpmode   = true;
			}
			else {
				$this->template = $xoopsOption['template_main'];
			}
		}

		// make it easier to access data directly in the template
		$mojavi   =& $this->controller()->getMojavi();
		$template =& $this->attributes;
		if($this->dumpmode) {
			$template['dummy_content']='<pre>'.print_r($this->attributes,true).'</pre>';
		}
		else {
			$template =& $this->attributes;
		}

		if ($this->mode == Xmf_Mvc::RENDER_VAR || $this->Controller()->getRenderMode() == Xmf_Mvc::RENDER_VAR)
		{
			$varRender = new Xmf_Mvc_XoopsTplRender;
			$varRender->setXTemplate($this->template);
			foreach ($template as $k=>$v) {
				$varRender->setAttribute($k,$v);
			}
			$varRender->setAttribute('xmfmvc',$mojavi);
			$this->result=$varRender->fetch();
			// echo $this->result;

		} else {
			$GLOBALS['xoopsOption']['template_main'] = $this->template;
			// the following is to make footer.php quit complaining
			if (false === strpos($xoopsOption['template_main'], ':')) {
				$GLOBALS['xoTheme']->contentTemplate = 'db:' . $xoopsOption['template_main'];
			} else {
				$GLOBALS['xoTheme']->contentTemplate = $xoopsOption['template_main'];
			}

			foreach ($template as $k=>$v) {
				$xoopsTpl->assign($k,$v);
			}
			$xoopsTpl->assign('xmfmvc',$mojavi);
			// templates and values are assigned, XOOPS will handle the rest
		}

	}


	// These following are unique to XoopsSmartyRenderer

	/**
	 * @brief Add Stylesheet
	 *
	 * @param $stylesheet URL of CSS stylesheet
	 *
	 * @since  1.0
	 */
	public function addStylesheet($stylesheet)
	{
		if (is_object($GLOBALS['xoTheme'])) {
			$GLOBALS['xoTheme']->addStylesheet($stylesheet);
		}
	}

	/**
	 * @brief Add Script
	 *
	 * @param $script URL to javascript file
	 *
	 * @since  1.0
	 */
	public function addScript($script)
	{
		if (is_object($GLOBALS['xoTheme'])) {
			$GLOBALS['xoTheme']->addScript($script);
		}
	}

	/**
	 * @brief Add Page Title
	 *
	 * @param string $pagetitle
	 *
	 * @since  1.0
	 */
	public function addPageTitle($pagetitle)
	{
		assign('xoops_pagetitle', htmlspecialchars($pagetitle));
	}

	/**
	 * @brief Add meta tag for keywords
	 *
	 * @param mixed $keywords meta keywords to include
	 *
	 * @since  1.0
	 */
	public function addMetaKeywords($keywords)
	{
		if(is_array($keywords)) {
			$keywords=implode(',',$keywords);
		}
		if (is_object($GLOBALS['xoTheme'])) {
			$GLOBALS['xoTheme']->addMeta('meta','keywords',htmlspecialchars($keywords, ENT_QUOTES,null,false));
		}
	}

	/**
	 * @brief Add meta tag for description
	 *
	 * @param string $description
	 *
	 * @since  1.0
	 */
	public function addMetaDescription($description)
	{
		if (is_object($GLOBALS['xoTheme'])) {
			$GLOBALS['xoTheme']->addMeta('meta','description',htmlspecialchars($pageX['meta_description'], ENT_QUOTES,null,false));
		}
	}

}

?>
