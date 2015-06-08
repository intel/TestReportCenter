<?php

/**
 * rss actions.
 *
 * @package    trc
 * @subpackage rss
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class rssActions extends mySfActions
{
	/**
	 * Executes product action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeProduct(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForProduct($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"]);
	}

	/**
	 * Executes environment action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeEnvironment(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Set current environment
		if($request->getParameter("environment"))
		{
			$this->currentEnvironment = Doctrine_Core::getTable("TestEnvironment")->getEnvironmentBySlug($request->getParameter("environment"));
			$this->forward404Unless($this->currentEnvironment != null, "This test environment type does not exist anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForEnvironment($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentEnvironment["id"]);
	}

	/**
	 * Executes image action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeImage(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Set current environment
		if($request->getParameter("environment"))
		{
			$this->currentEnvironment = Doctrine_Core::getTable("TestEnvironment")->getEnvironmentBySlug($request->getParameter("environment"));
			$this->forward404Unless($this->currentEnvironment != null, "This test environment type does not exist anymore or you lack sufficient privileges!");
		}

		// Set current image
		if($request->getParameter("image"))
		{
			$this->currentImage = Doctrine_Core::getTable("Image")->getImageBySlug($request->getParameter("image"));
			$this->forward404Unless($this->currentImage != null, "This image does not exist anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForImage($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->currentImage["id"]);
	}

	/**
	 * Executes product action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeProductBuild(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForProductForBuilds($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"]);
	}

	/**
	 * Executes build action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeBuild(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Set current build id
		if($request->getParameter("build"))
		{
			$this->currentBuild = Doctrine_Core::getTable("TestSession")->getBasicBuildBySlug($request->getParameter("build"));
			$this->forward404Unless($this->currentBuild != null, "This build id does not exist anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForBuild($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentBuild["build_id"]);
	}

	/**
	 * Executes environment action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeEnvironmentBuild(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Set current environment
		if($request->getParameter("environment"))
		{
			$this->currentEnvironment = Doctrine_Core::getTable("TestEnvironment")->getEnvironmentBySlug($request->getParameter("environment"));
			$this->forward404Unless($this->currentEnvironment != null, "This test environment type does not exist anymore or you lack sufficient privileges!");
		}

		// Set current build id
		if($request->getParameter("build"))
		{
			$this->currentBuild = Doctrine_Core::getTable("TestSession")->getBasicBuildBySlug($request->getParameter("build"));
			$this->forward404Unless($this->currentBuild != null, "This build id does not exist anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForEnvironmentForBuilds($this->projectGroupId, $this->currentProject["id"], $this->currentBuild["build_id"], $this->currentProduct["id"], $this->currentEnvironment["id"]);
	}

	/**
	 * Executes image action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeImageBuild(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Set current environment
		if($request->getParameter("environment"))
		{
			$this->currentEnvironment = Doctrine_Core::getTable("TestEnvironment")->getEnvironmentBySlug($request->getParameter("environment"));
			$this->forward404Unless($this->currentEnvironment != null, "This test environment type does not exist anymore or you lack sufficient privileges!");
		}

		// Set current image
		if($request->getParameter("image"))
		{
			$this->currentImage = Doctrine_Core::getTable("Image")->getImageBySlug($request->getParameter("image"));
			$this->forward404Unless($this->currentImage != null, "This image does not exist anymore or you lack sufficient privileges!");
		}

		// Set current build id
		if($request->getParameter("build"))
		{
			$this->currentBuild = Doctrine_Core::getTable("TestSession")->getBasicBuildBySlug($request->getParameter("build"));
			$this->forward404Unless($this->currentBuild != null, "This build id does not exist anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForImageForBuilds($this->projectGroupId, $this->currentProject["id"], $this->currentBuild["build_id"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->currentImage["id"]);
	}

	/**
	 * Executes product action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeProductTestset(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForProductForTestsets($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"]);
	}

	/**
	 * Executes testset action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeTestset(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Set current testset
		if($request->getParameter("testset"))
		{
			$this->currentTestset = Doctrine_Core::getTable("TestSession")->getBasicTestsetBySlug($request->getParameter("testset"));
			$this->forward404Unless($this->currentTestset != null, "This testset does not exist anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForTestset($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentTestset["testset"]);
	}

	/**
	 * Executes environment action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeEnvironmentTestset(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Set current environment
		if($request->getParameter("environment"))
		{
			$this->currentEnvironment = Doctrine_Core::getTable("TestEnvironment")->getEnvironmentBySlug($request->getParameter("environment"));
			$this->forward404Unless($this->currentEnvironment != null, "This test environment type does not exist anymore or you lack sufficient privileges!");
		}

		// Set current testset
		if($request->getParameter("testset"))
		{
			$this->currentTestset = Doctrine_Core::getTable("TestSession")->getBasicTestsetBySlug($request->getParameter("testset"));
			$this->forward404Unless($this->currentTestset != null, "This testset does not exist anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForEnvironmentForTestsets($this->projectGroupId, $this->currentProject["id"], $this->currentTestset["testset"], $this->currentProduct["id"], $this->currentEnvironment["id"]);
	}

	/**
	 * Executes image action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeImageTestset(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Set current project from URL parameters
		if($request->getParameter("project"))
		{
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectBySlug($request->getParameter("project"));
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Set current product
		if($request->getParameter("product"))
		{
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductBySlug($request->getParameter("product"));
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");
		}

		// Set current environment
		if($request->getParameter("environment"))
		{
			$this->currentEnvironment = Doctrine_Core::getTable("TestEnvironment")->getEnvironmentBySlug($request->getParameter("environment"));
			$this->forward404Unless($this->currentEnvironment != null, "This test environment type does not exist anymore or you lack sufficient privileges!");
		}

		// Set current image
		if($request->getParameter("image"))
		{
			$this->currentImage = Doctrine_Core::getTable("Image")->getImageBySlug($request->getParameter("image"));
			$this->forward404Unless($this->currentImage != null, "This image does not exist anymore or you lack sufficient privileges!");
		}

		// Set current testset
		if($request->getParameter("testset"))
		{
			$this->currentTestset = Doctrine_Core::getTable("TestSession")->getBasicTestsetBySlug($request->getParameter("testset"));
			$this->forward404Unless($this->currentTestset != null, "This testset does not exist anymore or you lack sufficient privileges!");
		}

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForImageForTestsets($this->projectGroupId, $this->currentProject["id"], $this->currentTestset["testset"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->currentImage["id"]);
	}
}
