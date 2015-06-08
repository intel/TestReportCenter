<?php

class BasesfApplySettingsForm extends sfGuardUserForm
{
  public function getUseFields()
  {
    // "Why not email address?" We just went to a lot of trouble to verify
    // their email address. If they change it, we don't have a verified
    // email address anymore. Also this can be used to casually steal a 
    // momentarily unmonitored account, something you can't do otherwise
    // in our setup
    return array('first_name', 'last_name');
  }
  
  public function configure()
  {
    parent::configure();

    $this->useFields($this->getUseFields());

    $this->widgetSchema->setNameFormat('sfApplySettings[%s]');
    $this->widgetSchema->setFormFormatterName('list');
    $this->widgetSchema->setLabels(array(
          'fullname' => 'Full Name'));
  }
}

