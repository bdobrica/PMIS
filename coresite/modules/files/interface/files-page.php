<?php
/*
Name: Files
Description:
Order: 0
Roles: *
*/
?><div class="normalheader small-header"><div class="hpanel"><div class="panel-body"><a class="small-header-action" href=""><div class="clip-header"><i class="fa fa-arrow-down"></i></div></a><div id="hbreadcrumb" class="pull-right m-t-lg"><?php $this->render ('breadcrumbs'); ?></div><h2 class="font-light m-b-xs"><?php self::_e ($this->page->name); ?></h2><small><?php if (isset ($this->page->description)) self::_e ($this->page->description); ?></small></div></div></div>

<div class="content"><div class="row">

<div class="col-lg-3"><div class="hpanel hgreen"><div class="panel-body">
<form action="" method="post" enctype="multipart/form-data">
<?php self::inp (['key' => 'file', 'type' => 'file', 'label' => /*T[*/'Upload File'/*]*/]); ?>
<button name="create_folder" class="btn btn-block btn-default"><?php self::_e (/*T[*/'Upload File'/*]*/); ?></button>
</form>
<hr>
<form action="" method="post">
<?php self::inp (['key' => 'folder', 'label' => /*T[*/'Create Folder'/*]*/]); ?>
<button name="create_folder" class="btn btn-block btn-default"><?php self::_e (/*T[*/'Create Folder'/*]*/); ?></button>
</form>
</div></div></div><!-- end .col-lg-4 -->

<div class="col-lg-9"><div class="hpanel"><ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-files">Files</a></li>
</ul><div class="tab-content">
<div id="tab-files" class="tab-pane active"><div class="panel-body icons-box">
	<div class="row">
<?php $files = new \CoreSite\Core\Find ('\CoreSite\Core\File');
foreach ($files->get () as $file) : ?>
		<div class="infont col-md-2 animated-panel zoomIn">
			<a href="#" class="font-icon-detail">
				<i class="pe-7s-file"></i>
				<span class="font-icon-name"><?php $file->out ('name'); ?></span>
			</a>
		</div>
<?php endforeach; ?>
	</div>
</div></div><!-- end .tab-pane -->
</div></div></div><!-- end .col-lg-8 -->
</div></div><!-- end .content --><?php
