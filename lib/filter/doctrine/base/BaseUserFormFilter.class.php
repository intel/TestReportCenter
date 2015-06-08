<?php

/**
 * User filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseUserFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'firstname'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'lastname'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'email'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'created_at'         => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'password'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'password_salt'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'activation_key'     => new sfWidgetFormFilterInput(),
      'authentication_key' => new sfWidgetFormFilterInput(),
      'report_token'       => new sfWidgetFormFilterInput(),
      'active'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'security_level'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'firstname'          => new sfValidatorPass(array('required' => false)),
      'lastname'           => new sfValidatorPass(array('required' => false)),
      'email'              => new sfValidatorPass(array('required' => false)),
      'created_at'         => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'password'           => new sfValidatorPass(array('required' => false)),
      'password_salt'      => new sfValidatorPass(array('required' => false)),
      'activation_key'     => new sfValidatorPass(array('required' => false)),
      'authentication_key' => new sfValidatorPass(array('required' => false)),
      'report_token'       => new sfValidatorPass(array('required' => false)),
      'active'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'security_level'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('user_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'User';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'firstname'          => 'Text',
      'lastname'           => 'Text',
      'email'              => 'Text',
      'created_at'         => 'Date',
      'password'           => 'Text',
      'password_salt'      => 'Text',
      'activation_key'     => 'Text',
      'authentication_key' => 'Text',
      'report_token'       => 'Text',
      'active'             => 'Number',
      'security_level'     => 'Number',
    );
  }
}
