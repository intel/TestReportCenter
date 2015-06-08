<?php if(!is_null($test_session->getEditorId())): ?>
<a href="<?php echo url_for("edit_user", array("id" => $test_session->getEditorId())); ?>"><?php echo Doctrine_Core::getTable("sfGuardUser")->findOneById($test_session->getEditorId())->getUsername(); ?></a>
<?php endif; ?>