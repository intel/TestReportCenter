<div id="upper_header_wrap">
	<div id="header_comparison_box">
		<?php include_partial("reports/comparisonCart"); ?>
	</div>

	<div id="upper_header">
		<ul class="h-navi">
			<li><?php echo mail_to(sfConfig::get("app_admin_contact"), "Give feedback", "encode=true class='mailto'"); ?></li>
			<li><a class="external" href="<?php echo sfConfig::get("app_documentation_url"); ?>" title="Documentation">Documentation</a></li>
			<li><a class="external" href="<?php echo sfConfig::get("app_submit_idea_url"); ?>" title="Submit an idea">Submit an idea</a></li>
			<?php if(sfConfig::get("app_wats_url", "") != ""): ?>
				<li><a href="<?php echo sfConfig::get("app_wats_url"); ?>" title="WATS"><img src="<?php echo image_path('tux_qareport.png'); ?>" alt="WATS" width=15 /></a></li>
			<?php endif; ?>
			<?php if(!$sf_user->isAuthenticated()): ?>
				<li><a class="ui_btn" href="<?php echo url_for('sf_guard_signin'); ?>" title="Sign in">Sign in</a></li>
			<?php else: ?>
				<li>
					<span class="username">
						<a href="<?php echo url_for("profile"); ?>" title="Edit profile">
							<?php if($sf_user->getGuardUser()->getFirstName() != "" && $sf_user->getGuardUser()->getFirstName() != ""): ?>
								<?php echo $sf_user->getGuardUser()->getFirstName()." ".$sf_user->getGuardUser()->getLastName(); ?>
							<?php else: ?>
								<?php echo $sf_user->getGuardUser()->getUsername(); ?>
							<?php endif; ?>
						</a>
					</span>
					<a class="ui_btn" href="<?php echo url_for('sf_guard_signout'); ?>" title="Sign out">Sign out</a>
				</li>
				<?php if($sf_user->getGuardUser()->getIsSuperAdmin()): ?>
					<li>
						<a class="ui_btn" href="<?php echo url_for( $sf_context->getConfiguration()->generateBackendUrl('sf_guard_user') ); ?>" title="Admin">Switch to Admin</a>
					</li>
				<?php endif; ?>
			<?php endif; ?>
		</ul> <!-- /h-navi -->
	</div> <!-- /upper_header -->
</div> <!-- /upper_header_wrap -->
