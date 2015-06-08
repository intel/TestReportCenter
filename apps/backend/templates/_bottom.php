<div id="bottom">

	<div style="text-align: center">
		<p style="margin-bottom: 0px; font-weight: bold; color: #aaa"><?php echo sfConfig::get("app_project_group"); ?> <?php echo sfConfig::get('app_project_name'); ?></p>
		<p style="color: #bbb; margin-bottom: 0px;">rev. <?php echo exec("git rev-parse --short HEAD"); ?></p>
		<p><?php echo mail_to(sfConfig::get("app_admin_contact"), "Contact administrator", "encode=true"); ?></p>
	</div>
</div> <!-- /bottom -->