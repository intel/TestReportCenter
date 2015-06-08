<?php

/**
 * Functions to convert integer values from database to their label or string values.
 */
class Labeler
{
	/**
	 * Return the label corresponding to a given decision criteria ID.
	 *
	 * @param int $decisionCriteriaId The decision criteria ID.
	 *
	 * @return string The label of the decision criteria.
	 */
	static function decisionToText($decisionCriteriaId)
	{
		switch($decisionCriteriaId)
		{
			case -1: return "pass";
			case -2: return "fail";
			case -3: return "block";
			case -4: return "deferred";
			case -5: return "not_run";
			default: return "undef";
		}
	}

	/**
	 * Get the label corresponding to the given user's security level.
	 *
	 * @param int $securityLevel The security level.
	 *
	 * @return string|boolean The label of the security level, FALSE if given security level is unknown.
	 */
	static function getSecurityLevelLabel($securityLevel)
	{
		switch($securityLevel)
		{
			case 0: return "Public";
			case 1: return "Confidential";
			case 2: return "Restricted Secret";
			case 3: return "Top secret";
			default: break;
		}

		return false;
	}

	/**
	 * Get the label corresponding to the given report's status.
	 *
	 * @param int $status The status of the report.
	 *
	 * @return string|boolean The label of the status, FALSE if status is unknown.
	 */
	static function getTestSessionStatusLabel($status)
	{
		switch($status)
		{
			case 0: return "Not started";
			case 1: return "In progress";
			case 2: return "Done";
			case 3: return "Stopped";
			case 4: return "Go";
			case 5: return "No go";
			default: break;
		}

		return false;
	}

	/**
	 * Get the label corresponding to the given result's status.
	 *
	 * @param int $status The status of the result.
	 *
	 * @return string|boolean The label of the status, FALSE if status is unknown.
	 */
	static function getTestResultStatusLabel($status)
	{
		switch($status)
		{
			case 0: return "Complete";
			case 1: return "In progress";
			case 2: return "Paused";
			case 3: return "Blocked";
			case 4: return "Incomplete";
			case 5: return "Error";
			default: break;
		}

		return false;
	}
}