<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="container-fluid  h-100 overflow-auto px-2">

	<!-- titulo & breadcrumb -->
	<div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
		<div class="flex-grow-1">
			<h5 class="fs-5 fw-semibold m-0"> <?php echo $pagetitle; ?></h5>
		</div>

		<div class="text-end  ">
			<?php echo $breadcrumb; ?>
		</div>
	</div>

	<section class="row">
		<div class="col">
			<div class="card d-block">

				<div class="card-body">
					<div class="box-header with-border">
						<h3 class="box-title"><?php echo anchor('admin/users/create', '<i class="fa fa-plus"></i> ' . lang('users_create_user'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
					</div>
					<div class="box-body">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th><?php echo lang('users_firstname'); ?></th>
									<th><?php echo lang('users_lastname'); ?></th>
									<th><?php echo lang('users_email'); ?></th>
									<th><?php echo lang('users_groups'); ?></th>
									<th><?php echo lang('users_status'); ?></th>
									<th><?php echo lang('users_action'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($users as $user): ?>
									<tr>
										<td><?php echo htmlspecialchars($user->first_name, ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars($user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8'); ?></td>
										<td>
											<?php

											foreach ($user->groups as $group) {

												// Disabled temporary !!!
												// echo anchor('admin/groups/edit/'.$group->id, '<span class="label" style="background:'.$group->bgcolor.';">'.htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8').'</span>');
												echo anchor('admin/groups/edit/' . $group->id, '<span class="label label-default">' . htmlspecialchars($group->name, ENT_QUOTES, 'UTF-8') . '</span>');
											}

											?>
										</td>
										<td><?php echo ($user->active) ? anchor('admin/users/deactivate/' . $user->id, '<span class="label label-success">' . lang('users_active') . '</span>') : anchor('admin/users/activate/' . $user->id, '<span class="label label-default">' . lang('users_inactive') . '</span>'); ?></td>
										<td>
											<?php echo anchor('admin/users/edit/' . $user->id, '<span class="label label-success">' . lang('actions_edit') . '</span>'); ?>&nbsp;
											<?php echo anchor('admin/users/profile/' . $user->id, '<span class="label label-primary">' . lang('actions_see') . '</span>'); ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

				</div>
			</div>
		</div>
	</section>
</div>