<?php

/**
 * sfApply actions.
 *
 * @package    5seven5
 * @subpackage sfApply
 * @author     Tom Boutell, tom@punkave.com
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class BasesfApplyActions extends sfActions
{
  public function executeApply(sfRequest $request)
  {
    $this->form = $this->newForm('sfApplyApplyForm');
    if ($request->isMethod('post'))
    {
      $parameter = $request->getParameter('sfApplyApply');
      $this->form->bind($request->getParameter('sfApplyApply'));
      if ($this->form->isValid())
      {
        $guid = "n" . self::createGuid();
        $this->form->setValidate($guid);
        $this->form->save();
        try
        {
          $user = $this->form->getObject();
          $this->sendVerificationMail($user);
          return 'After';
        }
        catch (Exception $e)
        {
          $user = $this->form->getObject();
          $user->delete();
          throw $e;
            // You could re-throw $e here if you want to 
          // make it available for debugging purposes
          return 'MailerError';
        }
      }
    }
  }

  /**
   * Accepts proof of identity from the client side Facebook SDK.
   * https://developers.facebook.com/docs/howtos/login/signed-request/#step2
   * This will not work if your site doesn't have a proper
   * domain name (it will not work in dev, in most cases).
   */

  public function executeFacebookLogin(sfWebRequest $request)
  {
    $fb = sfConfig::get('app_sfApplyPlugin_facebook');
    $secret = isset($fb['secret']) ? $fb['secret'] : null;
    if (!$secret) 
    {
      throw new sfException('app_sfApplyPlugin_facebook not configured, secret missing');
    }
    $signed_request = $request->getParameter('signed_request');
    list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

    // decode the data
    $sig = $this->base64UrlDecode($encoded_sig);
    $data = json_decode($this->base64UrlDecode($payload), true);

    // Contrary to FB docs we're not done yet, we have to
    // trade the 'code' in for an access token and then we
    // can query for information about the user
    $code = $data['code'];
    $url = "https://graph.facebook.com/oauth/access_token?" . http_build_query(array('client_id' => $fb['id'], 'redirect_uri' => '', 'client_secret' => $secret, 'code' => $code));
    $accessToken = file_get_contents($url);
    parse_str($accessToken, $result);
    $accessToken = $result['access_token'];
    $me = json_decode(file_get_contents("https://graph.facebook.com/me?" . http_build_query(array('access_token' => $accessToken))), true);

    if (!isset($me['email']))
    {
      $this->forward404();
    }
    $email = $me['email'];
    $first_name = $me['first_name'];
    $last_name = $me['last_name'];
    $username = 'fb_' . (isset($me['username']) ? $me['username'] : $me['id']);

    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') 
    {
      $this->forward404();
    }

    // Adding the verification of the signed_request below
    $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
    if ($sig !== $expected_sig) 
    {
      $this->forward404();
    }

    $user = Doctrine::getTable('sfGuardUser')->findOneByEmailAddress($email);
    if (!$user) 
    {
      $user = new sfGuardUser();
      $user->setIsActive(true);
      $user->setPassword(aGuid::generate());
      $user->setEmailAddress($email);
      $user->setUsername($username);
    }
    $user->setFirstName($firstName);
    $user->setLastName($lastName);
    $user->setEmailAddress($email);
    $user->save();
    $this->getUser()->signIn($user);
    return $this->renderText('OK'); 
  }

  protected function base64UrlDecode($s)
  {
    return base64_decode(strtr($s, '-_', '+/'));
  }

  // Don't want SwiftMailer? Override me
  protected function mail($options)
  {
    $required = array('subject', 'parameters', 'email', 'fullname', 'html', 'text');
    foreach ($required as $option)
    {
      if (!isset($options[$option]))
      {
        throw new sfException("Required option $option not supplied to sfApply::mail");
      }
    }
    // The new SwiftMailer API is sweet
    $message = $this->getMailer()->compose();
    $message->setSubject($options['subject']);
    $message->setTo(array($options['email'] => $options['fullname']));
    $address = $this->getFromAddress();
    $message->setFrom(array($address['email'] => $address['fullname']));
    $message->setBody($this->getPartial($options['html'], $options['parameters']), 'text/html');
    $message->addPart($this->getPartial($options['text'], $options['parameters']), 'text/plain');
    $this->getMailer()->send($message);
  }
  
  // apply uses this. Password reset also uses it in the case of a user who
  // was never verified to begin with
  
  protected function sendVerificationMail($user)
  {
    $this->mail(array('subject' => sfConfig::get('app_sfApplyPlugin_apply_subject',
        sfContext::getInstance()->getI18N()->__("Please verify your account on %1%", array('%1%' => $this->getRequest()->getHost()))),
      'fullname' => $this->getFullName($user),
      'email' => $user->getEmailAddress(),
      'parameters' => array('fullname' => $this->getFullName($user), 'validate' => $user->getValidate()),
      'text' => 'sfApply/sendValidateNewText',
      'html' => 'sfApply/sendValidateNew'));
  }
  
  public function executeResetRequest(sfRequest $request)
  {
    $user = $this->getUser();
    if ($user->isAuthenticated())
    {
      $guardUser = $this->getUser()->getGuardUser();
      $this->forward404Unless($guardUser);
      return $this->resetRequestBody($guardUser);
    }
    else
    {
      $this->form = $this->newForm('sfApplyResetRequestForm');
      if ($request->isMethod('post'))
      {
        $this->form->bind($request->getParameter('sfApplyResetRequest'));
        if ($this->form->isValid())
        {
          // The form matches unverified users, but retrieveByUsername does not, so
          // use an explicit query. We'll special-case the unverified users in
          // resetRequestBody
          
          $username_or_email = $this->form->getValue('username_or_email');
          if (strpos($username_or_email, '@') !== false)
          {
            $user = Doctrine::getTable('sfGuardUser')->createQuery('u')->where('u.email_address = ?', $username_or_email)->fetchOne();
            
          }
          else
          {
            $user = Doctrine::getTable('sfGuardUser')->createQuery('u')->where('u.username = ?', $username_or_email)->fetchOne();
          }
          return $this->resetRequestBody($user);
        }
      }
    }
  }

  public function resetRequestBody($user)
  {
    if (!$user)
    {
      return 'NoSuchUser';
    }
    $this->forward404Unless($user);

    if (!$user->getIsActive())
    {
      $type = $this->getValidationType($user->getValidate());
      if ($type === 'New')
      {
        try 
        {
          $this->sendVerificationMail($user);
        }
        catch (Exception $e)
        {
          return 'UnverifiedMailerError';
        }
        return 'Unverified';
      }
      elseif ($type === 'Reset')
      {
        // They lost their first password reset email. That's OK. let them try again
      }
      else
      {
        return 'Locked';
      }
    }
    $user->setValidate('r' . self::createGuid());
    $user->save();
    try
    {
      $this->mail(array('subject' => sfConfig::get('app_sfApplyPlugin_reset_subject',
          sfContext::getInstance()->getI18N()->__("Please verify your password reset request on %1%", array('%1%' => $this->getRequest()->getHost()))),
        'fullname' => $this->getFullName($user),
        'email' => $user->getEmailAddress(),
        'parameters' => array('fullname' => $this->getFullName($user), 'validate' => $user->getValidate(), 'username' => $user->getUsername()),
        'text' => 'sfApply/sendValidateResetText',
        'html' => 'sfApply/sendValidateReset'));
    } catch (Exception $e)
    {
      return 'MailerError';
    }
    return 'After';
  }

  protected function getFromAddress()
  {
    $from = sfConfig::get('app_sfApplyPlugin_from', false);
    if (!$from)
    {
      throw new Exception('app_sfApplyPlugin_from is not set');
    }
    // i18n the full name
    return array('email' => $from['email'], 'fullname' => sfContext::getInstance()->getI18N()->__($from['fullname']));
  }

  public function executeConfirm(sfRequest $request)
  {
    $validate = $this->request->getParameter('validate');

    $sfGuardUser = Doctrine::getTable('sfGuardUser')->findOneByValidate($validate);
    if (!$sfGuardUser)
    {
      return 'Invalid';
    }
    $type = self::getValidationType($validate);
    if (!strlen($validate))
    {
      return 'Invalid';
    }
    $sfGuardUser->setValidate(null);
    $sfGuardUser->save();
    if ($type == 'New')
    {
      $sfGuardUser->setIsActive(true);  
      $sfGuardUser->save();
      $this->getUser()->signIn($sfGuardUser);
    }
    if ($type == 'Reset')
    {
      $this->getUser()->setAttribute('sfApplyReset', $sfGuardUser->getId());
      return $this->redirect('sfApply/reset');
    }
  }

  public function executeReset(sfRequest $request)
  {
    $this->form = $this->newForm('sfApplyResetForm');
    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('sfApplyReset'));
      if ($this->form->isValid())
      {
        $this->id = $this->getUser()->getAttribute('sfApplyReset', false);
        $this->forward404Unless($this->id);
        $this->sfGuardUser = Doctrine::getTable('sfGuardUser')->find($this->id);
        $this->forward404Unless($this->sfGuardUser);
        $sfGuardUser = $this->sfGuardUser;
        $sfGuardUser->setPassword($this->form->getValue('password'));
        $sfGuardUser->save();
        $this->getUser()->signIn($sfGuardUser);
        $this->getUser()->setAttribute('sfApplyReset', null);
        return 'After';
      }
    }
  }

  public function executeResetCancel()
  {
    $this->getUser()->setAttribute('sfApplyReset', null);
    return $this->redirect(sfConfig::get('app_sfApplyPlugin_after', '@homepage'));
  }

  public function executeSettings(sfRequest $request)
  {
    // sfApplySettingsForm inherits from sfApplyApplyForm, which
    // inherits from sfGuardUserForm while disallowing the standard
    // sfGuardUser fields except for first name and last name. 
    
    // That minimizes the amount of duplication of effort. If you want, you can use a different
    // form class. I suggest inheriting from sfApplySettingsForm and
    // making further changes after calling parent::configure() from
    // your own configure() method. 

    if (!$this->getUser()->isAuthenticated())
    {
      return $this->redirect('@homepage');
    }
    
    $this->form = $this->newForm('sfApplySettingsForm', $this->getUser()->getGuardUser());
    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('sfApplySettings'));
      if ($this->form->isValid())
      {
        $this->form->save();
        return $this->redirect('@homepage');
      }
    }
  }

  static protected function createGuid()
  {
    $guid = "";
    // This was 16 before, which produced a string twice as
    // long as desired. I could change the schema instead
    // to accommodate a validation code twice as big, but
    // that is completely unnecessary and would break 
    // the code of anyone upgrading from the 1.0 version.
    // Ridiculously unpasteable validation URLs are a 
    // pet peeve of mine anyway.
    for ($i = 0; ($i < 8); $i++) {
      $guid .= sprintf("%02x", mt_rand(0, 255));
    }
    return $guid;
  }
  
  static protected function getValidationType($validate)
  {
    $t = substr($validate, 0, 1);  
    if ($t == 'n')
    {
      return 'New';
    } 
    elseif ($t == 'r')
    {
      return 'Reset';
    }
    else
    {
      return sfView::NONE;
    }
  }

  // A convenience method to instantiate a form of the
  // specified class... unless the user has specified a
  // replacement class in app.yml. Sweet, no?
  protected function newForm($className, $object = null)
  {
    $key = "app_sfApplyPlugin_$className" . "_class";
    $class = sfConfig::get($key,
      $className);
    if ($object !== null)
    {
      return new $class($object);
    }
    return new $class;
  }

  // This allows change of name order etc by simple override or via I18N.
  // It would be nice if sfDoctrineGuardPlugin had this but as near as I can tell
  // it does not
  
  protected function getFullName($user)
  {
    return sfContext::getInstance()->getI18N()->__('%FIRST_NAME% %LAST_NAME%', array('%FIRST_NAME%' => $user->getFirstName(), '%LAST_NAME%' => $user->getLastName()));
  }
}
