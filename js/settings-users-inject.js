(function() {
	var GROUPLIST_TEMPLATE = 
	'<li data-gid="registration" data-usercount="{{count}}" class="">' +
	'<a id="pending-reg" href="#" class="">' +
	'<span class="groupname" style="font-weight: bold;">{{label}}</span>' +
	'</a>' +
	'<span class="utils">' +
	'<span class="usercount">{{count}}</span>' +
	'</span>' +
	'</li>';

	var PENDING_USER = '';
	var REG_CONTENT = 
	'<div id="reg-content" style="display: none; overflow-y: auto;">' +
	'	<table id="reglist" class="grid">' +
	'		<thead>' +
	'			<tr>' +
	'				<th>{{label-username}}</th>' +
	'				<th>{{label-email}}</th>' +
	'				<th>{{label-emailvalidated}}</th>' +
	'				<th>{{label-actions}}</th>' +
	'			</tr>' +
	'		</thead>' +
	'		<tbody>' +
	'			{{#each users}}' +
	'			<tr>' +
	'				<td class="reg-username">{{this.username}}</td>' +
	'				<td class="reg-email">{{this.email}}</td>' +
	'				<td>{{#if this.email_validated}}' +
	'					<i class="reg-approve icon-checkmark"></i>'+
	'					{{else}}'+
	'					<i class="reg-deny icon-close"></i>'+
	'					{{/if}}</td>' +
	'				<td>'+
	'				<a class="reg-approve icon-checkmark">{{../label-approve}}</a> '+
	'				<a class="reg-delete icon-delete">{{../label-delete}}</a>' +
	'				</td>' +
	'			</tr>' +
	'			{{/each}}' +
	'		</tbody>' +
	'	</table>' +
	'</div>';

	$(document).ready(function() {
		var grouplist_template = Handlebars.compile(GROUPLIST_TEMPLATE);
		var reg_content = Handlebars.compile(REG_CONTENT);
		
		var reg_params = {
			'label-username': t('registration', 'Username'),
			'label-email': t('registration', 'Email'),
			'label-emailvalidated': t('registration', 'Email verified'),
			'label-actions': t('registration', 'Actions'),
			'label-approve': t('registration', 'Approve'),
			'label-delete': t('registration', 'Delete'),
			'users': [
				{'username': 'error', 'email': 'error', 'email_validated': false},
			],
		};
		
		//TODO remove hardcoded urls
		$.getJSON( "/index.php/apps/registration/getRegistrations", function(data) {		
			reg_params.users = data;
	//		console.log(reg_params);			
	
			$('#app-content').after(reg_content(reg_params));
						
			$(".reg-delete").click(function() {
				var row = $(this).parent().parent();
				var username = row.find("td.reg-username").text();				
			    $.post( "/index.php/apps/registration/deleteRegistration/" + username, function(data) {
					console.log(data);
					row.remove();
			   });
			});
			
			$(".reg-approve").click(function() {
				var row = $(this).parent().parent();
				var username = row.find("td.reg-username").text();				
			    $.post( "/index.php/apps/registration/approveRegistration/" + username, function(data) {
					console.log(data);
					row.remove();
			   });
			});
			
			var regcount = Object.keys(reg_params.users).length;
					
			$('#newgroup-init').after(grouplist_template({'count': regcount, 'label': t('registration', 'Pending registration request')}));
								
			$('#pending-reg').click(function() {
				$('#app-content').hide();
				$('#reg-content').show();
			});
			$('#usergrouplist').on('click', '.isgroup', function () {
				$('#app-content').show();
				$('#reg-content').hide();
			});
		});
	
	});
})();
