<?php

/**
 * ProjectToProduct form base class.
 *
 * @method ProjectToProduct getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseProjectToProductForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'project_group_id' => new sfWidgetFormInputText(),
      'project_id'       => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Project'), 'add_empty' => false)),
      'product_id'       => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'project_group_id' => new sfValidatorInteger(),
      'project_id'       => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Project'))),
      'product_id'       => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('project_to_product[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectToProduct';
  }

}
