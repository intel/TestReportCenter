<div id="page">
	<div id="wizard_progress" class="page_header">
		<strong>Publish test report:</strong>
		<span id="wizard_upload">
			<span id="upload">Upload</span>
			<strong>»</strong>
			<span id="finalize">Finalize</span>
			<strong>»</strong>
			<span id="publish">Publish</span>
		</span>
	</div>

	<div class="page_content">
		<form name="add_report" id="upload_report" class="" method="post" enctype="multipart/form-data" action="<?php echo url_for("add_report") ?>" accept-charset="UTF-8">
			<h1>New Test Report</h1>

			<div class="field last">
				* Mandatory field
			</div>

			<?php echo $form['_csrf_token']->render() ?>
			<?php echo $form["project_group_id"]->render() ?>

			<div class="field">
				<?php echo $form["name"]->renderLabel(); ?>
				<?php echo $form["name"]->render(array("class" => "text", "size" => "30")); ?>
				<div class="formError"><?php echo $form["name"]->renderError(); ?></div>
			</div>

			<div class="field">
				<?php if($mandatoryBuildId): ?>
					<?php echo $form["build_id"]->renderLabel(null, array("class" => "mandatory")); ?>
				<?php else: ?>
					<?php echo $form["build_id"]->renderLabel(); ?>
				<?php endif; ?>
				<?php echo $form["build_id"]->render(array("class" => "text", "size" => "30")); ?>
				<?php if(count($lastBuildIds) > 0): ?>
				<small class="suggestions">e.g. <?php foreach($lastBuildIds as $lastBuildId): ?><a href="javascript:;"
					onClick="fillField('test_session_build_id', '<?php echo $lastBuildId["build_id"]?>')">
					<?php echo $lastBuildId["build_id"]?></a>, <?php endforeach; ?>...</small>
				<?php endif; ?>
				<div class="formError"><?php echo $form["build_id"]->renderError(); ?></div>
			</div>

			<div class="field">
				<?php if($mandatoryTestset): ?>
					<?php echo $form["testset"]->renderLabel(null, array("class" => "mandatory")); ?>
				<?php else: ?>
					<?php echo $form["testset"]->renderLabel(); ?>
				<?php endif; ?>
				<?php echo $form["testset"]->render(array("class" => "text", "size" => "30")); ?>
				<?php if(count($lastTestsets) > 0): ?>
				<small class="suggestions">e.g. <?php foreach($lastTestsets as $lastTestset): ?><a href="javascript:;"
					onClick="fillField('test_session_testset', '<?php echo $lastTestset["testset"]?>')">
					<?php echo $lastTestset["testset"]?></a>, <?php endforeach; ?>...</small>
				<?php endif; ?>
				<div class="formError"><?php echo $form["testset"]->renderError(); ?></div>
			</div>

			<div class="field">
				<?php echo $form['project']->renderLabel(null, array("class" => "mandatory")); ?>
				<?php echo $form['project']->render(array('class' => 'version', 'onClick' => "selectProducts(this, ".$projectGroupId.")")); ?>
			</div>

			<div class="field">
				<?php echo $form["product"]->renderLabel(null, array("class" => "mandatory")); ?>
				<div id="productsList">
				    <?php echo $form["product"]->render(array('class' => 'version')); ?>
				</div>
				<img id="loader" src="<?php echo image_path('ajax-loader.gif'); ?>" style="vertical-align: middle; display: none" />
			</div>

			<div class="field">
				<?php echo $form["date"]->renderLabel(null, array("class" => "mandatory")); ?>
				<?php echo $form["date"]->render(); ?>
				<div class="formError"><?php echo $form["date"]->renderError(); ?></div>
			</div>

			<div class="field">
				<div style="display: none">
					<?php foreach($lastEnvironments as $lastEnvironment): ?>
						<div id="hiddenEnvironment_<?php echo $lastEnvironment["name_slug"]; ?>">
							<p id="hiddenEnvironment_<?php echo $lastEnvironment["name_slug"]; ?>_name"><?php echo $lastEnvironment["name"]; ?></p>
							<p id="hiddenEnvironment_<?php echo $lastEnvironment["name_slug"]; ?>_description"><?php echo $lastEnvironment["description"]; ?></p>
							<p id="hiddenEnvironment_<?php echo $lastEnvironment["name_slug"]; ?>_cpu"><?php echo $lastEnvironment["cpu"]; ?></p>
							<p id="hiddenEnvironment_<?php echo $lastEnvironment["name_slug"]; ?>_board"><?php echo $lastEnvironment["board"]; ?></p>
							<p id="hiddenEnvironment_<?php echo $lastEnvironment["name_slug"]; ?>_gpu"><?php echo $lastEnvironment["gpu"]; ?></p>
							<p id="hiddenEnvironment_<?php echo $lastEnvironment["name_slug"]; ?>_other_hardware"><?php echo $lastEnvironment["other_hardware"]; ?></p>
						</div>
					<?php endforeach; ?>
				</div>

				<div>
					<?php echo $form["environmentForm"]["name"]->renderLabel(null, array("class" => "mandatory")); ?>
					<?php echo $form["environmentForm"]["name"]->render(array("class" => "text", "size" => "30")); ?>
					<small class="suggestions">e.g. <?php foreach($lastEnvironments as $lastEnvironment): ?><a href="javascript:;"
						onClick="fillEnvironmentField('hiddenEnvironment_<?php echo $lastEnvironment["name_slug"]; ?>_', 'test_session_environmentForm_')">
						<?php echo $lastEnvironment["name"]?></a>, <?php endforeach; ?>...</small>
					<div class="formError"><?php echo $form["environmentForm"]["name"]->renderError(); ?></div>
				</div>

				<div style="margin-left: 210px; margin-bottom: 10px;">
					<small><a id="more_environment_details" href="javascript:;" onClick="showHide(this, 'additional_environment_fields')" title="More details">+ More details</a></small>
					<div style="clear: both"></div>
				</div>

				<div id="additional_environment_fields" style="display: none">
					<div style="margin-bottom: 10px;">
						<?php echo $form["environmentForm"]["description"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["description"]->render(); ?>
						<div class="formError"><?php echo $form["environmentForm"]["description"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["environmentForm"]["cpu"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["cpu"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["environmentForm"]["cpu"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["environmentForm"]["board"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["board"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["environmentForm"]["board"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["environmentForm"]["gpu"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["gpu"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["environmentForm"]["gpu"]->renderError(); ?></div>
					</div>

					<div>
						<?php echo $form["environmentForm"]["other_hardware"]->renderLabel(); ?>
						<?php echo $form["environmentForm"]["other_hardware"]->render(); ?>
						<div class="formError"><?php echo $form["environmentForm"]["other_hardware"]->renderError(); ?></div>
					</div>
				</div>
			</div>

			<div class="field">
				<div style="display: none">
					<?php foreach($lastImages as $lastImage): ?>
						<div id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>">
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_name"><?php echo $lastImage["name"]; ?></p>
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_description"><?php echo $lastImage["description"]; ?></p>
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_os"><?php echo $lastImage["os"]; ?></p>
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_distribution"><?php echo $lastImage["distribution"]; ?></p>
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_version"><?php echo $lastImage["version"]; ?></p>
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_kernel"><?php echo $lastImage["kernel"]; ?></p>
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_architecture"><?php echo $lastImage["architecture"]; ?></p>
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_other_fw"><?php echo $lastImage["other_fw"]; ?></p>
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_binary_link"><?php echo $lastImage["binary_link"]; ?></p>
							<p id="hiddenImage_<?php echo $lastImage["name_slug"]; ?>_source_link"><?php echo $lastImage["source_link"]; ?></p>
						</div>
					<?php endforeach; ?>
				</div>

				<div>
					<?php echo $form["imageForm"]["name"]->renderLabel(null, array("class" => "mandatory")); ?>
					<?php echo $form["imageForm"]["name"]->render(array("class" => "text", "size" => "30")); ?>
					<small class="suggestions">e.g. <?php foreach($lastImages as $lastImage): ?><a href="javascript:;"
						onClick="fillImageField('hiddenImage_<?php echo $lastImage["name_slug"]; ?>_', 'test_session_imageForm_')">
						<?php echo $lastImage["name"]?></a>, <?php endforeach; ?>...</small>
					<div class="formError"><?php echo $form["imageForm"]["name"]->renderError(); ?></div>
				</div>

				<div style="margin-left: 210px; margin-bottom: 10px;">
					<small><a id="more_image_details" href="javascript:;" onClick="showHide(this, 'additional_image_fields')" title="More details">+ More details</a></small>
					<div style="clear: both"></div>
				</div>

				<div id="additional_image_fields" style="display: none">
					<div style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["description"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["description"]->render(); ?>
						<div class="formError"><?php echo $form["imageForm"]["description"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["os"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["os"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["os"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["distribution"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["distribution"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["distribution"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["version"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["version"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["version"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["kernel"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["kernel"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["kernel"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["architecture"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["architecture"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["architecture"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["other_fw"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["other_fw"]->render(); ?>
						<div class="formError"><?php echo $form["imageForm"]["other_fw"]->renderError(); ?></div>
					</div>

					<div style="margin-bottom: 10px;">
						<?php echo $form["imageForm"]["binary_link"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["binary_link"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["binary_link"]->renderError(); ?></div>
					</div>

					<div>
						<?php echo $form["imageForm"]["source_link"]->renderLabel(); ?>
						<?php echo $form["imageForm"]["source_link"]->render(array("class" => "text", "size" => "30")); ?>
						<div class="formError"><?php echo $form["imageForm"]["source_link"]->renderError(); ?></div>
					</div>
				</div>
			</div>

			<div class="field last">
				<div id="drag_drop_area" class="indented">
					<input style="margin-bottom: 10px;" type="file" value="" id="uploadInput" name="upload[]" onChange="validate(this.form); listUploads('uploadInput', 'uploadList')" multiple>
					<ul id="uploadList" class="file_list item_list attachment"><li></li></ul>
					<small>See example
						<a href="<?php echo $sf_request->getUriPrefix().$sf_request->getRelativeUrlRoot()."/uploads/example.xml"; ?>">xml</a>,
						<a href="<?php echo $sf_request->getUriPrefix().$sf_request->getRelativeUrlRoot()."/uploads/example.csv"; ?>">csv</a> and
						<a href="http://meego.gitorious.org/meego-quality-assurance/test-definition/blobs/master/src/data/testdefinition-results.xsd">dataset specification</a> for reference
					</small>
				</div>
			</div>

			<div id="wizard_actions">
				<div id="wizard_buttons">
					<input class="big_btn cancel" type="button" value="Cancel" onclick="location.href='<?php echo $sf_request->getReferer(); ?>'">
					<input id="upload_report_submit" class="big_btn next" type="submit" value="Next" name="commit" disabled>
				</div>

				<p class="next_step">next step:<br/><strong>Finalize</strong></p>
			</div>
		</form>
	</div>
</div>

<div id="tempContainer"></div>

<script>
	function showHide(linkElement, divId)
	{
		var element = document.getElementById(divId);

		if(element)
		{
			if(element.style.display == "none")
			{
				linkElement.innerHTML = "- Less details";
				element.style.display = "block";
			}
			else
			{
				linkElement.innerHTML = "+ More details";
				element.style.display = "none";
			}
		}
	}

	function fillField(destinationId, value)
	{
		document.getElementById(destinationId).value = value;
	}

	function fillEnvironmentField(sourceId, destinationId)
	{
		document.getElementById(destinationId + "name").value = document.getElementById(sourceId + "name").innerHTML;
		document.getElementById(destinationId + "description").value = document.getElementById(sourceId + "description").innerHTML;
		document.getElementById(destinationId + "cpu").value = document.getElementById(sourceId + "cpu").innerHTML;
		document.getElementById(destinationId + "board").value = document.getElementById(sourceId + "board").innerHTML;
		document.getElementById(destinationId + "gpu").value = document.getElementById(sourceId + "gpu").innerHTML;
		document.getElementById(destinationId + "other_hardware").value = document.getElementById(sourceId + "other_hardware").innerHTML;

		document.getElementById("more_environment_details").innerHTML = "- Less details";
		document.getElementById("additional_environment_fields").style.display = "block";
	}

	function fillImageField(sourceId, destinationId)
	{
		document.getElementById(destinationId + "name").value = document.getElementById(sourceId + "name").innerHTML;
		document.getElementById(destinationId + "description").value = document.getElementById(sourceId + "description").innerHTML;
		document.getElementById(destinationId + "os").value = document.getElementById(sourceId + "os").innerHTML;
		document.getElementById(destinationId + "distribution").value = document.getElementById(sourceId + "distribution").innerHTML;
		document.getElementById(destinationId + "version").value = document.getElementById(sourceId + "version").innerHTML;
		document.getElementById(destinationId + "kernel").value = document.getElementById(sourceId + "kernel").innerHTML;
		document.getElementById(destinationId + "architecture").value = document.getElementById(sourceId + "architecture").innerHTML;
		document.getElementById(destinationId + "other_fw").value = document.getElementById(sourceId + "other_fw").innerHTML;
		document.getElementById(destinationId + "binary_link").value = document.getElementById(sourceId + "binary_link").innerHTML;
		document.getElementById(destinationId + "source_link").value = document.getElementById(sourceId + "source_link").innerHTML;

		document.getElementById("more_image_details").innerHTML = "- Less details";
		document.getElementById("additional_image_fields").style.display = "block";
	}

	function selectProducts(element, projectGroupId)
	{
	    var url = "<?php echo url_for("search_products", array("projectGroupId" => $projectGroupId, "projectId" => "0")); ?>";
	    var id = element.id.match(/[0-9]*$/);
	    url = url.replace(/[0-9]*$/g, '');

	    $('#loader').show();
	    $('#productsList').hide();
        $('#productsList').load(
	        url + id,
	        { 'projectGroupId': projectGroupId, 'productId': element.value },
	        function() { $('#loader').hide(); $('#productsList').show(); }
        );
	}

	function validate()
	{
	    var isNotValid = false;

	    var flds = new Array('upload[]');
	    var e = document.forms['add_report'].elements;

	    for (var i = 0; i < flds.length; i++ )
		{
	        if (e[ flds[ i ] ].value.length == 0)
		    {
			    isNotValid = true;
		    }
	    }
	    e['commit'].disabled = isNotValid;
	}

	function listUploads(inputId, listId)
	{
		var input = document.getElementById(inputId);
		var ul = document.getElementById(listId);
		while (ul.hasChildNodes()) {
			ul.removeChild(ul.firstChild);
		}
		for (var i = 0; i < input.files.length; i++) {
			var li = document.createElement("li");
			li.innerHTML = input.files[i].name;
			ul.appendChild(li);
		}
		if(!ul.hasChildNodes()) {
			var li = document.createElement("li");
			li.innerHTML = 'No Files Selected';
			ul.appendChild(li);
		}
	}
</script>
