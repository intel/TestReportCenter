<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('SearchTag', 'qa_core');

/**
 * BaseSearchTag
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * 
 * @method integer   getId()   Returns the current record's "id" value
 * @method string    getName() Returns the current record's "name" value
 * @method SearchTag setId()   Sets the current record's "id" value
 * @method SearchTag setName() Sets the current record's "name" value
 * 
 * @package    trc
 * @subpackage model
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseSearchTag extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('search_tag');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             'length' => 4,
             ));
        $this->hasColumn('name', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 45,
             ));

        $this->option('charset', 'utf8');
        $this->option('type', 'InnoDB');
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}