<?php

class mySfActions extends sfActions
{
	public function forward404($message = null)
	{
		// write to session and call from 404_template via $sf_user->getFlash("404message")
		$this->getUser()->setFlash("404message", $message);
		parent::forward404($message);
	}

	public function forward404Unless($condition, $message = null)
	{
		if(!$condition)
		{
			$this->forward403Unless($this->getUser()->isAuthenticated(), "errors", "error403", "Please login or contact a manager!");

			$this->getUser()->setFlash("404message", $message);
			parent::forward404Unless($condition, $message);
		}
	}

	public function forward404If($condition, $message = null)
	{
		if($condition)
		{
			$this->forward403Unless($this->getUser()->isAuthenticated(), "errors", "error403", "Please login or contact a manager!");

			$this->getUser()->setFlash("404message", $message);
			parent::forward404If($condition, $message);
		}
	}

	public function forward403Unless($condition, $module, $action, $message)
	{
		if(!$condition)
		{
			$this->getUser()->setFlash("403message", $message);
			parent::forwardUnless($condition, $module, $action);
		}
	}
}
