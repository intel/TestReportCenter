<?php

/**
 * Configuration form base class.
 *
 * @method Configuration getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseConfigurationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                    => new sfWidgetFormInputHidden(),
      'image_id'              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Image'), 'add_empty' => false)),
      'test_environment_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('TestEnvironment'), 'add_empty' => false)),
      'project_to_product_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectToProduct'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'                    => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'image_id'              => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Image'))),
      'test_environment_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('TestEnvironment'))),
      'project_to_product_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectToProduct'))),
    ));

    $this->widgetSchema->setNameFormat('configuration[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Configuration';
  }

}
