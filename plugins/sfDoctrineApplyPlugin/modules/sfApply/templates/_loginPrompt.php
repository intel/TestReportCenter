<?php use_helper('I18N') ?>
<?php // Override this, not everything ?>
<?php include_partial('sfApply/beforeLoginPrompt') ?>
<?php if (sfConfig::get('app_sfApplyPlugin_facebook')): ?>
<?php $facebook = sfConfig::get('app_sfApplyPlugin_facebook') ?>
<?php if ($facebook): ?>
  <script>
    $(function() {
      if (!$('#fb-root').length) {
        $('body').prepend($('<div id="fb-root"></div>'));
      }
      window.fbAsyncInit = function() {
        FB.init({
          appId      : <?php echo json_encode($facebook['id']) ?>, // App ID
          channelUrl : '//<?php echo $sf_request->getHost() ?>/sfDoctrineApplyPlugin/fb/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true  // parse XFBML
        });

        // Additional init code here

      };

      // Load the SDK Asynchronously (if not already loaded)
      (function(d) {
         var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
         if (d.getElementById(id)) {return;}
         js = d.createElement('script'); js.id = id; js.async = true;
         js.src = "//connect.facebook.net/en_US/all.js";
         ref.parentNode.insertBefore(js, ref);
       }(document));
    });
  </script>
<?php endif ?>
  <div class="sf_apply_login_options">
    <div class="sf_apply_login_option">
      <a href="#" class="sf_apply_fb_login_button" data-fb-login><img src="/sfDoctrineApplyPlugin/images/fb-login.png" /></a>
    </div>
    <div class="sf_apply_login_separator">
      <?php echo __('&mdash; OR &mdash;') ?>
    </div>
    <div class="sf_apply_login_option">
<?php endif ?>

      <?php // Override this, not everything, to dress up the ?>
      <?php // simple local login form ?>
      <?php include_partial('sfApply/localLoginPrompt', array('form' => $form)) ?>

<?php if (sfConfig::get('app_sfApplyPlugin_facebook')): ?>
    </div>
  </div>
  <script type="text/javascript">
  // Important: use a click event to avoid popup blockers
  $('[data-fb-login]').click(function() {
    FB.login(function(response) {
      if (response.authResponse) {
        // The server needs the user's id, cryptographically signed
        // to prove we really got it by logging in for this app.
        // That's possible with signed_request
        $.post('<?php echo url_for("@sf_apply_facebook_login") ?>', { signed_request: response.authResponse.signedRequest }, function(data) {
          window.location.reload(true);
        });
      }
    }, { scope: 'email' });
    return false;
  })
  </script>

<?php endif ?>

