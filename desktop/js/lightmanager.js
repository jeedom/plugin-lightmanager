
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

$("#div_lights").off('click','.bt_removeLight').on('click','.bt_removeLight',  function () {
  $(this).closest('.light').remove();
});

$("#div_presences").off('click','.bt_removeLight').on('click','.bt_removeLight',  function () {
  $(this).closest('.light').remove();
});

$("#div_luminositys").off('click','.bt_removeLuminosity').on('click','.bt_removeLuminosity',  function () {
  $(this).closest('.luminosity').remove();
});

$('#bt_addLight').off('click').on('click', function () {
  addLight({});
});

$('#bt_addPresence').off('click').on('click', function () {
  addPresence({});
});

$('#bt_addLuminosity').off('click').on('click', function () {
  addLuminosity({});
});

$("#div_mainContainer").off('click','.listCmdInfo').on('click','.listCmdInfo',  function () {
  var el = $(this).closest('.input-group').find('input');
  jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
    el.value(result.human);
  });
});

$("#div_mainContainer").off('click','.listCmdAction').on('click','.listCmdAction',  function () {
  var el = $(this).closest('.input-group').find('input');
  jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.form-group').find('.actionOptions').html(html);
      taAutosize();
    });
  });
});

$('body').off('focusout','.lightAttr[data-l1key=cmdOn]').on('focusout','.lightAttr[data-l1key=cmdOn]',  function (event) {
  var el = $(this).closest('.input-group').find('input');
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
  div += '<input type="checkbox" class="lightAttr" data-l1key="enable" checked />';
  div += '<a class="btn btn-default bt_removeTrigger btn-sm roundedLeft"><i class="fa fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="lightAttr form-control input-sm" data-l1key="cmdOn" />';
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
  div += '<input class="lightAttr form-control input-sm" data-l1key="cmdOff" />';
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
  div += '<input class="lightAttr form-control input-sm" data-l1key="cmdState" />';
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
  $('#div_lights').find('.light').last().setValues(_light, '.lightAttr');
  actionOptions.push({
    expression : init(_light.cmdOn, ''),
    options : _light.options,
    id : actionOption_id
  });
}


function printEqLogic(_eqLogic) {
  actionOptions = []
  $('#div_lights').empty();
  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.lights)) {
      for (var i in _eqLogic.configuration.lights) {
        addLight(_eqLogic.configuration.lights[i]);
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
  _eqLogic.configuration.lights = [];
  $('#div_lights .light').each(function () {
    var light = $(this).getValues('.lightAttr')[0];
    _eqLogic.configuration.lights.push(light);
  });
  
  return _eqLogic;
}
