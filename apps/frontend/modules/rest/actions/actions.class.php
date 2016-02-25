<?php

/**
 * rest actions.
 *
 * @package    trc
 * @subpackage rest_api_export
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */

class restActions extends sfActions
{
	public function executeImportRestApi(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$qa_core = sfConfig::get("app_table_qa_core");

		// Retrieve $_GET (main parameters)
		$get_params['auth_token'] = $request->getGetParameter("auth_token");
		$get_params['release_version'] = $request->getGetParameter("release_version");
		$get_params['target'] = $request->getGetParameter("target");
		$get_params['testtype'] = $request->getGetParameter("testtype");
		$get_params['testset'] = $request->getGetParameter("testset");
		$get_params['hwproduct'] = $request->getGetParameter("hwproduct");
		$get_params['product'] = $request->getGetParameter("product");
		$get_params['hardware'] = $request->getGetParameter("hardware");
		$get_params['image'] = $request->getGetParameter("image");
		$get_params['build_id'] = $request->getGetParameter("build_id");

		// Retrieve $_GET (additional parameters)
		$get_params['tested_at'] = $request->getGetParameter("tested_at");
		$get_params['report_title'] = $request->getGetParameter("title");
		$get_params['objective_txt'] = $request->getGetParameter("objective_txt");
		$get_params['build_txt'] = $request->getGetParameter("build_txt");
		$get_params['environment_txt'] = $request->getGetParameter("environment_txt");
		$get_params['qa_summary_txt'] = $request->getGetParameter("qa_summary_txt");
		$get_params['issue_summary_txt'] = $request->getGetParameter("issue_summary_txt");
		$get_params['status'] = $request->getGetParameter("status");

		// Retrieve $_GET (hwproduct additional fields)
		$get_params['te_desc'] = $request->getGetParameter("te_desc");
		$get_params['te_cpu'] = $request->getGetParameter("te_cpu");
		$get_params['te_board'] = $request->getGetParameter("te_board");
		$get_params['te_gpu'] = $request->getGetParameter("te_gpu");
		$get_params['te_hw'] = $request->getGetParameter("te_hw");

		// Retrieve $_GET (image additional fields)
		$get_params['img_desc'] = $request->getGetParameter("img_desc");
		$get_params['img_os'] = $request->getGetParameter("img_os");
		$get_params['img_dist'] = $request->getGetParameter("img_dist");
		$get_params['img_vers'] = $request->getGetParameter("img_vers");
		$get_params['img_kernel'] = $request->getGetParameter("img_kernel");
		$get_params['img_arch'] = $request->getGetParameter("img_arch");
		$get_params['img_other'] = $request->getGetParameter("img_other");
		$get_params['img_bin'] = $request->getGetParameter("img_bin");
		$get_params['img_src'] = $request->getGetParameter("img_src");

		// Old parameters support about test_environment (testtype)
		if (!isset($get_params['testtype']))
			$get_params['testtype'] = $get_params['testset'];

		// Old parameters support about image (hwproduct)
		if (!isset($get_params['hwproduct']))
		{
			if (!isset($get_params['product']))
				$get_params['hwproduct'] = $get_params['hardware'];
			else
				$get_params['hwproduct'] = $get_params['product'];
		}

		// Check if auth_token parameter is empty
		if (empty($get_params['auth_token']))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"Parameters error\":\"Missing auth_token parameter\"}}\n";
			exit;
		}

		// Check if release_version parameter is empty
		if (empty($get_params['release_version']))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"Parameters error\":\"Missing release_version parameter\"}}\n";
			exit;
		}

		// Check if target parameter is empty
		if (empty($get_params['target']))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"Parameters error\":\"Missing target parameter\"}}\n";
			exit;
		}

		// Check if hwproduct parameter is empty
		if (empty($get_params['hwproduct']))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"Parameters error\":\"Missing hwproduct parameter\"}}\n";
			exit;
		}

		// Check if image parameter is empty
		if (empty($get_params['image']))
		{
			$get_params['image'] = "Empty_image";
		}

		// Retrieve project_id relying on project name (if it doesn't exist, return an error)
		$query = "SELECT proj.id AS project_id
					FROM ".$qa_generic.".project proj
					WHERE proj.name = '".$get_params['release_version']."'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if (empty($result["project_id"]))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"release_version\":\"Incorrect release_version '" . $get_params['release_version'] . "'\"}}\n";
			exit;
		}
		$project_id = $result["project_id"];

		// Retrieve project_group_id relying on project_group_name (if it doesn't exist, return an error)
		$query = "SELECT pg.id AS project_group_id
					FROM ".$qa_core.".sf_guard_group pg
					WHERE pg.name = '".sfConfig::get("app_project_group")."'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if (empty($result["project_group_id"]))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"project_group_name\":\"Incorrect project_group_name '" . sfConfig::get("app_project_group") . "'\"}}\n";
			exit;
		}
		$project_group_id = $result["project_group_id"];

		// Retrieve product_id relying on product formfactor (if it doesn't exist, return an error)
		$query = "SELECT pt.id AS product_id
					FROM ".$qa_core.".product_type pt
					WHERE pt.name = '".$get_params['target']."'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if (empty($result["product_id"]))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"target\":\"Incorrect target '" . $get_params['target'] . "'\"}}\n";
			exit;
		}
		$product_id = $result["product_id"];

		// Retrieve project_to_product_id, relying on project_id, project_group_id, and product_id
		$query = "SELECT ptp.id AS ptp_id
					FROM ".$qa_generic.".project_to_product ptp
					WHERE ptp.project_id = ".$project_id."
						AND ptp.project_group_id = ".$project_group_id."
						AND ptp.product_id = ".$product_id;
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if (empty($result["ptp_id"]))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"project_to_product\":\"Cannot find project_to_product_id\"}}\n";
			exit;
		}
		$project_to_product_id = $result["ptp_id"];

		// Retrieve user_id, relying on auth_token
		$query = "SELECT up.user_id
					FROM ".$qa_core.".sf_guard_user_profile up
					WHERE up.token = '".$get_params['auth_token']."'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if (empty($result["user_id"]))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"auth_token\":\"Authorized token is not valid\"}}\n";
			exit;
		}
		$user_id = $result["user_id"];

		// Customize database connection to begin a transactionnal query
		$conn = Doctrine_Manager::getInstance()->getConnection("qa_generic");
		$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, FALSE);
		$conn->beginTransaction();

		// If test_environment_name exists, retrieve id, else, create new entry and retrieve id
		$query = "SELECT te.id AS test_environment_id
					FROM ".$qa_generic.".test_environment te
					WHERE te.name = '".$get_params['hwproduct']."'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if(empty($result["test_environment_id"]))
		{
			// Check if creation of a new entry is allowed
			if(sfConfig::get("app_rest_configuration_creation", false) == false)
			{
				$conn->rollback();
				$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
				echo "{\"ok\":\"0\",\"errors\":{\"test_environment\":\"Creation of new test environment is forbidden\"}}\n";
				exit;
			}
			else
			{
				// Add new environment
				$environment = new TestEnvironment();
				$environment->setName($get_params['hwproduct']);
				$environment->setNameSlug(MiscUtils::slugify($get_params['hwproduct']));

				// Add hwproduct additional fields if given as parameters
				if (isset($get_params['te_desc']))
					$environment->setDescription($get_params['te_desc']);
				if (isset($get_params['te_cpu']))
					$environment->setCpu($get_params['te_cpu']);
				if (isset($get_params['te_board']))
					$environment->setBoard($get_params['te_board']);
				if (isset($get_params['te_gpu']))
					$environment->setGpu($get_params['te_gpu']);
				if (isset($get_params['te_hw']))
					$environment->setOtherHardware($get_params['te_hw']);

				// Save new environment
				$environment->save($conn);

				$environmentId = $environment->getId();
			}
		}
		else
			$environmentId = $result["test_environment_id"];

		// If image_name exists, retrieve id, else, create new entry and retrieve id
		$query = "SELECT i.id AS image_id
					FROM ".$qa_generic.".image i
					WHERE i.name = '".$get_params['image']."'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if(empty($result["image_id"]))
		{
			// Check if creation of a new entry is allowed
			if(sfConfig::get("app_rest_configuration_creation", false) == false)
			{
				$conn->rollback();
				$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
				echo "{\"ok\":\"0\",\"errors\":{\"image\":\"Creation of new image is forbidden\"}}\n";
				exit;
			}
			else
			{
				// Add new image
				$image = new Image();
				$image->setName($get_params['image']);
				$image->setNameSlug(MiscUtils::slugify($get_params['image']));

				// Add image additional fields if given as parameters
				if (isset($get_params['img_desc']))
					$image->setDescription($get_params['img_desc']);
				if (isset($get_params['img_os']))
					$image->setOs($get_params['img_os']);
				if (isset($get_params['img_dist']))
					$image->setDistribution($get_params['img_dist']);
				if (isset($get_params['img_vers']))
					$image->setVersion($get_params['img_vers']);
				if (isset($get_params['img_kernel']))
					$image->setKernel($get_params['img_kernel']);
				if (isset($get_params['img_arch']))
					$image->setArchitecture($get_params['img_arch']);
				if (isset($get_params['img_other']))
					$image->setOtherFw($get_params['img_other']);
				if (isset($get_params['img_bin']))
					$image->setBinaryLink($get_params['img_bin']);
				if (isset($get_params['img_src']))
					$image->setSourceLink($get_params['img_src']);

				// Save new image
				$image->save($conn);

				$imageId = $image->getId();
			}
		}
		else
			$imageId = $result["image_id"];

		// If configuration exists, retrieve id, else, create new entry and retrieve id
		$query = "SELECT c.id AS configuration_id
					FROM ".$qa_generic.".configuration c
					WHERE c.project_to_product_id = ".$project_to_product_id."
						AND c.test_environment_id = ".$environmentId."
						AND c.image_id = ".$imageId;
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if(empty($result["configuration_id"]))
		{
			$configuration = new Configuration();
			$configuration->setProjectToProductId($project_to_product_id);
			$configuration->setTestEnvironmentId($environmentId);
			$configuration->setImageId($imageId);
			$configuration->save($conn);

			$configurationId = $configuration->getId();
		}
		else
			$configurationId = $result["configuration_id"];

		$date_now = date("Y-m-d H:i:s");
		$date_now_wo_sec = date("Y-m-d H:i");

		$testSession = new TestSession();
		$testSession->setName($get_params['target']." ".$get_params['testtype']." ".$get_params['hwproduct']." ".$date_now_wo_sec." ".$get_params['build_id']);
		$testSession->setUserId($user_id);
		$testSession->setCreatedAt($date_now);
		$testSession->setUpdatedAt($date_now);
		$testSession->setStatus(2);
		$testSession->setPublished(1);
		$testSession->setConfigurationId($configurationId);

		// Fill in the build_id if it is given
		if (!empty($get_params['build_id']))
		{
			$testSession->setBuildId($get_params['build_id']);
			$testSession->setBuildSlug(MiscUtils::slugify($get_params['build_id']));
		}

		// Fill in the testset if it is given
		if (!empty($get_params['testtype']))
		{
			$testSession->setTestset($get_params['testtype']);
			$testSession->setTestsetSlug(MiscUtils::slugify($get_params['testtype']));
		}

		if (isset($get_params['report_title']))
			$testSession->setName($get_params['report_title']);
		if (isset($get_params['objective_txt']))
			$testSession->setTestObjective($get_params['objective_txt']);
		if (isset($get_params['environment_txt']))
			$testSession->setNotes($get_params['environment_txt']);
		if (isset($get_params['qa_summary_txt']))
			$testSession->setQaSummary($get_params['qa_summary_txt']);
		if (isset($get_params['issue_summary_txt']))
			$testSession->setIssueSummary($get_params['issue_summary_txt']);
		if (isset($get_params['status']))
			$testSession->setStatus($get_params['status']);

		$testSession->save($conn);

		$testSessionId = $testSession->getId();

		// Retrieve table_name_test_session_id from table_name
		$query = "SELECT tn.id AS table_name_id
					FROM ".$qa_generic.".table_name tn
					WHERE tn.name = 'test_session'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		$tableNameTestSessionId = $result["table_name_id"];

		// Concatenate directory path
		$dir_path = sfConfig::get('sf_upload_dir') . "/testsession_" . $testSessionId;

		// Get all files sent
		$files = $request->getFiles();

		// Check if there is any report file to import
		if (empty($files))
		{
			$conn->rollback();
			$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
			echo "{\"ok\":\"0\",\"errors\":\"Missing report file\"}\n";
			exit;
		}

		// Import each report file and register attachment files
		$report_file_found = false;
		foreach($files as $key => $file)
		{
			$reportType = false;

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

				$web_path = "/uploads"."/testsession_".$testSessionId."/".$fileName;

				$fileAttachment = new FileAttachment();
				$fileAttachment->setName($fileName);
				$fileAttachment->setUserId($user_id);
				$fileAttachment->setUploadedAt(date("Y-m-d H:i:s"));
				$fileAttachment->setFilename($fileName);
				$fileAttachment->setFileSize($fileSize);
				$fileAttachment->setFileMimeType($fileType);
				$fileAttachment->setLink($web_path);
				$fileAttachment->setChecksum($fileChecksum);
				$fileAttachment->setTableNameId($tableNameTestSessionId);
				$fileAttachment->setTableEntryId($testSessionId);

				if ((preg_match("#\.xml$#i", $fileName) | preg_match("#\.csv$#i", $fileName)) & (!preg_match("#attachment.?[0-9]*#i", $key)))
				{
					$report_file_found = true;
					$reportType = true;
					$fileAttachment->setCategory(1);
				}
				else if (preg_match("#attachment.?[0-9]*#i", $key))
				{
					$fileAttachment->setCategory(2);
				}
				else
				{
					$conn->rollback();
					$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
					echo "{\"ok\":\"0\",\"errors\":\"Only upload files with the extension .xml or .csv\"}\n";
					exit;
				}

				$fileAttachment->save($conn);

				// If it is an XML or CSV file, parse it and fill qa_generic database
				if ($reportType)
				{
					if ($err_code = Import::file($dest_path, $testSessionId, $configurationId, $conn))
					{
						$error_message = Import::getImportErrorMessage($err_code);
						MiscUtils::deleteDir($dir_path);
						$conn->rollback();
						$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
						echo "{\"ok\":\"0\",\"errors\":\"File ".$fileName." is not valid = ".$error_message."\"}\n";
						exit;
					}
				}
			}
			else
			{
				MiscUtils::deleteDir($dir_path);
				$conn->rollback();
				$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
				echo "{\"ok\":\"0\",\"errors\":\"File ".$fileName." exceed maximum size\"}\n";
				exit;
			}
		}

		// If only attachment files have been found, cancel the new test session
		if (!$report_file_found)
		{
			$conn->rollback();
			$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
			echo "{\"ok\":\"0\",\"errors\":\"Missing report file\"}\n";
			exit;
		}

		$conn->commit();
		$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);

		// Retrieve project name_slug, product name_slug, test environment name_slug and image name_slug
		$query = "SELECT i.name_slug image_name_slug, te.name_slug test_environment_name_slug, p.name_slug project_name_slug, pt.name_slug product_name_slug
					FROM ".$qa_generic.".test_session ts
					JOIN ".$qa_generic.".configuration c ON c.id = ts.configuration_id
					JOIN ".$qa_generic.".image i ON i.id = c.image_id
					JOIN ".$qa_generic.".test_environment te ON te.id = c.test_environment_id
					JOIN ".$qa_generic.".project_to_product ptp ON ptp.id = c.project_to_product_id
					JOIN ".$qa_generic.".project p ON p.id = ptp.project_id
					JOIN ".$qa_core.".product_type pt ON pt.id = ptp.product_id
					WHERE ts.id = ".$testSessionId;
		$configInfo = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		$projectNameSlug = $configInfo['project_name_slug'];
		$productNameSlug = $configInfo['product_name_slug'];
		$testEnvironmentNameSlug = $configInfo['test_environment_name_slug'];
		$imageNameSlug = $configInfo['image_name_slug'];

		// Return datas to CATS
		$url_to_return = $request->getUriPrefix().$this->generateUrl("test_session", array('project' => $projectNameSlug, 'product' => $productNameSlug, 'environment' => $testEnvironmentNameSlug, 'image' => $imageNameSlug, 'id' => $testSessionId));
		echo "{\"ok\":\"1\",\"url\":\"" . $url_to_return . "\"}\n";

		// Return is done (with echo) so make sure nothing else will be sent
		exit;
	}

	public function executeImportRestApiUpdate(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$qa_core = sfConfig::get("app_table_qa_core");

		// Retrieve test session id to update
		$testSessionId = $request->getParameter("id");
		// Retrieve $_GET
		$get_params['auth_token'] = $request->getGetParameter("auth_token");

		// Check if id parameter is empty
		if (empty($testSessionId))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"Parameters error\":\"Missing test session id parameter\"}}\n";
			exit;
		}

		// Check if test session id exists
		$testSession = Doctrine_Core::getTable("TestSession")->findOneById($testSessionId);
		if(empty($testSession))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"Test session id\":\"Invalid id, test session doesn't exist\"}}\n";
			exit;
		}

		// Check if auth_token parameter is empty
		if (empty($get_params['auth_token']))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"Parameters error\":\"Missing auth_token parameter\"}}\n";
			exit;
		}

		// Check authorized token exists, and retrieve user_id
		$query = "SELECT up.user_id
					FROM ".$qa_core.".sf_guard_user_profile up
					WHERE up.token = '".$get_params['auth_token']."'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if (empty($result["user_id"]))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"auth_token\":\"Invalid authorized token\"}}\n";
			exit;
		}
		$user_id = $result["user_id"];

		// Retrieve table_name_test_session_id from table_name
		$tableName = Doctrine_Core::getTable("TableName")->findOneByName("test_result");
		$tableNameTestResultId = $tableName->getId();

		// Customize database connection to begin a transactionnal query
		$conn = Doctrine_Manager::getInstance()->getConnection("qa_generic");
		$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, FALSE);
		$conn->beginTransaction();

		// Update test_session table
		$testSession->setUpdatedAt(date("Y-m-d H:i:s"));
		$testSession->save($conn);

		// Delete measures entries relying on test_result ids
		$query = "DELETE m FROM ".$qa_generic.".measure m
					JOIN ".$qa_generic.".test_result tr ON m.test_result_id = tr.id
					WHERE tr.test_session_id = ".$testSessionId;
		$result = $conn->execute($query);

		// Delete features entries relying on test_result ids
		$query = "DELETE ctr FROM ".$qa_generic.".complementary_tool_relation ctr
					JOIN ".$qa_generic.".test_result tr ON ctr.table_entry_id = tr.id
					JOIN ".$qa_generic.".table_name tn ON ctr.table_name_id = ".$tableNameTestResultId."
					WHERE tr.test_session_id = ".$testSessionId;
		$result = $conn->execute($query);

		// Delete test_result ids relying on test_session id
		$query = "DELETE tr FROM ".$qa_generic.".test_result tr
					WHERE tr.test_session_id = ".$testSessionId;
		$result = $conn->execute($query);

		// Retrieve table_name_test_session_id from table_name
		$tableName = Doctrine_Core::getTable("TableName")->findOneByName("test_session");
		$tableNameTestSessionId = $tableName->getId();

		// Get file attachments id into an array
		$fileAttachmentIdList = array();
		$fileAttachments = Doctrine_Query::create()
			->select('*')
			->from('FileAttachment')
			->where('table_entry_id = ?', $testSessionId)
			->andWhere('table_name_id = ?', $tableNameTestSessionId)
			->execute();
		foreach ($fileAttachments as $fileAttachment)
		{
			$fileAttachmentIdList[] = $fileAttachment->getId();
		}

		// Concatenate directory path
		$dir_path = sfConfig::get('sf_upload_dir') . "/testsession_" . $testSessionId;

		// Get all files sent
		$files = $request->getFiles();
		foreach($files as $key => $file)
		{
			$reportType = false;

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
				$fileAttachment->setUserId($user_id);
				$fileAttachment->setUploadedAt(date("Y-m-d H:i:s"));
				$fileAttachment->setFilename($fileName);
				$fileAttachment->setFileSize($fileSize);
				$fileAttachment->setFileMimeType($fileType);
				$fileAttachment->setLink($web_path);
				$fileAttachment->setChecksum($fileChecksum);
				$fileAttachment->setTableNameId($tableNameTestSessionId);
				$fileAttachment->setTableEntryId($testSessionId);

				if ((preg_match("#\.xml$#i", $fileName) | preg_match("#\.csv$#i", $fileName)) & (!preg_match("#attachment.?[0-9]*#i", $key)))
				{
					$reportType = true;
					$fileAttachment->setCategory(1);
				}
				else if (preg_match("#attachment.?[0-9]*#i", $key))
				{
					$fileAttachment->setCategory(2);
				}
				else
				{
					$conn->rollback();
					$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
					echo "{\"ok\":\"0\",\"errors\":\"Only upload files with the extension .xml or .csv\"}\n";
					exit;
				}

				$fileAttachment->save($conn);

				// If it is an XML or CSV file, parse it and fill qa_generic database
				if ($reportType)
				{
					if ($err_code = Import::file($dest_path, $testSessionId, $configurationId, $conn))
					{
						// Delete new files
						$fileAttachments = Doctrine_Query::create()
							->select('*')
							->from('FileAttachment')
							->where('table_entry_id = ?', $testSessionId)
							->andWhere('table_name_id = ?', $tableNameTestSessionId)
							->andWhereNotIn('id', $fileAttachmentIdList)
							->execute();
						foreach ($fileAttachments as $fileAttachment)
						{
							unlink(sfConfig::get('sf_web_dir').$fileAttachment->getLink());
						}

						$error_message = Import::getImportErrorMessage($err_code);
						$conn->rollback();
						$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
						echo "{\"ok\":\"0\",\"errors\":\"File ".$fileName." is not valid = ".$error_message."\"}\n";
						exit;
					}
				}
			}
			else
			{
				// Delete new files
				$fileAttachments = Doctrine_Query::create()
					->select('*')
					->from('FileAttachment')
					->where('table_entry_id = ?', $testSessionId)
					->andWhere('table_name_id = ?', $tableNameTestSessionId)
					->andWhereNotIn('id', $fileAttachmentIdList)
					->execute();
				foreach ($fileAttachments as $fileAttachment)
				{
					unlink(sfConfig::get('sf_web_dir').$fileAttachment->getLink());
				}

				$conn->rollback();
				$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
				echo "{\"ok\":\"0\",\"errors\":\"File ".$fileName." exceed maximum size\"}\n";
				exit;
			}
		}

		if (!empty($fileAttachmentIdList))
		{
			// Delete old file attachments entries and old files
			$fileAttachments = Doctrine_Query::create()
				->select('*')
				->from('FileAttachment')
				->where('table_entry_id = ?', $testSessionId)
				->andWhere('table_name_id = ?', $tableNameTestSessionId)
				->andWhereIn('id', $fileAttachmentIdList)
				->execute();
			foreach ($fileAttachments as $fileAttachment)
			{
				unlink(sfConfig::get('sf_web_dir').$fileAttachment->getLink());
				$fileAttachment->delete($conn);
			}
		}

		$conn->commit();
		$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);

		// Retrieve project name_slug, product name_slug, test environment name_slug and image name_slug
		$query = "SELECT i.name_slug image_name_slug, te.name_slug test_environment_name_slug, p.name_slug project_name_slug, pt.name_slug product_name_slug
					FROM ".$qa_generic.".test_session ts
					JOIN ".$qa_generic.".configuration c ON c.id = ts.configuration_id
					JOIN ".$qa_generic.".image i ON i.id = c.image_id
					JOIN ".$qa_generic.".test_environment te ON te.id = c.test_environment_id
					JOIN ".$qa_generic.".project_to_product ptp ON ptp.id = c.project_to_product_id
					JOIN ".$qa_generic.".project p ON p.id = ptp.project_id
					JOIN ".$qa_core.".product_type pt ON pt.id = ptp.product_id
					WHERE ts.id = ".$testSessionId;
		$configInfo = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		$projectNameSlug = $configInfo['project_name_slug'];
		$productNameSlug = $configInfo['product_name_slug'];
		$testEnvironmentNameSlug = $configInfo['test_environment_name_slug'];
		$imageNameSlug = $configInfo['image_name_slug'];

		// Return datas to CATS
		$url_to_return = $request->getUriPrefix().$this->generateUrl("test_session", array('project' => $projectNameSlug, 'product' => $productNameSlug, 'environment' => $testEnvironmentNameSlug, 'image' => $imageNameSlug, 'id' => $testSessionId));
		echo "{\"ok\":\"1\",\"url\":\"" . $url_to_return . "\"}\n";

		// Return is done (with echo) so make sure nothing else will be sent
		exit;
	}

	public function executeImportRestApiMerge(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$qa_core = sfConfig::get("app_table_qa_core");

		// Retrieve test session id to update
		$testSessionId = $request->getParameter("id");
		// Retrieve $_GET
		$get_params['auth_token'] = $request->getGetParameter("auth_token");

		// Check if id parameter is empty
		if (empty($testSessionId))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"Parameters error\":\"Missing test session id parameter\"}}\n";
			exit;
		}

		// Check if auth_token parameter is empty
		if (empty($get_params['auth_token']))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"Parameters error\":\"Missing auth_token parameter\"}}\n";
			exit;
		}

		// Check authorized token exists, and retrieve user_id
		$sfGuardUserProfileObject = Doctrine_Core::getTable("sfGuardUserProfile")->findOneByToken($get_params['auth_token']);
		if (empty($sfGuardUserProfileObject))
		{
			echo "{\"ok\":\"0\",\"errors\":{\"auth_token\":\"Authorized token is not valid\"}}\n";
			exit;
		}
		$user_id = $sfGuardUserProfileObject->getUserId();

		// Customize database connection to begin a transactionnal query
		$conn = Doctrine_Manager::getInstance()->getConnection("qa_generic");
		$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, FALSE);
		$conn->beginTransaction();

		// Update test_session table
		$testSession = Doctrine_Core::getTable("TestSession")->findOneById($testSessionId);
		$testSession->setUpdatedAt(date("Y-m-d H:i:s"));
		$testSession->save($conn);

		// Retrieve table_name_test_session_id from table_name
		$tableName = Doctrine_Core::getTable("TableName")->findOneByName("test_session");
		$tableNameTestSessionId = $tableName->getId();

		// Get file attachments id into an array
		$fileAttachmentIdList = array();
		$fileAttachments = Doctrine_Query::create()
			->select('*')
			->from('FileAttachment')
			->where('table_entry_id = ?', $testSessionId)
			->andWhere('table_name_id = ?', $tableNameTestSessionId)
			->execute();
		foreach ($fileAttachments as $fileAttachment)
		{
			$fileAttachmentIdList[] = $fileAttachment->getId();
		}

		// Concatenate directory path
		$dir_path = sfConfig::get('sf_upload_dir') . "/testsession_" . $testSessionId;

		$fileAttachmentResultIds = array();
		$similarFileFound = false;

		// Get all files sent
		$files = $request->getFiles();

		// Check if there is any report file to import
		if (empty($files))
		{
			$conn->rollback();
			$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
			echo "{\"ok\":\"0\",\"errors\":\"Missing report file\"}\n";
			exit;
		}

		// Import each report file and register attachment files
		$report_file_found = false;
		foreach($files as $key => $file)
		{
			$reportType = false;

			$fileName = $file['name'];
			$fileSize = $file['size'];
			$fileType = $file['type'];
			$fileError = $file['error'];
			$fileChecksum = sha1_file($file["tmp_name"]);

			$fileAttachmentResult = Doctrine_Query::create()
				->select('*')
				->from('FileAttachment')
				->where('table_entry_id = ?', $testSessionId)
				->andWhere('table_name_id = ?', $tableNameTestSessionId)
				->andWhere('checksum = ?', $fileChecksum)
				->execute();

			if (!empty($fileAttachmentResult[0]))
			{
				$fileAttachmentResultIds[] = $fileAttachmentResult[0]->getId();
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
				$fileAttachment->setUserId($user_id);
				$fileAttachment->setUploadedAt(date("Y-m-d H:i:s"));
				$fileAttachment->setFilename($fileName);
				$fileAttachment->setFileSize($fileSize);
				$fileAttachment->setFileMimeType($fileType);
				$fileAttachment->setLink($web_path);
				$fileAttachment->setChecksum($fileChecksum);
				$fileAttachment->setTableNameId($tableNameTestSessionId);
				$fileAttachment->setTableEntryId($testSessionId);

				if ((preg_match("#\.xml$#i", $fileName) | preg_match("#\.csv$#i", $fileName)) & (!preg_match("#attachment.?[0-9]*#i", $key)))
				{
					$report_file_found = true;
					$reportType = true;
					$fileAttachment->setCategory(1);
				}
				else if (preg_match("#attachment.?[0-9]*#i", $key))
				{
					$fileAttachment->setCategory(2);
				}
				else
				{
					$conn->rollback();
					$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
					echo "{\"ok\":\"0\",\"errors\":\"Only upload files with the extension .xml or .csv\"}\n";
					exit;
				}

				$fileAttachment->save($conn);

				// If it is an XML or CSV file, parse it and fill qa_generic database
				if ($reportType)
				{
					if ($err_code = Import::file($dest_path, $testSessionId, $configurationId, $conn, true))
					{
						// Delete new files
						$fileAttachments = Doctrine_Query::create()
							->select('*')
							->from('FileAttachment')
							->where('table_entry_id = ?', $testSessionId)
							->andWhere('table_name_id = ?', $tableNameTestSessionId)
							->andWhereNotIn('id', $fileAttachmentIdList)
							->execute();
						foreach ($fileAttachments as $fileAttachment)
						{
							unlink(sfConfig::get('sf_web_dir').$fileAttachment->getLink());
						}

						$error_message = Import::getImportErrorMessage($err_code);
						$conn->rollback();
						$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
						echo "{\"ok\":\"0\",\"errors\":\"File ".$fileName." is not valid = ".$error_message."\"}\n";
						exit;
					}
				}
			}
			else
			{
				// Delete new files
				$fileAttachments = Doctrine_Query::create()
				->select('*')
				->from('FileAttachment')
				->where('table_entry_id = ?', $testSessionId)
				->andWhere('table_name_id = ?', $tableNameTestSessionId)
				->andWhereNotIn('id', $fileAttachmentIdList)
				->execute();
				foreach ($fileAttachments as $fileAttachment)
				{
					unlink(sfConfig::get('sf_web_dir').$fileAttachment->getLink());
				}

				$conn->rollback();
				$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
				echo "{\"ok\":\"0\",\"errors\":\"File ".$fileName." exceed maximum size\"}\n";
				exit;
			}
		}

		// If only attachment files have been found, cancel the new test session
		if (!$report_file_found)
		{
			$conn->rollback();
			$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);
			echo "{\"ok\":\"0\",\"errors\":\"Missing report file\"}\n";
			exit;
		}

		if ($similarFileFound)
		{
			// Delete similar files and attachment entries
			$fileAttachments = Doctrine_Query::create()
				->select('*')
				->from('FileAttachment')
				->where('table_entry_id = ?', $testSessionId)
				->andWhere('table_name_id = ?', $tableNameTestSessionId)
				->andWhereIn('id', $fileAttachmentResultIds)
				->execute();
			foreach ($fileAttachments as $fileAttachment)
			{
				unlink(sfConfig::get('sf_web_dir').$fileAttachment->getLink());
				$fileAttachment->delete($conn);
			}
		}

		$conn->commit();
		$conn->setAttribute(Doctrine_Core::ATTR_AUTOCOMMIT, TRUE);

		// Retrieve project name_slug, product name_slug, test environment name_slug and image name_slug
		$query = "SELECT i.name_slug image_name_slug, te.name_slug test_environment_name_slug, p.name_slug project_name_slug, pt.name_slug product_name_slug
					FROM ".$qa_generic.".test_session ts
					JOIN ".$qa_generic.".configuration c ON c.id = ts.configuration_id
					JOIN ".$qa_generic.".image i ON i.id = c.image_id
					JOIN ".$qa_generic.".test_environment te ON te.id = c.test_environment_id
					JOIN ".$qa_generic.".project_to_product ptp ON ptp.id = c.project_to_product_id
					JOIN ".$qa_generic.".project p ON p.id = ptp.project_id
					JOIN ".$qa_core.".product_type pt ON pt.id = ptp.product_id
					WHERE ts.id = ".$testSessionId;
		$configInfo = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		$projectNameSlug = $configInfo['project_name_slug'];
		$productNameSlug = $configInfo['product_name_slug'];
		$testEnvironmentNameSlug = $configInfo['test_environment_name_slug'];
		$imageNameSlug = $configInfo['image_name_slug'];

		// Return datas to CATS
		$url_to_return = $request->getUriPrefix().$this->generateUrl("test_session", array('project' => $projectNameSlug, 'product' => $productNameSlug, 'environment' => $testEnvironmentNameSlug, 'image' => $imageNameSlug, 'id' => $testSessionId));
		echo "{\"ok\":\"1\",\"url\":\"" . $url_to_return . "\"}\n";

		// Return is done (with echo) so make sure nothing else will be sent
		exit;
	}

	public function executeExportRestApiLimitAmount(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");

		// Retrieve $_GET
		$limit_amount = (int) $request->getGetParameter("limit_amount");
		$begin_time = $request->getGetParameter("begin_time");
		$brief = $request->getGetParameter("brief");

		// Check limit_amount variable
		if (!isset($limit_amount))
		{
			echo "ERROR : missing limit_amount attribute into URL";
			exit;
		}
		else if ($limit_amount == 0)
		{
			echo "ERROR : limit_amount value is not valid";
			exit;
		}

		// Check brief variable to determine rest api type
		if (isset($brief) AND ($brief == 'true'))
		{
			$rest_api_type = 2;
		}
		else
		{
			$rest_api_type = 1;
		}

		// Check begin_time variable, so as to rely on begin_time value or not
		if (isset($begin_time))
		{
			// Get last test sessions relying on begin_time
			$query = "SELECT ts.id, ts.build_id, ts.testset, ts.name, ts.status, ts.created_at, ts.updated_at, ts.qa_summary
				FROM " . $qa_generic . ".test_session ts
				WHERE ts.created_at >= '".$begin_time."'
				AND ts.published = 1
				ORDER BY ts.created_at ASC
				LIMIT 0," . $limit_amount;
		}
		else
		{
			// Get last test sessions
			$query = "SELECT ts.id, ts.build_id, ts.testset, ts.name, ts.status, ts.created_at, ts.updated_at, ts.qa_summary
				FROM " . $qa_generic . ".test_session ts
				WHERE ts.published = 1
				ORDER BY ts.created_at DESC
				LIMIT 0," . $limit_amount;
		}
		$testSessions = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

		$test_session_list = array();

		if ($rest_api_type == 1)
		{
			// Retrieve features and information regarding each test session
			foreach ($testSessions as $testSession)
			{
				$testSessionTotals = Doctrine_Core::getTable('TestSession')->getSessionNumbersWithMeasures($testSession['id']);
				$features = Doctrine_Core::getTable('TestSession')->getFeaturesNumbersWithMeasures($testSession['id']);
				$testResults = Doctrine_Core::getTable('TestSession')->getSessionResults($testSession['id']);

				// Retrieve project name, test environment name and image name, relying on test session id
				$configInfo = (is_null(Doctrine_Core::getTable('TestSession')->getConfigInfo($testSession['id']))) ? array() : Doctrine_Core::getTable('TestSession')->getConfigInfo($testSession['id']);

				// Retrieve test results and information regarding each feature
				$features_list = array();
				foreach ($features as $feature)
				{
					// Retrieve information regarding each test result
					$test_result_list = array();
					foreach ($testResults as $testResult)
					{
						if ($testResult['label'] == $feature['label'])
						{
							// Fill test result object
							$test_result_object = array('comment' => $testResult['comment'],
									'result' => $testResult['complement'],
									'bugs' => $testResult['bugs'],
									'name' => $testResult['name'],
									'qa_id' => $testResult['id']
							);;
							// Append test result object to test result list
							$test_result_list[] = $test_result_object;
						}
					}
					// Fill features object
					$features_object = array('name' => $feature['label'],
							'total_measured' => $feature['total_measured'],
							'total_na' => $feature['block'],
							'total_cases' => $feature['total'],
							'total_pass' => $feature['pass'],
							'comments' => '',
							'total_fail' => $feature['fail'],
							'qa_id' => $feature['id'],
							'cases' => $test_result_list
					);;
					// Append features object to features list
					$features_list[] = $features_object;
				}


				// Fill test session object
				$test_session_object = array('profile' => $configInfo['product_name'],
						'build_id' => $testSession['build_id'],
						'total_cases' => $testSessionTotals['total'],
						'title' => $testSession['name'],
						'created_at' => $testSession['created_at'],
						'total_measured' => $testSessionTotals['total_measured'],
						'total_pass' => $testSessionTotals['pass'],
						'updated_at' => $testSession['updated_at'],
						'tested_at' => $testSession['created_at'],
						'release' => $configInfo['proj_name'],
						'hardware' => $configInfo['te_name'],
						'weeknum' => date('W', strtotime($testSession['created_at'])),
						'qa_id' => $testSession['id'],
						'total_fail' => $testSessionTotals['fail'],
						'testtype' => $testSession['testset'],
						'total_na' => $testSessionTotals['block'],
						'qa_summary_txt' => $testSession['qa_summary'],
						'features' => $features_list
				);;

				// Append test session object to test session list
				$test_session_list[] = $test_session_object;
			}
		}
		else if ($rest_api_type == 2)
		{
			// Retrieve features and information regarding each test session
			foreach ($testSessions as $testSession)
			{
				$testSessionTotals = Doctrine_Core::getTable('TestSession')->getSessionNumbersWithMeasures($testSession['id']);

				// Retrieve project name, test environment name and image name, relying on test session id
				$configInfo = (is_null(Doctrine_Core::getTable('TestSession')->getConfigInfo($testSession['id']))) ? array() : Doctrine_Core::getTable('TestSession')->getConfigInfo($testSession['id']);

				// Fill test session object
				$test_session_object = array('profile' => $configInfo['product_name'],
						'build_id' => $testSession['build_id'],
						'total_cases' => $testSessionTotals['total'],
						'title' => $testSession['name'],
						'created_at' => $testSession['created_at'],
						'total_measured' => $testSessionTotals['total_measured'],
						'total_pass' => $testSessionTotals['pass'],
						'updated_at' => $testSession['updated_at'],
						'tested_at' => $testSession['created_at'],
						'release' => $configInfo['proj_name'],
						'hardware' => $configInfo['te_name'],
						'weeknum' => date('W', strtotime($testSession['created_at'])),
						'qa_id' => $testSession['id'],
						'total_fail' => $testSessionTotals['fail'],
						'testtype' => $testSession['testset'],
						'total_na' => $testSessionTotals['block'],
						'qa_summary_txt' => $testSession['qa_summary']
				);;

				// Append test session object to test session list
				$test_session_list[] = $test_session_object;
			}
		}

		// Return JSON list
		echo "\n\n" . json_encode($test_session_list, JSON_FORCE_OBJECT) . "\n\n";
		exit;
	}

	public function executeExportRestApiAuthToken(sfWebRequest $request)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$qa_core = sfConfig::get("app_table_qa_core");

		// Retrieve $_GET
		$test_session_id = $request->getParameter("id");
		$auth_token = $request->getGetParameter("auth_token");

		// Check test session id
		if ($test_session_id == 0)
		{
			echo "ERROR : test session id is not valid";
			exit;
		}

		// Check authorized token exists, and retrieve user_id
		$query = "SELECT up.user_id
					FROM ".$qa_core.".sf_guard_user_profile up
					WHERE up.token = '".$auth_token."'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
		if (empty($result["user_id"]))
		{
			echo "ERROR : invalid authorized auth_token";
			exit;
		}
		$user_id = $result["user_id"];

		// Retrieve testsession and user profile
		$testSession = Doctrine_Core::getTable('TestSession')->findOneById($test_session_id);
		if (empty($testSession))
		{
			echo "ERROR : test session id doesn't exist";
			exit;
		}
		$userProfile = Doctrine_Core::getTable("SfGuardUserProfile")->findOneByUserId($user_id);
		if (empty($userProfile))
		{
			echo "ERROR : user profile doesn't exist";
			exit;
		}

		$testSessionTotals = Doctrine_Core::getTable('TestSession')->getSessionNumbersWithMeasures($test_session_id);
		$features = Doctrine_Core::getTable('TestSession')->getFeaturesNumbersWithMeasures($test_session_id);
		$testResults = Doctrine_Core::getTable('TestSession')->getSessionResults($test_session_id);

		// Retrieve project name, test environment name and image name, relying on test session id
		$configInfo = (is_null(Doctrine_Core::getTable('TestSession')->getConfigInfo($test_session_id))) ? array() : Doctrine_Core::getTable('TestSession')->getConfigInfo($test_session_id);

		// Retrieve test results and information regarding each feature
		$features_list = array();
		foreach ($features as $feature)
		{
			// Retrieve information regarding each test result
			$test_result_list = array();
			foreach ($testResults as $testResult)
			{
				if ($testResult['label'] == $feature['label'])
				{
					// Fill test result object
					$test_result_object = array('comment' => $testResult['comment'],
							'result' => $testResult['complement'],
							'bugs' => $testResult['bugs'],
							'name' => $testResult['name'],
							'qa_id' => $testResult['id']
					);;
					// Append test result object to test result list
					$test_result_list[] = $test_result_object;
				}
			}
			// Fill features object
			$features_object = array('name' => $feature['label'],
					'total_measured' => $feature['total_measured'],
					'total_na' => $feature['block'],
					'total_cases' => $feature['total'],
					'total_pass' => $feature['pass'],
					'comments' => '',
					'total_fail' => $feature['fail'],
					'qa_id' => $feature['id'],
					'cases' => $test_result_list
			);;
			// Append features object to features list
			$features_list[] = $features_object;
		}

		// Fill test session object
		$test_session_object = array('profile' => $configInfo['product_name'],
				'build_id' => $testSession['build_id'],
				'total_cases' => $testSessionTotals['total'],
				'title' => $testSession['name'],
				'qa_summary_txt' => $testSession['qa_summary'],
				'created_at' => $testSession['created_at'],
				'total_measured' => $testSessionTotals['total_measured'],
				'total_pass' => $testSessionTotals['pass'],
				'updated_at' => $testSession['updated_at'],
				'tested_at' => $testSession['created_at'],
				'release' => $configInfo['proj_name'],
				'hardware' => $configInfo['te_name'],
				'weeknum' => date('W', strtotime($testSession['created_at'])),
				'qa_id' => $testSession['id'],
				'total_fail' => $testSessionTotals['fail'],
				'testtype' => $testSession['testset'],
				'total_na' => $testSessionTotals['block'],
				'features' => $features_list
		);;

		// Return JSON object
		echo "\n\n" . json_encode($test_session_object, JSON_FORCE_OBJECT) . "\n\n";
		exit;
	}

	public function executeExportRestApiDownloadCsv(sfWebRequest $request)
	{
		// Retrieve $_GET
		$test_session_id = $request->getGetParameter("id");
		$product_type = $request->getGetParameter("product");
		$project = $request->getGetParameter("release_version");
		$environment = $request->getGetParameter("target");
		$testset = $request->getGetParameter("testset");

		// Check test session id
		if (empty($test_session_id))
		{
			echo "ERROR : test session id is not valid";
			exit;
		}
		$testSession = Doctrine_Core::getTable('TestSession')->findOneById($test_session_id);
		if (empty($testSession))
		{
			echo "ERROR : test session id doesn't exist";
			exit;
		}

		Import::exportAsCsv($test_session_id);
	}
}
