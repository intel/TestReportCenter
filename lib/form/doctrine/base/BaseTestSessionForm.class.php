<?php

/**
 * TestSession form base class.
 *
 * @method TestSession getObject() Returns the current form's model object
 *
 * @package    trc
 * @subpackage form
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseTestSessionForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'build_id'          => new sfWidgetFormInputText(),
      'testset'           => new sfWidgetFormInputText(),
      'name'              => new sfWidgetFormInputText(),
      'test_objective'    => new sfWidgetFormInputText(),
      'qa_summary'        => new sfWidgetFormInputText(),
      'user_id'           => new sfWidgetFormInputText(),
      'created_at'        => new sfWidgetFormDateTime(),
      'editor_id'         => new sfWidgetFormInputText(),
      'updated_at'        => new sfWidgetFormDateTime(),
      'project_release'   => new sfWidgetFormInputText(),
      'project_milestone' => new sfWidgetFormInputText(),
      'issue_summary'     => new sfWidgetFormInputText(),
      'status'            => new sfWidgetFormInputText(),
      'published'         => new sfWidgetFormInputText(),
      'configuration_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Configuration'), 'add_empty' => false)),
      'campaign_checksum' => new sfWidgetFormInputText(),
      'build_slug'        => new sfWidgetFormInputText(),
      'testset_slug'      => new sfWidgetFormInputText(),
      'notes'             => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'build_id'          => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'testset'           => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'name'              => new sfValidatorString(array('max_length' => 255)),
      'test_objective'    => new sfValidatorPass(array('required' => false)),
      'qa_summary'        => new sfValidatorPass(array('required' => false)),
      'user_id'           => new sfValidatorInteger(),
      'created_at'        => new sfValidatorDateTime(),
      'editor_id'         => new sfValidatorInteger(array('required' => false)),
      'updated_at'        => new sfValidatorDateTime(array('required' => false)),
      'project_release'   => new sfValidatorString(array('max_length' => 45, 'required' => false)),
      'project_milestone' => new sfValidatorInteger(array('required' => false)),
      'issue_summary'     => new sfValidatorPass(array('required' => false)),
      'status'            => new sfValidatorInteger(),
      'published'         => new sfValidatorInteger(array('required' => false)),
      'configuration_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Configuration'))),
      'campaign_checksum' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'build_slug'        => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'testset_slug'      => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'notes'             => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('test_session[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'TestSession';
  }

}
