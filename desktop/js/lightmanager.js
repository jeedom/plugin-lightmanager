
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

$('#btn_addRazAlarm').off('click').on('click', function () {
  addAction({}, 'raz', '{{Réinitialisation}}');
});

$('#btn_addRazAlarm').off('click').on('click', function () {
  addAction({}, 'raz', '{{Réinitialisation}}');
});

$('#bt_addLuminosity').off('click').on('click', function () {
  addAction({}, 'raz', '{{Réinitialisation}}');
});
