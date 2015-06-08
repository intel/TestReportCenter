<?php include_partial("reports/navigation", array("projects" => $projects, "currentProject" => $currentProject, "currentFilter" => $currentFilter, "products" => $products)); ?>

<div id="page">
	<div id="index_page">
		<div class="index_links">
			<span>Go to: </span>
			<a id="build_index_link" href="<?php echo url_for("project_reports", array("project" => $currentProject["name_slug"])); ?>" title="Standard index">Standard index</a>
			<a id="build_index_link" href="<?php echo url_for("project_testsets", array("project" => $currentProject["name_slug"])); ?>" title="Testset index">Testset index</a>
		</div>

		<table id="report_navigation">
			<thead>
				<tr class="profiles">
					<?php foreach($products as $product): ?>
						<th><a href="<?php echo url_for("product_builds", array("project" => $currentProject["name_slug"], "product" => $product["name_slug"])); ?>" title="<?php echo $product["name"]; ?>"><?php echo $product["name"]; ?></a></th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<tr class="profiles">
					<?php foreach($products as $product): ?>
						<td class="build_ids">

							<?php foreach($imagesForEnvironments as $data): ?>
								<?php if($data["product_id"] == $product["id"]): ?>

									<?php foreach($data["builds"] as $build): ?>
										<div>
											<a class="name" href="<?php echo url_for("build_reports", array("build" => $build["ts_build_slug"], "project" => $currentProject["name_slug"], "product" => $product["name_slug"])); ?>" title="<?php echo $build["ts_build_id"]; ?>"><?php echo $build["ts_build_id"]; ?></a>
										</div>

										<?php foreach($build["environments"] as $environment): ?>
											<ul class="testsets">
												<div>
													<a class="name" href="<?php echo url_for("environment_builds", array("build" => $build["ts_build_slug"], "project" => $currentProject["name_slug"], "product" => $product["name_slug"], "environment" => $environment["te_slug"])); ?>" title="<?php echo $environment["te_name"]; ?>"><?php echo $environment["te_name"]; ?></a>

													<ul class="products">
														<?php foreach($environment["images"] as $image): ?>
															<li>
																<a class="name" href="<?php echo url_for("image_builds", array("build" => $build["ts_build_slug"], "project" => $currentProject["name_slug"], "product" => $product["name_slug"], "environment" => $environment["te_slug"], "image" => $image["i_slug"])); ?>" title="<?php echo $image["i_name"]; ?>"><?php echo $image["i_name"]; ?></a>
															</li>
														<?php endforeach; ?>
													</ul>
												</div>
											</ul>
										<?php endforeach; ?>
									<?php endforeach; ?>

								<?php endif; ?>
							<?php endforeach; ?>

						</td>
					<?php endforeach; ?>
				</tr>
			</tbody>
		</table>
	</div> <!-- /index_page -->
</div> <!-- /page -->
