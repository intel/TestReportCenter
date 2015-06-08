<?php

class backendConfiguration extends sfApplicationConfiguration
{
	protected $frontendRouting = null;

	public function configure()
	{
	}

	public function generateFrontendUrl($name, $parameters = array())
	{
		return sfContext::getInstance()->getRequest()->getUriPrefix().sfContext::getInstance()->getRequest()->getRelativeUrlRoot().$this->getFrontendRouting()->generate($name, $parameters);
	}

	public function getFrontendRouting()
	{
		if (!$this->frontendRouting)
		{
			$this->frontendRouting = new sfPatternRouting(new sfEventDispatcher());

			$config = new sfRoutingConfigHandler();
			$routes = $config->evaluate(array(sfConfig::get('sf_apps_dir').'/frontend/config/routing.yml'));

			$this->frontendRouting->setRoutes($routes);
		}

		return $this->frontendRouting;
	}
}
