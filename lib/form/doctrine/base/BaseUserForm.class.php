<?php

/**
 * User form base class.
 *
 * @method User getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseUserForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                 => new sfWidgetFormInputHidden(),
      'firstname'          => new sfWidgetFormInputText(),
      'lastname'           => new sfWidgetFormInputText(),
      'email'              => new sfWidgetFormInputText(),
      'created_at'         => new sfWidgetFormDateTime(),
      'password'           => new sfWidgetFormInputText(),
      'password_salt'      => new sfWidgetFormInputText(),
      'activation_key'     => new sfWidgetFormInputText(),
      'authentication_key' => new sfWidgetFormInputText(),
      'report_token'       => new sfWidgetFormInputText(),
      'active'             => new sfWidgetFormInputText(),
      'security_level'     => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'firstname'          => new sfValidatorString(array('max_length' => 128)),
      'lastname'           => new sfValidatorString(array('max_length' => 128)),
      'email'              => new sfValidatorString(array('max_length' => 255)),
      'created_at'         => new sfValidatorDateTime(),
      'password'           => new sfValidatorString(array('max_length' => 128)),
      'password_salt'      => new sfValidatorString(array('max_length' => 128)),
      'activation_key'     => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'authentication_key' => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'report_token'       => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'active'             => new sfValidatorInteger(),
      'security_level'     => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('user[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'User';
  }

}
