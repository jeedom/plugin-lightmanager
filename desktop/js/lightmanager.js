
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});


function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}};
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {};
  }
  if(!_cmd.logicalId){
    _cmd.logicalId = 'mode';
    _cmd.type = 'action';
    _cmd.subType = 'other';
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
  tr += '<span class="cmdAttr" data-l1key="logicalId" style="display:none;"></span>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
  tr += '</td>';
  tr += '<td>';
  tr += '<span class="type" type="' + init(_cmd.type) + '" style="display:none;">' + jeedom.cmd.availableType() + '</span>';
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '" style="display:none;"></span>';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
  tr += '</td>';
  tr += '<td>';
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
  }
  tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
  tr += '</td>';
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
  if (isset(_cmd.type)) {
    $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
  }
  jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

$("#div_lights").off('click','.bt_removeLight').on('click','.bt_removeLight',  function () {
  $(this).closest('.light').remove();
});

$("#div_motions").off('click','.bt_removeMotion').on('click','.bt_removeMotion',  function () {
  $(this).closest('.motion').remove();
});

$("#div_luminositys").off('click','.bt_removeLuminosity').on('click','.bt_removeLuminosity',  function () {
  $(this).closest('.luminosity').remove();
});

$('#bt_addLight').off('click').on('click', function () {
  addLight({});
});

$('#bt_addMotion').off('click').on('click', function () {
  addMotion({});
});

$('#bt_addLuminosity').off('click').on('click', function () {
  addLuminosity({});
});

$("#div_mainContainer").off('click','.listCmdInfo').on('click','.listCmdInfo',  function () {
  var el = $(this).closest('.input-group').find('input.form-control');
  jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
    el.value(result.human);
  });
});

$("#div_mainContainer").off('click','.listCmdAction').on('click','.listCmdAction',  function () {
  var el = $(this).closest('.input-group').find('input.form-control');
  jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
    el.value(result.human);
    if(el.closest('.form-group').find('.actionOptions').html() != undefined){
      jeedom.cmd.displayActionOption(result.human, '', function (html) {
        el.closest('.form-group').find('.actionOptions').html(html);
        taAutosize();
      });
    }
  });
});

$('body').off('focusout','.expressionAttr[data-l1key=cmdOn]').on('focusout','.expressionAttr[data-l1key=cmdOn]',  function (event) {
  var el = $(this).closest('.input-group').find('input.form-control');
  var expression = $(this).closest('.form-group').getValues('.expressionAttr');
  jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
    el.closest('.form-group').find('.actionOptions').html(html);
    taAutosize();
  })
});

function addLight(_light) {
  if (!isset(_light)) {
    _light = {};
  }
  var div = '<div class="light">';
  div += '<form class="form-horizontal">';
  div += '<fieldset>';
  div += '<legend>{{Lumière}}</legend>';
  div += '<div class="form-group">';
  div += '<label class="col-sm-1 control-label">{{On}}</label>';
  div += '<div class="col-sm-3">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<input type="checkbox" class="expressionAttr" data-l1key="enable" checked />';
  div += '<a class="btn btn-default bt_removeLight btn-sm roundedLeft"><i class="fa fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="expressionAttr form-control input-sm" data-l1key="cmdOn" />';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-sm listCmdAction btn-success roundedRight"><i class="fa fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  var actionOption_id = uniqId();
  div += '<div class="col-sm-5 actionOptions" id="'+actionOption_id+'"></div>';
  div += '</div>';
  div += '<div class="form-group">';
  div += '<label class="col-sm-1 control-label">{{Off}}</label>';
  div += '<div class="col-sm-3">';
  div += '<div class="input-group">';
  div += '<input class="expressionAttr form-control input-sm" data-l1key="cmdOff" />';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-sm listCmdAction btn-success roundedRight"><i class="fa fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  div += '<div class="form-group">';
  div += '<label class="col-sm-1 control-label">{{Etat}}</label>';
  div += '<div class="col-sm-3">';
  div += '<div class="input-group">';
  div += '<input class="expressionAttr form-control input-sm" data-l1key="cmdState" />';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-sm listCmdInfo btn-success roundedRight"><i class="fa fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  div += '</fieldset>';
  div += '</form>';
  div += '</div>';
  $('#div_lights').append(div);
  $('#div_lights').find('.light').last().setValues(_light, '.expressionAttr');
  actionOptions.push({
    expression : init(_light.cmdOn, ''),
    options : _light.options,
    id : actionOption_id
  });
}

function addMotion(_motion) {
  if (!isset(_motion)) {
    _motion = {};
  }
  var div = '<div class="motion">';
  div += '<div class="form-group">';
  div += '<label class="col-sm-1 control-label">{{Mouvement}}</label>';
  div += '<div class="col-sm-3">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<input type="checkbox" class="motionAttr" data-l1key="enable" checked />';
  div += '<a class="btn btn-default bt_removeMotion btn-sm roundedLeft"><i class="fa fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="motionAttr form-control input-sm" data-l1key="cmdMotion" />';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-sm listCmdInfo btn-success roundedRight"><i class="fa fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  $('#div_motions').append(div);
  $('#div_motions').find('.motion').last().setValues(_motion, '.motionAttr');
}

function addLuminosity(_luminosity) {
  if (!isset(_luminosity)) {
    _luminosity = {};
  }
  var div = '<div class="luminosity">';
  div += '<div class="form-group">';
  div += '<label class="col-sm-1 control-label">{{Luminosité}}</label>';
  div += '<div class="col-sm-3">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<input type="checkbox" class="luminosityAttr" data-l1key="enable" checked />';
  div += '<a class="btn btn-default bt_removeLuminosity btn-sm roundedLeft"><i class="fa fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="luminosityAttr form-control input-sm" data-l1key="cmdLuminosity" />';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-sm listCmdInfo btn-success roundedRight"><i class="fa fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  div += '<label class="col-sm-1 control-label">{{Seuil}}</label>';
  div += '<div class="col-sm-1 has-success">';
  div += '<input class="luminosityAttr form-control input-sm" data-l1key="threshold" value="0" />';
  div += '</div>';
  div += '<label class="col-sm-1 control-label">{{Sur les X dernieres minutes}}</label>';
  div += '<div class="col-sm-1 has-success">';
  div += '<input class="luminosityAttr form-control input-sm" data-l1key="min_last_min" value="0" />';
  div += '</div>';
  div += '</div>';
  $('#div_luminositys').append(div);
  $('#div_luminositys').find('.luminosity').last().setValues(_luminosity, '.luminosityAttr');
}

function printEqLogic(_eqLogic) {
  actionOptions = []
  $('#div_lights').empty();
  $('#div_luminositys').empty();
  $('#div_motions').empty();
  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.lights)) {
      for (var i in _eqLogic.configuration.lights) {
        addLight(_eqLogic.configuration.lights[i]);
      }
    }
    if (isset(_eqLogic.configuration.motions)) {
      for (var i in _eqLogic.configuration.motions) {
        addMotion(_eqLogic.configuration.motions[i]);
      }
    }
    if (isset(_eqLogic.configuration.luminositys)) {
      for (var i in _eqLogic.configuration.luminositys) {
        addLuminosity(_eqLogic.configuration.luminositys[i]);
      }
    }
  }
  jeedom.cmd.displayActionsOption({
    params : actionOptions,
    async : false,
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success : function(data){
      for(var i in data){
        $('#'+data[i].id).append(data[i].html.html);
      }
      taAutosize();
    }
  });
}

function saveEqLogic(_eqLogic) {
  if (!isset(_eqLogic.configuration)) {
    _eqLogic.configuration = {};
  }
  _eqLogic.configuration.lights = $('#div_lights .light').getValues('.expressionAttr');
  _eqLogic.configuration.motions = $('#div_motions .motion').getValues('.motionAttr');
  _eqLogic.configuration.luminositys= $('#div_luminositys .luminosity').getValues('.luminosityAttr');
  return _eqLogic;
}
