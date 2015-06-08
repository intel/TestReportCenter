<?php

class BasesfApplyResetRequestForm extends BaseForm
{
  public function configure()
  {
    parent::configure();

    $this->setWidget('username_or_email',
      new sfWidgetFormInput(
        array(), array('maxlength' => 100)));

    $this->setValidator('username_or_email',
      new sfValidatorOr(
        array(
          new sfValidatorAnd(
            array(
              new sfValidatorString(array('required' => true,
                'trim' => true,
                'min_length' => 4,
                'max_length' => 16)),
              new sfValidatorDoctrineChoice(array('model' => 'sfGuardUser',
                'column' => 'username'), array("invalid" => "There is no such user.")))),
          new sfValidatorEmail(array('required' => true)))));
        
    $this->widgetSchema->setNameFormat('sfApplyResetRequest[%s]');
  }
}

