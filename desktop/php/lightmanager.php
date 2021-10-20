<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('lightmanager');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes lumières}}</legend>
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Général}}</a></li>
			<li role="presentation"><a href="#lighttab" aria-controls="profile" role="tab" data-toggle="tab"><i class="far fa-lightbulb"></i> {{Lumière}}</a></li>
			<li role="presentation"><a href="#motiontab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-female"></i> {{Mouvement}}</a></li>
			<li role="presentation"><a href="#luminositytab" aria-controls="profile" role="tab" data-toggle="tab"><i class="far fa-sun"></i> {{Luminosité}}</a></li>
			<li role="presentation"><a href="#timmertab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-hourglass-start"></i> {{Temporisation}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br />
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
							<div class="col-sm-3">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement template}}" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Objet parent}}</label>
							<div class="col-sm-3">
								<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
									<option value="">{{Aucun}}</option>
									<?php
									$options = '';
									foreach ((jeeObject::buildTree(null, false)) as $object) {
										$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
									}
									echo $options;
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Catégorie}}</label>
							<div class="col-sm-9">
								<?php
								foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
									echo '<label class="checkbox-inline">';
									echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
									echo '</label>';
								}
								?>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label"></label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Ne rien faire automatiquement si}}</label>
							<div class="col-sm-6">
								<div class="input-group">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="doNothingIf" />
									<span class="input-group-btn">
										<a class="btn listCmdInfo btn-default roundedRight" data-atCaret="1"><i class="fa fa-list-alt"></i></a>
									</span>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="lighttab">
				<a class="btn btn-success btn-xs pull-right" id="bt_addLight"><i class="fas fa-plus-circle"></i> {{Ajouter lumieres}}</a>
				<br /><br />
				<div id="div_lights"></div>
			</div>
			<div role="tabpanel" class="tab-pane" id="motiontab">
				<a class="btn btn-success btn-xs pull-right" id="bt_addMotion"><i class="fas fa-plus-circle"></i> {{Ajouter présence}}</a>
				<br /><br />
				<form class="form-horizontal">
					<fieldset>
						<div id="div_motions"></div>
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="luminositytab">
				<a class="btn btn-success btn-xs pull-right" id="bt_addLuminosity"><i class="fas fa-plus-circle"></i> {{Ajouter luminosité}}</a>
				<br /><br />
				<form class="form-horizontal">
					<fieldset>
						<div id="div_luminositys"></div>
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="timmertab">
				<br />
				<form class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Délai d'extinction après absence (min)}}</label>
							<div class="col-sm-3">
								<input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="delay::off_no_motion" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Débrayage automatique}}</label>
							<div class="col-sm-3">
								<input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="auto_walkout" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">{{Reprendre la main après (min)}}</label>
							<div class="col-sm-3">
								<input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="delay::regain_control" />
							</div>
						</div>
					</fieldset>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br /><br />
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{Nom}}</th>
							<th>{{Options}}</th>
							<th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'lightmanager', 'js', 'lightmanager'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>