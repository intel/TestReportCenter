<?php

/**
 *
 */
class Import
{
	/**
	 * Import file. Call one of three specific functions
	 *
	 * @param report_file_path The file path to import.
	 * @param test_session_id The session's id.
	 * @param configuration_id The configuration's id.
	 */
	static public function file($report_file_path, $test_session_id, $configuration_id, $conn = null, $merge_flag = false)
	{
		// Analyze file path extension
		// If it is a CATS-NOTIFICATION
		if (preg_match("#CATS\-NOTIFICATION\.result\.xml$#i",$report_file_path))
			return Import::import_cats_notification($report_file_path, $test_session_id, $conn);
		// If it is a XML format
		elseif (preg_match("#\.xml$#i", $report_file_path))
			return Import::import_xml($report_file_path, $test_session_id, $configuration_id, $conn, $merge_flag);
		// Else if it is a CSV format
		elseif (preg_match("#\.csv$#i", $report_file_path))
			return @Import::import_csv($report_file_path, $test_session_id, $conn, $merge_flag);
	}

	/**
	 * Import specific XML file : CATS-NOTIFICATION
	 *
	 * @param report_file_path The file path to import.
	 * @param test_session_id The session's id.
	 */
	static public function import_cats_notification($report_file_path, $test_session_id, $conn)
	{
		// Retrieve table_name_id from table_name table
		$table_name_test_result = Doctrine_Core::getTable("TableName")->findOneByName("test_result");
		$table_name_test_result_id = $table_name_test_result->getId();

		// Create XMLReader object
		$reader = new XMLReader();
		if (!$reader->open($report_file_path)) {
			die("Failed to open " . $report_file_path);
		}

		// Initialize flags
		$flag_eof = false;

		// While there are results
		while ($reader->read())
		{
			// Parse the line so as to retrieve datas
			switch ($reader->nodeType)
			{
				case (XMLREADER::ELEMENT):
					switch ($reader->name)
					{
						case ("suite"):
							$suite_name = $reader->getAttribute("name");
							break;
						case ("set"):
							$set_name = $reader->getAttribute("name");
							break;
						case ("testcase"):
							$testcase_priority = $reader->getAttribute("priority");
							$testcase_status = $reader->getAttribute("status");
							$testcase_requirement_ref = $reader->getAttribute("requirement_ref");
							$testcase_result = $reader->getAttribute("result");
							$testcase_type = $reader->getAttribute("type");
							$testcase_purpose = $reader->getAttribute("purpose");
							$testcase_component = $reader->getAttribute("component");
							$testcase_id = $reader->getAttribute("id");
							$testcase_execution_type = $reader->getAttribute("execution_type");
							break;
						case ("notes"):
							$reader->read();
							$notes_text = $reader->value;
							break;
						case ("step"):
							$step_order = $reader->getAttribute("order");
							break;
						case ("expected"):
							$reader->read();
							$expected_text = $reader->value;
							break;
					}
					break;
				case (XMLReader::END_ELEMENT):
					switch ($reader->name)
					{
						case ("suite"):
							$flag_eof = true;
							break;
					}
					break;
			}
			// Exit the loop if it has reached end of file
			if ($flag_eof)
			{
				// Set default values if variables have not been set
				$testcase_id = (empty($testcase_id)) ? "Notification" : $testcase_id;
				$notes_text = (empty($notes_text)) ? " " : $notes_text;

				// 0 means there has been an issue while uploading files from CATS to QA_Report
				$decision_criteria_id = -2;
				$status = 0;
				// Write into test_result table
				$testResult = new TestResult();
				$testResult->setName($testcase_id);
				$testResult->setComplement(" ");
				$testResult->setTestSessionId($test_session_id);
				$testResult->setDecisionCriteriaId($decision_criteria_id);
				$testResult->setComment($notes_text);
				$testResult->setStatus($status);
				$testResult->save($conn);

				// Retrieve test_result id created
				$test_result_id = $testResult->getId();

				// Category = Feature = 1
				$category = 1;

				// Write into complementary_tool_relation table
				$complementaryToolRelation = new ComplementaryToolRelation();
				$complementaryToolRelation->setLabel("Empty_Feature");
				$complementaryToolRelation->setTableNameId($table_name_test_result_id);
				$complementaryToolRelation->setTableEntryId($test_result_id);
				$complementaryToolRelation->setCategory($category);
				$complementaryToolRelation->save($conn);

				break;
			}
		}
		return 0;
	}

	/**
	 * Import XML file
	 *
	 * @param report_file_path The file path to import.
	 * @param test_session_id The session's id.
	 * @param configuration_id The configuration's id.
	 */
	static public function import_xml($report_file_path, $test_session_id, $configuration_id, $conn, $merge_flag)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");

		// Retrieve table_name_id from table_name table
		$table_name_test_result = Doctrine_Core::getTable("TableName")->findOneByName("test_result");
		$table_name_test_result_id = $table_name_test_result->getId();
		// Initialize xml version variable
		$xml_version = 0;
		// Create XMLReader object
		$reader = new XMLReader();
		if (!$reader->open($report_file_path)) {
			return 10;
		}
		// Detect structure type (v1 or v2)
		$xml_version = 0;
		while ($reader->read())
		{
			if ($reader->nodeType == XMLReader::ELEMENT)
			{
				if ($reader->name == "testresults")
				{
					$xml_version = 1;
					break;
				}
				elseif (($reader->name == "test_definition") | ($reader->name == "testcase"))
				{
					if ($reader->name == "testcase")
					{
						$tag_eof = "suite";
					}
					else
					{
						$tag_eof = "test_definition";
					}

					$xml_version = 2;
					break;
				}
			}
		}
		if ($xml_version == 0)
		{
			return 1000;
		}
		// Close the file
		$reader->close();


		// Re-open the file
		if (!$reader->open($report_file_path)) {
			return 10;
		}
		// Initialize flags
		$flag_eof = false;
		$flag_step_end = false;
		$flag_case_found = false;
		$test_result_exists_flag = false;
		$flag_test_environment_and_image_set = false;
		$measureObjectList = array();
		$flag_series_node_found = false;
		// If structure type == v1
		if ($xml_version == 1)
		{
			// While there are results
			while ($reader->read())
			{
				$new_measure = false;
				// Parse the line so as to retrieve datas
				switch ($reader->nodeType)
				{
					case (XMLREADER::ELEMENT):
						switch ($reader->name)
						{
							case ("testresults"):
								$testresults_version = $reader->getAttribute("version");
								$testresults_environment = $reader->getAttribute("environment");
								$testresults_hwproduct = $reader->getAttribute("hwproduct");
								$testresults_hwbuild = $reader->getAttribute("hwbuild");
								break;
							case ("suite"):
								$suite_name = $reader->getAttribute("name");
								$suite_domain = $reader->getAttribute("domain");
								break;
							case ("set"):
								$set_name = $reader->getAttribute("name");
								$set_description = $reader->getAttribute("description");
								$set_feature = $reader->getAttribute("feature");
								$set_environment = $reader->getAttribute("environment");
								break;
							case ("case"):
								$case_name = $reader->getAttribute("name");
								$case_description = $reader->getAttribute("description");
								$case_manual = $reader->getAttribute("manual");
								$case_insignificant = $reader->getAttribute("insignificant");
								$case_result = $reader->getAttribute("result");
								$case_subfeature = $reader->getAttribute("subfeature");
								$case_level = $reader->getAttribute("level");
								$case_comment = $reader->getAttribute("comment");
								$case_failure_info = $reader->getAttribute("failure_info");
								break;
							case ("step"):
								$step_command = $reader->getAttribute("command");
								$step_result = $reader->getAttribute("result");
								$step_failure_info = $reader->getAttribute("failure_info");
								break;
							case ("expected_result"):
								$reader->read();
								$expected_result_text = $reader->value;
								break;
							case ("return_code"):
								$reader->read();
								$return_code_text = $reader->value;
								break;
							case ("start"):
								$reader->read();
								$start_text = $reader->value;
								break;
							case ("end"):
								$reader->read();
								$end_text = $reader->value;
								break;
							case ("stdout"):
								$reader->read();
								$stdout_text = $reader->value;
								break;
							case ("stderr"):
								$reader->read();
								$stderr_text = $reader->value;
								break;
							case ("series"):
								$flag_series_node_found = true;
								$series_name = $reader->getAttribute("name");
								$series_group = $reader->getAttribute("group");
								$series_unit = $reader->getAttribute("unit");
								$series_interval = $reader->getAttribute("interval");
								$series_interval_unit = $reader->getAttribute("interval_unit");
								break;
							case ("measurement"):
								$new_measure = true;
								$measurement_name = $reader->getAttribute("name");
								$measurement_power = $reader->getAttribute("power");
								$measurement_value = preg_replace('#(,)#','.',$reader->getAttribute("value"));
								$measurement_failure = preg_replace('#(,)#','.',$reader->getAttribute("failure"));
								$measurement_unit = $reader->getAttribute("unit");
								$measurement_target = preg_replace('#(,)#','.',$reader->getAttribute("target"));
								break;
						}
						break;
					case (XMLReader::END_ELEMENT):
						switch ($reader->name)
						{
							case ("series"):
								$flag_series_node_found = false;
								break;
							case ("case"):
								$flag_step_end = true;
								$flag_case_found = true;
								break;
							case ("testresults"):
								$flag_eof = true;
								break;
						}
						break;
				}

				// Exit the loop if it has reached end of file
				if ($flag_eof)
				{
					if (!$flag_case_found)
					{
						return 1001;
					}
					break;
				}

				if ($flag_step_end)
				{
					// Write it into qa_generic database
					if (!$flag_test_environment_and_image_set)
					{
						if (!empty($suite_name))
						{
							// Retrieve test_session_name from test_session_id
							$testSession = Doctrine_Core::getTable("TestSession")->findOneById($test_session_id);
							$name_buffer = $testSession->getName();
							// If test_session_name is empty, replace it with suite_name
							if ($name_buffer == "Empty testsession")
							{
								Doctrine_Query::create()
								->update('TestSession')
								->set('name', '?', $suite_name)
								->where('id = ?', $test_session_id)
								->execute();
							}
						}
						$flag_test_environment_and_image_set = true;
					}
					// Determine decision_criteria_id
					if (preg_match("#^PASS$#i",$case_result))
						$decision_criteria_id = -1;
					else if (preg_match("#^FAIL$#i",$case_result))
						$decision_criteria_id = -2;
					else if (preg_match("#^BLOCK$#i",$case_result))
						$decision_criteria_id = -3;
					else if (preg_match("#^DEFER$#i",$case_result) || preg_match("#^DEFERRED$#i",$case_result))
						$decision_criteria_id = -4;
					else if (preg_match("#^NOT_RUN$#i",$case_result) || preg_match("#^NOTRUN$#i",$case_result))
						$decision_criteria_id = -5;
					else
						$decision_criteria_id = -3;
					// Determine execution_time
					$datetime_start = new DateTime($start_text);
					$datetime_end = new DateTime($end_text);
					$interval = date_diff($datetime_start,$datetime_end);
					$execution_time = $interval->format('00%Y-%M-%D %H:%I:%S');

					// Create name
					if (empty($case_name))
					{
						return 1101;
					}
					else
					{
						$name = $case_name;
					}

					// Create label
					if (empty($set_feature))
					{
						return 1102;
					}
					else
					{
						$label = $set_feature;
					}

					// Create complement
					$complement = (empty($case_description)) ? " " : $case_description;

					// Create comment
					$comment = (empty($case_comment)) ? " " : $case_comment;

					// Status hard coded
					$resultStatus = 0;

					if ($merge_flag)
					{
						// Retrieve test result id relying on test name and feature label, if it exists
						$query = "SELECT tr.id tr_id
								FROM ".$qa_generic.".test_result tr
										JOIN ".$qa_generic.".table_name tn ON tn.name = 'test_result'
												JOIN ".$qa_generic.".complementary_tool_relation ctr ON ctr.table_name_id = tn.id
														AND ctr.table_entry_id = tr.id
														AND ctr.label = '".addslashes($label)."'
																WHERE tr.test_session_id = ".$test_session_id."
																		AND tr.name = '".addslashes($name)."'";
						$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
						if (!empty($result))
						{
							$test_result_exists_flag = true;
							$testResultId = $result['tr_id'];
						}
					}

					// Write into test_result table
					if ($test_result_exists_flag)
					{
						$measures = Doctrine_Query::create()
						->select('*')
						->from('Measure')
						->where('test_result_id = ?', $testResultId)
						->execute();
						foreach ($measures as $measure)
						{
							$measure->delete($conn);
						}

						$testResult = Doctrine_Core::getTable("TestResult")->findOneById($testResultId);
					}
					else
					{
						$testResult = new TestResult();
						$testResult->setName($name);
						$testResult->setTestSessionId($test_session_id);
					}

					$testResult->setComplement($complement);
					$testResult->setDecisionCriteriaId($decision_criteria_id);
					$testResult->setComment($comment);
					$testResult->setStartedAt($start_text);
					$testResult->setExecutionTime($execution_time);
					$testResult->setStatus($resultStatus);
					$testResult->save($conn);

					// Retrieve test_result id created
					$test_result_id = $testResult->getId();

					// Determine category
					if (preg_match("#^ ?Component ?$#i",$case_level))
					{
						$category = 0;
					}
					elseif (preg_match("#^ ?Feature ?$#i",$case_level))
					{
						$category = 1;
					}
					elseif (preg_match("#^ ?Test ?case ?$#i",$case_level))
					{
						$category = 2;
					}
					elseif (preg_match("#^ ?Bug ?$#i",$case_level))
					{
						$category = 3;
					}
					elseif (preg_match("#^ ?Testing ?tool ?$#i",$case_level))
					{
						$category = 4;
					}
					else // Default value
					{
						$category = 1;
					}
					$category = 1;

					// Write into complementary_tool_relation table
					if (!$test_result_exists_flag)
					{
						$complementaryToolRelation = new ComplementaryToolRelation();
						$complementaryToolRelation->setLabel($label);
						$complementaryToolRelation->setTableNameId($table_name_test_result_id);
						$complementaryToolRelation->setTableEntryId($test_result_id);
						$complementaryToolRelation->setCategory($category);
						$complementaryToolRelation->save($conn);
					}

					// Save Measure objects
					foreach ($measureObjectList as $measureObject)
					{
						$measureObject->setTestResultId($test_result_id);
						$measureObject->save($conn);
					}

					// Pass flags to false
					$flag_step_end = false;
					$test_result_exists_flag = false;
					$measureObjectList = array();
					$flag_series_node_found = false;
				}

				// Fill in Measure objects
				$measurement_unit = empty($measurement_unit) ? " " : $measurement_unit;
				if (!$flag_series_node_found)
				{
					if ($new_measure AND !empty($measurement_name))
					{
						if (!empty($measurement_value))
						{
							$measureObject = new Measure();
							$measureObject->setValue($measurement_value);
							$measureObject->setUnit($measurement_unit);
							$measureObject->setDescription($measurement_name);
							$measureObject->setCategory(1);
							$measureObject->setOperator(1);
							$measureObjectList[] = $measureObject;
						}

						if (!empty($measurement_target))
						{
							$measureObject = new Measure();
							$measureObject->setValue($measurement_target);
							$measureObject->setUnit($measurement_unit);
							$measureObject->setDescription($measurement_name);
							$measureObject->setCategory(2);
							$measureObject->setOperator(1);
							$measureObjectList[] = $measureObject;
						}

						if (!empty($measurement_failure))
						{
							$measureObject = new Measure();
							$measureObject->setValue($measurement_failure);
							$measureObject->setUnit($measurement_unit);
							$measureObject->setDescription($measurement_name);
							$measureObject->setCategory(3);
							$measureObject->setOperator(1);
							$measureObjectList[] = $measureObject;
						}
					}
				}
			}
		}
		// Else if structure type == v2
		elseif ($xml_version == 2)
		{
			// While there are results
			while ($reader->read())
			{
				$new_measure = false;
				// Parse the line so as to retrieve datas
				switch ($reader->nodeType)
				{
					case (XMLREADER::ELEMENT):
						switch ($reader->name)
						{
							case ("environment"):
								$environment_device_id = $reader->getAttribute("device_id");
								$environment_device_model = $reader->getAttribute("device_model");
								$environment_device_name = $reader->getAttribute("device_name");
								$environment_firmware_version = $reader->getAttribute("firmware_version");
								$environment_host = $reader->getAttribute("host");
								$environment_os_version = $reader->getAttribute("os_version");
								$environment_resolution = $reader->getAttribute("resolution");
								$environment_screen_size = $reader->getAttribute("screen_size");
								break;
							case ("other"):
								$reader->read();
								$other_text = $reader->value;
								break;
							case ("summary"):
								$summary_test_plan_name = $reader->getAttribute("test_plan_name");
								break;
							case ("start_at"):
								$reader->read();
								$start_at_text = $reader->value;
								break;
							case ("end_at"):
								$reader->read();
								$end_at_text = $reader->value;
								break;
							case ("suite"):
								$suite_name = $reader->getAttribute("name");
								break;
							case ("set"):
								$set_name = $reader->getAttribute("name");
								break;
							case ("testcase"):
								$testcase_component = $reader->getAttribute("component");
								$testcase_execution_type = $reader->getAttribute("execution_type");
								$testcase_id = $reader->getAttribute("id");
								$testcase_priority = $reader->getAttribute("priority");
								$testcase_purpose = $reader->getAttribute("purpose");
								$testcase_result = $reader->getAttribute("result");
								$testcase_status = $reader->getAttribute("status");
								$testcase_type = $reader->getAttribute("type");
								break;
							case ("pre_condition"):
								$reader->read();
								$pre_condition_text = $reader->value;
								break;
							case ("notes"):
								$reader->read();
								$notes_text = $reader->value;
								break;
							case ("test_script_entry"):
								$test_script_entry_test_script_expected_result = $reader->getAttribute("test_script_expected_result");
								$test_script_entry_timeout = $reader->getAttribute("timeout");
								$reader->read();
								$test_script_entry_text = $reader->value;
							case ("actual_result"):
								$reader->read();
								$actual_result_text = $reader->value;
								break;
							case ("start"):
								$reader->read();
								$start_text = $reader->value;
								break;
							case ("end"):
								$reader->read();
								$end_text = $reader->value;
								break;
							case ("stdout"):
								$reader->read();
								$stdout_text = $reader->value;
								break;
							case ("stderr"):
								$reader->read();
								$stderr_text = $reader->value;
								break;
							case ("series"):
								$flag_series_node_found = true;
								$series_name = $reader->getAttribute("name");
								$series_group = $reader->getAttribute("group");
								$series_unit = $reader->getAttribute("unit");
								$series_interval = $reader->getAttribute("interval");
								$series_interval_unit = $reader->getAttribute("interval_unit");
								break;
							case ("measurement"):
								$new_measure = true;
								$measurement_name = $reader->getAttribute("name");
								$measurement_power = $reader->getAttribute("power");
								$measurement_value = preg_replace('#(,)#','.',$reader->getAttribute("value"));
								$measurement_failure = preg_replace('#(,)#','.',$reader->getAttribute("failure"));
								$measurement_unit = $reader->getAttribute("unit");
								$measurement_target = preg_replace('#(,)#','.',$reader->getAttribute("target"));
								break;
						}
						break;
					case (XMLReader::END_ELEMENT):
						switch ($reader->name)
						{
							case ("series"):
								$flag_series_node_found = false;
								break;
							case ("testcase"):
								$flag_step_end = true;
								$flag_case_found = true;
								break;
							case ($tag_eof):
								$flag_eof = true;
								break;
						}
						break;
				}

				// Exit the loop if it has reached end of file
				if ($flag_eof)
				{
					if (!$flag_case_found)
					{
						return 1002;
					}
					break;
				}

				if ($flag_step_end)
				{
					// Write it into qa_generic database
					if (!$flag_test_environment_and_image_set)
					{
						if (!empty($suite_name))
						{
							// Retrieve test_session_name from test_session_id
							$testSession = Doctrine_Core::getTable("TestSession")->findOneById($test_session_id);
							$name_buffer = $testSession->getName();
							// If test_session_name is empty, replace it with suite_name
							if ($name_buffer == "Empty testsession")
							{
								Doctrine_Query::create()
								->update('TestSession')
								->set('name', '?', $suite_name)
								->where('id = ?', $test_session_id)
								->execute();
							}
						}
						$flag_test_environment_and_image_set = true;
					}
					// Determine decision_criteria_id
					if (preg_match("#^PASS$#i",$testcase_result))
						$decision_criteria_id = -1;
					elseif (preg_match("#^FAIL$#i",$testcase_result))
						$decision_criteria_id = -2;
					else if (preg_match("#^BLOCK$#i",$testcase_result))
						$decision_criteria_id = -3;
					else if (preg_match("#^DEFER$#i",$testcase_result) || preg_match("#^DEFERRED$#i",$testcase_result))
						$decision_criteria_id = -4;
					else if (preg_match("#^NOT_RUN$#i",$testcase_result) || preg_match("#^NOTRUN$#i",$testcase_result))
						$decision_criteria_id = -5;
					else
						$decision_criteria_id = -3;
					// Determine execution_time
					if (empty($start_text))
					{
						$start_text = " ";
						$execution_time = " ";
					}
					elseif (empty($end_text))
					{
						$execution_time = " ";
					}
					else
					{
						$start_text = preg_replace('#^([0-9]{4}-[0-9]{2}-[0-9]{2})_([0-9]{2})_([0-9]{2})_([0-9]{2})$#', '$1 $2:$3:$4', $start_text);
						$end_text = preg_replace('#^([0-9]{4}-[0-9]{2}-[0-9]{2})_([0-9]{2})_([0-9]{2})_([0-9]{2})$#', '$1 $2:$3:$4', $end_text);
						$datetime_start = new DateTime($start_text);
						$datetime_end = new DateTime($end_text);
						$interval = date_diff($datetime_start,$datetime_end);
						$execution_time = $interval->format('00%Y-%M-%D %H:%I:%S');
					}

					// Create name
					if (empty($testcase_id))
					{
						return 1201;
					}
					else
					{
						$name = $testcase_id;
					}

					// Create label
					if (empty($testcase_component))
					{
						return 1202;
					}
					else
					{
						$label = $testcase_component;
					}

					// Create complement
					$complement = (empty($testcase_purpose)) ? " " : $testcase_purpose;

					// Create comment
					$comment = (empty($notes_text)) ? " " : $notes_text;

					// Status hard coded
					$resultStatus = 0;

					if ($merge_flag)
					{
						// Retrieve test result id relying on test name and feature label, if it exists
						$query = "SELECT tr.id tr_id
								FROM ".$qa_generic.".test_result tr
										JOIN ".$qa_generic.".table_name tn ON tn.name = 'test_result'
												JOIN ".$qa_generic.".complementary_tool_relation ctr ON ctr.table_name_id = tn.id
														AND ctr.table_entry_id = tr.id
														AND ctr.label = '".addslashes($label)."'
																WHERE tr.test_session_id = ".$test_session_id."
																		AND tr.name = '".addslashes($name)."'";
						$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
						if (!empty($result))
						{
							$test_result_exists_flag = true;
							$testResultId = $result['tr_id'];
						}
					}

					// Write into test_result table
					if ($test_result_exists_flag)
					{
						$measures = Doctrine_Query::create()
						->select('*')
						->from('Measure')
						->where('test_result_id = ?', $testResultId)
						->execute();
						foreach ($measures as $measure)
						{
							$measure->delete($conn);
						}

						$testResult = Doctrine_Core::getTable("TestResult")->findOneById($testResultId);
					}
					else
					{
						$testResult = new TestResult();
						$testResult->setName($name);
						$testResult->setTestSessionId($test_session_id);
					}

					$testResult->setComplement($complement);
					$testResult->setDecisionCriteriaId($decision_criteria_id);
					$testResult->setComment($comment);
					$testResult->setStartedAt($start_text);
					$testResult->setExecutionTime($execution_time);
					$testResult->setStatus($resultStatus);
					$testResult->save($conn);

					// Retrieve test_result id created
					$test_result_id = $testResult->getId();

					// Category = Feature = 1
					$category = 1;

					// Write into complementary_tool_relation table
					if (!$test_result_exists_flag)
					{
						$complementaryToolRelation = new ComplementaryToolRelation();
						$complementaryToolRelation->setLabel($label);
						$complementaryToolRelation->setTableNameId($table_name_test_result_id);
						$complementaryToolRelation->setTableEntryId($test_result_id);
						$complementaryToolRelation->setCategory($category);
						$complementaryToolRelation->save($conn);
					}

					// Save Measure objects
					foreach ($measureObjectList as $measureObject)
					{
						$measureObject->setTestResultId($test_result_id);
						$measureObject->save($conn);
					}

					// Pass flags to false
					$flag_step_end = false;
					$test_result_exists_flag = false;
					$measureObjectList = array();
					$flag_series_node_found = false;
				}

				// Fill in Measure objects
				$measurement_unit = empty($measurement_unit) ? " " : $measurement_unit;
				if (!$flag_series_node_found)
				{
					if ($new_measure AND !empty($measurement_name))
					{
						if (!empty($measurement_value))
						{
							$measureObject = new Measure();
							$measureObject->setValue($measurement_value);
							$measureObject->setUnit($measurement_unit);
							$measureObject->setDescription($measurement_name);
							$measureObject->setCategory(1);
							$measureObject->setOperator(1);
							$measureObjectList[] = $measureObject;
						}

						if (!empty($measurement_target))
						{
							$measureObject = new Measure();
							$measureObject->setValue($measurement_target);
							$measureObject->setUnit($measurement_unit);
							$measureObject->setDescription($measurement_name);
							$measureObject->setCategory(2);
							$measureObject->setOperator(1);
							$measureObjectList[] = $measureObject;
						}

						if (!empty($measurement_failure))
						{
							$measureObject = new Measure();
							$measureObject->setValue($measurement_failure);
							$measureObject->setUnit($measurement_unit);
							$measureObject->setDescription($measurement_name);
							$measureObject->setCategory(3);
							$measureObject->setOperator(1);
							$measureObjectList[] = $measureObject;
						}
					}
				}
			}
		}
		// Close the file
		$reader->close();

		return 0;
	}

	/**
	 * Import CSV file
	 *
	 * @param report_file_path The file path to import.
	 * @param test_session_id The session's id.
	 */
	static public function import_csv($report_file_path, $test_session_id, $conn, $merge_flag)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");

		// Retrieve table_name_id from table_name table
		$table_name_test_result = Doctrine_Core::getTable("TableName")->findOneByName("test_result");
		$table_name_test_result_id = $table_name_test_result->getId();

		$test_result_exists_flag = false;

		// Open the file
		$csv_report_file = @fopen($report_file_path,'r');
		if ($csv_report_file)
		{
			// Get file first line
			$first_line = fgets($csv_report_file);

			// Detect delimiter
			if (preg_match("#,#", $first_line))
				$delimiter = ',';
			else if (preg_match("#;#", $first_line))
				$delimiter = ';';
			else
				return 2001;

			// Detect structure type
			// CSV old
			if (preg_match("#(?=.*Feature)(?=.*Check ?points)(?=.*Notes ?\(bugs\))(?=.*Status)#i", $first_line))
			{
				// Return at the beginning of file
				fseek($csv_report_file,0);

				// Determine order between different fields on the first line
				$idx = 0;
				$ref_tab = array();
				foreach (fgetcsv($csv_report_file, 0, $delimiter) as $element)
				{
					if (preg_match("#^ ?Feature.?$#i",$element))
						$ref_tab[0] = $idx;
					elseif (preg_match("#^ ?Check ?points.?$#i",$element))
						$ref_tab[1] = $idx;
					elseif (preg_match("#^ ?Notes ?\(bugs\).?$#i",$element))
						$ref_tab[2] = $idx;
					elseif (preg_match("#^ ?Status.?$#i",$element))
						$ref_tab[3] = $idx;
					elseif (preg_match("#^ ?Duration.?$#i",$element))
						$ref_tab[4] = $idx;
					elseif (preg_match("#^ ?Bug.?$#i",$element))
						$ref_tab[5] = $idx;
					$idx++;
				}

				// Go through file and fill datas
				$data_tab = array();
				while ($data_tab = fgetcsv($csv_report_file, 0, $delimiter))
				{
					if (count($data_tab) > 3)
					{
						// Check if feature is not empty
						if (empty($data_tab[$ref_tab[0]])) return 2101;
						else $feature = $data_tab[$ref_tab[0]];

						// Check if check points is not empty
						if (empty($data_tab[$ref_tab[1]])) return 2102;
						else $case_id = $data_tab[$ref_tab[1]];

						// Check if status is not empty
						if (empty($data_tab[$ref_tab[3]])) return 2103;
						else $status = $data_tab[$ref_tab[3]];

						// Check if execution time is not empty
						if(!empty($data_tab[$ref_tab[4]])) $executionTime = $data_tab[$ref_tab[4]];
						else $executionTime = 0;

						// Check if bug list is not empty
						if(!empty($data_tab[$ref_tab[5]])) $bugs = $data_tab[$ref_tab[5]];
						else $bugs = "";

						// Write datas into qa_generic database
						if (preg_match("#^ ?pass(ed)? ?$#i", $status))
							$decision_criteria_id = -1;
						else if (preg_match("#^ ?fail(ed)? ?$#i", $status))
							$decision_criteria_id = -2;
						else if (preg_match("#^ ?block(ed)? ?$#i", $status))
							$decision_criteria_id = -3;
						else if (preg_match("#^ ?defer(red)? ?$#i", $status))
							$decision_criteria_id = -4;
						else if (preg_match("#^ ?not ?run ?$#i", $status) || preg_match("#^ ?not_run ?$#i", $status))
							$decision_criteria_id = -5;
						else
							return 2104;

						// Status hard coded
						$resultStatus = 0;

						if ($merge_flag)
						{
							// Retrieve test result id relying on test name and feature label, if it exists
							$query = "SELECT tr.id tr_id
									FROM ".$qa_generic.".test_result tr
										JOIN ".$qa_generic.".table_name tn ON tn.name = 'test_result'
										JOIN ".$qa_generic.".complementary_tool_relation ctr ON ctr.table_name_id = tn.id
											AND ctr.table_entry_id = tr.id
											AND ctr.label = '".addslashes($feature)."'
									WHERE tr.test_session_id = ".$test_session_id."
										AND tr.name = '".addslashes($case_id)."'";
							$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
							if (!empty($result))
							{
								$test_result_exists_flag = true;
								$testResultId = $result['tr_id'];
							}
						}

						// Write into test_result table
						if ($test_result_exists_flag)
						{
							$testResult = Doctrine_Core::getTable("TestResult")->findOneById($testResultId);
						}
						else
						{
							$testResult = new TestResult();
							$testResult->setName(stripslashes($case_id));
							$testResult->setTestSessionId($test_session_id);
						}

						$testResult->setDecisionCriteriaId($decision_criteria_id);
						$testResult->setComplement(stripslashes($test_case));
						$testResult->setComment(stripslashes($comment));
						$testResult->setStatus($resultStatus);
						$testResult->setExecutionTime($executionTime);
						$testResult->setBugs(stripslashes($bugs));
						$testResult->save($conn);

						// Retrieve test_result id created
						$test_result_id = $testResult->getId();

						// Category = Feature = 1
						$category = 1;

						// Write into complementary_tool_relation table
						if (!$test_result_exists_flag)
						{
							$complementaryToolRelation = new ComplementaryToolRelation();
							$complementaryToolRelation->setLabel(stripslashes($feature));
							$complementaryToolRelation->setTableNameId($table_name_test_result_id);
							$complementaryToolRelation->setTableEntryId($test_result_id);
							$complementaryToolRelation->setCategory($category);
							$complementaryToolRelation->save($conn);
						}
					}
					$data_tab = array();
					$test_result_exists_flag = false;
				}
			}

			// CSV old and v1 mix
			elseif (preg_match("#(?=.*Feature)(?=.*Check ?points)(?=.*Notes ?\(bugs\))(?=.*Pass)(?=.*Fail)(?=.*N/A)#i", $first_line))
			{
				// Return at the beginning of file
				fseek($csv_report_file,0);

				// Determine order between different fields on the first line
				$idx = 0;
				$ref_tab = array();
				foreach (fgetcsv($csv_report_file, 0, $delimiter) as $element)
				{
					if (preg_match("#^ ?Feature.?$#i",$element))
						$ref_tab[0] = $idx;
					elseif (preg_match("#^ ?Check ?points.?$#i",$element))
						$ref_tab[1] = $idx;
					elseif (preg_match("#^ ?Notes ?\(bugs\).?$#i",$element))
						$ref_tab[2] = $idx;
					elseif (preg_match("#^ ?Pass.?$#i",$element))
						$ref_tab[3] = $idx;
					elseif (preg_match("#^ ?Fail.?$#i",$element))
						$ref_tab[4] = $idx;
					elseif (preg_match("#^ ?N/?A.?$#i",$element))
						$ref_tab[5] = $idx;
					elseif (preg_match("#^ ?Defer(red)?.?$#i",$element))
						$ref_tab[8] = $idx;
					elseif (preg_match("#^ ?Not ?run.?$#i",$element) || preg_match("#^ ?Not_run.?$#i",$element))
						$ref_tab[9] = $idx;
					elseif (preg_match("#^ ?Duration.?$#i",$element))
						$ref_tab[6] = $idx;
					elseif (preg_match("#^ ?Bug.?$#i",$element))
						$ref_tab[7] = $idx;
					$idx++;
				}

				// Go through file and fill datas
				$data_tab = array();
				while ($data_tab = fgetcsv($csv_report_file, 0, $delimiter))
				{
					if (count($data_tab) > 5)
					{
						// Check if feature is not empty
						if (empty($data_tab[$ref_tab[0]])) return 2201;
						else $feature = $data_tab[$ref_tab[0]];

						// Check if case_id is not empty
						if (empty($data_tab[$ref_tab[1]])) return 2202;
						else $case_id = $data_tab[$ref_tab[1]];

						// Check if bug list is not empty
						if (empty($data_tab[$ref_tab[7]])) $bugs = "";
						else $bugs = $data_tab[$ref_tab[7]];

						$comment = (empty($data_tab[$ref_tab[2]])) ? " " : $data_tab[$ref_tab[2]];

						if (!(($data_tab[$ref_tab[3]] == "1") OR ($data_tab[$ref_tab[4]] == "1") OR ($data_tab[$ref_tab[5]] == "1") OR ($data_tab[$ref_tab[8]] == "1") OR ($data_tab[$ref_tab[9]] == "1")))
							return 2203;

						$pass = (empty($data_tab[$ref_tab[3]])) ? "" : $data_tab[$ref_tab[3]];
						$fail = (empty($data_tab[$ref_tab[4]])) ? "" : $data_tab[$ref_tab[4]];
						$block = (empty($data_tab[$ref_tab[5]])) ? "" : $data_tab[$ref_tab[5]];
						$deferred = (empty($data_tab[$ref_tab[8]])) ? "" : $data_tab[$ref_tab[8]];
						$notrun = (empty($data_tab[$ref_tab[9]])) ? "" : $data_tab[$ref_tab[9]];

						if ($pass == "1")
							$decision_criteria_id = -1;
						elseif ($fail == "1")
							$decision_criteria_id = -2;
						elseif ($block == "1")
							$decision_criteria_id = -3;
						elseif ($deferred == "1")
							$decision_criteria_id = -4;
						elseif ($notrun == "1")
							$decision_criteria_id = -5;

						if(!empty($data_tab[$ref_tab[6]]))
							$executionTime = $data_tab[$ref_tab[6]];
						else
							$executionTime = 0;

						// Status hard coded
						$status = 0;

						if ($merge_flag)
						{
							// Retrieve test result id relying on test name and feature label, if it exists
							$query = "SELECT tr.id tr_id
									FROM ".$qa_generic.".test_result tr
										JOIN ".$qa_generic.".table_name tn ON tn.name = 'test_result'
										JOIN ".$qa_generic.".complementary_tool_relation ctr ON ctr.table_name_id = tn.id
											AND ctr.table_entry_id = tr.id
											AND ctr.label = '".addslashes($feature)."'
									WHERE tr.test_session_id = ".$test_session_id."
										AND tr.name = '".addslashes($case_id)."'";
							$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
							if (!empty($result))
							{
								$test_result_exists_flag = true;
								$testResultId = $result['tr_id'];
							}
						}

						// Write into test_result table
						if ($test_result_exists_flag)
						{
							$testResult = Doctrine_Core::getTable("TestResult")->findOneById($testResultId);
						}
						else
						{
							$testResult = new TestResult();
							$testResult->setName(stripslashes($case_id));
							$testResult->setTestSessionId($test_session_id);
						}

						$testResult->setDecisionCriteriaId($decision_criteria_id);
						$testResult->setComplement(stripslashes($test_case));
						$testResult->setComment(stripslashes($comment));
						$testResult->setStatus($status);
						$testResult->setExecutionTime($executionTime);
						$testResult->setBugs(stripslashes($bugs));
						$testResult->save($conn);

						// Retrieve test_result id created
						$test_result_id = $testResult->getId();

						// Category = Feature = 1
						$category = 1;

						// Write into complementary_tool_relation table
						if (!$test_result_exists_flag)
						{
							$complementaryToolRelation = new ComplementaryToolRelation();
							$complementaryToolRelation->setLabel(stripslashes($feature));
							$complementaryToolRelation->setTableNameId($table_name_test_result_id);
							$complementaryToolRelation->setTableEntryId($test_result_id);
							$complementaryToolRelation->setCategory($category);
							$complementaryToolRelation->save($conn);
						}
					}
					$data_tab = array();
					$test_result_exists_flag = false;
				}
			}

			// CSV v1
			elseif (preg_match("#(?=.*Feature)(?=.*Case ?id)(?=.*Check ?points)(?=.*Notes)(?=.*Pass)(?=.*Fail)(?=.*N/A)#i", $first_line))
			{
				// Return at the beginning of file
				fseek($csv_report_file,0);

				// Determine order between different fields on the first line
				$idx = 0;
				$ref_tab = array();
				foreach (fgetcsv($csv_report_file, 0, $delimiter) as $element)
				{
					if (preg_match("#^ ?Feature.?$#i",$element))
						$ref_tab[0] = $idx;
					elseif (preg_match("#^ ?Case ?id.?$#i",$element))
						$ref_tab[1] = $idx;
					elseif (preg_match("#^ ?Check ?points.?$#i",$element))
						$ref_tab[2] = $idx;
					elseif (preg_match("#^ ?Notes.?$#i",$element))
						$ref_tab[3] = $idx;
					elseif (preg_match("#^ ?Pass.?$#i",$element))
						$ref_tab[4] = $idx;
					elseif (preg_match("#^ ?Fail.?$#i",$element))
						$ref_tab[5] = $idx;
					elseif (preg_match("#^ ?N/?A.?$#i",$element))
						$ref_tab[6] = $idx;
					elseif (preg_match("#^ ?Defer(red)?.?$#i",$element))
						$ref_tab[9] = $idx;
					elseif (preg_match("#^ ?Not ?run.?$#i",$element) || preg_match("#^ ?Not_run.?$#i",$element))
						$ref_tab[10] = $idx;
					elseif (preg_match("#^ ?Duration.?$#i",$element))
						$ref_tab[7] = $idx;
					elseif (preg_match("#^ ?Bug.?$#i",$element))
						$ref_tab[8] = $idx;
					$idx++;
				}

				// Go through file and fill datas
				$data_tab = array();
				while ($data_tab = fgetcsv($csv_report_file, 0, $delimiter))
				{
					if (count($data_tab) > 6)
					{
						// Check if feature is not empty
						if (empty($data_tab[$ref_tab[0]])) return 2301;
						else $feature = $data_tab[$ref_tab[0]];

						// Check if case_id is not empty
						if (empty($data_tab[$ref_tab[1]])) return 2302;
						else $case_id = $data_tab[$ref_tab[1]];

						$test_case = (empty($data_tab[$ref_tab[2]])) ? " " : $data_tab[$ref_tab[2]];
						$comment = (empty($data_tab[$ref_tab[3]])) ? " " : $data_tab[$ref_tab[3]];

						if (!(($data_tab[$ref_tab[4]] == "1") OR ($data_tab[$ref_tab[5]] == "1") OR ($data_tab[$ref_tab[6]] == "1")))
							return 2303;

						$pass = (empty($data_tab[$ref_tab[4]])) ? "" : $data_tab[$ref_tab[4]];
						$fail = (empty($data_tab[$ref_tab[5]])) ? "" : $data_tab[$ref_tab[5]];
						$block = (empty($data_tab[$ref_tab[6]])) ? "" : $data_tab[$ref_tab[6]];
						$deferred = (empty($data_tab[$ref_tab[9]])) ? "" : $data_tab[$ref_tab[9]];
						$notrun = (empty($data_tab[$ref_tab[10]])) ? "" : $data_tab[$ref_tab[10]];

						if ($pass == "1")
							$decision_criteria_id = -1;
						elseif ($fail == "1")
							$decision_criteria_id = -2;
						elseif ($block == "1")
							$decision_criteria_id = -3;
						elseif ($deferred == "1")
							$decision_criteria_id = -4;
						elseif ($notrun == "1")
							$decision_criteria_id = -5;

						// Check if execution time is not empty
						if(!empty($data_tab[$ref_tab[7]])) $executionTime = $data_tab[$ref_tab[7]];
						else $executionTime = 0;

						// Check if bug list is not empty
						if (empty($data_tab[$ref_tab[8]])) $bugs = "";
						else $bugs = $data_tab[$ref_tab[8]];

						// Status hard coded
						$status = 0;

						if ($merge_flag)
						{
							// Retrieve test result id relying on test name and feature label, if it exists
							$query = "SELECT tr.id tr_id
									FROM ".$qa_generic.".test_result tr
										JOIN ".$qa_generic.".table_name tn ON tn.name = 'test_result'
										JOIN ".$qa_generic.".complementary_tool_relation ctr ON ctr.table_name_id = tn.id
											AND ctr.table_entry_id = tr.id
											AND ctr.label = '".addslashes($feature)."'
									WHERE tr.test_session_id = ".$test_session_id."
										AND tr.name = '".addslashes($case_id)."'";
							$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
							if (!empty($result))
							{
								$test_result_exists_flag = true;
								$testResultId = $result['tr_id'];
							}
						}

						// Write into test_result table
						if ($test_result_exists_flag)
						{
							$testResult = Doctrine_Core::getTable("TestResult")->findOneById($testResultId);
						}
						else
						{
							$testResult = new TestResult();
							$testResult->setName(stripslashes($case_id));
							$testResult->setTestSessionId($test_session_id);
						}

						$testResult->setDecisionCriteriaId($decision_criteria_id);
						$testResult->setComplement(stripslashes($test_case));
						$testResult->setComment(stripslashes($comment));
						$testResult->setStatus($status);
						$testResult->setExecutionTime($executionTime);
						$testResult->setBugs(stripslashes($bugs));
						$testResult->save($conn);

						// Retrieve test_result id created
						$test_result_id = $testResult->getId();

						// Category = Feature = 1
						$category = 1;

						// Write into complementary_tool_relation table
						if (!$test_result_exists_flag)
						{
							$complementaryToolRelation = new ComplementaryToolRelation();
							$complementaryToolRelation->setLabel(stripslashes($feature));
							$complementaryToolRelation->setTableNameId($table_name_test_result_id);
							$complementaryToolRelation->setTableEntryId($test_result_id);
							$complementaryToolRelation->setCategory($category);
							$complementaryToolRelation->save($conn);
						}
					}
					$data_tab = array();
					$test_result_exists_flag = false;
				}
			}

			// CSV v2
			elseif (preg_match("#(?=.*Feature)(?=.*Case ?Id)(?=.*Test ?Case)(?=.*Pass)(?=.*Fail)(?=.*N/A)(?=.*Comment)#i", $first_line))
			{
				// Return at the beginning of file
				fseek($csv_report_file,0);

				// Determine order between different fields on the first line
				$idx = 0;
				$ref_tab = array();
				foreach (fgetcsv($csv_report_file, 0, $delimiter) as $element)
				{
					if (preg_match("#^ ?Feature.?$#i",$element))
						$ref_tab[0] = $idx;
					elseif (preg_match("#^ ?Case ?Id.?$#i",$element))
						$ref_tab[1] = $idx;
					elseif (preg_match("#^ ?Test ?Case.?$#i",$element))
						$ref_tab[2] = $idx;
					elseif (preg_match("#^ ?Pass.?$#i",$element))
						$ref_tab[3] = $idx;
					elseif (preg_match("#^ ?Fail.?$#i",$element))
						$ref_tab[4] = $idx;
					elseif (preg_match("#^ ?N/?A.?$#i",$element))
						$ref_tab[5] = $idx;
					elseif (preg_match("#^ ?Measured.?$#i",$element))
						$ref_tab[6] = $idx;
					elseif (preg_match("#^ ?Comment.?$#i",$element))
						$ref_tab[7] = $idx;
					elseif (preg_match("#^ ?Measurement ?Name.?$#i",$element))
						$ref_tab[8] = $idx;
					elseif (preg_match("#^ ?Value.?$#i",$element))
						$ref_tab[9] = $idx;
					elseif (preg_match("#^ ?Unit.?$#i",$element))
						$ref_tab[10] = $idx;
					elseif (preg_match("#^ ?Target.?$#i",$element))
						$ref_tab[11] = $idx;
					elseif (preg_match("#^ ?Failure.?$#i",$element))
						$ref_tab[12] = $idx;
					elseif (preg_match("#^ ?Duration.?$#i",$element))
						$ref_tab[13] = $idx;
					elseif (preg_match("#^ ?Bug.?$#i",$element))
						$ref_tab[14] = $idx;
					$idx++;
				}

				// Go through file and fill datas
				$data_tab = array();
				while ($data_tab = fgetcsv($csv_report_file, 0, $delimiter))
				{
					if (count($data_tab) > 6)
					{
						// Check if feature is not empty
						if (empty($data_tab[$ref_tab[0]])) return 2401;
						else $feature = $data_tab[$ref_tab[0]];

						// Check if case_id is not empty
						if (empty($data_tab[$ref_tab[1]])) return 2402;
						else $case_id = $data_tab[$ref_tab[1]];

						if (!(($data_tab[$ref_tab[3]] == "1") OR ($data_tab[$ref_tab[4]] == "1") OR ($data_tab[$ref_tab[5]] == "1") OR ($data_tab[$ref_tab[6]] == "1")))
							return 2403;

						$test_case = (empty($data_tab[$ref_tab[2]])) ? " " : $data_tab[$ref_tab[2]];
						$pass = (empty($data_tab[$ref_tab[3]])) ? "" : $data_tab[$ref_tab[3]];
						$fail = (empty($data_tab[$ref_tab[4]])) ? "" : $data_tab[$ref_tab[4]];
						$block = (empty($data_tab[$ref_tab[5]])) ? "" : $data_tab[$ref_tab[5]];
						$deferred = (empty($data_tab[$ref_tab[15]])) ? "" : $data_tab[$ref_tab[15]];
						$notrun = (empty($data_tab[$ref_tab[16]])) ? "" : $data_tab[$ref_tab[16]];
						$measured = (empty($data_tab[$ref_tab[6]])) ? "" : $data_tab[$ref_tab[6]];
						$comment = (empty($data_tab[$ref_tab[7]])) ? " " : $data_tab[$ref_tab[7]];
						$measurement_name = (empty($data_tab[$ref_tab[8]])) ? "" : $data_tab[$ref_tab[8]];
						$value = (empty($data_tab[$ref_tab[9]])) ? "" : preg_replace('#(,)#','.',$data_tab[$ref_tab[9]]);
						$unit = (empty($data_tab[$ref_tab[10]])) ? " " : $data_tab[$ref_tab[10]];
						$target = (empty($data_tab[$ref_tab[11]])) ? "" : preg_replace('#(,)#','.',$data_tab[$ref_tab[11]]);
						$failure = (empty($data_tab[$ref_tab[12]])) ? "" : preg_replace('#(,)#','.',$data_tab[$ref_tab[12]]);
						$executionTime = (empty($data_tab[$ref_tab[13]])) ? 0 : $data_tab[$ref_tab[13]];
						$bugs = (empty($data_tab[$ref_tab[14]])) ? "" : $data_tab[$ref_tab[14]];

						preg_replace('#(,)#','.',$data_tab[$ref_tab[9]]);

						// Write datas into qa_generic database
						if ($pass == "1")
							$decision_criteria_id = -1;
						elseif ($fail == "1")
							$decision_criteria_id = -2;
						elseif ($block == "1")
							$decision_criteria_id = -3;
						elseif ($deferred == "1")
							$decision_criteria_id = -4;
						elseif ($notrun == "1")
							$decision_criteria_id = -5;
						elseif ($measured == "1")
						{
							if (empty($failure))
							{
								if ($value < $target) $decision_criteria_id = -2;
								else $decision_criteria_id = -1;
							}
							else
							{
								if ($failure > $target)
								{
									if ($value < $failure) $decision_criteria_id = -1;
									else $decision_criteria_id = -2;
								}
								elseif ($failure < $target)
								{
									if ($value > $failure) $decision_criteria_id = -1;
									else $decision_criteria_id = -2;
								}
								else
								{
									if ($value < $target) $decision_criteria_id = -2;
									else $decision_criteria_id = -1;
								}
							}
						}

						// Status hard coded
						$status = 0;

						if ($merge_flag)
						{
							// Retrieve test result id relying on test name and feature label, if it exists
							$query = "SELECT tr.id tr_id
									FROM ".$qa_generic.".test_result tr
										JOIN ".$qa_generic.".table_name tn ON tn.name = 'test_result'
										JOIN ".$qa_generic.".complementary_tool_relation ctr ON ctr.table_name_id = tn.id
											AND ctr.table_entry_id = tr.id
											AND ctr.label = '".addslashes($feature)."'
									WHERE tr.test_session_id = ".$test_session_id."
										AND tr.name = '".addslashes($case_id)."'";
							$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
							if (!empty($result))
							{
								$test_result_exists_flag = true;
								$testResultId = $result['tr_id'];
							}
						}

						// Write into test_result table
						if ($test_result_exists_flag)
						{
							$measures = Doctrine_Query::create()
								->select('*')
								->from('Measure')
								->where('test_result_id = ?', $testResultId)
								->execute();

							foreach ($measures as $measure)
							{
								$measure->delete($conn);
							}

							$testResult = Doctrine_Core::getTable("TestResult")->findOneById($testResultId);
						}
						else
						{
							$testResult = new TestResult();
							$testResult->setName(stripslashes($case_id));
							$testResult->setTestSessionId($test_session_id);
						}

						$testResult->setDecisionCriteriaId($decision_criteria_id);
						$testResult->setComplement(stripslashes($test_case));
						$testResult->setComment(stripslashes($comment));
						$testResult->setStatus($status);
						$testResult->setExecutionTime($executionTime);
						$testResult->setBugs(stripslashes($bugs));
						$testResult->save($conn);

						// Retrieve test_result id created
						$test_result_id = $testResult->getId();

						// Category = Feature = 1
						$category = 1;

						// Write into complementary_tool_relation table
						if (!$test_result_exists_flag)
						{
							$complementaryToolRelation = new ComplementaryToolRelation();
							$complementaryToolRelation->setLabel(stripslashes($feature));
							$complementaryToolRelation->setTableNameId($table_name_test_result_id);
							$complementaryToolRelation->setTableEntryId($test_result_id);
							$complementaryToolRelation->setCategory($category);
							$complementaryToolRelation->save($conn);
						}

						// Write into measure table
						if (!empty($measurement_name) AND !empty($value))
						{
							$measureObject = new Measure();
							$measureObject->setTestResultId($test_result_id);
							$measureObject->setValue($value);
							$measureObject->setUnit($unit);
							$measureObject->setDescription($measurement_name);
							$measureObject->setCategory(1);
							$measureObject->setOperator(1);
							$measureObject->save($conn);

							if (!empty($target))
							{
								$measureObject = new Measure();
								$measureObject->setTestResultId($test_result_id);
								$measureObject->setValue($target);
								$measureObject->setUnit($unit);
								$measureObject->setDescription($measurement_name);
								$measureObject->setCategory(2);
								$measureObject->setOperator(1);
								$measureObject->save($conn);
							}

							if (!empty($failure))
							{
								$measureObject = new Measure();
								$measureObject->setTestResultId($test_result_id);
								$measureObject->setValue($failure);
								$measureObject->setUnit($unit);
								$measureObject->setDescription($measurement_name);
								$measureObject->setCategory(3);
								$measureObject->setOperator(1);
								$measureObject->save($conn);
							}
						}
					}
					$data_tab = array();
					$test_result_exists_flag = false;
				}
			}

			/*
			 *	==> format_3_0_1_for_ET.csv <==
			 *	Component,Name,Status,Feature,Description,Comment,Bug,Measurement Name,Value,Unit,Target,Failure
			 *	My feature name 1,TestCase name 1,failed,Feature info not displayed,Description1,Comment1,[[JRA-1(internal)]],Length,12,ms,10,13
			 *	My feature name 2,TestCase name 2,Pass,Feature info not displayed,Test steps or comments,Note on Passing test,[[BUG-1]],,,,,
			 *	My feature name 2,TestCase name 3,Fail,Feature info not displayed,Test steps or comments,Note on Failing test,[[BUG-1]],,,,,
			 *	My feature name 2,TestCase name 4,Block,Feature info not displayed,Test steps or comments,Note on Blocking test,[[BUG-1]],,,,,
			 *
			 *	==> format_3_0_2_for_ET.csv <==
			 *	Name,Status,Feature,Description,Comment,Bug,Package,Measurement Name,Value,Unit,Target,Failure
			 *	TestCase name 1,failed,Feature info not displayed,Description1,Comment1,[[JRA-1(internal)]],Package1/Package2/Package3/Feature Name 1,Length,12,ms,10,13
			 *	TestCase name 2,Pass,Feature info not displayed,Test steps or comments,Note on Passing test,[[BUG-1]],Package1/Package2/Feature Name 2,,,,,
			 *	TestCase name 3,Fail,Feature info not displayed,Test steps or comments,Note on Failing test,[[BUG-1]],Package1/Package2/Feature Name 2,,,,,
			 *	TestCase name 4,Block,Feature info not displayed,Test steps or comments,Note on Blocking test,[[BUG-1]],Package1/Package2/Feature Name 2,,,,,
			 *
			 *	==> format_3_0_3_for_ET.csv <==
			 *	Component,Name,Status,Feature,Description,Comment,Bug,Package,Measurement Name,Value,Unit,Target,Failure
			 *	My feature name 1,TestCase name 1,failed,Feature info not displayed,Description1,Comment1,[[JRA-1(internal)]],Package info not displayed,Length,12,ms,10,13
			 *	My feature name 2,TestCase name 2,Pass,Feature info not displayed,Test steps or comments,Note on Passing test,[[BUG-1]],Package info not displayed,,,,,
			 *	My feature name 2,TestCase name 3,Fail,Feature info not displayed,Test steps or comments,Note on Failing test,[[BUG-1]],Package info not displayed,,,,,
			 *	My feature name 2,TestCase name 4,Block,Feature info not displayed,Test steps or comments,Note on Blocking test,[[BUG-1]],Package info not displayed,,,,,
			 */
			elseif (preg_match("#(?=.*Name)(?=.*\b ?Status ?\b)#i", $first_line))
			{
				// Return at the beginning of file
				fseek($csv_report_file,0);

				// Determine order between different fields on the first line
				$idx = 0;
				$ref_tab = array();
				$entityTypeFound = false;
				foreach (fgetcsv($csv_report_file, 0, $delimiter) as $element)
				{
					if (preg_match("#^ ?Component.?$#i",$element))
						$ref_tab[0] = $idx;
					elseif (preg_match("#^ ?Name.?$#i",$element))
						$ref_tab[1] = $idx;
					elseif (preg_match("#^ ?Status.?$#i",$element))
						$ref_tab[2] = $idx;
					elseif (preg_match("#^ ?Feature.?$#i",$element))
						$ref_tab[3] = $idx;
					elseif (preg_match("#^ ?Description.?$#i",$element))
						$ref_tab[4] = $idx;
					elseif (preg_match("#^ ?Comment.?$#i",$element))
						$ref_tab[5] = $idx;
					elseif (preg_match("#^ ?Bug.?$#i",$element))
						$ref_tab[6] = $idx;
					elseif (preg_match("#^ ?Package.?$#i",$element))
						$ref_tab[7] = $idx;
					elseif (preg_match("#^ ?Measurement ?Name.?$#i",$element))
						$ref_tab[8] = $idx;
					elseif (preg_match("#^ ?Value.?$#i",$element))
						$ref_tab[9] = $idx;
					elseif (preg_match("#^ ?Unit.?$#i",$element))
						$ref_tab[10] = $idx;
					elseif (preg_match("#^ ?Target.?$#i",$element))
						$ref_tab[11] = $idx;
					elseif (preg_match("#^ ?Failure.?$#i",$element))
						$ref_tab[12] = $idx;
					elseif (preg_match("#^ ?StepActualResult.?$#i",$element))
						$ref_tab[13] = $idx;
					elseif (preg_match("# ?EntityType.?#i",$element))
					{
						$ref_tab[14] = $idx;
						$entityTypeFound = true;
					}
					elseif (preg_match("#^ ?Duration.?$#i",$element))
						$ref_tab[15] = $idx;
					elseif (preg_match("#^ ?StepNotes.?$#i",$element))
						$ref_tab[16] = $idx;
					$idx++;
				}

				if (!isset($ref_tab[1]))
					return 2501;

				if (!isset($ref_tab[2]))
					return 2502;

				if (!isset($ref_tab[0]) && !isset($ref_tab[7]))
					return 2509;

				// Go through file and fill datas
				$data_tab = array();
				while ($data_tab = fgetcsv($csv_report_file, 0, $delimiter))
				{
					if (count($data_tab) > 2)
					{
						// If 'EntityType' label is found, pass over 'StepRunResult' lines
						if ($entityTypeFound)
						{
							if (empty($data_tab[$ref_tab[14]]))
								return 2503;
							elseif (!preg_match("#^ ?TestScriptAssignment.?$#i",$data_tab[$ref_tab[14]]))
								continue;
						}

						// Check if feature is not empty
						if (empty($data_tab[$ref_tab[0]]) AND empty($data_tab[$ref_tab[7]]))
						{
							return 2504;
						}
						else
						{
							if (!empty($data_tab[$ref_tab[0]]))
								$feature = $data_tab[$ref_tab[0]];
							else
								$feature = $data_tab[$ref_tab[7]];
						}

						// If 'EntityType' label is found, keep last two string (delimited by '|') in the feature label
						if ($entityTypeFound)
						{
							$feat_delimiter = '|';
							$feature_tab = explode($feat_delimiter,$feature);
							$tab_length = count($feature_tab);
							$feature = $feature_tab[$tab_length-2].$feat_delimiter.$feature_tab[$tab_length-1];
						}

						// Check if case_id is not empty
						if (empty($data_tab[$ref_tab[1]]))
							return 2506;
						else
							$case_id = $data_tab[$ref_tab[1]];

						// Check if status is not empty
						if (empty($data_tab[$ref_tab[2]]))
							return 2507;
						else
							$status = $data_tab[$ref_tab[2]];

						$test_case = (empty($data_tab[$ref_tab[4]])) ? " " : $data_tab[$ref_tab[4]];
						$comment = (empty($data_tab[$ref_tab[5]])) ? "" : $data_tab[$ref_tab[5]];
						// $bug = (empty($data_tab[$ref_tab[6]])) ? "" : $data_tab[$ref_tab[6]];
						$step_actual_result = (empty($data_tab[$ref_tab[13]])) ? "" : $data_tab[$ref_tab[13]];
						$step_notes = (empty($data_tab[$ref_tab[16]])) ? "" : $data_tab[$ref_tab[16]];
						$measurement_name = (empty($data_tab[$ref_tab[8]])) ? "" : $data_tab[$ref_tab[8]];
						$value = (empty($data_tab[$ref_tab[9]])) ? "" : preg_replace('#(,)#','.',$data_tab[$ref_tab[9]]);
						$unit = (empty($data_tab[$ref_tab[10]])) ? " " : $data_tab[$ref_tab[10]];
						$target = (empty($data_tab[$ref_tab[11]])) ? "" : preg_replace('#(,)#','.',$data_tab[$ref_tab[11]]);
						$failure = (empty($data_tab[$ref_tab[12]])) ? "" : preg_replace('#(,)#','.',$data_tab[$ref_tab[12]]);
						$executionTime = (empty($data_tab[$ref_tab[15]])) ? 0 : $data_tab[$ref_tab[12]];

						// Concatenate notes
						if (empty($comment))
						{
							if (empty($step_notes)) $notes = "";
							else $notes = $step_notes;
						}
						else
						{	if (empty($step_notes)) $notes = $comment;
							else $notes = $comment." ".$step_notes;
						}

						// Check if step_actual_result is not empty
						if (empty($data_tab[$ref_tab[13]]))
							$bugs = "";
						else
							$bugs = $data_tab[$ref_tab[13]];

						// Write datas into qa_generic database
						if (preg_match("#^ ?pass(ed)? ?$#i",$status))
							$decision_criteria_id = -1;
						elseif (preg_match("#^ ?fail(ed)? ?$#i",$status))
							$decision_criteria_id = -2;
						elseif (preg_match("#^ ?block(ed)? ?$#i",$status))
							$decision_criteria_id = -3;
						elseif (preg_match("#^ ?defer(red)? ?$#i",$status))
							$decision_criteria_id = -4;
						elseif (preg_match("#^ ?not_run ?$#i",$status) || preg_match("#^ ?not ?run ?$#i",$status))
							$decision_criteria_id = -5;
						elseif (preg_match("#^ ?measured ?$#i",$status))
						{
							if (empty($failure))
							{
								if ($value < $target) $decision_criteria_id = -2;
								else $decision_criteria_id = -1;
							}
							else
							{
								if ($failure > $target)
								{
									if ($value < $failure) $decision_criteria_id = -1;
									else $decision_criteria_id = -2;
								}
								elseif ($failure < $target)
								{
									if ($value > $failure) $decision_criteria_id = -1;
									else $decision_criteria_id = -2;
								}
								else
								{
									if ($value < $target) $decision_criteria_id = -2;
									else $decision_criteria_id = -1;
								}
							}
						}
						else if (preg_match("#^ ?in ?progress ?$#i",$status))
							$decision_criteria_id = -5;
						else
							return 2508;

						// Status hard coded
						$resultStatus = 0;

						if ($merge_flag)
						{
							// Retrieve test result id relying on test name and feature label, if it exists
							$query = "SELECT tr.id tr_id
									FROM ".$qa_generic.".test_result tr
										JOIN ".$qa_generic.".table_name tn ON tn.name = 'test_result'
										JOIN ".$qa_generic.".complementary_tool_relation ctr ON ctr.table_name_id = tn.id
											AND ctr.table_entry_id = tr.id
											AND ctr.label = '".addslashes($feature)."'
									WHERE tr.test_session_id = ".$test_session_id."
										AND tr.name = '".addslashes($case_id)."'";
							$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
							if (!empty($result))
							{
								$test_result_exists_flag = true;
								$testResultId = $result['tr_id'];
							}
						}

						// Write into test_result table
						if ($test_result_exists_flag)
						{
							$measures = Doctrine_Query::create()
								->select('*')
								->from('Measure')
								->where('test_result_id = ?', $testResultId)
								->execute();

							foreach ($measures as $measure)
							{
								$measure->delete($conn);
							}

							$testResult = Doctrine_Core::getTable("TestResult")->findOneById($testResultId);
						}
						else
						{
							$testResult = new TestResult();
							$testResult->setName(stripslashes($case_id));
							$testResult->setTestSessionId($test_session_id);
						}

						$testResult->setDecisionCriteriaId($decision_criteria_id);
						$testResult->setComplement(stripslashes($test_case));
						$testResult->setStatus($resultStatus);
						$testResult->setExecutionTime($executionTime);
						$testResult->setBugs(stripslashes($bugs));
						$testResult->setComment($notes);
						$testResult->save($conn);

						// Retrieve test_result id created
						$test_result_id = $testResult->getId();

						// Category = Feature = 1
						$category = 1;

						// Write into complementary_tool_relation table
						if (!$test_result_exists_flag)
						{
							$complementaryToolRelation = new ComplementaryToolRelation();
							$complementaryToolRelation->setLabel(stripslashes($feature));
							$complementaryToolRelation->setTableNameId($table_name_test_result_id);
							$complementaryToolRelation->setTableEntryId($test_result_id);
							$complementaryToolRelation->setCategory($category);
							$complementaryToolRelation->save($conn);
						}

						// Write into measure table
						if (!empty($measurement_name) AND !empty($value))
						{
							$measureObject = new Measure();
							$measureObject->setTestResultId($test_result_id);
							$measureObject->setValue($value);
							$measureObject->setUnit($unit);
							$measureObject->setDescription($measurement_name);
							$measureObject->setCategory(1);
							$measureObject->setOperator(1);
							$measureObject->save($conn);

							if (!empty($target))
							{
								$measureObject = new Measure();
								$measureObject->setTestResultId($test_result_id);
								$measureObject->setValue($target);
								$measureObject->setUnit($unit);
								$measureObject->setDescription($measurement_name);
								$measureObject->setCategory(2);
								$measureObject->setOperator(1);
								$measureObject->save($conn);
							}

							if (!empty($failure))
							{
								$measureObject = new Measure();
								$measureObject->setTestResultId($test_result_id);
								$measureObject->setValue($failure);
								$measureObject->setUnit($unit);
								$measureObject->setDescription($measurement_name);
								$measureObject->setCategory(3);
								$measureObject->setOperator(1);
								$measureObject->save($conn);
							}
						}
					}
					$data_tab = array();
					$test_result_exists_flag = false;
				}
			}
			else
			{
				// Close the file
				fclose($csv_report_file);
				return 2000;
			}

			// Close the file
			fclose($csv_report_file);

			return 0;
		}
		else
			return 10;
	}

	/**
	 * Export as CSV file.
	 *
	 * @param test_session_id The session's id.
	 */
	static public function exportAsCsv($test_session_id)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");

		$filename = "Detailed_test_results.csv";

		header('Content-type: text/csv');
		header('Content-disposition: attachment; filename="'.$filename.'"');

		// Open export_file.csv
		$csv_export_file = @fopen("php://output",'w');
		if($csv_export_file)
		{
			// Fill export_file.csv
			fputs($csv_export_file, "Feature,Case Id,Test Case,Pass,Fail,N/A,Measured,Deferred,Not Run,Comment,Measurement Name,Value,Unit,Target,Failure,Duration,Bug\n");

			// Retrieve build_id from test_session table
			$testSessionObject = Doctrine_Core::getTable("TestSession")->findOneById($test_session_id);
			$testSessionBuildId = $testSessionObject->getBuildId();

			// Retrieve table_name_id from table_name table
			$query = "SELECT tn.id FROM ".$qa_generic.".table_name tn WHERE tn.name = 'test_result'";
			$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$table_name_id = $result['id'];

			// Retrieve datas from test_result table
			$query = "SELECT tr.id, tr.name, tr.complement, tr.decision_criteria_id, tr.status, tr.bugs, tr.comment, tr.execution_time, tr.started_at
					FROM ".$qa_generic.".test_result tr
							WHERE tr.test_session_id = ".$test_session_id;
			$testResults = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

			// For each element into test_result table
			foreach ($testResults as $testResult)
			{
				$case_id = addslashes($testResult['name']);
				$test_case = addslashes($testResult['complement']);
				$comment = addslashes($testResult['comment']);
				$test_result_id = ($testResult['id']);
				$duration = ($testResult['execution_time']);
				$bugs = addslashes($testResult['bugs']);
				$decision_criteria_id = ($testResult['decision_criteria_id']);

				// Retrieve label (feature) from complementary_tool_relation table
				$query = "SELECT ctr.label
						FROM ".$qa_generic.".complementary_tool_relation ctr
								WHERE ctr.table_name_id = ".$table_name_id."
										AND ctr.table_entry_id = ".$test_result_id;
				$complementaryToolRelation = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);

				$feature = (empty($complementaryToolRelation)) ? "" : addslashes($complementaryToolRelation['label']);

				// Retrieve datas from measure table
				$query = "SELECT meas.value, meas.unit, meas.description, meas.category
						FROM ".$qa_generic.".measure meas
								WHERE meas.test_result_id = ".$test_result_id;
				$measures = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

				$value = "";
				$target = "";
				$failure = "";
				$unit = "";
				$measurement_name = "";

				foreach ($measures as $measure)
				{
					if ($measure['category'] == 1)
						$value = addslashes($measure['value']);
					else if ($measure['category'] == 2)
						$target = addslashes($measure['value']);
					else if ($measure['category'] == 3)
						$failure = addslashes($measure['value']);
					else
						die ("Category error into Measure table");

					$unit = addslashes($measure['unit']);
					$measurement_name = addslashes($measure['description']);
				}

				if($decision_criteria_id == -1)
					fputs($csv_export_file, $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",1,,,,,,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . ","  . $duration . "," . $bugs . "\n" );
				else if($decision_criteria_id == -2)
					fputs($csv_export_file, $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",,1,,,,,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . "," . $duration . "," . $bugs . "\n" );
				else if($decision_criteria_id == -3)
					fputs($csv_export_file, $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",,,1,,,,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . "," . $duration . "," . $bugs . "\n" );
				else if($decision_criteria_id == -4)
					fputs($csv_export_file, $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",,,,,1,,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . "," . $duration . "," . $bugs . "\n" );
				else if($decision_criteria_id == -5)
					fputs($csv_export_file, $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",,,,,,1,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . "," . $duration . "," . $bugs . "\n" );
				else
					fputs($csv_export_file, $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",,,,1,,,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . "," . $duration . "," . $bugs . "\n" );
			}

			fclose($csv_export_file);
		}
		// File is done so make sure nothing else will be sent
		exit;
	}

	/**
	 * Export a test sessions as CSV file.
	 *
	 * @param test_sessions_id The session's id.
	 */
	static public function exportTestSessionsAsCsv($test_sessions_id)
	{
		$qa_generic = sfConfig::get("app_table_qa_generic");
		$qa_core = sfConfig::get("app_table_qa_core");

		$filename = "Detailed_test_results.csv";

		header('Content-type: text/csv');
		header('Content-disposition: attachment; filename="'.$filename.'"');

		// Open export_file.csv
		$csv_export_file = @fopen("php://output",'w');
		if($csv_export_file)
		{
			// Fill export_file.csv
			fputs($csv_export_file, "Test Execution Date,Release,Profile,Test Set,Hardware,Test Report Name,Build Id,Feature,Case Id,Test Case,Pass,Fail,N/A,Measured,Comment,Measurement Name,Value,Unit,Target,Failure,Author,Last Modified By,Duration,Bug\n");

			// Retrieve table_name_id from table_name table
			$query = "SELECT tn.id FROM ".$qa_generic.".table_name tn WHERE tn.name = 'test_result'";
			$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
			$table_name_id = $result['id'];

			$lastConfigurationId = -1;
			$lastBuildId = -1;
			foreach ($test_sessions_id as $test_session_id)
			{
				// Retrieve configuration id from test_session table
				$query = "SELECT ts.configuration_id, ts.build_id
						FROM ".$qa_generic.".test_session ts
								WHERE ts.id = ".$test_session_id;
				$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
				$configurationId = $result['configuration_id'];
				$buildId = $result['build_id'];

				if ($configurationId != $lastConfigurationId || $buildId != $lastBuildId)
				{
					// Retrieve project name, product name, test environment name and image name
					$query = "SELECT ts.build_id test_session_build_id, ts.testset test_session_testset, ts.name test_session_name, ts.created_at test_session_created_at, i.name image_name, te.name test_environment_name, p.name project_name, pt.name product_name, (SELECT sfgu.first_name FROM ".$qa_core.".sf_guard_user sfgu WHERE sfgu.id = ts.user_id) AS author_first_name, (SELECT sfgu.last_name FROM ".$qa_core.".sf_guard_user sfgu WHERE sfgu.id = ts.user_id) AS author_last_name, (SELECT sfgu.first_name FROM ".$qa_core.".sf_guard_user sfgu WHERE sfgu.id = ts.editor_id) AS editor_first_name, (SELECT sfgu.last_name FROM ".$qa_core.".sf_guard_user sfgu WHERE sfgu.id = ts.editor_id) AS editor_last_name
							FROM ".$qa_generic.".test_session ts
								JOIN ".$qa_generic.".configuration c ON c.id = ts.configuration_id
								JOIN ".$qa_generic.".image i ON i.id = c.image_id
								JOIN ".$qa_generic.".test_environment te ON te.id = c.test_environment_id
								JOIN ".$qa_generic.".project_to_product ptp ON ptp.id = c.project_to_product_id
								JOIN ".$qa_generic.".project p ON p.id = ptp.project_id
								JOIN ".$qa_core.".product_type pt ON pt.id = ptp.product_id
							WHERE ts.id = ".$test_session_id;
					$configInfo = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);
					$projectName = $configInfo['project_name'];
					$productName = $configInfo['product_name'];
					$testEnvironmentName = $configInfo['test_environment_name'];
					$imageName = $configInfo['image_name'];
					$testSessionBuildId = $configInfo['test_session_build_id'];
					$testSessionTestset = $configInfo['test_session_testset'];
					$testSessionName = $configInfo['test_session_name'];
					$testSessionCreatedAt = $configInfo['test_session_created_at'];
					$authorName = $configInfo['author_first_name'] . " " . $configInfo['author_last_name'];
					$editorName = $configInfo['editor_first_name'] . " " . $configInfo['editor_last_name'];
				}
				$lastConfigurationId = $configurationId;
				$lastBuildId = $buildId;

				// Retrieve datas from test_result table
				$query = "SELECT tr.id, tr.name, tr.complement, tr.decision_criteria_id, tr.execution_time, tr.status, tr.bugs, tr.comment, tr.started_at
						FROM ".$qa_generic.".test_result tr
								WHERE tr.test_session_id = ".$test_session_id;
				$testResults = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

				// For each element into test_result table
				foreach ($testResults as $testResult)
				{
					// If test_result started_at date is not initialized, then it gets test_session created_at date
					$temp_execution_date = (($testResult['started_at'] == "0000-00-00 00:00:00") OR ($testResult['started_at'] == "1970-01-01 00:00:00")) ? $testSessionCreatedAt : $testResult['started_at'];

					$case_id = addslashes($testResult['name']);
					$test_case = addslashes($testResult['complement']);
					$comment = addslashes($testResult['comment']);
					$test_result_id = addslashes($testResult['id']);
					$decision_criteria_id = addslashes($testResult['decision_criteria_id']);
					$duration = addslashes($testResult['execution_time']);
					$bugs = addslashes($testResult['bugs']);
					$test_execution_date = addslashes($temp_execution_date);

					// Retrieve label (feature) from complementary_tool_relation table
					$query = "SELECT ctr.label
							FROM ".$qa_generic.".complementary_tool_relation ctr
									WHERE ctr.table_name_id = ".$table_name_id."
											AND ctr.table_entry_id = ".$test_result_id;
					$complementaryToolRelation = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);

					$feature = (empty($complementaryToolRelation)) ? "" : addslashes($complementaryToolRelation['label']);

					// Retrieve datas from measure table
					$query = "SELECT meas.value, meas.unit, meas.description, meas.category
							FROM ".$qa_generic.".measure meas
									WHERE meas.test_result_id = ".$test_result_id;
					$measures = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

					$value = "";
					$target = "";
					$failure = "";
					$unit = "";
					$measurement_name = "";

					foreach ($measures as $measure)
					{
						if ($measure['category'] == 1)
							$value = addslashes($measure['value']);
						else if ($measure['category'] == 2)
							$target = addslashes($measure['value']);
						else if ($measure['category'] == 3)
							$failure = addslashes($measure['value']);
						else
							die ("Category error into Measure table");

						$unit = addslashes($measure['unit']);
						$measurement_name = addslashes($measure['description']);
					}

					if($decision_criteria_id == -1)
						fputs($csv_export_file, $test_execution_date . "," . $projectName . "," . $productName . "," . $testSessionTestset . "," . $testEnvironmentName . "," . $testSessionName . "," . $testSessionBuildId . "," . $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",1,,,,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . "," . $authorName . "," . $editorName . "," . $duration . "," . $bugs . "\n" );
					else if($decision_criteria_id == -2)
						fputs($csv_export_file, $test_execution_date . "," . $projectName . "," . $productName . "," . $testSessionTestset . "," . $testEnvironmentName . "," . $testSessionName . "," . $testSessionBuildId . "," . $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",,1,,,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . "," . $authorName . "," . $editorName . "," . $duration . "," . $bugs . "\n" );
					else if($decision_criteria_id == -3)
						fputs($csv_export_file, $test_execution_date . "," . $projectName . "," . $productName . "," . $testSessionTestset . "," . $testEnvironmentName . "," . $testSessionName . "," . $testSessionBuildId . "," . $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",,,1,,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . "," . $authorName . "," . $editorName . "," . $duration . "," . $bugs . "\n" );
					else
						fputs($csv_export_file, $test_execution_date . "," . $projectName . "," . $productName . "," . $testSessionTestset . "," . $testEnvironmentName . "," . $testSessionName . "," . $testSessionBuildId . "," . $feature . ",\"" . $case_id . "\",\"" . $test_case . "\",,,,,\"" . $comment . "\"," . $measurement_name . "," . $value . "," . $unit . "," . $target . "," . $failure . "," . $authorName . "," . $editorName . "," . $duration . "," . $bugs . "\n" );
				}
			}
			fclose($csv_export_file);
		}
		// File is done so make sure nothing else will be sent
		exit;
	}

	/**
	 * Give the error message corresponding to an error code.
	 *
	 * @param error_code The error code.
	 */
	static public function getImportErrorMessage($error_code)
	{
		$xml_v1_text = "into XML v1";
		$xml_v2_text = "into XML v2";

		$csv_old_text = "into old CSV format";
		$csv_old_v1_text = "into old CSV v1";
		$csv_v1_text = "into CSV v1";
		$csv_v2_text = "into CSV v2";
		$csv_v3_text = "into CSV v3";

		$error_message = "";
		switch ($error_code)
		{
			case (10):
				$error_message = "Unable to open the report file";
				break;
			case (1000):
				$error_message = "Unknown XML format";
				break;
			case (1001):
				$error_message = "No test results found ".$xml_v1_text;
				break;
			case (1002):
				$error_message = "No test results found ".$xml_v2_text;
				break;
			case (1101):
				$error_message = "Test case name missing (case tag, name attribute) ".$xml_v1_text;
				break;
			case (1102):
				$error_message = "Test case feature missing (set tag, feature attribute) ".$xml_v1_text;
				break;
			case (1201):
				$error_message = "Test case name missing (testcase tag, id attribute) ".$xml_v2_text;
				break;
			case (1202):
				$error_message = "Test case feature missing (testcase tag, component attribute) ".$xml_v2_text;
				break;
			case (2000):
				$error_message = "Unknown CSV format";
				break;
			case (2001):
				$error_message = "Unknown CSV delimiter";
				break;
			case (2101):
				$error_message = "Feature field is empty ".$csv_old_text;
				break;
			case (2102):
				$error_message = "Check points field is empty ".$csv_old_text;
				break;
			case (2103):
				$error_message = "Status field is empty ".$csv_old_text;
				break;
			case (2104):
				$error_message = "Status field should be PASS or FAIL or BLOCK or NOT RUN ".$csv_old_text;
				break;
			case (2201):
				$error_message = "Feature field is empty ".$csv_old_v1_text;
				break;
			case (2202):
				$error_message = "Check points field is empty ".$csv_old_v1_text;
				break;
			case (2203):
				$error_message = "PASS or FAIL or N/A should be filled ".$csv_old_v1_text;
				break;
			case (2301):
				$error_message = "Feature field is empty ".$csv_v1_text;
				break;
			case (2302):
				$error_message = "Case Id field is empty ".$csv_v1_text;
				break;
			case (2303):
				$error_message = "PASS or FAIL or N/A should be filled ".$csv_v1_text;
				break;
			case (2401):
				$error_message = "Feature field is empty ".$csv_v2_text;
				break;
			case (2402):
				$error_message = "Case Id field is empty ".$csv_v2_text;
				break;
			case (2403):
				$error_message = "PASS or FAIL or N/A should be filled ".$csv_v2_text;
				break;
			case (2501):
				$error_message = "Name field missing ".$csv_v3_text;
				break;
			case (2502):
				$error_message = "Status field missing ".$csv_v3_text;
				break;
			case (2503):
				$error_message = "EntityType field is empty ".$csv_v3_text;
				break;
			case (2504):
				$error_message = "Component or Package field should be filled ".$csv_v3_text;
				break;
			case (2505):
				$error_message = "Unknown delimiter on EntityType field ".$csv_v3_text;
				break;
			case (2506):
				$error_message = "Case Id field is empty ".$csv_v3_text;
				break;
			case (2507):
				$error_message = "Status field is empty ".$csv_v3_text;
				break;
			case (2508):
				$error_message = "Status field should be PASS or FAIL or BLOCK or MEASURED or NOT RUN or IN PROGRESS ".$csv_v3_text;
				break;
			case (2509):
				$error_message = "Component or Package field missing ".$csv_v3_text;
				break;
		}
		return $error_message;
	}
}
