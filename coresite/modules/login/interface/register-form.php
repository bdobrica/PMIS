<?php
/*
Name: Register
Order: 1
*/
?><div class="register-container"><div class="row"><div class="col-md-12"><div class="text-center m-b-md"><h3><?php self::_e (/*T[*/'PROJECT MANAGEMENT INFORMATION SYSTEM'/*]*/); ?></h3><small><?php self::_e (/*T[*/'Developed for Pro-Youth by Core Security Advisers'/*]*/); ?></small></div><div class="hpanel"><div class="panel-body"><form action="" method="post" id="loginForm"><div class="row"><div class="col-lg-12"><?php
self::inp (['key' => 'username', 'label' => /*T[*/'Username'/*]*/, 'error' => $this->err ('A')]);
?></div></div><div class="row"><div class="col-lg-6"><?php
self::inp (['key' => 'password', 'type' => 'password', 'label' => /*T[*/'Password'/*]*/, 'error' => $this->err ('B')]);
?></div><div class="col-lg-6"><?php
self::inp (['key' => 'confirm_password', 'type' => 'password', 'label' => /*T[*/'Confirm Password'/*]*/, 'error' => $this->err ('C')]);
?></div></div><div class="row"><div class="col-lg-6"><?php
self::inp (['key' => 'email', 'label' => /*T[*/'E-Mail Address'/*]*/, 'error' => $this->err ('D')]);
?></div><div class="col-lg-6"><?php
self::inp (['key' => 'phone', 'label' => /*T[*/'Phone'/*]*/, 'error' => $this->err ('E')]);
?></div></div><div class="row"><div class="col-lg-12"><?php
self::inp (['key' => 'terms', 'type' => 'checkbox', 'label' => /*T[*/'I agree with PMIS terms and conditions.'/*]*/, 'error' => $this->err ('F')]);
?></div></div><div class="row"><div class="col-lg-6"><button class="btn btn-success btn-block" name="register_user"><?php self::_e (/*T[*/'Register'/*]*/); ?></button></div><div class="col-lg-6"><a href="<?php $this->out ('url', ['page' => null]); ?>" class="btn btn-default btn-block"><?php self::_e (/*T[*/'Cancel'/*]*/); ?></a></div></div></form></div></div></div></div><div class="row"><div class="col-md-12 text-center"><strong><?php self::_e (/*T[*/'PMIS'/*]*/); ?></strong> - <?php self::_e (/*T[*/'Project Management Information System'/*]*/); ?><br/> 2016-<?php echo date('Y'); ?> &copy; Pro-Youth / Core Security Advisers</div></div></div>
