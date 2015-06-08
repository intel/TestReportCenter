<?php

/**
 * Image form base class.
 *
 * @method Image getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseImageForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'name'         => new sfWidgetFormInputText(),
      'description'  => new sfWidgetFormInputText(),
      'os'           => new sfWidgetFormInputText(),
      'distribution' => new sfWidgetFormInputText(),
      'version'      => new sfWidgetFormInputText(),
      'kernel'       => new sfWidgetFormInputText(),
      'architecture' => new sfWidgetFormInputText(),
      'other_fw'     => new sfWidgetFormInputText(),
      'binary_link'  => new sfWidgetFormInputText(),
      'source_link'  => new sfWidgetFormInputText(),
      'name_slug'    => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'         => new sfValidatorString(array('max_length' => 255)),
      'description'  => new sfValidatorPass(array('required' => false)),
      'os'           => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'distribution' => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'version'      => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'kernel'       => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'architecture' => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'other_fw'     => new sfValidatorPass(array('required' => false)),
      'binary_link'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'source_link'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'name_slug'    => new sfValidatorString(array('max_length' => 255)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'Image', 'column' => array('name_slug')))
    );

    $this->widgetSchema->setNameFormat('image[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Image';
  }

}
