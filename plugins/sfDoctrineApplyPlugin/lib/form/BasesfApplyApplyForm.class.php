<?php

class BasesfApplyApplyForm extends sfGuardUserForm
{
  private $validate = null;

  public function getUseFields()
  {
    return array('username', 'password', 'first_name', 'last_name', 'email_address');
  }

  public function configure()
  {
    parent::configure();

    // To add your own fields, override sfApplyApplyForm with your own project-level version in which you 
    // return a more generous result for getUseFields. This approach prevents the problem
    // of unexpected fields appearing when sfGuardUser's list of relations grows in
    // places you're not thinking about consciously
    $this->useFields($this->getUseFields());
    
    // useFields doesn't touch hidden fields
    unset($this['id']);
    
    // We're making a new user or editing the user who is
    // logged in. In neither case is it appropriate for
    // the user to get to pick an existing userid. The user
    // also doesn't get to modify the validate field which
    // is part of how their account is verified by email.

    $this->setWidget('username', new sfWidgetFormInput(
      array(), array('maxlength' => 16)
    ));
    
    $this->setWidget('password', new sfWidgetFormInputPassword(
      array(), array('maxlength' => 128)
    ));
    
    $this->setWidget('password2', new sfWidgetFormInputPassword(
      array(), array('maxlength' => 128)
    ));
    
    $this->widgetSchema->moveField('password2', sfWidgetFormSchema::AFTER, 'password');
    
    $email = $this->getWidget('email_address');
    $class = get_class($email);
    $this->setWidget('email2', new $class(
      array(), array('maxlength' => $email->getAttribute('maxlength'))
    ));
    
    $this->widgetSchema->moveField('email2', sfWidgetFormSchema::AFTER, 'email_address');
    
    $this->widgetSchema->setLabels(array(
      'password2' => 'Confirm Password',
      'email2' => 'Confirm Email'
    ));

    $this->widgetSchema->setNameFormat('sfApplyApply[%s]');
    
    // We have the right to an opinion on these fields because we
    // implement at least part of their behavior. Validators for the
    // rest of the user object come from the schema and from the
    // developer's form subclass
    
    $this->setValidator('username',
      new sfValidatorAnd(array(
        new sfValidatorString(array(
          'required' => true,
          'trim' => true,
          'min_length' => 4,
          'max_length' => 16
        )),
        // Usernames should be safe to output without escaping and generally username-like.
        new sfValidatorRegex(array(
          'pattern' => '/^\w+$/'
        ), array('invalid' => 'Usernames must contain only letters, numbers and underscores.')),
        new sfValidatorDoctrineUnique(array(
          'model' => 'sfGuardUser',
          'column' => 'username'
        ), array('invalid' => 'There is already a user by that name. Choose another.'))
      ))
    );
    
    // Passwords are never printed - ever - except in the context of Symfony form validation which has built-in escaping.
    // So we don't need a regex here to limit what is allowed
    
    // Don't print passwords when complaining about inadequate length
    $this->setValidator('password', new sfValidatorString(array(
      'required' => true,
      'trim' => true,
      'min_length' => 6,
      'max_length' => 128
    ), array(
      'min_length' => 'That password is too short. It must contain a minimum of %min_length% characters.')));
        
    $this->setValidator('password2', new sfValidatorString(array(
      'required' => true,
      'trim' => true,
      'min_length' => 6,
      'max_length' => 128
    ), array(
      'min_length' => 'That password is too short. It must contain a minimum of %min_length% characters.')));

    // Be aware that sfValidatorEmail doesn't guarantee a string that is preescaped for HTML purposes.
    // If you choose to echo the user's email address somewhere, make sure you escape entities.
    // <, > and & are rare but not forbidden due to the "quoted string in the local part" form of email address
    // (read the RFC if you don't believe me...).
    
    $this->setValidator('email_address', new sfValidatorAnd(array(
      new sfValidatorEmail(array('required' => true, 'trim' => true)),
      new sfValidatorString(array('required' => true, 'max_length' => 80)),
      new sfValidatorDoctrineUnique(array(
        'model' => 'sfGuardUser',
        'column' => 'email_address'
      ), array('invalid' => 'An account with that email address already exists. If you have forgotten your password, click "cancel", then "Reset My Password."'))
    )));
    
    $this->setValidator('email2', new sfValidatorEmail(array(
      'required' => true,
      'trim' => true
    )));
    
    $schema = $this->validatorSchema;
    
    // Hey Fabien, adding more postvalidators is kinda verbose!
    $postValidator = $schema->getPostValidator();
    
    $postValidators = array(
      new sfValidatorSchemaCompare(
        'password',
        sfValidatorSchemaCompare::EQUAL,
        'password2',
        array(),
        array('invalid' => 'The passwords did not match.')
      ),
      new sfValidatorSchemaCompare(
        'email_address',
        sfValidatorSchemaCompare::EQUAL,
        'email2',
        array(),
        array('invalid' => 'The email addresses did not match.')
      )
    );
        
    $this->validatorSchema->setPostValidator(new sfValidatorAnd($postValidators));
  }
    
  public function setValidate($validate)
  {
    $this->validate = $validate;
  }
    
  public function updateObject($values = null)
  {
    if (is_null($values))
    {
      $values = $this->getValues();
    }
    $object = parent::updateObject($values);
    $object->setValidate($this->validate);
    $object->setIsActive(false);

    // Don't break subclasses!
    return $object;
  }
}

