<?php

/**
 * ImageTable
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ImageTable extends PluginImageTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object ImageTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Image');
    }

    /**
     * Check if given slug does not already exist in the database.
     *
     * @param string $slug The slug.
     * @param int $id The identifier of the object if it already exists in the database.
     *
     * @return boolean TRUE if the slug exists, FALSE otherwise.
     */
    public function checkSlug($slug, $id=null)
    {
		$qa_generic = sfConfig::get("app_table_qa_generic");

		$query = "SELECT i.id FROM ".$qa_generic.".image i WHERE i.name_slug = '".$slug."'";
		if(!is_null($id))
			$query .= " AND i.id != '".$id."'";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->rowCount();

		if($result > 0)
			return true;

		return false;
    }

    /**
     * Check if an image exist.
     *
     * @param array $array The values of the image to search.
     *
     * @return array An associative array with values of the image if it exist, NULL otherwise.
     */
    public function findByArray($array)
    {
		$qa_generic = sfConfig::get("app_table_qa_generic");

		$query = "SELECT i.id, i.name, i.description, i.os, i.distribution, i.version, i.kernel, i.architecture, i.other_fw, i.binary_link, i.source_link, i.name_slug
			FROM ".$qa_generic.".image i
			WHERE i.name = '".addslashes($array["name"])."'
				AND (i.description = '".addslashes($array["description"])."' OR i.description IS NULL)
				AND (i.os = '".addslashes($array["os"])."' OR i.os IS NULL)
				AND (i.distribution = '".addslashes($array["distribution"])."' OR i.distribution IS NULL)
				AND (i.version = '".addslashes($array["version"])."' OR i.version IS NULL)
				AND (i.kernel = '".addslashes($array["kernel"])."' OR i.kernel IS NULL)
				AND (i.architecture = '".addslashes($array["architecture"])."' OR i.architecture IS NULL)
				AND (i.other_fw = '".addslashes($array["other_fw"])."' OR i.other_fw IS NULL)
				AND (i.binary_link = '".addslashes($array["binary_link"])."' OR i.binary_link IS NULL)
				AND (i.source_link = '".addslashes($array["source_link"])."' OR i.source_link IS NULL)";
		$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);

		if(!empty($result))
			return $result;

		return null;
    }

    /**
     * Get the three last used images by default.
     *
     * @param number $limit The number of images to retrieve.
	 *
     * @return array An associative array with values of retrieved images, or NULL.
     */
    public function getLastImages($limit = 3)
    {
		$qa_generic = sfConfig::get("app_table_qa_generic");

		$query = "SELECT DISTINCT i.id, i.name, i.description, i.os, i.distribution, i.version, i.kernel, i.architecture, i.other_fw, i.binary_link, i.source_link, i.name_slug
			FROM ".$qa_generic.".image i
				JOIN ".$qa_generic.".configuration c ON c.image_id = i.id
				JOIN ".$qa_generic.".test_session ts ON ts.configuration_id = c.id
			WHERE ts.created_at <= '".date("Y-m-d H:i:s")."'
			GROUP BY i.id
			ORDER BY ts.created_at DESC
			LIMIT ".$limit;
    	$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetchAll(PDO::FETCH_ASSOC);

		return $result;
    }

	/**
	 * Get image identified by given slug.
	 *
	 * @param string $slug The slug.
	 *
     * @return array An associative array with values of the image, or NULL.
	 */
    public function getImageBySlug($slug)
    {
		$qa_generic = sfConfig::get("app_table_qa_generic");

    	$query = "SELECT i.id, i.name, i.description, i.os, i.distribution, i.version, i.kernel, i.architecture, i.other_fw, i.binary_link, i.source_link, i.name_slug
			FROM ".$qa_generic.".image i
			WHERE i.name_slug = '".$slug."'";
    	$result = Doctrine_Manager::getInstance()->getCurrentConnection()->execute($query)->fetch(PDO::FETCH_ASSOC);

		if(!empty($result))
			return $result;

		return null;
    }
}