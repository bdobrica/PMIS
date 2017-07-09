<?php
/*
Name: Login
*/
$cs_fb_login_url = '#';
if (!isset ($cs_facebook))
	$cs_facebook = new CoreSite\Module\Login\Facebook ();
if (isset ($cs_facebook))
	$cs_fb_login_url = $cs_facebook->get ('login_url');
?><div class="login-container"><div class="row"><div class="col-md-12"><div class="text-center m-b-md"><h3><?php self::_e (/*T[*/'PROJECT MANAGEMENT INFORMATION SYSTEM'/*]*/); ?></h3><small><?php self::_e (/*T[*/'Developed for Pro-Youth by Core Security Advisers'/*]*/); ?></small></div><div class="hpanel"><div class="panel-body"><form action="" method="post" id="loginForm"><?php
self::inp (['key' => 'username', 'label' => /*T[*/'Username'/*]*/, 'error' => $this->err ('A')]);
self::inp (['key' => 'password', 'label' => /*T[*/'Password'/*]*/, 'type' => 'password', 'help' => '<a href="' . $this->get ('url', ['page' => 'recover-form']) . '">' . self::__(/*T[*/'Click here if you forgot your password.'/*]*/) . '</a>', 'error' => $this->err ('B')]);
?><button name="login_user" class="btn btn-success btn-block"><?php self::_e (/*T[*/'Login'/*]*/); ?></button>
<?php if (FALSE) : ?><a class="btn btn-info btn-block" href="<?php echo $cs_fb_login_url; ?>"><?php self::_e (/*T[*/'Login with Facebook'/*]*/); ?></a><?php endif; ?>
<a class="btn btn-default btn-block" href="<?php $this->out ('url', ['page' => 'register-form']); ?>"><?php self::_e (/*T[*/'Register'/*]*/); ?></a></form></div></div></div></div><div class="row"><div class="col-md-12 text-center"><strong><?php self::_e (/*T[*/'PMIS'/*]*/); ?></strong> - <?php self::_e (/*T[*/'Project Management Information System'/*]*/); ?><br/> 2016-<?php echo date('Y'); ?> &copy; Pro-Youth / Core Security Advisers</div></div></div>
