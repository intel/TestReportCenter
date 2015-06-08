<?php

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('backend', 'prod', false);
$myInstance = sfContext::createInstance($configuration);

if (!($myInstance->getUser()->getGuardUser()->getIsSuperAdmin()))
{
	throw new sfException("Testing the 500 error");
}

$myInstance->dispatch();