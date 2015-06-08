<?php

class myUser extends sfGuardSecurityUser
{
	/**
	 * (non-PHPdoc)
	 * @see sfGuardSecurityUser::setReferer()
	 */
	public function setReferer($referer)
	{
		$this->setAttribute("previousReferer", $this->getAttribute("referer", "/"));
		$this->setAttribute("referer", $referer);
	}

	/**
	 * (non-PHPdoc)
	 * @see sfGuardSecurityUser::getReferer()
	 */
	public function getReferer($default = "/")
	{
	    return $this->getAttribute("previousReferer", $default);
	}

	/**
	 *
	 * @param string $default
	 * @return mixed
	 */
	public function getCurrentReferer($default = "/")
	{
		return $this->getAttribute("referer", $default);
	}

	/**
	 * Clear referer from user's session.
	 */
	public function clearReferer()
	{
		$this->getAttributeHolder()->remove("previousReferer");
		$this->getAttributeHolder()->remove("referer");
	}
}
