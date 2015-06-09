<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ProjectToProduct', 'qa_generic');

/**
 * BaseProjectToProduct
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $project_group_id
 * @property integer $project_id
 * @property integer $product_id
 * @property Project $Project
 * @property Doctrine_Collection $ProjectToProducts
 * 
 * @method integer             getId()                Returns the current record's "id" value
 * @method integer             getProjectGroupId()    Returns the current record's "project_group_id" value
 * @method integer             getProjectId()         Returns the current record's "project_id" value
 * @method integer             getProductId()         Returns the current record's "product_id" value
 * @method Project             getProject()           Returns the current record's "Project" value
 * @method Doctrine_Collection getProjectToProducts() Returns the current record's "ProjectToProducts" collection
 * @method ProjectToProduct    setId()                Sets the current record's "id" value
 * @method ProjectToProduct    setProjectGroupId()    Sets the current record's "project_group_id" value
 * @method ProjectToProduct    setProjectId()         Sets the current record's "project_id" value
 * @method ProjectToProduct    setProductId()         Sets the current record's "product_id" value
 * @method ProjectToProduct    setProject()           Sets the current record's "Project" value
 * @method ProjectToProduct    setProjectToProducts() Sets the current record's "ProjectToProducts" collection
 * 
 * @package    trc
 * @subpackage model
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseProjectToProduct extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('project_to_product');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             'length' => 4,
             ));
        $this->hasColumn('project_group_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => 4,
             ));
        $this->hasColumn('project_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => 4,
             ));
        $this->hasColumn('product_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => 4,
             ));


        $this->index('fk_p2p_project', array(
             'fields' => 
             array(
              0 => 'project_id',
             ),
             ));
        $this->index('fk_p2p_product', array(
             'fields' => 
             array(
              0 => 'product_id',
             ),
             ));
        $this->index('fk_p2p_project_group', array(
             'fields' => 
             array(
              0 => 'project_group_id',
             ),
             ));
        $this->option('charset', 'utf8');
        $this->option('type', 'InnoDB');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Project', array(
             'local' => 'project_id',
             'foreign' => 'id',
             'onDelete' => 'no action',
             'onUpdate' => 'no action'));

        $this->hasMany('Configuration as ProjectToProducts', array(
             'local' => 'id',
             'foreign' => 'project_to_product_id'));
    }
}