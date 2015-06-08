<?php

/**
 * Configuration filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseConfigurationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'image_id'              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Image'), 'add_empty' => true)),
      'test_environment_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TestEnvironment'), 'add_empty' => true)),
      'project_to_product_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectToProduct'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'image_id'              => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Image'), 'column' => 'id')),
      'test_environment_id'   => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('TestEnvironment'), 'column' => 'id')),
      'project_to_product_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ProjectToProduct'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('configuration_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Configuration';
  }

  public function getFields()
  {
    return array(
      'id'                    => 'Number',
      'image_id'              => 'ForeignKey',
      'test_environment_id'   => 'ForeignKey',
      'project_to_product_id' => 'ForeignKey',
    );
  }
}
