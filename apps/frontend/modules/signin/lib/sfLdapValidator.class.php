<?php
class sfLdapValidator extends sfValidatorBase
{
	public function configure($options = array(), $messages = array())
	{
		$this->addOption("username_field", "username");
		$this->addOption("password_field", "password");
		$this->addOption("throw_global_error", false);

		$this->setMessage("invalid", "The username and/or password is invalid.");
	}

	protected function doClean($values)
	{
		$username = isset($values[$this->getOption("username_field")]) ? $values[$this->getOption("username_field")] : "";
		$password = isset($values[$this->getOption("password_field")]) ? $values[$this->getOption("password_field")] : "";

		$host = sfConfig::get("app_ldap_host", "localhost");
		$port = sfConfig::get("app_ldap_port", 389);
		$baseuser = sfConfig::get("app_ldap_baseuser");
		$ldap_username = sfConfig::get("app_ldap_username", "admin");
		$ldap_password = sfConfig::get("app_ldap_password", "password");

		if($username && $password)
		{
			$conn = ldap_connect($host, $port);
			if($conn === false)
				throw new sfValidatorErrorSchema($this, array($this->getOption('username_field') => new sfValidatorError($this, 'Unable to connect to LDAP server.')));

			if(@ldap_bind($conn, $ldap_username, $ldap_password))
			{
				$search = ldap_search($conn, $baseuser, "(|(mail=".$username.")(samaccountname=".$username."))", array("cn"));
				$info = ldap_get_entries($conn, $search);

				$name = explode(",", $info[0]["cn"][0]);
				$firstname = trim($name[1]);
				$lastname = trim($name[0]);

				return array_merge($values, array("firstname" => $firstname, "lastname" => $lastname));
			}
			else
			{
				throw new ErrorException("Unable reach LDAP server!", 500);
			}
		}

	    if ($this->getOption('throw_global_error'))
	    {
			throw new sfValidatorError($this, 'invalid');
	    }

	    throw new sfValidatorErrorSchema($this, array($this->getOption('username_field') => new sfValidatorError($this, 'invalid')));
	}
}
