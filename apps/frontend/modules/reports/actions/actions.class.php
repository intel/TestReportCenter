<?php

/**
 * reports actions.
 *
 * @package    trc
 * @subpackage reports
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class reportsActions extends mySfActions
{
	//============================================================================================//
	//      STANDARD VIEWS                                                                        //
	//============================================================================================//

	/**
	 * Display the homepage of the application.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeProject(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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
		else
			$this->currentProject = $this->projects[0];

		// Check if at least one project has been found and user is not authenticated
		if(!$this->getUser()->isAuthenticated() && $this->currentProject == null)
		{
			$this->getUser()->setFlash("error", "Your security level is not sufficient to visualize projects. Please login with appropriate credentials!");
			$this->redirect("sf_guard_signin");
		}

		// Redirect to a 404 if user is authenticated and there is no projects with right security level
		$this->forward404Unless($this->currentProject != null, "There is no project to display for your security level. Contact your administrator for more information.");

		// Set current filter
		$this->currentFilter = $request->getParameter("filter", "recent");
		$filter = ($this->currentFilter == "all") ? false : true;

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Get list of all test environments and images
		$this->imagesForEnvironments = Doctrine_Core::getTable("Project")->getImagesForEnvironments($this->projectGroupId, $this->currentProject["id"], MiscUtils::arrayColumn($this->products, 'id', true), $filter);
	}

	/**
	 * Display product's view, with list of all sessions for this product.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeProduct(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForProduct($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForProduct($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Display environment's view, with list of all sessions for this environment.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeEnvironment(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForEnvironment($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForEnvironment($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentEnvironment["id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Display image's view, with list of all sessions for this environment.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeImage(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		// Initialize values
		$this->regressionCount = $this->passToFailCount = $this->passToNaCount = 0;
		$this->progressCount = $this->failToPassCount = $this->naToPassCount = 0;
		$this->newPassCount = $this->newFailCount = $this->newNaCount = 0;

		$this->lastSessions = Doctrine_Core::getTable("TestSession")->getLatestSessionsForImage($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], 2);
		if(!empty($this->lastSessions[0]) AND !empty($this->lastSessions[1]))
		{
			$i = 0;
			while($i < 2) {
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->lastSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->lastSessions[$i]["results"] = $array;
				$i++;
			}
			$previousSessionResults = $this->lastSessions[1]["results"];

			foreach($this->lastSessions[0]["results"] as $key => $lastSessionResult)
			{
				if(isset($previousSessionResults[$key]))
				{
					$previousSessionResult = $previousSessionResults[$key];

					if($previousSessionResult["decision_criteria_id"] == -1)
					{
						if($lastSessionResult["decision_criteria_id"] == -2)
						{
							$this->passToFailCount++;
						}
						else if($lastSessionResult["decision_criteria_id"] == -3)
						{
							$this->passToNaCount++;
						}
					}
					else if ($lastSessionResult["decision_criteria_id"] == -1)
					{
						if($previousSessionResult["decision_criteria_id"] == -2)
						{
							$this->failToPassCount++;
						}
						else if($previousSessionResult["decision_criteria_id"] == -3)
						{
							$this->naToPassCount++;
						}
					}
				}
				else
				{
					if($lastSessionResult["decision_criteria_id"] == -1)
					{
						$this->newPassCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -2)
					{
						$this->newFailCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -3)
					{
						$this->newNaCount++;
					}
				}
			}

			$this->regressionCount = $this->passToFailCount + $this->passToNaCount;
			$this->progressCount = $this->failToPassCount + $this->naToPassCount;
		}

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForImage($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->currentImage["id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForImage($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->currentImage["id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Display a test session with its statistics and test results.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeSession(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		// Set current display
		$this->currentDisplay = $request->getParameter("display", "basic");
		if($this->currentDisplay != "basic" && $this->currentDisplay != "detailed" && $this->currentDisplay != "history")
			$this->currentDisplay = "basic";

		// Set current session
		if($request->getParameter("id"))
		{
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id")." AND ts.published = 1";
			$this->currentSession = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->currentSession), "This test session is not accessible anymore or you lack sufficient privileges!");

			// Get list of features with their numbers for current report
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			if($this->currentDisplay != "basic")
			{
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->currentSession["results"] = $array;

				// Get all measurements (and add associative key for each too)
				$array = array();
				$array_keys = array();
				$targetPercentageSum = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
					$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
				}
				$this->currentSession["measures"] = $array;
				if(count($array) > 0)
					$this->nftIndex = round($targetPercentageSum / count($array) * 100);
			}
			else
			{
				// Get all measurements (and add associative key for each too)
				$targetPercentageSum = 0;
				$count = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				{
					$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
					$count++;
				}
				if($count > 0)
					$this->nftIndex = round($targetPercentageSum / $count * 100);
			}
		}

		// Get author and editor of the test session
		$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["user_id"]."";
		$this->author = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if($this->currentSession["editor_id"])
		{
			$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["editor_id"]."";
			$this->editor = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			$this->editor = $this->author;
		}

		// Get file attachments
		$this->attachments = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 2);
		$this->resultFiles = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 1);

		// Get previous and next session
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDate($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, false);
		if(!empty($result))
			$this->previousSession = $result[0];
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDate($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, true);
		if(!empty($result))
			$this->nextSession = $result[0];

		// Get numbers of result summary
		$this->currentSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbers($this->currentSession["id"]);
		$this->previousSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSummaryNumbers($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3));
		$this->reversedPreviousSummaryNumbers = array_reverse($this->previousSummaryNumbers);

		// Get 3 (by default) previous test sessions
		$this->previousSessions = Doctrine_Core::getTable("TestSession")->getSessionsByDate($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3), false);
		for($i=0; $i<count($this->previousSessions); $i++)
		{
			// Get list of features with their numbers for previous reports
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSessions[$i]['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSessions[$i]["features"] = $array;

			if($this->currentDisplay == "history")
			{
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->previousSessions[$i]["results"] = $array;

				// Get all measurements (and add associative key for each too)
				$array = array();
				$array_keys = array();
				$targetPercentageSum = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[$i]["id"]) as $key => $value)
				{
					if(!empty($value["measures"]))
					{
						$key = MiscUtils::slugify($value["name"].$value["label"]);
						if(array_key_exists($key, $array))
						{
							$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
							$key = $key."-".$array_keys[$key];
						}
						$array[$key] = $value;
						$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
					}
				}
				$this->previousSessions[$i]["measures"] = $array;
				if(count($array) > 0)
					$this->previousNftIndex = round($targetPercentageSum / count($array) * 100);
			}
			else
			{
				// Get all measurements (and add associative key for each too)
				$targetPercentageSum = 0;
				$count = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[count($this->previousSessions) - 1]["id"]) as $key => $value)
				{
					if(!empty($value["measures"]))
					{
						$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
						$count++;
					}
				}
				if($count > 0)
					$this->previousNftIndex = round($targetPercentageSum / $count * 100);
			}
		}

		// Get session' max number of results for features
		$this->totalFeaturesResults = 0;
		foreach($this->currentSession["features"] as $data)
		{
			if($this->totalFeaturesResults < $data["total"])
				$this->totalFeaturesResults = $data["total"];
		}
	}

	/**
	 * Display a simplified test session for print purpose.
	 *
	 * @param sfWebRequest $request
	 */
	public function executePrint(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		// Set current session
		if($request->getParameter("id"))
		{
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id")." AND ts.published = 1";
			$this->currentSession = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->currentSession), "This test session is not accessible anymore or you lack sufficient privileges!");

			// Get list of features with their numbers for current report
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			$this->currentSession["results"] = $array;

			// Get all measurements (and add associative key for each too)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			$this->currentSession["measures"] = $array;
		}

		// Get author and editor of the test session
		$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["user_id"]."";
		$this->author = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if($this->currentSession["editor_id"])
		{
			$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["editor_id"]."";
			$this->editor = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			$this->editor = $this->author;
		}

		// Get file attachments
		$this->attachments = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 2);
		$this->resultFiles = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 1);

		// Get previous and next session
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDate($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, false);
		if(!empty($result))
			$this->previousSession = $result[0];
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDate($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, true);
		if(!empty($result))
			$this->nextSession = $result[0];

		// Get numbers of result summary
		$this->currentSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbers($this->currentSession["id"]);
		$this->previousSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSummaryNumbers($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3));
		$this->reversedPreviousSummaryNumbers = array_reverse($this->previousSummaryNumbers);

		// Get 3 (by default) previous test sessions
		$this->previousSessions = Doctrine_Core::getTable("TestSession")->getSessionsByDate($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3), false);
		for($i=0; $i<count($this->previousSessions); $i++)
		{
			// Get list of features with their numbers for previous reports
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSessions[$i]['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSessions[$i]["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSessions[$i]["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			$this->previousSessions[$i]["results"] = $array;

			// Get all measurements (and add associative key for each too)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[$i]["id"]) as $key => $value)
			{
				if(!empty($value["measures"]))
					$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			}
			$this->previousSessions[$i]["measures"] = $array;
		}

		// Get session' max number of results for features
		$this->totalFeaturesResults = 0;
		foreach($this->currentSession["features"] as $data)
		{
			if($this->totalFeaturesResults < $data["total"])
				$this->totalFeaturesResults = $data["total"];
		}
	}

	/**
	 * Display a test session retrieved from "shortcut" route: /session/:id/:display.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeSee(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Set current session
		if($request->getParameter("id"))
		{
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id")." AND ts.published = 1";
			$this->currentSession = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->currentSession), "This test session is not accessible anymore or you lack sufficient privileges!");

			// Set current display
			$this->currentDisplay = $request->getParameter("display", "basic");
			if($this->currentDisplay != "basic" && $this->currentDisplay != "detailed" && $this->currentDisplay != "history")
				$this->currentDisplay = "basic";

			//-----
			// Retrieve image and environment identifiers from configuration table
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".configuration c WHERE c.id = ".$this->currentSession['configuration_id'];
			$configuration = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);

			// Retrieve "project to product" relationship
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".project_to_product ptp WHERE ptp.id = ".$configuration['project_to_product_id'];
			$projectToProduct = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);

			// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
			$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

			// Get current project
			$this->currentProject = Doctrine_Core::getTable("Project")->getBasicProjectById($projectToProduct['project_id']);
			$this->forward404Unless($this->currentProject != null, "This project does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProject, $this->projects), "This project is not accessible anymore or you lack sufficient privileges!");

			// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
			$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

			// Get current product
			$this->currentProduct = Doctrine_Core::getTable("ProductType")->getBasicProductById($projectToProduct['product_id']);
			$this->forward404Unless($this->currentProduct != null, "This product type does not exist anymore or you lack sufficient privileges!");
			$this->forward404Unless(in_array($this->currentProduct, $this->products), "This product type is not accessible anymore or you lack sufficient privileges!");

			// Get current environment
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_environment te WHERE te.id = ".$configuration['test_environment_id'];
			$this->currentEnvironment = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless($this->currentEnvironment != null, "This test environment type does not exist anymore or you lack sufficient privileges!");

			// Get current image
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".image i WHERE i.id = ".$configuration['image_id'];
			$this->currentImage = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless($this->currentImage != null, "This image does not exist anymore or you lack sufficient privileges!");
			//-----

			// Get list of features with their numbers for current report
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			if($this->currentDisplay != "basic")
			{
				// Get all results (and add associative key for each result)
				$array = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
					$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
				$this->currentSession["results"] = $array;

				// Get all measurements (and add associative key for each too)
				$array = array();
				$targetPercentageSum = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				{
					$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
					$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
				}
				$this->currentSession["measures"] = $array;
				if(count($array) > 0)
					$this->nftIndex = round($targetPercentageSum / count($array) * 100);
			}
			else
			{
				// Get all measurements (and add associative key for each too)
				$targetPercentageSum = 0;
				$count = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				{
					$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
					$count++;
				}
				if($count > 0)
					$this->nftIndex = round($targetPercentageSum / $count * 100);
			}
		}

		// Get author and editor of the test session
		$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["user_id"]."";
		$this->author = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if($this->currentSession["editor_id"])
		{
			$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["editor_id"]."";
			$this->editor = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			$this->editor = $this->author;
		}

		// Get file attachments
		$this->attachments = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 2);
		$this->resultFiles = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 1);

		// Get previous and next session
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDate($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, false);
		if(!empty($result))
			$this->previousSession = $result[0];
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDate($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, true);
		if(!empty($result))
			$this->nextSession = $result[0];

		// Get numbers of result summary
		$this->currentSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbers($this->currentSession["id"]);
		$this->previousSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSummaryNumbers($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3));
		$this->reversedPreviousSummaryNumbers = array_reverse($this->previousSummaryNumbers);

		// Get 3 (by default) previous test sessions
		$this->previousSessions = Doctrine_Core::getTable("TestSession")->getSessionsByDate($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3), false);
		for($i=0; $i<count($this->previousSessions); $i++)
		{
			// Get list of features with their numbers for previous reports
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSessions[$i]['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSessions[$i]["features"] = $array;

			if($this->currentDisplay == "history")
			{
				// Get all results (and add associative key for each result)
				$array = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSessions[$i]["id"]) as $key => $value)
					$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
				$this->previousSessions[$i]["results"] = $array;

				// Get all measurements (and add associative key for each too)
				$array = array();
				$targetPercentageSum = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[$i]["id"]) as $key => $value)
				{
					if(!empty($value["measures"]))
					{
						$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
						$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
					}
				}
				$this->previousSessions[$i]["measures"] = $array;
				if(count($array) > 0)
					$this->previousNftIndex = round($targetPercentageSum / count($array) * 100);
			}
			else
			{
				// Get all measurements (and add associative key for each too)
				$targetPercentageSum = 0;
				$count = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[count($this->previousSessions) - 1]["id"]) as $key => $value)
				{
					if(!empty($value["measures"]))
					{
						$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
						$count++;
					}
				}
				if($count > 0)
					$this->previousNftIndex = round($targetPercentageSum / $count * 100);
			}
		}

		// Get session' max number of results for features
		$this->totalFeaturesResults = 0;
		foreach($this->currentSession["features"] as $data)
		{
			if($this->totalFeaturesResults < $data["total"])
				$this->totalFeaturesResults = $data["total"];
		}

		$this->setTemplate("session");
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeCompare(sfWebRequest $request)
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

		// Initialize values
		$this->regressionCount = $this->passToFailCount = $this->passToNaCount = 0;
		$this->progressCount = $this->failToPassCount = $this->naToPassCount = 0;
		$this->newPassCount = $this->newFailCount = $this->newNaCount = 0;

		$this->lastSessions = Doctrine_Core::getTable("TestSession")->getLatestSessionsForImage($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], 2);
		if(!empty($this->lastSessions[0]) AND !empty($this->lastSessions[1]))
		{
			$i = 0;
			while($i < 2) {
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->lastSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->lastSessions[$i]["results"] = $array;
				$i++;
			}
			$previousSessionResults = $this->lastSessions[1]["results"];

			foreach($this->lastSessions[0]["results"] as $key => $lastSessionResult)
			{
				if(isset($previousSessionResults[$key]))
				{
					$previousSessionResult = $previousSessionResults[$key];

					if($previousSessionResult["decision_criteria_id"] == -1)
					{
						if($lastSessionResult["decision_criteria_id"] == -2)
						{
							$this->passToFailCount++;
						}
						else if($lastSessionResult["decision_criteria_id"] == -3)
						{
							$this->passToNaCount++;
						}
					}
					else if ($lastSessionResult["decision_criteria_id"] == -1)
					{
						if($previousSessionResult["decision_criteria_id"] == -2)
						{
							$this->failToPassCount++;
						}
						else if($previousSessionResult["decision_criteria_id"] == -3)
						{
							$this->naToPassCount++;
						}
					}
				}
				else
				{
					if($lastSessionResult["decision_criteria_id"] == -1)
					{
						$this->newPassCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -2)
					{
						$this->newFailCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -3)
					{
						$this->newNaCount++;
					}
				}
			}

			$this->regressionCount = $this->passToFailCount + $this->passToNaCount;
			$this->progressCount = $this->failToPassCount + $this->naToPassCount;


			$this->currentSession = $this->lastSessions[0];
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
			$this->currentSession["results"] = $array;

			$this->previousSession = $this->lastSessions[1];
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
			$this->previousSession["results"] = $array;


			$this->unchangedFeatures = array();
			foreach($this->currentSession["features"] as $feature)
			{
				$this->unchangedFeatures[$feature["label"]] = 0;
			}

			// Compare results to determine number of unchanged tests
			foreach($this->currentSession["results"] as $key => $result)
			{
				$previousResult = $this->previousSession["results"];
				// If current key exists into previous test session
				if(array_key_exists($key,$previousResult))
				{
					// If previousResult[key] == currentResult[key], test is unchanged
					if($previousResult[$key]["decision_criteria_id"] == $result["decision_criteria_id"])
					{
						$this->unchangedFeatures[$result["label"]]++;
					}
				}
			}
		}
	}

	/**
	 * Compare two reports chosen by their IDs.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeCompareTo(sfWebRequest $request)
	{
		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get list of all projects (Android, Tizen, Yocto, Linux, ...)
		$this->projects = Doctrine_Core::getTable("Project")->getBasicProjects($this->projectGroupId, $userSecurityLevel);

		// Get report IDs from URL parameters
		if($request->getParameter("id1") && $request->getParameter("id2"))
		{
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id1")." AND ts.published = 1";
			$this->report1 = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->report1), "This test session is not accessible anymore or you lack sufficient privileges!");

			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id2")." AND ts.published = 1";
			$this->report2 = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->report2), "This test session is not accessible anymore or you lack sufficient privileges!");
		}

		// Initialize values
		$this->regressionCount = $this->passToFailCount = $this->passToNaCount = 0;
		$this->progressCount = $this->failToPassCount = $this->naToPassCount = 0;
		$this->newPassCount = $this->newFailCount = $this->newNaCount = 0;

		$this->lastSessions = array($this->report1, $this->report2);
		if(!empty($this->lastSessions[0]) AND !empty($this->lastSessions[1]))
		{
			$i = 0;
			while($i < 2) {
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->lastSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->lastSessions[$i]["results"] = $array;
				$i++;
			}
			$previousSessionResults = $this->lastSessions[1]["results"];

			foreach($this->lastSessions[0]["results"] as $key => $lastSessionResult)
			{
				if(isset($previousSessionResults[$key]))
				{
					$previousSessionResult = $previousSessionResults[$key];

					if($previousSessionResult["decision_criteria_id"] == -1)
					{
						if($lastSessionResult["decision_criteria_id"] == -2)
						{
							$this->passToFailCount++;
						}
						else if($lastSessionResult["decision_criteria_id"] == -3)
						{
							$this->passToNaCount++;
						}
					}
					else if ($lastSessionResult["decision_criteria_id"] == -1)
					{
						if($previousSessionResult["decision_criteria_id"] == -2)
						{
							$this->failToPassCount++;
						}
						else if($previousSessionResult["decision_criteria_id"] == -3)
						{
							$this->naToPassCount++;
						}
					}
				}
				else
				{
					if($lastSessionResult["decision_criteria_id"] == -1)
					{
						$this->newPassCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -2)
					{
						$this->newFailCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -3)
					{
						$this->newNaCount++;
					}
				}
			}

			$this->regressionCount = $this->passToFailCount + $this->passToNaCount;
			$this->progressCount = $this->failToPassCount + $this->naToPassCount;


			$this->currentSession = $this->lastSessions[0];
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
			$this->currentSession["results"] = $array;

			$this->previousSession = $this->lastSessions[1];
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
			$this->previousSession["results"] = $array;


			$this->unchangedFeatures = array();
			foreach($this->currentSession["features"] as $feature)
			{
				$this->unchangedFeatures[$feature["label"]] = 0;
			}

			// Compare results to determine number of unchanged tests
			foreach($this->currentSession["results"] as $key => $result)
			{
				$previousResult = $this->previousSession["results"];
				// If current key exists into previous test session
				if(array_key_exists($key,$previousResult))
				{
					// If previousResult[key] == currentResult[key], test is unchanged
					if($previousResult[$key]["decision_criteria_id"] == $result["decision_criteria_id"])
					{
						$this->unchangedFeatures[$result["label"]]++;
					}
				}
			}
		}
	}

	/**
	 * Add a test session to the comparison box from an AJAX call.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeCompareAdd(sfWebRequest $request)
	{
		// Get session ids from user session
		$sessionComparisons = $this->getUser()->getAttribute('session_comparison', array());

		// Get session id from URL parameters
		$id = $request->getParameter("id");

		// Add the session id to the comparison list
	    if (! in_array($id, $sessionComparisons))
	    {
			array_push($sessionComparisons, $id);

			// Maintain only 2 elements in the list
			while(count($sessionComparisons) > 2)
				array_shift($sessionComparisons);

			// Store the comparison list back into the user session
			$this->getUser()->setAttribute('session_comparison', $sessionComparisons);
	    }
	}

	//============================================================================================//
	//      BUILD VIEWS                                                                           //
	//============================================================================================//

	/**
	 * Homepage of the build index view. Display environments and images grouped by build indexes.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeProjectBuild(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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
		else
			$this->currentProject = $this->projects[0];

		// Check if at least one project has been found and user is not authenticated
		if(!$this->getUser()->isAuthenticated() && $this->currentProject == null)
		{
			$this->getUser()->setFlash("error", "Your security level is not sufficient to visualize projects. Please login with appropriate credentials!");
			$this->redirect("sf_guard_signin");
		}

		// Set current filter
		$this->currentFilter = $request->getParameter("filter", "recent");
		$filter = ($this->currentFilter == "all") ? false : true;

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Get list of all test environments and images with build ids
		$this->imagesForEnvironments = Doctrine_Core::getTable("Project")->getImagesForEnvironmentsForBuilds($this->projectGroupId, $this->currentProject["id"], MiscUtils::arrayColumn($this->products, 'id', true), $filter);

		// ...convert data retrieved from database
		if(count($this->imagesForEnvironments) > 0)
		{
			$arrayProducts = array();

			$previousProduct = null;
			$previousBuild = null;
			$previousEnvironment = null;
			$previousImage = null;

			foreach($this->imagesForEnvironments as $data)
			{
				if($data["product_id"] != $previousProduct)
				{
					$arrayProducts[$data["product_id"]] = array("product_id" => $data["product_id"], "builds" => array());
					$previousProduct = $data["product_id"];

					$previousBuild = null;
					$previousEnvironment = null;
					$previousImage = null;
				}

				if($data["ts_build_id"] != $previousBuild)
				{
					$arrayProducts[$data["product_id"]]["builds"][$data["ts_build_id"]] = array("ts_build_id" => $data["ts_build_id"], "ts_build_slug" => $data["ts_build_slug"], "environments" => array());
					$previousBuild = $data["ts_build_id"];

					$previousEnvironment = null;
					$previousImage = null;
				}

				if($data["te_name"] != $previousEnvironment)
				{
					$arrayProducts[$data["product_id"]]["builds"][$data["ts_build_id"]]["environments"][$data["te_name"]] = array("te_id" => $data["te_id"], "te_name" => $data["te_name"], "te_slug" => $data["te_slug"], "images" => array());
					$previousEnvironment = $data["te_name"];

					$previousImage = null;
				}

				if($data["i_name"] != $previousImage)
				{
					$arrayProducts[$data["product_id"]]["builds"][$data["ts_build_id"]]["environments"][$data["te_name"]]["images"][$data["i_name"]] = array("i_id" => $data["i_id"], "i_name" => $data["i_name"], "i_slug" => $data["i_slug"]);
					$previousImage = $data["i_name"];
				}
			}

			$this->imagesForEnvironments = $arrayProducts;
		}
		else
			$this->imagesForEnvironments = array();
	}

	/**
	 * Product view for build indexes. Display test sessions grouped by images for the given product.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeProductBuild(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForProductForBuilds($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForProductForBuilds($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Build view. Display test sessions grouped by images for the given build id.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeBuild(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForBuild($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentBuild["build_id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForBuild($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentBuild["build_id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Environment view for build indexes. Display test sessions grouped by images for the given environment.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeEnvironmentBuild(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForEnvironmentForBuilds($this->projectGroupId, $this->currentProject["id"], $this->currentBuild["build_id"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForEnvironmentForBuilds($this->projectGroupId, $this->currentProject["id"], $this->currentBuild["build_id"], $this->currentProduct["id"], $this->currentEnvironment["id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Image view for build indexes. Display test sessions of given image.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeImageBuild(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		// Initialize values
		$this->regressionCount = $this->passToFailCount = $this->passToNaCount = 0;
		$this->progressCount = $this->failToPassCount = $this->naToPassCount = 0;
		$this->newPassCount = $this->newFailCount = $this->newNaCount = 0;

		$this->lastSessions = Doctrine_Core::getTable("TestSession")->getLatestSessionsForImageBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild["build_id"], $this->currentEnvironment['id'], $this->currentImage['id'], 2);
		if(!empty($this->lastSessions[0]) AND !empty($this->lastSessions[1]))
		{
			$i = 0;
			while($i < 2) {
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->lastSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->lastSessions[$i]["results"] = $array;
				$i++;
			}
			$previousSessionResults = $this->lastSessions[1]["results"];

			foreach($this->lastSessions[0]["results"] as $key => $lastSessionResult)
			{
				if(isset($previousSessionResults[$key]))
				{
					$previousSessionResult = $previousSessionResults[$key];

					if($previousSessionResult["decision_criteria_id"] == -1)
					{
						if($lastSessionResult["decision_criteria_id"] == -2)
						{
							$this->passToFailCount++;
						}
						else if($lastSessionResult["decision_criteria_id"] == -3)
						{
							$this->passToNaCount++;
						}
					}
					else if ($lastSessionResult["decision_criteria_id"] == -1)
					{
						if($previousSessionResult["decision_criteria_id"] == -2)
						{
							$this->failToPassCount++;
						}
						else if($previousSessionResult["decision_criteria_id"] == -3)
						{
							$this->naToPassCount++;
						}
					}
				}
				else
				{
					if($lastSessionResult["decision_criteria_id"] == -1)
					{
						$this->newPassCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -2)
					{
						$this->newFailCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -3)
					{
						$this->newNaCount++;
					}
				}
			}

			$this->regressionCount = $this->passToFailCount + $this->passToNaCount;
			$this->progressCount = $this->failToPassCount + $this->naToPassCount;
		}

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForImageForBuilds($this->projectGroupId, $this->currentProject["id"], $this->currentBuild["build_id"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->currentImage["id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForImageForBuilds($this->projectGroupId, $this->currentProject["id"], $this->currentBuild["build_id"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->currentImage["id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Display a test session for build index views.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeSessionBuild(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		// Set current display
		$this->currentDisplay = $request->getParameter("display", "basic");
		if($this->currentDisplay != "basic" && $this->currentDisplay != "detailed" && $this->currentDisplay != "history")
			$this->currentDisplay = "basic";

		// Set current session
		if($request->getParameter("id"))
		{
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id")." AND ts.published = 1";
			$this->currentSession = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->currentSession), "This test session is not accessible anymore or you lack sufficient privileges!");

			// Get list of features with their numbers for current report
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			if($this->currentDisplay != "basic")
			{
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->currentSession["results"] = $array;

				// Get all measurements (and add associative key for each too)
				$array = array();
				$array_keys = array();
				$targetPercentageSum = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
					$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
				}
				$this->currentSession["measures"] = $array;
				if(count($array) > 0)
					$this->nftIndex = round($targetPercentageSum / count($array) * 100);
			}
			else
			{
				// Get all measurements (and add associative key for each too)
				$targetPercentageSum = 0;
				$count = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				{
					$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
					$count++;
				}
				if($count > 0)
					$this->nftIndex = round($targetPercentageSum / $count * 100);
			}
		}

		// Get author and editor of the test session
		$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["user_id"]."";
		$this->author = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if($this->currentSession["editor_id"])
		{
			$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["editor_id"]."";
			$this->editor = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			$this->editor = $this->author;
		}

		// Get file attachments
		$this->attachments = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 2);
		$this->resultFiles = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 1);

		// Get previous and next session
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDateForBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild['build_id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, false);
		if(!empty($result))
			$this->previousSession = $result[0];
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDateForBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild['build_id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, true);
		if(!empty($result))
			$this->nextSession = $result[0];

		// Get numbers of result summary
		$this->currentSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbers($this->currentSession["id"]);
		$this->previousSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSummaryNumbersForBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild['build_id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3));
		$this->reversedPreviousSummaryNumbers = array_reverse($this->previousSummaryNumbers);

		// Get 3 (by default) previous test sessions
		$this->previousSessions = Doctrine_Core::getTable("TestSession")->getSessionsByDateForBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild['build_id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3), false);
		for($i=0; $i<count($this->previousSessions); $i++)
		{
			// Get list of features with their numbers for previous reports
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSessions[$i]['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSessions[$i]["features"] = $array;

			if($this->currentDisplay == "history")
			{
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->previousSessions[$i]["results"] = $array;

				// Get all measurements (and add associative key for each too)
				$array = array();
				$array_keys = array();
				$targetPercentageSum = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[$i]["id"]) as $key => $value)
				{
					if(!empty($value["measures"]))
					{
						$key = MiscUtils::slugify($value["name"].$value["label"]);
						if(array_key_exists($key, $array))
						{
							$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
							$key = $key."-".$array_keys[$key];
						}
						$array[$key] = $value;
						$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
					}
				}
				$this->previousSessions[$i]["measures"] = $array;
				if(count($array) > 0)
					$this->previousNftIndex = round($targetPercentageSum / count($array) * 100);
			}
			else
			{
				// Get all measurements (and add associative key for each too)
				$targetPercentageSum = 0;
				$count = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[count($this->previousSessions) - 1]["id"]) as $key => $value)
				{
					if(!empty($value["measures"]))
					{
						$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
						$count++;
					}
				}
				if($count > 0)
					$this->previousNftIndex = round($targetPercentageSum / $count * 100);
			}
		}

		// Get session' max number of results for features
		$this->totalFeaturesResults = 0;
		foreach($this->currentSession["features"] as $data)
		{
			if($this->totalFeaturesResults < $data["total"])
				$this->totalFeaturesResults = $data["total"];
		}
	}

	/**
	 * Display a simplified test session for print purpose.
	 *
	 * @param sfWebRequest $request
	 */
	public function executePrintBuild(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		// Set current session
		if($request->getParameter("id"))
		{
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id")." AND ts.published = 1";
			$this->currentSession = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->currentSession), "This test session is not accessible anymore or you lack sufficient privileges!");

			// Get list of features with their numbers for current report
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			$this->currentSession["results"] = $array;

			// Get all measurements (and add associative key for each too)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			$this->currentSession["measures"] = $array;
		}

		// Get author and editor of the test session
		$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["user_id"]."";
		$this->author = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if($this->currentSession["editor_id"])
		{
			$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["editor_id"]."";
			$this->editor = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			$this->editor = $this->author;
		}

		// Get file attachments
		$this->attachments = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 2);
		$this->resultFiles = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 1);

		// Get previous and next session
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDateForBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild['build_id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, false);
		if(!empty($result))
			$this->previousSession = $result[0];
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDateForBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild['build_id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, true);
		if(!empty($result))
			$this->nextSession = $result[0];

		// Get numbers of result summary
		$this->currentSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbers($this->currentSession["id"]);
		$this->previousSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSummaryNumbersForBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild['build_id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3));
		$this->reversedPreviousSummaryNumbers = array_reverse($this->previousSummaryNumbers);

		// Get 3 (by default) previous test sessions
		$this->previousSessions = Doctrine_Core::getTable("TestSession")->getSessionsByDateForBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild['build_id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3), false);
		for($i=0; $i<count($this->previousSessions); $i++)
		{
			// Get list of features with their numbers for previous reports
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSessions[$i]['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSessions[$i]["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSessions[$i]["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			$this->previousSessions[$i]["results"] = $array;

			// Get all measurements (and add associative key for each too)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[$i]["id"]) as $key => $value)
			{
				if(!empty($value["measures"]))
					$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			}
			$this->previousSessions[$i]["measures"] = $array;
		}

		// Get session' max number of results for features
		$this->totalFeaturesResults = 0;
		foreach($this->currentSession["features"] as $data)
		{
			if($this->totalFeaturesResults < $data["total"])
				$this->totalFeaturesResults = $data["total"];
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeCompareBuild(sfWebRequest $request)
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

		// Initialize values
		$this->regressionCount = $this->passToFailCount = $this->passToNaCount = 0;
		$this->progressCount = $this->failToPassCount = $this->naToPassCount = 0;
		$this->newPassCount = $this->newFailCount = $this->newNaCount = 0;

		$this->lastSessions = Doctrine_Core::getTable("TestSession")->getLatestSessionsForImageBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild["build_id"], $this->currentEnvironment['id'], $this->currentImage['id'], 2);
		if(!empty($this->lastSessions[0]) AND !empty($this->lastSessions[1]))
		{
			$i = 0;
			while($i < 2) {
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->lastSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->lastSessions[$i]["results"] = $array;
				$i++;
			}
			$previousSessionResults = $this->lastSessions[1]["results"];

			foreach($this->lastSessions[0]["results"] as $key => $lastSessionResult)
			{
				if(isset($previousSessionResults[$key]))
				{
					$previousSessionResult = $previousSessionResults[$key];

					if($previousSessionResult["decision_criteria_id"] == -1)
					{
						if($lastSessionResult["decision_criteria_id"] == -2)
						{
							$this->passToFailCount++;
						}
						else if($lastSessionResult["decision_criteria_id"] == -3)
						{
							$this->passToNaCount++;
						}
					}
					else if ($lastSessionResult["decision_criteria_id"] == -1)
					{
						if($previousSessionResult["decision_criteria_id"] == -2)
						{
							$this->failToPassCount++;
						}
						else if($previousSessionResult["decision_criteria_id"] == -3)
						{
							$this->naToPassCount++;
						}
					}
				}
				else
				{
					if($lastSessionResult["decision_criteria_id"] == -1)
					{
						$this->newPassCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -2)
					{
						$this->newFailCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -3)
					{
						$this->newNaCount++;
					}
				}
			}

			$this->regressionCount = $this->passToFailCount + $this->passToNaCount;
			$this->progressCount = $this->failToPassCount + $this->naToPassCount;


			$this->currentSession = $this->lastSessions[0];
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
			$this->currentSession["results"] = $array;

			$this->previousSession = $this->lastSessions[1];
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
			$this->previousSession["results"] = $array;

			$this->unchangedFeatures = array();
			foreach($this->currentSession["features"] as $feature)
			{
				$this->unchangedFeatures[$feature["label"]] = 0;
			}

			// Compare results to determine number of unchanged tests
			foreach($this->currentSession["results"] as $key => $result)
			{
				$previousResult = $this->previousSession["results"];
				// If current key exists into previous test session
				if(array_key_exists($key,$previousResult))
				{
					// If previousResult[key] == currentResult[key], test is unchanged
					if($previousResult[$key]["decision_criteria_id"] == $result["decision_criteria_id"])
					{
						$this->unchangedFeatures[$result["label"]]++;
					}
				}
			}
		}
	}

	//============================================================================================//
	//      TESTSET VIEWS                                                                         //
	//============================================================================================//

	/**
	 * Homepage of the testset index view. Display environments and images grouped by testsets.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeProjectTestset(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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
		else
			$this->currentProject = $this->projects[0];

		// Check if at least one project has been found and user is not authenticated
		if(!$this->getUser()->isAuthenticated() && $this->currentProject == null)
		{
			$this->getUser()->setFlash("error", "Your security level is not sufficient to visualize projects. Please login with appropriate credentials!");
			$this->redirect("sf_guard_signin");
		}

		// Set current filter
		$this->currentFilter = $request->getParameter("filter", "recent");
		$filter = ($this->currentFilter == "all") ? false : true;

		// Get list of all products (Smartphone, Tablet, Netbook, Laptop, ...)
		$this->products = Doctrine_Core::getTable("Project")->getBasicProducts($this->projectGroupId, $this->currentProject["id"]);

		// Get list of all test environments and images with build ids
		$this->imagesForEnvironments = Doctrine_Core::getTable("Project")->getImagesForEnvironmentsForTestsets($this->projectGroupId, $this->currentProject["id"], MiscUtils::arrayColumn($this->products, 'id', true), $filter);

		// ...convert data retrieved from database
		if(count($this->imagesForEnvironments) > 0)
		{
			$arrayProducts = array();

			$previousProduct = null;
			$previousTestset = null;
			$previousEnvironment = null;
			$previousImage = null;

			foreach($this->imagesForEnvironments as $data)
			{
				if($data["product_id"] != $previousProduct)
				{
					$arrayProducts[$data["product_id"]] = array("product_id" => $data["product_id"], "testsets" => array());
					$previousProduct = $data["product_id"];

					$previousTestset = null;
					$previousEnvironment = null;
					$previousImage = null;
				}

				if($data["ts_testset"] != $previousTestset)
				{
					$arrayProducts[$data["product_id"]]["testsets"][$data["ts_testset"]] = array("ts_testset" => $data["ts_testset"], "ts_testset_slug" => $data["ts_testset_slug"], "environments" => array());
					$previousTestset = $data["ts_testset"];

					$previousEnvironment = null;
					$previousImage = null;
				}

				if($data["te_name"] != $previousEnvironment)
				{
					$arrayProducts[$data["product_id"]]["testsets"][$data["ts_testset"]]["environments"][$data["te_name"]] = array("te_id" => $data["te_id"], "te_name" => $data["te_name"], "te_slug" => $data["te_slug"], "images" => array());
					$previousEnvironment = $data["te_name"];

					$previousImage = null;
				}

				if($data["i_name"] != $previousImage)
				{
					$arrayProducts[$data["product_id"]]["testsets"][$data["ts_testset"]]["environments"][$data["te_name"]]["images"][$data["i_name"]] = array("i_id" => $data["i_id"], "i_name" => $data["i_name"], "i_slug" => $data["i_slug"]);
					$previousImage = $data["i_name"];
				}
			}

			$this->imagesForEnvironments = $arrayProducts;
		}
		else
			$this->imagesForEnvironments = array();
	}

	/**
	 * Product view for testset index. Display test sessions grouped by images for the given product.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeProductTestset(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForProductForTestsets($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForProductForTestsets($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Testset view. Display test sessions grouped by images for the given testset.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeTestset(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForTestset($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentTestset["testset"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForTestset($this->projectGroupId, $this->currentProject["id"], $this->currentProduct["id"], $this->currentTestset["testset"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Environment view for testset index. Display test sessions grouped by images for the given environment.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeEnvironmentTestset(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForEnvironmentForTestsets($this->projectGroupId, $this->currentProject["id"], $this->currentTestset["testset"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForEnvironmentForTestsets($this->projectGroupId, $this->currentProject["id"], $this->currentTestset["testset"], $this->currentProduct["id"], $this->currentEnvironment["id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Image view for testset index. Display test sessions of given image.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeImageTestset(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		// Initialize values
		$this->regressionCount = $this->passToFailCount = $this->passToNaCount = 0;
		$this->progressCount = $this->failToPassCount = $this->naToPassCount = 0;
		$this->newPassCount = $this->newFailCount = $this->newNaCount = 0;

		$this->lastSessions = Doctrine_Core::getTable("TestSession")->getLatestSessionsForImageTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset["testset"], $this->currentEnvironment['id'], $this->currentImage['id'], 2);
		if(!empty($this->lastSessions[0]) AND !empty($this->lastSessions[1]))
		{
			$i = 0;
			while($i < 2) {
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->lastSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->lastSessions[$i]["results"] = $array;
				$i++;
			}
			$previousSessionResults = $this->lastSessions[1]["results"];

			foreach($this->lastSessions[0]["results"] as $key => $lastSessionResult)
			{
				if(isset($previousSessionResults[$key]))
				{
					$previousSessionResult = $previousSessionResults[$key];

					if($previousSessionResult["decision_criteria_id"] == -1)
					{
						if($lastSessionResult["decision_criteria_id"] == -2)
						{
							$this->passToFailCount++;
						}
						else if($lastSessionResult["decision_criteria_id"] == -3)
						{
							$this->passToNaCount++;
						}
					}
					else if ($lastSessionResult["decision_criteria_id"] == -1)
					{
						if($previousSessionResult["decision_criteria_id"] == -2)
						{
							$this->failToPassCount++;
						}
						else if($previousSessionResult["decision_criteria_id"] == -3)
						{
							$this->naToPassCount++;
						}
					}
				}
				else
				{
					if($lastSessionResult["decision_criteria_id"] == -1)
					{
						$this->newPassCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -2)
					{
						$this->newFailCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -3)
					{
						$this->newNaCount++;
					}
				}
			}

			$this->regressionCount = $this->passToFailCount + $this->passToNaCount;
			$this->progressCount = $this->failToPassCount + $this->naToPassCount;
		}

		$this->totalNumberOfHistograms = sfConfig::get("app_views_number_of_histograms", 20);
		// Get total of passed/failed/blocked results grouped by images
		$this->resultsNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbersForImageForTestsets($this->projectGroupId, $this->currentProject["id"], $this->currentTestset["testset"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->currentImage["id"], $this->totalNumberOfHistograms);

		// Get list of all test sessions with their associated images
		$this->sessionsForImages = Doctrine_Core::getTable("TestSession")->getSessionsForImageForTestsets($this->projectGroupId, $this->currentProject["id"], $this->currentTestset["testset"], $this->currentProduct["id"], $this->currentEnvironment["id"], $this->currentImage["id"]);

		// Get sessions' max number of results
		$this->totalResults = 0;
		foreach($this->sessionsForImages as $data)
		{
			if($this->totalResults < $data["total"])
				$this->totalResults = $data["total"];
		}
	}

	/**
	 * Display a test session for testset index views.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeSessionTestset(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		// Set current display
		$this->currentDisplay = $request->getParameter("display", "basic");
		if($this->currentDisplay != "basic" && $this->currentDisplay != "detailed" && $this->currentDisplay != "history")
			$this->currentDisplay = "basic";

		// Set current session
		if($request->getParameter("id"))
		{
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id")." AND ts.published = 1";
			$this->currentSession = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->currentSession), "This test session is not accessible anymore or you lack sufficient privileges!");

			// Get list of features with their numbers for current report
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			if($this->currentDisplay != "basic")
			{
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->currentSession["results"] = $array;

				// Get all measurements (and add associative key for each too)
				$array = array();
				$array_keys = array();
				$targetPercentageSum = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
					$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
				}
				$this->currentSession["measures"] = $array;
				if(count($array) > 0)
					$this->nftIndex = round($targetPercentageSum / count($array) * 100);
			}
			else
			{
				// Get all measurements (and add associative key for each too)
				$targetPercentageSum = 0;
				$count = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				{
					$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
					$count++;
				}
				if($count > 0)
					$this->nftIndex = round($targetPercentageSum / $count * 100);
			}
		}

		// Get author and editor of the test session
		$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["user_id"]."";
		$this->author = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if($this->currentSession["editor_id"])
		{
			$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["editor_id"]."";
			$this->editor = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			$this->editor = $this->author;
		}

		// Get file attachments
		$this->attachments = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 2);
		$this->resultFiles = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 1);

		// Get previous and next session
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDateForTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset['testset'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, false);
		if(!empty($result))
			$this->previousSession = $result[0];
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDateForTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset['testset'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, true);
		if(!empty($result))
			$this->nextSession = $result[0];

		// Get numbers of result summary
		$this->currentSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbers($this->currentSession["id"]);
		$this->previousSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSummaryNumbersForTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset['testset'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3));
		$this->reversedPreviousSummaryNumbers = array_reverse($this->previousSummaryNumbers);

		// Get 3 (by default) previous test sessions
		$this->previousSessions = Doctrine_Core::getTable("TestSession")->getSessionsByDateForTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset['testset'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3), false);
		for($i=0; $i<count($this->previousSessions); $i++)
		{
			// Get list of features with their numbers for previous reports
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSessions[$i]['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSessions[$i]["features"] = $array;

			if($this->currentDisplay == "history")
			{
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->previousSessions[$i]["results"] = $array;

				// Get all measurements (and add associative key for each too)
				$array = array();
				$array_keys = array();
				$targetPercentageSum = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[$i]["id"]) as $key => $value)
				{
					if(!empty($value["measures"]))
					{
						$key = MiscUtils::slugify($value["name"].$value["label"]);
						if(array_key_exists($key, $array))
						{
							$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
							$key = $key."-".$array_keys[$key];
						}
						$array[$key] = $value;
						$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
					}
				}
				$this->previousSessions[$i]["measures"] = $array;
				if(count($array) > 0)
					$this->previousNftIndex = round($targetPercentageSum / count($array) * 100);
			}
			else
			{
				// Get all measurements (and add associative key for each too)
				$targetPercentageSum = 0;
				$count = 0;
				foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[count($this->previousSessions) - 1]["id"]) as $key => $value)
				{
					if(!empty($value["measures"]))
					{
						$targetPercentageSum += $value["measures"]["value"]["value"] / $value["measures"]["target"]["value"];
						$count++;
					}
				}
				if($count > 0)
					$this->previousNftIndex = round($targetPercentageSum / $count * 100);
			}
		}

		// Get session' max number of results for features
		$this->totalFeaturesResults = 0;
		foreach($this->currentSession["features"] as $data)
		{
			if($this->totalFeaturesResults < $data["total"])
				$this->totalFeaturesResults = $data["total"];
		}
	}

	/**
	 * Display a simplified test session for print purpose.
	 *
	 * @param sfWebRequest $request
	 */
	public function executePrintTestset(sfWebRequest $request)
	{
		// Set referer
		$this->getUser()->setReferer($request->getUri());

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

		// Set current session
		if($request->getParameter("id"))
		{
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id")." AND ts.published = 1";
			$this->currentSession = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->currentSession), "This test session is not accessible anymore or you lack sufficient privileges!");

			// Get list of features with their numbers for current report
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			$this->currentSession["results"] = $array;

			// Get all measurements (and add associative key for each too)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			$this->currentSession["measures"] = $array;
		}

		// Get author and editor of the test session
		$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["user_id"]."";
		$this->author = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if($this->currentSession["editor_id"])
		{
			$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["editor_id"]."";
			$this->editor = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			$this->editor = $this->author;
		}

		// Get file attachments
		$this->attachments = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 2);
		$this->resultFiles = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 1);

		// Get previous and next session
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDateForTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset['testset'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, false);
		if(!empty($result))
			$this->previousSession = $result[0];
		$result = Doctrine_Core::getTable("TestSession")->getSessionsByDateForTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset['testset'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], 1, true);
		if(!empty($result))
			$this->nextSession = $result[0];

		// Get numbers of result summary
		$this->currentSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbers($this->currentSession["id"]);
		$this->previousSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSummaryNumbersForTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset['testset'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3));
		$this->reversedPreviousSummaryNumbers = array_reverse($this->previousSummaryNumbers);

		// Get 3 (by default) previous test sessions
		$this->previousSessions = Doctrine_Core::getTable("TestSession")->getSessionsByDateForTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset['testset'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3), false);
		for($i=0; $i<count($this->previousSessions); $i++)
		{
			// Get list of features with their numbers for previous reports
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSessions[$i]['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSessions[$i]["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSessions[$i]["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			$this->previousSessions[$i]["results"] = $array;

			// Get all measurements (and add associative key for each too)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->previousSessions[$i]["id"]) as $key => $value)
			{
				if(!empty($value["measures"]))
					$array[MiscUtils::slugify($value["id"].$value["name"].$value["label"])] = $value;
			}
			$this->previousSessions[$i]["measures"] = $array;
		}

		// Get session' max number of results for features
		$this->totalFeaturesResults = 0;
		foreach($this->currentSession["features"] as $data)
		{
			if($this->totalFeaturesResults < $data["total"])
				$this->totalFeaturesResults = $data["total"];
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeCompareTestset(sfWebRequest $request)
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

		// Initialize values
		$this->regressionCount = $this->passToFailCount = $this->passToNaCount = 0;
		$this->progressCount = $this->failToPassCount = $this->naToPassCount = 0;
		$this->newPassCount = $this->newFailCount = $this->newNaCount = 0;

		$this->lastSessions = Doctrine_Core::getTable("TestSession")->getLatestSessionsForImageTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset["testset"], $this->currentEnvironment['id'], $this->currentImage['id'], 2);
		if(!empty($this->lastSessions[0]) AND !empty($this->lastSessions[1]))
		{
			$i = 0;
			while($i < 2) {
				// Get all results (and add associative key for each result)
				$array = array();
				$array_keys = array();
				foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->lastSessions[$i]["id"]) as $key => $value)
				{
					$key = MiscUtils::slugify($value["name"].$value["label"]);
					if(array_key_exists($key, $array))
					{
						$array_keys[$key] = (array_key_exists($key, $array_keys)) ? $array_keys[$key] += 1 : 1;
						$key = $key."-".$array_keys[$key];
					}
					$array[$key] = $value;
				}
				$this->lastSessions[$i]["results"] = $array;
				$i++;
			}
			$previousSessionResults = $this->lastSessions[1]["results"];

			foreach($this->lastSessions[0]["results"] as $key => $lastSessionResult)
			{
				if(isset($previousSessionResults[$key]))
				{
					$previousSessionResult = $previousSessionResults[$key];

					if($previousSessionResult["decision_criteria_id"] == -1)
					{
						if($lastSessionResult["decision_criteria_id"] == -2)
						{
							$this->passToFailCount++;
						}
						else if($lastSessionResult["decision_criteria_id"] == -3)
						{
							$this->passToNaCount++;
						}
					}
					else if ($lastSessionResult["decision_criteria_id"] == -1)
					{
						if($previousSessionResult["decision_criteria_id"] == -2)
						{
							$this->failToPassCount++;
						}
						else if($previousSessionResult["decision_criteria_id"] == -3)
						{
							$this->naToPassCount++;
						}
					}
				}
				else
				{
					if($lastSessionResult["decision_criteria_id"] == -1)
					{
						$this->newPassCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -2)
					{
						$this->newFailCount++;
					}
					else if($lastSessionResult["decision_criteria_id"] == -3)
					{
						$this->newNaCount++;
					}
				}
			}

			$this->regressionCount = $this->passToFailCount + $this->passToNaCount;
			$this->progressCount = $this->failToPassCount + $this->naToPassCount;


			$this->currentSession = $this->lastSessions[0];
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->currentSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
			$this->currentSession["results"] = $array;

			$this->previousSession = $this->lastSessions[1];
			$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->previousSession['id']);

			// Add associative key to each feature
			$array = array();
			foreach($features as $key => $value)
				$array[MiscUtils::slugify($value["label"])] = $value;
			$this->previousSession["features"] = $array;

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->previousSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
			$this->previousSession["results"] = $array;

			$this->unchangedFeatures = array();
			foreach($this->currentSession["features"] as $feature)
			{
				$this->unchangedFeatures[$feature["label"]] = 0;
			}

			// Compare results to determine number of unchanged tests
			foreach($this->currentSession["results"] as $key => $result)
			{
				$previousResult = $this->previousSession["results"];
				// If current key exists into previous test session
				if(array_key_exists($key,$previousResult))
				{
					// If previousResult[key] == currentResult[key], test is unchanged
					if($previousResult[$key]["decision_criteria_id"] == $result["decision_criteria_id"])
					{
						$this->unchangedFeatures[$result["label"]]++;
					}
				}
			}
		}
	}

	//============================================================================================//
	//      FORMS AND ACTIONS FOR FORMS                                                           //
	//============================================================================================//

	/**
	 * Display the form to add a new test session.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeAdd(sfWebRequest $request)
	{
		$url_prefix = $this->getContext()->getInstance()->getRequest()->getUriPrefix().$this->getContext()->getInstance()->getRequest()->getRelativeUrlRoot();
		// Set referer
		if(!$request->isMethod("post"))
		{
			$route = $this->getContext()->getRouting()->findRoute(MiscUtils::getUri($url_prefix, $this->getUser()->getCurrentReferer()));
			if(strpos($route["pattern"], "upload") === false)
				$this->getUser()->setReferer($request->getUri());
		}

		// Get referer to set default selected values in form
		$referer = MiscUtils::getUri($url_prefix, $this->getUser()->getReferer());
		$route = $this->getContext()->getRouting()->findRoute($referer);
		$defaultProject = (isset($route['parameters']['project'])) ? $route['parameters']['project'] : "";
		$defaultProduct = (isset($route['parameters']['product'])) ? $route['parameters']['product'] : "";
		$defaultEnvironment = (isset($route["parameters"]["environment"])) ? $route["parameters"]["environment"] : "";
		$defaultImage = (isset($route["parameters"]["image"])) ? $route["parameters"]["image"] : "";
		$defaultBuild = (isset($route["parameters"]["build"])) ? $route["parameters"]["build"] : "";
		$defaultTestset = (isset($route["parameters"]["testset"])) ? $route["parameters"]["testset"] : "";

		// Get project group id from project group name defined in app.yml
		$this->projectGroupId = Doctrine_Core::getTable("sfGuardGroup")->getProjectGroupId(sfConfig::get('app_project_group'));
		if($this->projectGroupId == null)
			throw new ErrorException("Unable to retrieve project group configuration! Application might need additional configuration or data!", 500);

		// Get security level of current user
		$userSecurityLevel = ($this->getUser()->isAuthenticated()) ? $userSecurityLevel = $this->getUser()->getGuardUser()->getProfile()->getSecurityLevel() : 0;

		// Get last build ids
		$this->lastBuildIds = Doctrine_Core::getTable("TestSession")->getLastBuildIds();
		// Get last testsets
		$this->lastTestsets = Doctrine_Core::getTable("TestSession")->getLastTestsets();

		// Get last environments and images used to add sessions (for user experience)
		$this->lastEnvironments = Doctrine_Core::getTable("TestEnvironment")->getLastEnvironments();
		$this->lastImages = Doctrine_Core::getTable("Image")->getLastImages();

		$this->mandatoryBuildId = sfConfig::get('app_mandatory_build_id', false);
		$this->mandatoryTestset = sfConfig::get('app_mandatory_testset', false);

		// Create a new session form to import test results
		$this->form = new ImportForm(array(), array("projectGroupId" => $this->projectGroupId, "securityLevel" => $userSecurityLevel, "projectSlug" => $defaultProject, "productSlug" => $defaultProduct, "environmentSlug" => $defaultEnvironment, "imageSlug" => $defaultImage, "buildSlug" => $defaultBuild, "testsetSlug" => $defaultTestset, "mandatoryBuildId" => $this->mandatoryBuildId, "mandatoryTestset" => $this->mandatoryTestset));

		// Update list of products when submitting form (to match ajax selection)
		if(isset($_POST["test_session"]))
		{
		    $project = Doctrine_Core::getTable("Project")->getBasicProjectById($_POST["test_session"]["project"]);
		    $this->form = new ImportForm(array(), array("projectGroupId" => $this->projectGroupId, "securityLevel" => $userSecurityLevel, "projectSlug" => $project["name_slug"], "productSlug" => $defaultProduct, "environmentSlug" => $defaultEnvironment, "imageSlug" => $defaultImage, "buildSlug" => $defaultBuild, "testsetSlug" => $defaultTestset, "mandatoryBuildId" => $this->mandatoryBuildId, "mandatoryTestset" => $this->mandatoryTestset));
		}

		// Process the form
		if($request->isMethod("post"))
		{
			$this->processAdd($request, $this->form);
		}
	}

	/**
	 * Display a form to edit a test session.
	 *
	 * @param sfWebRequest $request
	 */
	public function executeEdit(sfWebRequest $request)
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

		// Set submit button's label and cancel's route
		if(strpos(sfContext::getInstance()->getRouting()->getCurrentRouteName(), "finalize") === false)
		{
			$this->submitButton = "Save";
			$this->cancelRoute = "test_session";
		}
		else
		{
			$this->submitButton = "Finalize";
			$this->cancelRoute = "cancel_report";

			// Set prefill templates
			$prefill_templates = sfConfig::get('app_prefill_templates');
			if(!is_null($prefill_templates))
				foreach($prefill_templates as $key => $template)
				{
					// Remove "template" from "template_my_app_var"
					$key = substr($key, 8);
					// Camelize "_my_app_var" to "myAppVar"
					$key = MiscUtils::camelize($key);

					if(is_file(sfConfig::get('sf_upload_dir')."/../templates/".$template))
					{
						$content = file_get_contents(sfConfig::get('sf_upload_dir')."/../templates/".$template);
						${$key} = $content;
					}
				}
		}
		$this->currentRoute = sfContext::getInstance()->getRouting()->getCurrentRouteName();

		// Set current session
		if($request->getParameter("id"))
		{
			$query = "SELECT * FROM ".sfConfig::get("app_table_qa_generic").".test_session ts WHERE ts.id = ".$request->getParameter("id")."";

			/*
			 * WORKAROUND To detect "finalize" in route's name because empty URL parameters are not
			* detected by hasParameter() method.
			*/
			if(strpos(sfContext::getInstance()->getRouting()->getCurrentRouteName(), "finalize") === false)
				$query .= " AND ts.published = 1";
			else
				$query .= " AND ts.published = 0";

			$this->currentSession = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$this->forward404Unless(!empty($this->currentSession), "This test session is not accessible anymore or you lack sufficient privileges!");
		}

		// Get list of features with their numbers for current report
		$features = Doctrine_Core::getTable("TestSession")->getFeatures($this->currentSession['id']);

		// Add associative key to each feature
		$array = array();
		foreach($features as $key => $value)
			$array[MiscUtils::slugify($value["label"])] = $value;
		$this->currentSession["features"] = $array;

		// Get all results (and add associative key for each result)
		$array = array();
		foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
			$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
		$this->currentSession["results"] = $array;

		// Get all measures (and add associative key for each measure)
		$array = array();
		foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
			$array[MiscUtils::slugify($value["name"].$value["label"])] = $value;
		$this->currentSession["measures"] = $array;

		// Get author and editor of the test session
		$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["user_id"]."";
		$this->author = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if($this->currentSession["editor_id"])
		{
			$query = "SELECT * FROM sf_guard_user u WHERE u.id = ".$this->currentSession["editor_id"]."";
			$this->editor = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			$this->editor = $this->author;
		}

		// Get file attachments
		$this->attachments = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 2);
		$this->resultFiles = Doctrine_Core::getTable("TestSession")->getFileAttachments($this->currentSession["id"], 1);

		// Get numbers of result summary
		$this->currentSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSessionNumbers($this->currentSession["id"]);
		$this->previousSummaryNumbers = Doctrine_Core::getTable("TestSession")->getSummaryNumbers($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id'], $this->currentSession['created_at'], sfConfig::get("app_views_number_of_histograms_in_session", 3));
		$this->reversedPreviousSummaryNumbers = array_reverse($this->previousSummaryNumbers);

		$this->mandatoryBuildId = sfConfig::get('app_mandatory_build_id', false);
		$this->mandatoryTestset = sfConfig::get('app_mandatory_testset', false);

		// Initialize form
		$this->form = new SessionForm(array(), array(
			"projectGroupId" => $this->projectGroupId,
			"securityLevel" => $userSecurityLevel,
			"projectId" => $this->currentProject["id"],
			"productId" => $this->currentProduct["id"],
			"session" => $this->currentSession,
			"environment" => $this->currentEnvironment,
			"image" => $this->currentImage,
			"mandatoryBuildId" => $this->mandatoryBuildId,
			"mandatoryTestset" => $this->mandatoryTestset,
		));

		// Set prefilled templates
		if($this->submitButton == "Finalize")
		{
			$this->form->setDefault("qa_summary", isset($qaSummary) ? $qaSummary : "");
			$this->form->setDefault("test_objective", isset($testObjective) ? $testObjective : "");
			$this->form->setDefault("environment_summary", isset($environmentSummary) ? $environmentSummary : "");
			$this->form->setDefault("issue_summary", isset($issueSummary) ? $issueSummary : "");
		}

		// Update list of products when submitting form (to match ajax selection)
		if(isset($_POST["test_session"]))
		{
		    $project = Doctrine_Core::getTable("Project")->getBasicProjectById($_POST["test_session"]["project"]);
	        $this->form = new SessionForm(array(), array(
	        	"projectGroupId" => $this->projectGroupId,
	        	"securityLevel" => $userSecurityLevel,
	        	"projectId" => $project["id"],
	        	"productId" => $this->currentProduct["id"],
	        	"session" => $this->currentSession,
	        	"environment" => $this->currentEnvironment,
	        	"image" => $this->currentImage,
	        	"mandatoryBuildId" => $this->mandatoryBuildId,
	        	"mandatoryTestset" => $this->mandatoryTestset,
	        ));
		}

		// Process form
		if($request->isMethod("post"))
		{
			$this->processEdit($request, $this->form);
		}
	}

	/**
	 * Delete a test session and all dependencies
	 *
	 * @param sfWebRequest $request
	 */
	public function executeCancel(sfWebRequest $request)
	{
		// Get qa_generic db
		$qa_generic = sfConfig::get("app_table_qa_generic");

		// Get test session id
		$testSessionId = $request->getParameter("id");

		// Get session object
		$session = Doctrine_Core::getTable("TestSession")->find($testSessionId);

		$this->forward404Unless($session);

		// Get table name id corresponding to 'test_session'
		$tableNameTestSession = Doctrine_Core::getTable("TableName")->findOneByName('test_session');
		$tableNameTestSessionId = $tableNameTestSession->getId();

		// Get file attachment paths
		$query = "SELECT fa.filename, fa.link FROM ".$qa_generic.".file_attachment fa WHERE fa.table_name_id = ".$tableNameTestSessionId." AND fa.table_entry_id = ".$testSessionId;
		$attachments = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

		// Delete directories and files
		foreach ($attachments as $attachment)
		{
			$dirPath = str_replace("/".$attachment['filename'], "", $attachment['link']);
			MiscUtils::deleteDir($dirPath);
		}

		//Delete entries into file_attachment table
		$query = "DELETE FROM ".$qa_generic.".file_attachment WHERE table_name_id = ".$tableNameTestSessionId." AND table_entry_id = ".$testSessionId;
		Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);

		// Get table name id corresponding to 'test_result'
		$tableNameTestResult = Doctrine_Core::getTable("TableName")->findOneByName('test_result');
		$tableNameTestResultId = $tableNameTestResult->getId();

		// Get all test result id about current test session
		$query = "SELECT tr.id FROM ".$qa_generic.".test_result tr WHERE tr.test_session_id = ".$testSessionId;
		$testResults = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

		if (count($testResults) > 0)
		{
			$testResultIdList = array();
			foreach ($testResults as $testResult)
			{
				$testResultIdList[] = $testResult['id'];
			}
			$stringTestResultIdList = implode(",", $testResultIdList);

			// Delete entries into complementary_tool_relation table
			$query = "DELETE FROM ".$qa_generic.".complementary_tool_relation WHERE table_name_id = ".$tableNameTestResultId." AND table_entry_id IN ( ".$stringTestResultIdList." )";
			Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);

			// Delete entries into measure table
			$query = "DELETE FROM ".$qa_generic.".measure WHERE test_result_id IN ( ".$stringTestResultIdList." )";
			Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);

			// Delete entries into test_result table
			$query = "DELETE FROM ".$qa_generic.".test_result WHERE test_session_id = ".$testSessionId;
			Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);
		}

		// Delete entry into test_session table
		$query = "DELETE FROM ".$qa_generic.".test_session WHERE id = ".$testSessionId;
		Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query);

		$this->redirect($this->getUser()->getReferer());
	}

	/**
	 * Unpublish a test session (i.e set the published field to 0).
	 *
	 * @param sfWebRequest $request
	 */
	public function executeDelete(sfWebRequest $request)
	{
		$session = Doctrine_Core::getTable("TestSession")->find($request->getParameter("id"));
		$this->forward404Unless($session);

		$session->setPublished(0);
		$session->setUpdatedAt(date("Y-m-d H:m:i"));
		$session->setEditorId($this->getUser()->getGuardUser()->getId());
		$session->save();

		$this->getUser()->setFlash("notice", "Test session was deleted successfully");

		$this->redirect("@homepage");
	}

	/**
	 * Process the form to add a new test session.
	 *
	 * @param sfWebRequest $request
	 * @param ImportForm $form
	 */
	protected function processAdd(sfWebRequest $request, ImportForm $form)
	{
		$form->bind(
				$request->getParameter($form->getName()),
				$request->getFiles($form->getName())
		);

		if($form->isValid())
		{
			// Get sent values and uploaded files
			$values = $form->getValues();
			$files = $request->getFiles();

			// Retrieve values from form
			$projectGroupId = $values["project_group_id"];
			$projectId = $values["project"];
			$productId = $values["product"];
			$date = $values["date"]." ".date("H:i:s");
			$buildId = $values["build_id"];
			$testType = $values["testset"];
			$title = $values["name"];
			$environmentForm = $form->getValue("environmentForm");
			$imageForm = $form->getValue("imageForm");
			$userId = $this->getUser()->getGuardUser()->getId();
			$buildSlug = MiscUtils::slugify($buildId);
			$testTypeSlug = MiscUtils::slugify($testType);

			// Customize database connection to begin a transactionnal query
			$conn = Doctrine_Manager::getInstance()->getConnection("qa_generic");
			$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, FALSE);
			$conn->beginTransaction();

			$project = Doctrine_Core::getTable("Project")->getBasicProjectById($projectId);
			$product = Doctrine_Core::getTable("ProductType")->getBasicProductById($productId);

			// Create a new relationship between project group, project and product if needed
			$projectToProductId = Doctrine_Core::getTable("ProjectToProduct")->getProjectToProductId($projectGroupId, $projectId, $productId);
			if($projectToProductId == null)
			{
				$projectToProduct = new ProjectToProduct();
				$projectToProduct->setProjectGroupId($projectGroupId);
				$projectToProduct->setProjectId($projectId);
				$projectToProduct->setProductId($productId);
				$projectToProduct->save($conn);

				$projectToProductId = $projectToProduct->getId();
			}

			// Create a new environment if needed
			$environment = Doctrine_Core::getTable("TestEnvironment")->findByArray($environmentForm);
			if($environment == null)
			{
				// Add new environment
				$environment = new TestEnvironment();
				$environment->setName($environmentForm["name"]);
				$environment->setDescription($environmentForm["description"]);
				$environment->setCpu($environmentForm["cpu"]);
				$environment->setBoard($environmentForm["board"]);
				$environment->setGpu($environmentForm["gpu"]);
				$environment->setOtherHardware($environmentForm["other_hardware"]);

				// Check if its slug does not already exist and generate a new one if needed
				$slug = MiscUtils::slugify($environmentForm["name"]);
				$size = 1;
				while(Doctrine_Core::getTable("TestEnvironment")->checkSlug($slug))
				{
					$slug = MiscUtils::slugify($environmentForm["name"]).substr(microtime(), -$size);
					$size++;
				}
				$environment->setNameSlug($slug);
				$environment->save($conn);

				// Convert object into associative array
				$environment = $environment->toArray();
			}

			// Create a new image if needed
			$image = Doctrine_Core::getTable("Image")->findByArray($imageForm);
			if($image == null)
			{
				// Add new image
				$image = new Image();
				$image->setName($imageForm["name"]);
				$image->setDescription($imageForm["description"]);
				$image->setOs($imageForm["os"]);
				$image->setDistribution($imageForm["distribution"]);
				$image->setVersion($imageForm["version"]);
				$image->setKernel($imageForm["kernel"]);
				$image->setArchitecture($imageForm["architecture"]);
				$image->setOtherFw($imageForm["other_fw"]);
				$image->setBinaryLink($imageForm["binary_link"]);
				$image->setSourceLink($imageForm["source_link"]);

				// Check if its slug does not already exist and generate a new one if needed
				$slug = MiscUtils::slugify($imageForm["name"]);
				$size = 1;
				while(Doctrine_Core::getTable("Image")->checkSlug($slug))
				{
					$slug = MiscUtils::slugify($imageForm["name"]).substr(microtime(), -$size);
					$size++;
				}

				$image->setNameSlug(MiscUtils::slugify($slug));
				$image->save($conn);

				// Convert object into associative array
				$image = $image->toArray();
			}

			// Create a new configuration relationship if needed
			$configurationId = Doctrine_Core::getTable("Configuration")->getConfigurationId($projectToProductId, $environment["id"], $image["id"]);
			if($configurationId == null)
			{
				$configuration = new Configuration();
				$configuration->setProjectToProductId($projectToProductId);
				$configuration->setTestEnvironmentId($environment["id"]);
				$configuration->setImageId($image["id"]);
				$configuration->save($conn);

				$configurationId = $configuration->getId();
			}

			// Add the new session into DB
			$testSession = new TestSession();
			if(empty($title))
			{
				$title = $product["name"]." Test Report: ".$environment["name"]." ".$image["name"]." ".substr($date, 0 , -3);
				if(!empty($buildId))
					$title .= " Build ID: ".$buildId;
			}
			$testSession->setBuildId($buildId);
			$testSession->setTestset($testType);
			$testSession->setName($title);
			$testSession->setUserId($userId);
			$testSession->setCreatedAt($date);
			$testSession->setUpdatedAt($date);
			$testSession->setStatus(2);
			$testSession->setPublished(0);
			$testSession->setConfigurationId($configurationId);
			$testSession->setBuildSlug($buildSlug);
			$testSession->setTestsetSlug($testTypeSlug);
			$testSession->save($conn);

			$testSessionId = $testSession->getId();

			$tableName = Doctrine_Core::getTable("TableName")->findOneByName("test_session");
			$tableNameId = $tableName->getId();

			// Concatenate directory path
			$dir_path = sfConfig::get('sf_upload_dir') . "/testsession_" . $testSessionId;

			// Upload attachments and result files
			foreach($files["upload"] as $key => $file)
			{
				$fileName = $file['name'];
				$fileSize = $file['size'];
				$fileType = $file['type'];
				$fileError = $file['error'];
				$fileChecksum = sha1_file($file["tmp_name"]);
				// Check file error and file size
				if (!$fileError AND $fileSize <= sfConfig::get('app_max_file_size','10000000'))
				{
					if (!is_dir($dir_path))
					{
						mkdir($dir_path, 0777, true);
					}
					$dest_path = $dir_path . "/" . $fileName;
					// Move file to uploads directory
					move_uploaded_file($file['tmp_name'], $dest_path);
					// If it is an XML file, parse it and fill qa_generic database
					if ((preg_match("#\.xml *$#i", $fileName)) || (preg_match("#\.csv *$#i", $fileName)))
					{
						// Fill qa_generic database
						if ($err_code = Import::file($dest_path, $testSessionId, $configurationId, $conn))
						{
							$error_message = Import::getImportErrorMessage($err_code);
							MiscUtils::deleteDir($dir_path);
							$conn->rollback();
							$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
							$this->getUser()->setFlash("error", "Invalid file content on ".$fileName." : ".$error_message);
							$this->redirect("add_report", array());
						}
					}
					else
					{
						MiscUtils::deleteDir($dir_path);
						$conn->rollback();
						$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
						$this->getUser()->setFlash("error", "Invalid file format : only XML and CSV format are supported");
						$this->redirect("add_report", array());
					}

					$web_path = "/uploads"."/testsession_".$testSessionId."/".$fileName;

					$fileAttachment = new FileAttachment();
					$fileAttachment->setName($fileName);
					$fileAttachment->setUserId($userId);
					$fileAttachment->setUploadedAt(date("Y-m-d H:i:s"));
					$fileAttachment->setFilename($fileName);
					$fileAttachment->setFileSize($fileSize);
					$fileAttachment->setFileMimeType($fileType);
					$fileAttachment->setLink($web_path);
					$fileAttachment->setChecksum($fileChecksum);
					$fileAttachment->setTableNameId($tableNameId);
					$fileAttachment->setTableEntryId($testSessionId);
					$fileAttachment->setCategory(1);

					$fileAttachment->save($conn);
				}
				else
				{
					MiscUtils::deleteDir($dir_path);
					$conn->rollback();
					$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
					$this->getUser()->setFlash("error", "File size limit reached");
					$this->redirect("add_report", array());
				}
			}

			$conn->commit();
			$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);

			$this->redirect("finalize_report", array("project" => $project["name_slug"], "product" => $product["name_slug"], "environment" => $environment["name_slug"], "image" => $image["name_slug"], "id" => $testSessionId));
		}
	}

	/**
	 * Process the form to edit an existing test session.
	 *
	 * @param sfWebRequest $request
	 * @param SessionForm $form
	 */
	protected function processEdit(sfWebRequest $request, SessionForm $form)
	{
		$form->bind(
				$request->getParameter($form->getName()),
				$request->getFiles($form->getName())
		);

		if($form->isValid())
		{
			$qa_generic = sfConfig::get("app_table_qa_generic");

			// Get sent values and uploaded files
			$values = $form->getValues();
			$files = $request->getFiles();

			// Retrieve values from form
			$projectGroupId = $values["project_group_id"];
			$projectId = $values["project"];
			$productId = $values["product"];

			// Get test environment and image names
			$environmentForm = $form->getValue("environmentForm");
			$imageForm = $form->getValue("imageForm");

			// Create a new relationship between project group, project and product if needed
			$projectToProductId = Doctrine_Core::getTable("ProjectToProduct")->getProjectToProductId($projectGroupId, $projectId, $productId);
			if($projectToProductId == null)
			{
				$projectToProduct = new ProjectToProduct();
				$projectToProduct->setProjectGroupId($projectGroupId);
				$projectToProduct->setProjectId($projectId);
				$projectToProduct->setProductId($productId);
				$projectToProduct->save($conn);

				$projectToProductId = $projectToProduct->getId();
			}

			// Create a new environment if needed
			$environment = Doctrine_Core::getTable("TestEnvironment")->findByArray($environmentForm);
			if($environment == null)
			{
				// Add new environment
				$environment = new TestEnvironment();
				$environment->setName($environmentForm["name"]);
				$environment->setDescription($environmentForm["description"]);
				$environment->setCpu($environmentForm["cpu"]);
				$environment->setBoard($environmentForm["board"]);
				$environment->setGpu($environmentForm["gpu"]);
				$environment->setOtherHardware($environmentForm["other_hardware"]);

				// Check if its slug does not already exist and generate a new one if needed
				$slug = MiscUtils::slugify($environmentForm["name"]);
				$size = 1;
				while(Doctrine_Core::getTable("TestEnvironment")->checkSlug($slug))
				{
					$slug = MiscUtils::slugify($environmentForm["name"]).substr(microtime(), -$size);
					$size++;
				}
				$environment->setNameSlug($slug);
				$environment->save($conn);

				// Convert object into associative array
				$environment = $environment->toArray();
			}

			// Create a new image if needed
			$image = Doctrine_Core::getTable("Image")->findByArray($imageForm);
			if($image == null)
			{
				// Add new image
				$image = new Image();
				$image->setName($imageForm["name"]);
				$image->setDescription($imageForm["description"]);
				$image->setOs($imageForm["os"]);
				$image->setDistribution($imageForm["distribution"]);
				$image->setVersion($imageForm["version"]);
				$image->setKernel($imageForm["kernel"]);
				$image->setArchitecture($imageForm["architecture"]);
				$image->setOtherFw($imageForm["other_fw"]);
				$image->setBinaryLink($imageForm["binary_link"]);
				$image->setSourceLink($imageForm["source_link"]);

				// Check if its slug does not already exist and generate a new one if needed
				$slug = MiscUtils::slugify($imageForm["name"]);
				$size = 1;
				while(Doctrine_Core::getTable("Image")->checkSlug($slug))
				{
					$slug = MiscUtils::slugify($imageForm["name"]).substr(microtime(), -$size);
					$size++;
				}

				$image->setNameSlug(MiscUtils::slugify($slug));
				$image->save($conn);

				// Convert object into associative array
				$image = $image->toArray();
			}

			// Create a new configuration relationship if needed
			$configurationId = Doctrine_Core::getTable("Configuration")->getConfigurationId($projectToProductId, $environment["id"], $image["id"]);
			if($configurationId == null)
			{
				$configuration = new Configuration();
				$configuration->setProjectToProductId($projectToProductId);
				$configuration->setTestEnvironmentId($environment["id"]);
				$configuration->setImageId($image["id"]);
				$configuration->save($conn);

				$configurationId = $configuration->getId();
			}

			// Edit the session into DB
			$testSession = Doctrine_Core::getTable("TestSession")->find($values["id"]);
			$testSession->setId($values["id"]);
			$testSession->setBuildId($values["build_id"]);
			$testSession->setTestset($values["testset"]);
			$testSession->setName($values["name"]);
			$testSession->setTestObjective($values["test_objective"]);
			$testSession->setQaSummary($values["qa_summary"]);
			$testSession->setUserId($values["user_id"]);
			$testSession->setCreatedAt($values["created_at"]);
			$testSession->setEditorId($values["editor_id"]);
			$testSession->setUpdatedAt($values["updated_at"]);
			$testSession->setProjectRelease($values["project_release"]);
			$testSession->setProjectMilestone($values["project_milestone"]);
			$testSession->setIssueSummary($values["issue_summary"]);
			$testSession->setStatus($values["status"]);
			$testSession->setPublished($values["published"]);
			$testSession->setConfigurationId(intval($configurationId));
			$testSession->setCampaignChecksum($values["campaign_checksum"]);
			$testSession->setBuildSlug(MiscUtils::slugify($values["build_id"]));
			$testSession->setTestsetSlug(MiscUtils::slugify($values["testset"]));
			$testSession->setNotes($values["notes"]);
			$testSession->save();

			$testSessionId = $values["id"];

			// Get all results (and add associative key for each result)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionResults($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["label"]).$value["id"]] = $value;
			$results = $array;

			// Get all measures (and add associative key for each measure)
			$array = array();
			foreach(Doctrine_Core::getTable("TestSession")->getSessionMeasures($this->currentSession["id"]) as $key => $value)
				$array[MiscUtils::slugify($value["label"]).$value["id"]] = $value;
			$measures = $array;

			// Save test results
			foreach($results as $key => $result)
			{
				if(array_key_exists($key, $values))
				{
					$resultForm = Doctrine_Core::getTable("TestResult")->find($result["id"]);
					$resultForm->setId($result["id"]);
					$resultForm->setDecisionCriteriaId($values[$key]["decision_criteria_id"]);
					$resultForm->setComment($values[$key]["comment"]);
					$resultForm->save();
				}
			}

			// Save measures
			foreach($measures as $key => $measure)
			{
				if(array_key_exists($key, $values))
				{
					$resultForm = Doctrine_Core::getTable("TestResult")->find($measure["id"]);
					$resultForm->setId($measure["id"]);
					$resultForm->setDecisionCriteriaId($values[$key]["decision_criteria_id"]);
					$resultForm->setComment($values[$key]["comment"]);
					$resultForm->save();
				}
			}

			// Get project and product name slug
			$projectSlug = Doctrine_Core::getTable("Project")->getSlugById($projectId);
			$productSlug = Doctrine_Core::getTable("ProductType")->getSlugById($productId);

			// Retrieve table_name_test_session_id from table_name
			$tableName = Doctrine_Core::getTable("TableName")->findOneByName("test_session");
			$tableNameTestSessionId = $tableName->getId();

			// Import attachments and result files
			foreach($files["attachments"] as $key => $file)
			{
				if($file["error"] != UPLOAD_ERR_NO_FILE)
				{
					$fileName = $file['name'];
					$fileSize = $file['size'];
					$fileType = $file['type'];
					$fileError = $file['error'];
					$fileChecksum = sha1_file($file["tmp_name"]);
					// Check file error and file size (5Mo max)
					if (!$fileError AND $fileSize <= 5000000)
					{
						// Concatenate destination path
						$dest_path = sfConfig::get('sf_upload_dir') . "/testsession_" . $testSessionId;
						if (!is_dir($dest_path))
						{
							mkdir($dest_path, 0777, true);
						}
						$dest_path .= "/" . $fileName;
						// Move file to uploads directory
						move_uploaded_file($file['tmp_name'], $dest_path);

						$web_path = "/uploads"."/testsession_".$testSessionId."/".$fileName;

						$fileAttachment = new FileAttachment();
						$fileAttachment->setName($fileName);
						$fileAttachment->setUserId($this->getUser()->getGuardUser()->getId());
						$fileAttachment->setUploadedAt(date("Y-m-d H:i:s"));
						$fileAttachment->setFilename($fileName);
						$fileAttachment->setFileSize($fileSize);
						$fileAttachment->setFileMimeType($fileType);
						$fileAttachment->setLink($web_path);
						$fileAttachment->setChecksum($fileChecksum);
						$fileAttachment->setTableNameId($tableNameTestSessionId);
						$fileAttachment->setTableEntryId($testSessionId);
						$fileAttachment->setCategory(2);

						$fileAttachment->save();
					}
				}
			}

			// Customize database connection to begin a transactionnal query
			$conn = Doctrine_Manager::getInstance()->getConnection("qa_generic");
			$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, FALSE);
			$conn->beginTransaction();

			// Get file attachments id into an array
			$fileAttachmentIdList = array();
			$query = "SELECT fa.id
					FROM ".$qa_generic.".file_attachment fa
					WHERE fa.table_entry_id = ".$testSessionId."
						AND fa.table_name_id = ".$tableNameTestSessionId;
			$results = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

			foreach ($results as $result)
			{
				$fileAttachmentIdList[] = $result["id"];
			}
			$fileAttachmentIdStringList = implode (",", $fileAttachmentIdList);

			// Concatenate directory path
			$dir_path = sfConfig::get('sf_upload_dir') . "/testsession_" . $testSessionId;

			$fileAttachmentResultIds = array();
			$similarFileFound = false;

			foreach($files["result_files"] as $key => $file)
			{
				if($file["error"] != UPLOAD_ERR_NO_FILE)
				{
					$reportType = false;

					$fileName = $file['name'];
					$fileSize = $file['size'];
					$fileType = $file['type'];
					$fileError = $file['error'];
					$fileChecksum = sha1_file($file["tmp_name"]);

					$query = "SELECT fa.id
							FROM ".$qa_generic.".file_attachment fa
							WHERE fa.table_entry_id = ".$testSessionId."
								AND fa.table_name_id = ".$tableNameTestSessionId."
								AND fa.checksum = '".$fileChecksum."'";
					$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);

					if (!empty($result["id"]))
					{
						$fileAttachmentResultIds[] = $result["id"];
						$similarFileFound = true;
					}

					// Check file error and file size
					if (!$fileError AND $fileSize <= sfConfig::get('app_max_file_size','10000000'))
					{
						if (!is_dir($dir_path))
						{
							mkdir($dir_path, 0777, true);
						}
						$dest_path = $dir_path . "/" . $fileName;

						$idx = 0;
						while (is_file($dest_path))
						{
							$idx++;
							$dest_path = $dir_path . "/" . "(".$idx.")".$fileName;
						}

						// Move file to uploads directory
						move_uploaded_file($file['tmp_name'], $dest_path);

						if ($idx == 0)
						{
							$web_path = "/uploads"."/testsession_".$testSessionId."/".$fileName;
						}
						else
						{
							$web_path = "/uploads"."/testsession_".$testSessionId."/"."(".$idx.")".$fileName;
						}

						$fileAttachment = new FileAttachment();
						$fileAttachment->setName($fileName);
						$fileAttachment->setUserId($this->getUser()->getGuardUser()->getId());
						$fileAttachment->setUploadedAt(date("Y-m-d H:i:s"));
						$fileAttachment->setFilename($fileName);
						$fileAttachment->setFileSize($fileSize);
						$fileAttachment->setFileMimeType($fileType);
						$fileAttachment->setLink($web_path);
						$fileAttachment->setChecksum($fileChecksum);
						$fileAttachment->setTableNameId($tableNameTestSessionId);
						$fileAttachment->setTableEntryId($testSessionId);

						if (preg_match("#\.xml$#i", $fileName) | preg_match("#\.csv$#i", $fileName))
						{
							$reportType = true;
							$fileAttachment->setCategory(1);
						}
						else
						{
							$fileAttachment->setCategory(2);
						}

						$fileAttachment->save($conn);

						// If it is an XML or CSV file, parse it and fill qa_generic database
						if ($reportType)
						{
							if ($err_code = Import::file($dest_path, $testSessionId, $configurationId, $conn, true))
							{
								// Delete new files
								$query = "SELECT fa.link
										FROM ".$qa_generic.".file_attachment fa
										WHERE fa.table_entry_id = ".$testSessionId."
											AND fa.table_name_id = ".$tableNameTestSessionId."
											AND fa.id NOT IN (".$fileAttachmentIdStringList.")";
								$results = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

								foreach ($results as $result)
								{
									unlink(sfConfig::get('sf_web_dir').$result["link"]);
								}

								$error_message = Import::getImportErrorMessage($err_code);
								$conn->rollback();
								$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
								$this->getUser()->setFlash("error", "Invalid file content on ".$fileName." : ".$error_message);
								$this->redirect("edit_report", array("project" => $projectSlug, "product" => $productSlug, "environment" => $environment["name_slug"], "image" => $image["name_slug"], "id" => $testSessionId));
							}
						}
					}
					else
					{
						// Delete new files
						$query = "SELECT fa.link
								FROM ".$qa_generic.".file_attachment fa
								WHERE fa.table_entry_id = ".$testSessionId."
									AND fa.table_name_id = ".$tableNameTestSessionId."
									AND fa.id NOT IN (".$fileAttachmentIdStringList.")";
						$results = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

						foreach ($results as $result)
						{
							unlink(sfConfig::get('sf_web_dir').$result["link"]);
						}

						$conn->rollback();
						$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
						$this->getUser()->setFlash("error", "File size limit reached");
						$this->redirect("edit_report", array("project" => $projectSlug, "product" => $productSlug, "environment" => $environment["name_slug"], "image" => $image["name_slug"], "id" => $testSessionId));
					}
				}
			}

			if ($similarFileFound)
			{
				$fileAttachmentStringResultIds = implode (",", $fileAttachmentResultIds);

				// Delete similar files and attachment entries
				$query = "SELECT fa.id, fa.link
						FROM ".$qa_generic.".file_attachment fa
						WHERE fa.table_entry_id = ".$testSessionId."
							AND fa.table_name_id = ".$tableNameTestSessionId."
							AND fa.id IN (".$fileAttachmentStringResultIds.")";
				$results = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

				foreach ($results as $result)
				{
					unlink(sfConfig::get('sf_web_dir').$result["link"]);
					Doctrine_Core::getTable("FileAttachment")->deleteFileAttachmentById($result["id"], $conn);
				}
			}

			$conn->commit();
			$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);

// 			// Clear cache for actions related to this test session
// 			$cacheManager = $this->getContext()->getViewCacheManager();
// 			$cacheManager->remove("reports/session?project=".$projectSlug."&product=".$productSlug."&environment=".$environment["name_slug"]."&image=".$image["name_slug"]."?id=".$testSession["id"]);

			// Set flash for user and redirect to session display
			$this->getUser()->setFlash("notice", "Test session was updated successfully");

			if(!empty($testSession["build_slug"]))
				$this->redirect("build_session", array("project" => $projectSlug, "product" => $productSlug, "build" => $testSession["build_slug"], "environment" => $environment["name_slug"], "image" => $image["name_slug"], "id" => $request->getParameter("id")));
			else
				$this->redirect("test_session", array("project" => $projectSlug, "product" => $productSlug, "environment" => $environment["name_slug"], "image" => $image["name_slug"], "id" => $request->getParameter("id")));
		}
	}

	/**
	 * @param sfWebRequest $request
	 */
	public function executeUpdateBuild(sfWebRequest $request)
	{

	}

	/**
	 * @param sfWebRequest $request
	 */
	public function executeUpdateEnvironment(sfWebRequest $request)
	{
		$this->environment = Doctrine_Core::getTable("TestEnvironment")->find($request->getParameter("id"));

		// Initialize form
		$this->form = new ImportTestEnvironmentForm($this->environment);

		// Process form
		if($request->isMethod("post"))
		{
			$this->form->bind(
					$request->getParameter($this->form->getName()),
					$request->getFiles($this->form->getName())
			);

			if($this->form->isValid())
			{
				$values = $this->form->getValues();

				// Check if an environment with the same values does not already exist
				$environment = Doctrine_Core::getTable("TestEnvironment")->findByArray($values);

				// If not, just update the existing environment
				if($environment == null)
				{
					$environment = Doctrine_Core::getTable("TestEnvironment")->find($values["id"]);
					$environment->setId($values["id"]);
					$environment->setName($values["name"]);
					$environment->setDescription($values["description"]);
					$environment->setCpu($values["cpu"]);
					$environment->setBoard($values["board"]);
					$environment->setGpu($values["gpu"]);
					$environment->setOtherHardware($values["other_hardware"]);

					// Check if its slug does not already exist and generate a new one if needed
					$slug = MiscUtils::slugify($values["name"]);
					$size = 1;
					while(Doctrine_Core::getTable("TestEnvironment")->checkSlug($slug))
					{
						$slug = MiscUtils::slugify($values["name"]).substr(microtime(), -$size);
						$size++;
					}
					$environment->setNameSlug($slug);
					$environment->save();
				}
				// Otherwise, merge the actual environment with the already existing one
				else
				{
					Doctrine_Query::create()
						->update("Configuration")
						->set("test_environment_id", $environment["id"])
						->where("test_environment_id = ?", $values["id"])
						->execute();
				}
			}
		}
	}

	/**
	 * @param sfWebRequest $request
	 */
	public function executeUpdateImage(sfWebRequest $request)
	{
		$this->image = Doctrine_Core::getTable("Image")->find($request->getParameter("id"));

		// Initialize form
		$this->form = new ImportImageForm($this->image);

		// Process form
		if($request->isMethod("post"))
		{
			$this->form->bind(
					$request->getParameter($this->form->getName()),
					$request->getFiles($this->form->getName())
			);

			if($this->form->isValid())
			{
				$values = $this->form->getValues();

				// Check if an image with the same values does not already exist
				$image = Doctrine_Core::getTable("Image")->findByArray($values);

				// If not, just update the existing image
				if($image == null)
				{
					$image = Doctrine_Core::getTable("Image")->find($values["id"]);
					$image->setName($values["name"]);
					$image->setDescription($values["description"]);
					$image->setOs($values["os"]);
					$image->setDistribution($values["distribution"]);
					$image->setVersion($values["version"]);
					$image->setKernel($values["kernel"]);
					$image->setArchitecture($values["architecture"]);
					$image->setOtherFw($values["other_fw"]);
					$image->setBinaryLink($values["binary_link"]);
					$image->setSourceLink($values["source_link"]);

					// Check if its slug does not already exist and generate a new one if needed
					$slug = MiscUtils::slugify($values["name"]);
					$size = 1;
					while(Doctrine_Core::getTable("Image")->checkSlug($slug))
					{
						$slug = MiscUtils::slugify($values["name"]).substr(microtime(), -$size);
						$size++;
					}

					$image->setNameSlug(MiscUtils::slugify($slug));
					$image->save();
				}
				// Otherwise, merge the actual image with the already existing one
				else
				{
					Doctrine_Query::create()
						->update("Configuration")
						->set("image_id", $image["id"])
						->where("image_id = ?", $values["id"])
						->execute();
				}
			}
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeUpdateResult(sfWebRequest $request)
	{
		$this->result = Doctrine_Core::getTable("TestResult")->find($request->getParameter("id"));

		// Initialize form
		$this->form = new ResultForm($this->result);

		// Process form
		if($request->isMethod("post"))
		{
			$this->form->bind(
					$request->getParameter($this->form->getName()),
					$request->getFiles($this->form->getName())
			);

			if($this->form->isValid())
			{
				$this->form->save();
			}
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeRefreshResult(sfWebRequest $request)
	{
		$this->result = Doctrine_Core::getTable("TestResult")->find($request->getParameter("id"));
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeDeleteResult(sfWebRequest $request)
	{
		if($request->isXmlHttpRequest())
		{
			Doctrine_Query::create()
				->delete("TestResult")
				->where("id = ?", $request->getParameter("id"))
				->execute();
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeUpdateMeasure(sfWebRequest $request)
	{
		$this->result = Doctrine_Core::getTable("TestResult")->find($request->getParameter("id"));
		$this->measure = Doctrine_Core::getTable("TestResult")->getMeasure($request->getParameter("id"));

		// Initialize form
		$this->form = new ResultForm($this->result);

		// Process form
		if($request->isMethod("post"))
		{
			$this->form->bind(
				$request->getParameter($this->form->getName()),
				$request->getFiles($this->form->getName())
			);

			if($this->form->isValid())
			{
				$this->form->save();
			}
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeRefreshMeasure(sfWebRequest $request)
	{
		$this->measure = Doctrine_Core::getTable("TestResult")->getMeasure($request->getParameter("id"));
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeDeleteMeasure(sfWebRequest $request)
	{
		if($request->isXmlHttpRequest())
		{
			Doctrine_Query::create()
				->delete("Measure")
				->where("test_result_id = ?", $request->getParameter("id"))
				->execute();

			Doctrine_Query::create()
				->delete("TestResult")
				->where("id = ?", $request->getParameter("id"))
				->execute();
		}
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeDeleteAttachment(sfWebRequest $request)
	{
		if($request->isXmlHttpRequest())
		{
			Doctrine_Query::create()
				->delete("FileAttachment")
				->where("id = ?", $request->getParameter("id"))
				->execute();
		}
	}

	//============================================================================================//
	//      EXPORT ACTIONS                                                                        //
	//============================================================================================//

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExport(sfWebRequest $request)
	{
		$test_session_id = $request->getParameter("id");
		Import::exportAsCsv($test_session_id);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportProduct(sfWebRequest $request)
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

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForProduct($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportEnvironment(sfWebRequest $request)
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

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForEnvironment($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportImage(sfWebRequest $request)
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

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForImage($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportProductBuild(sfWebRequest $request)
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

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForProductBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportBuild(sfWebRequest $request)
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

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForBuild($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentBuild["build_id"]);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportEnvironmentBuild(sfWebRequest $request)
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

		// Set current environment
		if($request->getParameter("environment"))
		{
			$this->currentEnvironment = Doctrine_Core::getTable("TestEnvironment")->getEnvironmentBySlug($request->getParameter("environment"));
			$this->forward404Unless($this->currentEnvironment != null, "This test environment type does not exist anymore or you lack sufficient privileges!");
		}

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForEnvironmentBuild($this->projectGroupId, $this->currentProject['id'], $this->currentBuild["build_id"], $this->currentProduct['id'], $this->currentEnvironment['id']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportImageBuild(sfWebRequest $request)
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

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForImageBuild($this->projectGroupId, $this->currentProject['id'], $this->currentBuild["build_id"], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportProductTestset(sfWebRequest $request)
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

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForProductTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportTestset(sfWebRequest $request)
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

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForTestset($this->projectGroupId, $this->currentProject['id'], $this->currentProduct['id'], $this->currentTestset['testset']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportEnvironmentTestset(sfWebRequest $request)
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

		// Set current environment
		if($request->getParameter("environment"))
		{
			$this->currentEnvironment = Doctrine_Core::getTable("TestEnvironment")->getEnvironmentBySlug($request->getParameter("environment"));
			$this->forward404Unless($this->currentEnvironment != null, "This test environment type does not exist anymore or you lack sufficient privileges!");
		}

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForEnvironmentTestset($this->projectGroupId, $this->currentProject['id'], $this->currentTestset['testset'], $this->currentProduct['id'], $this->currentEnvironment['id']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	/**
	 *
	 * @param sfWebRequest $request
	 */
	public function executeExportImageTestset(sfWebRequest $request)
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

		$testSessions = Doctrine_Core::getTable("TestSession")->getSessionsIdForImageTestset($this->projectGroupId, $this->currentProject['id'], $this->currentTestset['testset'], $this->currentProduct['id'], $this->currentEnvironment['id'], $this->currentImage['id']);
		$testSessionsId = array();
		foreach ($testSessions as $testSession)
		{
			$testSessionsId[] = $testSession['id'];
		}

		Import::exportTestSessionsAsCsv($testSessionsId);
	}

	public function executeSearchProducts(sfWebRequest $request)
	{
	    if(($projectGroupId = $request->getParameter("projectGroupId")) && ($projectId = $request->getParameter("projectId")))
	    {
    	    $this->formProduct = new SearchProductsForm(array(), array("projectGroupId" => $projectGroupId, "projectId" => $projectId));

    	    if($request->isXmlHttpRequest())
    	    {
    	        return $this->renderPartial('reports/searchProducts', array('formProduct' => $this->formProduct));
    	    }
	    }
	}
}
