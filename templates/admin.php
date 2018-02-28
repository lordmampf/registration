<?php
script('registration', 'settings');
?>
<form id="registration_settings_form" class="section">
	<h2><?php p($l->t('Registration')); ?></h2><span id="registration_settings_msg" class="msg"></span>
	<p>
		<label for="registered_user_group"><?php p($l->t('Default group that all registered users belong')); ?></label>
		<select id="registered_user_group" name="registered_user_group">
			<option value="none" <?php echo $_['current'] === 'none' ? 'selected="selected"' : ''; ?>><?php p($l->t('None')); ?></option>
			<?php
			foreach ( $_['groups'] as $group ) {
				$selected = $_['current'] === $group ? 'selected="selected"' : '';
				echo '<option value="'.$group.'" '.$selected.'>'.$group.'</option>';
			}
			?>
		</select>
	</p>
	<br>
	<p>
		<label for="allowed_domains"><?php p($l->t('Allowed mail address domains for registration')); ?></label>
		<input type="text" id="allowed_domains" name="allowed_domains" value=<?php p($_['allowed']);?>>
		</p>
		<p>
		<em><?php p($l->t('Enter a semicolon-separated list of allowed domains. Example: owncloud.com;github.com'));?></em>
	</p>
	<br>
	<p>
		<label for="admin_approval_required"><?php p($l->t('Require admin approval?')); ?>
		<input type="checkbox" id="admin_approval_required" name="admin_approval_required" <?php if($_['approval_required'] === "yes" ) echo " checked"; ?>>
		</label>
	</p>
	<br>
	<?php /*  Realized that there is a better way
	<p>
		<h3><?php p($l->t('Users which needs Approvement')); ?></h3>
		
		<table class="grid">
			<thead>
			<tr>
				<th id="headerEmail" scope="col"><?php p($l->t('Email')); ?></th>
				<th id="headerUsername" scope="col"><?php p($l->t('Username')); ?></th>
				<th id="headerDate" scope="col"><?php p($l->t('Registration Date')); ?></th>
				<th id="headerApprove" scope="col"><?php p($l->t('Approve')); ?></th>
			</tr>
			</thead>
			<tbody>

		
			<?php	foreach ( $_['registrations_needs_approvement'] as $registration ) { ?>
			
				<tr>
					<td><?php echo $registration->getEmail(); ?> </td>
					<td><?php echo $registration->getUsername(); ?> </td>
					<td><?php echo $registration->getRequested(); ?> </td>
					
					<td>
						<button type="submit" class ="approveBtn" value="<?php echo $registration->getEmail(); ?>"><?php p($l->t('Approve')); ?></button>
					</td>
				</tr>
			
			<?php } ?>
			</tbody>
		</table>
	</p> */ ?>
	
</form>
