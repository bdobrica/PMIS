<?php
/*
Name: Profile
Roles: *
*/
?><div class="normalheader small-header"><div class="hpanel"><div class="panel-body"><a class="small-header-action" href=""><div class="clip-header"><i class="fa fa-arrow-down"></i></div></a><div id="hbreadcrumb" class="pull-right m-t-lg"><?php $this->render ('breadcrumbs'); ?></div><h2 class="font-light m-b-xs"><?php self::_e ($this->page->name); ?></h2><small><?php if (isset ($this->page->description)) self::_e ($this->page->description); ?></small></div></div></div>
<div class="content"><div class="row">

<div class="col-lg-4"><div class="hpanel hgreen"><div class="panel-body"><div class="pull-right text-right"><div class="btn-group">
<a href="#" data-change="this.facebook_url"><i class="fa fa-facebook btn btn-default btn-xs"></i></a>
<a href="#" data-change="this.twitter_url"><i class="fa fa-twitter btn btn-default btn-xs"></i></a>
<a href="#" data-change="this.linkedin_url"><i class="fa fa-linkedin btn btn-default btn-xs"></i></a>
</div></div>
<a href="#" data-change="this.avatar"><img alt="logo" class="img-circle m-b m-t-md" src="<?php $this->user->out ('avatar', ['opts' => ['key' => 'url', 'opts' => '76x76']]); ?>" data-update="this.avatar"></a>
<h3><a href="#" data-change="this.full_name" data-update="this.full_name"><?php $this->user->out ('full_name', ['default' => /*T[*/'Ionut Popescu'/*]*/]); ?></a></h3>
<p><strong><?php self::_e (/*T[*/'Phone'/*]*/); ?></strong>:<a href="#" data-change="this.phone" data-update="this.phone"><?php $this->user->out ('phone', ['default' => /*T[*/'07xx xxx xxx'/*]*/]); ?></a></p>
<p><strong><?php self::_e (/*T[*/'Description'/*]*/); ?></strong>:<br><a href="#" data-change="this.description" data-update="this.description"><?php $this->user->out ('description', ['default' => /*T[*/'The most awesome description there is!'/*]*/]); ?></a></p>
<p><strong><?php self::_e (/*T[*/'Password'/*]*/); ?></strong>: <a href="#" data-change="this.password">********</a></p>
</div></div></div><!-- end .col-lg-4 -->

<div class="col-lg-8"><div class="hpanel"><ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-resume">Resume</a></li>
</ul><div class="tab-content">
<div id="tab-resume" class="tab-pane active"><div class="panel-body">
<div class="row"><div class="col-lg-8"><h3><?php self::_e (/*T[*/'Work Experience'/*]*/); ?></h3></div><div class="col-lg-4 text-right"><a href="#" data-change="this.work_experience" class="btn-icon"><i class="pe-7s-plus"></i></a></div></div>
<hr><div data-create="this.work_experience"></div>
<div class="row"><div class="col-lg-8"><h3><?php self::_e (/*T[*/'Professional Skills'/*]*/); ?></h3></div><div class="col-lg-4 text-right"><a href="#" data-change="this.professional_skill" class="btn-icon"><i class="pe-7s-plus"></i></a></div></div>
<hr><div data-create="this.professional_skills"></div>
<div class="row"><div class="col-lg-8"><h3><?php self::_e (/*T[*/'Education'/*]*/); ?></h3></div><div class="col-lg-4 text-right"><a href="#" data-change="this.education" class="btn-icon"><i class="pe-7s-plus"></i></a></div></div>
<hr><div data-create="this.education"></div>
</div></div><!-- end .tab-pane -->
</div></div></div><!-- end .col-lg-8 -->
</div></div><!-- end .content --><?php
self::mod ('header', ['id' => 'facebook_url', 'name' => /*T[*/'Update Facebook'/*]*/]);
self::inp (['key' => 'facebook_url', 'label' => /*T[*/'Facebook Profile URL'/*]*/]);
self::mod ('footer', ['action' => 'update_user', 'button' => /*T[*/'Update Facebook'/*]*/]);

self::mod ('header', ['id' => 'twitter_url', 'name' => /*T[*/'Update Twitter'/*]*/]);
self::inp (['key' => 'twitter_url', 'label' => /*T[*/'Twitter Profile URL'/*]*/]);
self::mod ('footer', ['action' => 'update_user', 'button' => /*T[*/'Update Twitter'/*]*/]);

self::mod ('header', ['id' => 'linkedin_url', 'name' => /*T[*/'Update LinkedIn'/*]*/]);
self::inp (['key' => 'linkedin_url', 'label' => /*T[*/'LinkedIn Profile URL'/*]*/]);
self::mod ('footer', ['action' => 'update_user', 'button' => /*T[*/'Update LinkedIn'/*]*/]);

self::mod ('header', ['id' => 'avatar', 'name' => /*T[*/'Change Avatar'/*]*/]);
self::inp (['key' => 'avatar', 'label' => /*T[*/'Avatar'/*]*/, 'type' => 'file', 'value' => $this->user->get ('avatar', ['key' => 'name'])]);
self::mod ('footer', ['action' => 'update_user', 'button' => /*T[*/'Change Avatar'/*]*/]);

self::mod ('header', ['id' => 'full_name', 'name' => /*T[*/'Change Name'/*]*/]);
self::inp (['key' => 'first_name', 'label' => /*T[*/'First Name'/*]*/, 'value' => $this->user->get ('first_name')]);
self::inp (['key' => 'last_name', 'label' => /*T[*/'Last Name'/*]*/, 'value' => $this->user->get ('last_name')]);
self::mod ('footer', ['action' => 'update_user', 'button' => /*T[*/'Change Name'/*]*/]);

self::mod ('header', ['id' => 'phone', 'name' => /*T[*/'Change Phone'/*]*/]);
self::inp (['key' => 'phone', 'label' => /*T[*/'Phone'/*]*/, 'value' => $this->user->get ('phone')]);
self::mod ('footer', ['action' => 'update_user', 'button' => /*T[*/'Change Phone'/*]*/]);

self::mod ('header', ['id' => 'description', 'name' => /*T[*/'Change Description'/*]*/]);
self::inp (['key' => 'description', 'label' => /*T[*/'Description'/*]*/, 'type' => 'textarea']);
self::mod ('footer', ['action' => 'update_user', 'button' => /*T[*/'Change Description'/*]*/]);

self::mod ('header', ['id' => 'password', 'name' => /*T[*/'Change Password'/*]*/]);
self::inp (['key' => 'password', 'label' => /*T[*/'Old Password'/*]*/, 'type' => 'password']);
self::inp (['key' => 'new_password', 'label' => /*T[*/'New Password'/*]*/, 'type' => 'password']);
self::inp (['key' => 'confirm_password', 'label' => /*T[*/'Confirm Password'/*]*/, 'type' => 'password']);
self::mod ('footer', ['action' => 'update_user', 'button' => /*T[*/'Change Password'/*]*/]);

self::mod ('header', ['id' => 'work_experience', 'name' => /*T[*/'Add Experience'/*]*/]);
self::inp (['key' => 'period', 'label' => /*T[*/'Period'/*]*/, 'type' => 'interval']);
self::inp (['key' => 'name', 'label' => /*T[*/'Job Position'/*]*/]);
self::inp (['key' => 'employer', 'label' => /*T[*/'Employer'/*]*/]);
self::inp (['key' => 'description', 'label' => /*T[*/'Job Short Description'/*]*/, 'type' => 'textarea']);
self::mod ('footer', ['action' => 'create_resume', 'button' => /*T[*/'Add Experience'/*]*/]);

self::mod ('header', ['id' => 'professional_skill', 'name' => /*T[*/'Add Professional Skills'/*]*/]);
self::inp (['key' => 'category', 'label' => /*T[*/'Category'/*]*/, 'type' => 'select', 'options' => \CoreSite\Module\Profile\Resume::$type['professional_skill']]);
self::inp (['key' => 'name', 'label' => /*T[*/'Skill'/*]*/]);
self::inp (['key' => 'period', 'label' => /*T[*/'Period'/*]*/, 'type' => 'interval']);
self::inp (['key' => 'level', 'label' => /*T[*/'Competence Level'/*]*/, 'type' => 'select', 'options' => \CoreSite\Module\Profile\Resume::$level]);
self::mod ('footer', ['action' => 'create_resume', 'button' => /*T[*/'Add Experience'/*]*/]);

self::mod ('header', ['id' => 'education', 'name' => /*T[*/'Add Education'/*]*/]);
self::inp (['key' => 'period', 'label' => /*T[*/'Period'/*]*/, 'type' => 'interval']);
self::inp (['key' => 'category', 'label' => /*T[*/'Diploma'/*]*/, 'type' => 'select', 'options' => \CoreSite\Module\Profile\Resume::$type['education']]);
self::inp (['key' => 'name', 'label' => /*T[*/'Field of Study'/*]*/]);
self::inp (['key' => 'entity', 'label' => /*T[*/'Institution'/*]*/]);
self::mod ('footer', ['action' => 'create_resume', 'button' => /*T[*/'Add Education'/*]*/]);
?>
