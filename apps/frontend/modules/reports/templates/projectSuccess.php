<?php include_partial("reports/navigation", array("projects" => $projects, "currentProject" => $currentProject, "currentFilter" => $currentFilter, "products" => $products)); ?>

<div id="page">
	<div id="index_page">
		<div class="index_links">
			<span>Go to: </span>
			<a id="build_index_link" href="<?php echo url_for("project_builds", array("project" => $currentProject["name_slug"])); ?>" title="Build index">Build index</a>
			<a id="build_index_link" href="<?php echo url_for("project_testsets", array("project" => $currentProject["name_slug"])); ?>" title="Testset index">Testset index</a>
		</div>

		<div style="position: relative">
			<a href="javascript:;" onclick="editEnvironmentsImages(1);" id="home_edit_link" title="Edit"></a>
			<a class="small_btn" href="javascript:;" onclick="editEnvironmentsImages(0);" id="home_edit_done_link" title="Done">Done</a>
		</div>

		<table id="report_navigation">
			<thead>
				<tr class="profiles">
					<?php foreach($products as $product): ?>
						<th><a href="<?php echo url_for("product_reports", array("project" => $currentProject["name_slug"], "product" => $product["name_slug"])); ?>" title="<?php echo $product["name"]; ?>"><?php echo $product["name"]; ?></a></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<tr class="profiles">
					<?php foreach($products as $product): ?>
						<?php $previousEnvironment = null; ?>
						<td class="testsets">

							<?php foreach($imagesForEnvironments as $data): ?>

								<?php if($data["product_id"] == $product["id"]): ?>
									<?php if($data["te_name"] != $previousEnvironment): ?>
										</ul> <!-- Lazy non valid xHTML strict thing -->
										<div>
											<a class="name editable_text" style="display: none" href="javascript:;" onclick="updateEnvironmentImage('<?php echo url_for("update_environment", array("id" => $data["te_id"])); ?>')" title="Edit test environment"><?php echo $data["te_name"]; ?></a>
											<a class="name" href="<?php echo url_for("environment_reports", array("project" => $currentProject["name_slug"], "product" => $product["name_slug"], "environment" => $data["te_slug"])); ?>" title="<?php echo $data["te_name"]; ?>"><?php echo $data["te_name"]; ?></a>
										</div>

										<ul class="products">

										<?php $previousEnvironment = $data["te_name"]; ?>
									<?php endif; ?>

											<li>
												<a class="name editable_text" style="display: none;" href="javascript:;" onclick="updateEnvironmentImage('<?php echo url_for("update_image", array("id" => $data["i_id"])); ?>')" title="Edit image"><?php echo $data["i_name"]; ?></a>
												<a class="name" href="<?php echo url_for("image_reports", array("project" => $currentProject["name_slug"], "product" => $product["name_slug"], "environment" => $data["te_slug"], "image" => $data["i_slug"])); ?>" title="<?php echo $data["i_name"]; ?>"><?php echo $data["i_name"]; ?></a>
											</li>

								<?php endif; ?>

							<?php endforeach; ?>

						</td>
					<?php endforeach; ?>
				</tr>
			</tbody>
		</table>
	</div> <!-- /index_page -->
</div> <!-- /page -->

<script>
	function editEnvironmentsImages(editionMode)
	{
		var elems = document.getElementsByTagName('a'), i;

		if(editionMode == 0)
		{
			document.getElementById('home_edit_link').style.display = 'inline';
			document.getElementById('home_edit_done_link').style.display = 'none';

		    for (i in elems)
		    {
		        if((' ' + elems[i].className + ' ').indexOf(' ' + 'name editable_text' + ' ') > -1)
		        {
		            elems[i].style.display = 'none';
		        }
		        else if((' ' + elems[i].className + ' ').indexOf(' ' + 'name' + ' ') > -1)
		        {
		            elems[i].style.display = 'inherit';
		        }
		    }
		}
		else if(editionMode == 1)
		{
			document.getElementById('home_edit_link').style.display = 'none';
			document.getElementById('home_edit_done_link').style.display = 'inline';

			for (i in elems)
		    {
		        if((' ' + elems[i].className + ' ').indexOf(' ' + 'name editable_text' + ' ') > -1)
		        {
		            elems[i].style.display = 'inherit';
		        }
		        else if((' ' + elems[i].className + ' ').indexOf(' ' + 'name' + ' ') > -1)
		        {
		            elems[i].style.display = 'none';
		        }
		    }
		}
	}

	var popup = null;

	function updateEnvironmentImage(url)
	{
		popup = window.open(url);
	}

	function refreshEnvironmentImage(url)
	{
		location.reload();
		popup.close();
	}
</script>
