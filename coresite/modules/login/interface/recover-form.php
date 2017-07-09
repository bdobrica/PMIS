<?php
/*
Name: Password Recovery
Order: 2
*/
?><div class="login-container"><div class="row"><div class="col-md-12"><div class="text-center m-b-md"><h3><?php self::_e (/*T[*/'PROJECT MAMANGEMENT INFORMATION SYSTEM'/*]*/); ?></h3><small><?php self::_e (/*T[*/'Password recovery.'/*]*/); ?></small></div><div class="hpanel"><div class="panel-body"><form action="#" id="loginForm"><?php self::inp (['key' => 'email', 'label' => /*T[*/'E-Mail Address'/*]*/]); ?><button name="" class="btn btn-success btn-block"><?php self::_e (/*T[*/'Recover Password'/*]*/); ?></button><a class="btn btn-default btn-block" href="<?php $this->out ('url', ['page' => 'login-form']); ?>"><?php self::_e (/*T[*/'Return to Login'/*]*/); ?></a></form></div></div></div></div><div class="row"><div class="col-md-12 text-center"><strong><?php self::_e (/*T[*/'PMIS'/*]*/); ?></strong> - <?php self::_e (/*T[*/'Project Management Information System'/*]*/); ?><br/> 2016-<?php echo date('Y'); ?> &copy; Pro-Youth / Core Security Advisers</div></div></div>
