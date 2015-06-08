<?php

/**
 * Image filter form base class.
 *
 * @package    trc
 * @subpackage filter
 * @author     Julian Dumez <julianx.dumez@intel.com>
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseImageFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'description'  => new sfWidgetFormFilterInput(),
      'os'           => new sfWidgetFormFilterInput(),
      'distribution' => new sfWidgetFormFilterInput(),
      'version'      => new sfWidgetFormFilterInput(),
      'kernel'       => new sfWidgetFormFilterInput(),
      'architecture' => new sfWidgetFormFilterInput(),
      'other_fw'     => new sfWidgetFormFilterInput(),
      'binary_link'  => new sfWidgetFormFilterInput(),
      'source_link'  => new sfWidgetFormFilterInput(),
      'name_slug'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'name'         => new sfValidatorPass(array('required' => false)),
      'description'  => new sfValidatorPass(array('required' => false)),
      'os'           => new sfValidatorPass(array('required' => false)),
      'distribution' => new sfValidatorPass(array('required' => false)),
      'version'      => new sfValidatorPass(array('required' => false)),
      'kernel'       => new sfValidatorPass(array('required' => false)),
      'architecture' => new sfValidatorPass(array('required' => false)),
      'other_fw'     => new sfValidatorPass(array('required' => false)),
      'binary_link'  => new sfValidatorPass(array('required' => false)),
      'source_link'  => new sfValidatorPass(array('required' => false)),
      'name_slug'    => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('image_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Image';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'name'         => 'Text',
      'description'  => 'Text',
      'os'           => 'Text',
      'distribution' => 'Text',
      'version'      => 'Text',
      'kernel'       => 'Text',
      'architecture' => 'Text',
      'other_fw'     => 'Text',
      'binary_link'  => 'Text',
      'source_link'  => 'Text',
      'name_slug'    => 'Text',
    );
  }
}
