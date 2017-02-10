<?php

include_once("../../config.php");


global $CFG, $PAGE, $OUTPUT;
include_once($CFG->libdir . "/authlib.php");

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

// get wantsurl from session and pass to the samlUrl
$samlUrl = "index.php";
if(isset($SESSION->wantsurl)) {
    $samlUrl = $samlUrl . "?wantsurl=" . urlencode($SESSION->wantsurl);
}

// if autologin is enabled redirect to the idp without showing the login form
$saml_config = get_config('auth/saml');
if(isset($saml_config->autologin)  && $saml_config->autologin)
{
       header('Location: '.$samlUrl);
       exit;
}

$errorcode = optional_param('errorcode', 0, PARAM_INT);


$context = CONTEXT_SYSTEM::instance();
$PAGE->set_url("$CFG->httpswwwroot/auth/saml/login.php");
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');


/// Define variables used in page
$site = get_site();

if (!empty($CFG->registerauth) or is_enabled_auth('none') or !empty($CFG->auth_instructions)) {
    $show_instructions = true;
} else {
    $show_instructions = false;
}

$loginsite = get_string("loginsite");
$PAGE->navbar->add($loginsite);

$PAGE->set_title("$site->fullname: $loginsite");
$PAGE->set_heading("$site->fullname");

echo $OUTPUT->header();

if ($show_instructions) {
    $columns = 'twocolumns';
} else {
    $columns = 'onecolumn';
}

if (empty($CFG->xmlstrictheaders) and !empty($CFG->loginpasswordautocomplete)) {
    $autocomplete = 'autocomplete="off"';
} else {
    $autocomplete = '';
}
?>
<div class="loginbox clearfix <?php echo $columns ?>">
  <div class="loginpanel">
<?php
  if (($CFG->registerauth == 'email') || !empty($CFG->registerauth)) { ?>
      <div class="skiplinks"><a class="skip" href="<?php echo $CFG->httpswwwroot; ?>/login/signup.php"><?php print_string("tocreatenewaccount"); ?></a></div>
<?php
  } ?>
    <h2><?php print_string("returningtosite") ?></h2>

<?php

$saml_config = get_config('auth/saml');
$authsequence = get_enabled_auth_plugins(true);

$frm = data_submitted();

if (empty($errormsg) && $errorcode == AUTH_LOGIN_UNAUTHORISED) {
    $errormsg = get_string("unauthorisedlogin", "", $frm->username);
} else if(empty($errormsg) && $errorcode == AUTH_LOGIN_FAILED) {
    $errormsg = get_string("invalidlogin");
} else if (empty($errormsg) && $errorcode == AUTH_LOGIN_LOCKOUT) {
    $errormsg = get_string('sessionerroruser', 'error');
} else if (empty($errormsg) && !empty($SESSION->loginerrormsg)) {
    // We had some errors before redirect, show them now.
    $errormsg = $SESSION->loginerrormsg;
    unset($SESSION->loginerrormsg);
}

if (!empty($errormsg) && method_exists($PAGE->requires, 'js_init_call')) {
    $PAGE->requires->js_init_call('M.util.focus_login_error', null, true);
}

echo '<center>';

if (in_array('saml', $authsequence)){
    if (isset($saml_config->samllogoimage) && $saml_config->samllogoimage != NULL) {
        echo '<a href="' . $samlUrl . '"><img src="'.$saml_config->samllogoimage.'" border="0" alt="SAML login" ></a>';
    }
    if (isset($saml_config->samllogoinfo)) {
        echo "<div class='desc'>$saml_config->samllogoinfo</div>";
    }
}
echo '</center>';

?>
      <div class="subcontent loginsub">
        <div class="desc">
          <?php
            print_string("auth_saml_loginusing", "auth_saml");
            echo '<br/>';
            echo '('.get_string("cookiesenabled").')';
            echo $OUTPUT->help_icon('cookiesenabled');
           ?>
        </div>
        <?php
          if (!empty($errormsg)) {
              echo '<div class="loginerrors">';
              echo $OUTPUT->error_text($errormsg);
              echo '</div>';
          }
        ?>
        <form action="<?php echo $CFG->httpswwwroot; ?>/login/index.php" method="post" id="login" <?php echo $autocomplete; ?> >
          <div class="loginform">
            <div class="form-label"><label for="username"><?php print_string("username") ?></label></div>
            <div class="form-input">
              <input type="text" name="username" id="username" size="15" value="<?php echo isset($frm->username)? $frm->username: ''; ?>" />
            </div>
            <div class="clearer"><!-- --></div>
            <div class="form-label"><label for="password"><?php print_string("password") ?></label></div>
            <div class="form-input">
              <input type="password" name="password" id="password" size="15" value="" <?php echo $autocomplete; ?> />
              <input type="submit" id="loginbtn" value="<?php print_string("login") ?>" />
            </div>
          </div>
            <div class="clearer"><!-- --></div>
              <?php if (isset($CFG->rememberusername) and $CFG->rememberusername == 2) { ?>
              <div class="rememberpass">
                  <input type="checkbox" name="rememberusername" id="rememberusername" value="1" <?php if (isset($frm->username)) {echo 'checked="checked"';} ?> />
                  <label for="rememberusername"><?php print_string('rememberusername', 'admin') ?></label>
              </div>
              <?php } ?>
          <div class="clearer"><!-- --></div>
          <div class="forgetpass"><a href="<?php echo $CFG->httpswwwroot; ?>/login/forgot_password.php"><?php print_string("forgotten") ?></a></div>
        </form>
      </div>

<?php if ($CFG->guestloginbutton and !isguestuser()) {  ?>
      <div class="subcontent guestsub">
        <div class="desc">
          <?php print_string("someallowguest") ?>
        </div>
        <form action="<?php echo $CFG->httpswwwroot; ?>/login/index.php" method="post" id="guestlogin">
          <div class="guestform">
            <input type="hidden" name="username" value="guest" />
            <input type="hidden" name="password" value="guest" />
            <input type="submit" value="<?php print_string("loginguest") ?>" />
          </div>
        </form>
      </div>
<?php } ?>
     </div>
<?php if ($show_instructions) { ?>
    <div class="signuppanel">
      <h2><?php print_string("firsttime") ?></h2>
      <div class="subcontent">
<?php     if (is_enabled_auth('none')) { // instructions override the rest for security reasons
              print_string("loginstepsnone");
          } else if ($CFG->registerauth == 'email') {
              if (!empty($CFG->auth_instructions)) {
                  echo format_text($CFG->auth_instructions);
              } else {
                  print_string("loginsteps", "", $CFG->httpswwwroot . "/login/signup.php");
              } ?>
                 <div class="signupform">
                   <form action="<?php echo $CFG->httpswwwroot; ?>/login/signup.php" method="get" id="signup">
                   <div><input type="submit" value="<?php print_string("startsignup") ?>" /></div>
                   </form>
                 </div>
<?php     } else if (!empty($CFG->registerauth)) {
              echo format_text($CFG->auth_instructions); ?>
              <div class="signupform">
                <form action="<?php echo $CFG->httpswwwroot; ?>/login/signup.php" method="get" id="signup">
                <div><input type="submit" value="<?php print_string("startsignup") ?>" /></div>
                </form>
              </div>
<?php     } else {
              echo format_text($CFG->auth_instructions);
          } ?>
      </div>
    </div>
<?php } ?>
<?php if (!empty($potentialidps)) { ?>
    <div class="subcontent potentialidps">
        <h6><?php print_string('potentialidps', 'auth'); ?></h6>
        <div class="potentialidplist">
<?php foreach ($potentialidps as $idp) {
    echo  '<div class="potentialidp"><a href="' . $idp['url']->out() . '" title="' . $idp['name'] . '">' . $OUTPUT->render($idp['icon'], $idp['name']) . '&nbsp;' . $idp['name'] . '</a></div>';
} ?>
        </div>
    </div>
<?php } ?>
</div>

<?php

if (!empty($CFG->loginpageautofocus)) {
    //focus username or password
    $PAGE->requires->js_init_call('M.util.focus_login_form', null, true);
}

echo $OUTPUT->footer();
