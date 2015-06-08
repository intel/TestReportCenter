<?php
class sfWidgetFormTextareaTinyMCECustom extends sfWidgetFormTextareaTinyMCE
{
	/**
	 * (non-PHPdoc)
	 * @see sfWidgetFormTextareaTinyMCE::configure()
	 */
	protected function configure($options = array(), $attributes = array())
	{
		parent::configure();

		$this->setOption("config", "theme_advanced_buttons1: 'bold,italic,underline,strikethrough,|,link,unlink,|,bullist,numlist,|,formatselect,fontsizeselect',
							theme_advanced_statusbar_location: 'none'");
	}
}