<?php
/*
Name: Clients
Description: A list of all your clients
Order: 0
Roles: *
*/
namespace CoreSite\Core;

?><div class="normalheader small-header"><div class="hpanel"><div class="panel-body"><div id="hbreadcrumb" class="pull-right m-t-lg"><?php $this->render ('breadcrumbs'); ?></div><h2 class="font-light m-b-xs"><?php self::_e ($this->page->name); ?></h2><small><?php if (isset ($this->page->description)) self::_e ($this->page->description); ?></small></div></div>
	<div class="content">
		<div class="row">
			<div class="col-lg-12">
				<div class="hpanel horange">
					<div class="panel-heading hbuilt">
						<div class="panel-tools">
							<a href="#" class="btn btn-xs btn-success" data-toggle="modal" data-target="#modal-add-new-client"><i class="pe-7s-plus"></i> <?php self::_e (/*T[*/'Add New Client'/*]*/); ?></a>
							<a href="#" class="btn btn-xs btn-success" data-toggle="modal" data-target="#modal-add-new-client"><i class="pe-7s-plus"></i> <?php self::_e (/*T[*/'Import Client List'/*]*/); ?></a>
							<a href="#" class="btn btn-xs btn-info" data-toggle="modal" data-target="#modal-display-options"><i class="pe-7s-plus"></i> <?php self::_e (/*T[*/'Display Options'/*]*/); ?></a>
						</div>
						<?php self::_e (/*T[*/'Your Clients'/*]*/); ?>
					</div>
					<div class="panel-body">
<?php $clients = new Find ('\CoreSite\Core\Person', isset ($filter) ? $filter : null, isset ($order) ? $order : null); ?>
						<div>
							<div class="row">
								<div class="col-sm-6">
									<form class="form-inline">
										<label><?php self::_e (/*T[*/'Search'/*]*/); ?>:
											<input class="form-control input-sm" placeholder="" type="search">
										</label>
										<button class="btn btn-sm btn-warning"><i class="pe-7s-search"></i></button>
									</form>
								</div>
								<div class="col-sm-6 text-right">
									<form class="form-inline">
										<label><?php self::_e (/*T[*/'Filter by '/*]*/); ?>:
											<select class="form-control input-sm select2" multiple="multiple">
												<option value="text">Text</option>
												<option value="name">Name</option>
											</select>
										</label>
										<button class="btn btn-sm btn-warning"><i class="pe-7s-filter"></i></button>
									</form>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-12">
<?php if ($clients->is ('empty')) : ?>
<?php else : ?>
									<table class="table table-condensed table-striped">
										<thead>
											<tr>
												<th><input type="checkbox" class="i-checks"></th>
<?php	foreach (Person::$F['read'] as $key => $label) : ?>
												<th><?php self::_e ($label); ?></th>
<?php	endforeach; ?>
											</tr>
										</thead>
										<tbody>
<?php	foreach ($clients->get () as $client) : ?>
											<tr>
												<th><input type="checkbox" class="i-checks" value="<?php $client->out (); ?>"></th>
<?php		foreach (Person::$F['read'] as $key => $label) : ?>
												<td><?php $client->out ($key); ?></td>
<?php		endforeach; ?>
											</tr>
<?php	endforeach; ?>
										</tbody>
									</table>
<?php endif; ?>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<form class="form-inline">
										<label><?php self::_e (/*T[*/'With selected '/*]*/); ?>:
											<select class="form-control input-sm select2">
												<option value="text">Text</option>
												<option value="name">Name</option>
											</select>
										</label>
										<button class="btn btn-sm btn-success"><i class="pe-7s-angle-right-circle"></i></button>
									</form>
								</div>
								<div class="col-sm-6 text-right">
									<ul class="pagination">
										<li class="paginate_button previous"><a href="#">Previous</a></li>
										<li class="paginate_button active"><a href="#">1</a></li>
										<li class="paginate_button"><a href="#">2</a></li>
										<li class="paginate_button"><a href="#">3</a></li>
										<li class="paginate_button next"><a href="#">Next</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">Showing 1 to 10 of 42 entries.</div>
				</div>
			</div>
		</div>
		<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="modal-add-new-client">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="color-line"></div>
					<div class="modal-header">
						<h4>xxx</h4>
						<small>xxxx</h4>
					</div>
					<div class="modal-body">
				<div class="hpanel hgreen">
					<div class="panel-heading">
						<div class="panel-tools">
							<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							<a class="closebox"><i class="fa fa-times"></i></a>
						</div>
						<?php self::_e (/*T[*/'Add New Client'/*]*/); ?>
					</div>
					<div class="panel-body">
						<form action="" method="post" class="form-horizontal">
						<?php self::inp (['key' => 'password', 'label' => /*T[*/'Old Password'/*]*/, 'type' => 'password', 'split' => '2:10']); ?>
						<div class="hr-line-dashed"></div>
						<?php self::inp (['key' => 'new_password', 'label' => /*T[*/'New Password'/*]*/, 'type' => 'password', 'split' => '2:10']); ?>
						<div class="hr-line-dashed"></div>
						<?php self::inp (['key' => 'confirm_password', 'label' => /*T[*/'Confirm Password'/*]*/, 'type' => 'password', 'split' => '2:10']); ?>
						<div class="hr-line-dashed"></div>
						<div class="col-sm-6">
						<a href="" class="btn btn-sm btn-block btn-danger"><?php self::_e (/*T[*/'Cancel'/*]*/); ?></a>
						</div>
						<div class="col-sm-6">
						<button name="update_user" class="btn btn-sm btn-block btn-info"><?php self::_e (/*T[*/'Change Password'/*]*/); ?></button>
						</div>
						</form>
					</div>
					<div class="panel-footer contact-footer"></div>
				</div>
				<div class="hpanel hred">
					<div class="panel-heading">
						<div class="panel-tools">
							<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							<a class="closebox"><i class="fa fa-times"></i></a>
						</div>
						<?php self::_e (/*T[*/'Import / Export'/*]*/); ?>
					</div>
					<div class="panel-body">
					</div>
					<div class="panel-footer contact-footer"></div>
				</div>
				<div class="hpanel hblue crudl">
					<div class="panel-heading">
						<div class="panel-tools">
							<a class="showhide"><i class="fa fa-chevron-up"></i></a>
							<a class="closebox"><i class="fa fa-times"></i></a>
						</div>
						<?php self::_e (/*T[*/'Manage Additional Fields'/*]*/); ?>
					</div>
					<div class="panel-body crudl-panel" data-crudl="list">
						<div class="row">
							<div class="col-sm-9">
								<span class="font-bold">Some Field (String)</span><br>
								<span class="font-light">Some Field Description Bla Bla Bla</span>
							</div>
							<div class="col-sm-3 text-right">
								<a href="#" class="btn btn-sm btn-danger crudl-control" data-crudl="delete"><i class="pe-7s-trash"></i></a>
								<a href="#" class="btn btn-sm btn-info crudl-control" data-crudl="update"><i class="pe-7s-pen"></i></a>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-9">
								<span class="font-bold">Some Field (String)</span><br>
								<span class="font-light">Some Field Description Bla Bla Bla</span>
							</div>
							<div class="col-sm-3 text-right">
								<a href="#" class="btn btn-sm btn-danger" data-crudl="delete"><i class="pe-7s-trash"></i></a>
								<a href="#" class="btn btn-sm btn-info" data-crudl="update"><i class="pe-7s-pen"></i></a>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-9">
								<span class="font-bold">Some Field (String)</span><br>
								<span class="font-light">Some Field Description Bla Bla Bla</span>
							</div>
							<div class="col-sm-3 text-right">
								<a href="#" class="btn btn-sm btn-danger" data-crudl="delete"><i class="pe-7s-trash"></i></a>
								<a href="#" class="btn btn-sm btn-info" data-crudl="update"><i class="pe-7s-pen"></i></a>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-12">
								<a href="#" class="btn btn-sm btn-block btn-success crudl-control" data-crudl="create"><i class="pe-7s-pen"></i></a>
							</div>
						</div>
					</div>
					<div class="panel-body crudl-panel hidden" data-crudl="create">
						<form action="" method="post" class="form-horizontal">
						<?php self::inp (['key' => 'name', 'label' => /*T[*/'Field Name'/*]*/, 'type' => 'password', 'split' => '2:10']); ?>
						<?php self::inp (['key' => 'description', 'label' => /*T[*/'Field Description'/*]*/, 'type' => 'textarea', 'split' => '2:10']); ?>
						<?php self::inp (['key' => 'type', 'label' => /*T[*/'Field Type'/*]*/, 'type' => 'select', 'split' => '2:10', 'options' => self::$field_types]); ?>
						<?php self::inp (['key' => 'confirm_password', 'label' => /*T[*/'Confirm Password'/*]*/, 'type' => 'password', 'split' => '2:10']); ?>
						<div class="col-sm-6">
						<a href="" class="btn btn-sm btn-block btn-danger crudl-control" data-crudl="list"><?php self::_e (/*T[*/'Cancel'/*]*/); ?></a>
						</div>
						<div class="col-sm-6">
						<button name="update_user" class="btn btn-sm btn-block btn-success"><?php self::_e (/*T[*/'Add Field'/*]*/); ?></button>
						</div>
						</form>
					</div>
					<div class="panel-body crudl-panel hidden" data-crudl="update">
						<form action="" method="post" class="form-horizontal">
						<?php self::inp (['key' => 'name', 'label' => /*T[*/'Field Name'/*]*/, 'type' => 'password', 'split' => '2:10']); ?>
						<?php self::inp (['key' => 'description', 'label' => /*T[*/'Field Description'/*]*/, 'type' => 'textarea', 'split' => '2:10']); ?>
						<?php self::inp (['key' => 'type', 'label' => /*T[*/'Field Type'/*]*/, 'type' => 'select', 'split' => '2:10', 'options' => self::$field_types]); ?>
						<?php self::inp (['key' => 'confirm_password', 'label' => /*T[*/'Confirm Password'/*]*/, 'type' => 'password', 'split' => '2:10']); ?>
						<div class="col-sm-6">
						<a href="" class="btn btn-sm btn-block btn-danger crudl-control" data-crudl="list"><?php self::_e (/*T[*/'Cancel'/*]*/); ?></a>
						</div>
						<div class="col-sm-6">
						<button name="update_user" class="btn btn-sm btn-block btn-success"><?php self::_e (/*T[*/'Add Field'/*]*/); ?></button>
						</div>
						</form>
					</div>
					<div class="panel-body crudl-panel hidden" data-crudl="delete">
						<form action="" method="post" class="form-horizontal">
						<div class="col-sm-6">
						<a href="" class="btn btn-sm btn-block btn-danger crudl-control" data-crudl="list"><?php self::_e (/*T[*/'Cancel'/*]*/); ?></a>
						</div>
						<div class="col-sm-6">
						<button name="update_user" class="btn btn-sm btn-block btn-success"><?php self::_e (/*T[*/'Add Field'/*]*/); ?></button>
						</div>
						</form>
					</div>
					<div class="panel-footer contact-footer"></div>
				</div>
					</div>
			</div>
		</div>
	</div>
</div>
