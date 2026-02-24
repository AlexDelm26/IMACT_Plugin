$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true,
});

// Pop-up du bouton Ajouter Led
$("#btn_add_LED").on("click", function () {
  $("#md_modal").dialog({ title: "{{Ajouter LED}}" });
  $("#md_modal")
    .load("index.php?v=d&plugin=ImactPlugin&modal=addLED")
    .dialog("open");
});

// Pop-up du bouton Ajouter Thermostat
$("#btn_add_THERMOSTAT").on("click", function () {
  $("#md_modal").dialog({ title: "{{Ajouter Thermostat}}" });
  $("#md_modal")
    .load("index.php?v=d&plugin=ImactPlugin&modal=addThermostat")
    .dialog("open");
});

$(document).on('click', '.bt_selectEqLogic', function () {
  let input = $(this).data('input');
  let inputElement = $('.eqLogicAttr[data-l1key="' + input + '"]');

  jeedom.eqLogic.getSelectModal({}, function (result) {
    inputElement.val(result.name);
    inputElement.attr('data-eqlogic-id', result.id);
  });
});

function addChampLED(selectorNbLed) {
  let nb_led = document.querySelector(selectorNbLed).value;
  if (nb_led <= 0) {
    alert("Saisissez au moins 1 LED");
    // document.getElementById('led_number').classList.add()
  } else {
    document.getElementById('btn_valider').style.display = 'block';
    let container = document.querySelector("#led_array");
    let html = '<table class="table table-bordered">';
    html += "<thead><tr>";
    html += "<th>{{N°}}</th>";
    html += "<th>{{Equipement}}</th>";
    html += "</tr></thead><tbody>";
    for (let i = 1; i <= nb_led; i++) {

      html += "<tr>";
      html += "<td>" + i + "</td>";

      html += "<td>";
      html += "<div class='input-group'>";

      // IMPORTANT : ajout d'un id correspondant au data-input
      html += "<input type='text' " +
        "class='form-control eqLogicAttr led-equipment' " +
        "data-l1key='equipment_" + i + "' " +
        "id='equipment_" + i + "' " +
        "placeholder='Sélectionner équipement' readonly>";

      html += "<span class='input-group-btn'>";
      html += "<a class='btn btn-default btn-sm bt_selectEqLogic' " +
        "data-input='equipment_" + i + "'>";
      html += "<i class='fas fa-list-alt'></i>";
      html += "</a>";
      html += "</span>";

      html += "</div>";
      html += "</td>";
      html += "</tr>";
    }

    html += "</tbody></table>";
    container.innerHTML = html;

    $(document).off('click', '.bt_selectEqLogic').on('click', '.bt_selectEqLogic', function () {

      var inputId = $(this).data('input');

      jeedom.eqLogic.getSelectModal({}, function (result) {

        if (result) {
          $('#' + inputId)
            .val(result.human)                 // ✅ on stocke l'ID
            .attr('data-eqlogic-id', result.id) // facultatif
            .trigger('change');

          // On affiche le nom lisible
          $('#' + inputId).val(result.human);
        }

      });

    });


  }
}

function addLED() {
  let leds = [];
  let rows = document.querySelectorAll("#led_array tbody tr");
  rows.forEach((row) => {
    let inputEquipement = row.querySelector(".led-equipment")
    leds.push({
      idEquipement: inputEquipement.getAttribute('data-eqlogic-id')
    });
  });

  fetch("plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "addLEDS",
      leds: JSON.stringify(leds),
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.state === "ok") {
        alert(leds.length + " LED(s) créée(s) avec succès test");
        document.querySelector("#md_modal").style.display = "none";
        location.reload();
      } else {
        alert(data.result);
      }
    })
    .catch((error) => {
      console.error("Erreur:", error);
      alert("Erreur lors de la création");
    });
}
function addThermostat() {
  let nb_thermostat = parseInt(document.querySelector('#thermostat_number').value)
  const thermostats = []
  for (let i = 1; i <= nb_thermostat; i++) {
    thermostats.push({
      nomThermostat: document.getElementById('nomThermostat_' + i).value,
      commandePersonnelle: document.getElementById('cmd_custom_' + i)?.getAttribute('data-cmd-id') ?? null,
      temperatureInterieure: document.getElementById('cmd_temp_' + i)?.getAttribute('data-cmd-id') ?? null,
      commandeChauffer: document.getElementById('cmd_heat_' + i)?.getAttribute('data-cmd-id') ?? null,
      commandeArreter: document.getElementById('cmd_stop_' + i)?.getAttribute('data-cmd-id') ?? null,
      commandeConsigne: document.getElementById('cmd_setpoint_' + i)?.getAttribute('data-cmd-id') ?? null,
      consigneZone: document.getElementById('zone_' + i)?.getAttribute('data-eqlogic-id') ?? null
    });
  }
  fetch("plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "addTHERMOSTATS",
      thermostat: JSON.stringify(thermostats)
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.state === "ok") {
        alert(" Thermostats créée(s) avec succès test");
        document.querySelector("#md_modal").style.display = "none";
        location.reload();
      } else {
        alert(data.result);
      }
    })
    .catch((error) => {
      console.error("Erreur:", error);
      alert("Erreur lors de la création :" + error);
    });
}

function addChampThermostat() {
  let nb_thermostat = document.querySelector('#thermostat_number').value;
  if (nb_thermostat <= 0) {
    alert("Saisissez au moins 1 LED");
    // document.getElementById('led_number').classList.add()
  } else {
    document.getElementById('btn_valider').style.display = 'block';
    let container = document.querySelector("#thermostat_array");
    let html = '<table class="table table-bordered">';
    html += "<thead><tr>";
    html += "<th>{{N°}}</th>";
    html += "<th>{{Configuration}}</th>";
    html += "<th>{{Valeur}}</th>";
    html += "</tr></thead><tbody>";

    for (let i = 1; i <= nb_thermostat; i++) {

      // Ligne 1 : Équipement
      html += "<tr style='border-top: 3px solid #337ab7'>";
      html += "<td rowspan='5'>" + i + "</td>";
      html += "<td>{{Nom}}</td>";
      html += "<td>";
      html += "<input type='text' class='form-control' data-l1key='equipment_" + i + "' id='nomThermostat_" + i + "' value='Thermostat - '>";
      html += "</td></tr>";


      // Ligne 2 : Commande personnelle
      html += "<tr>";
      html += "<td>{{Commande personnelle}}</td>";
      html += "<td><div class='input-group'>";
      html += "<input type='text' class='form-control eqLogicAttr' data-l1key='cmd_custom_" + i + "' id='cmd_custom_" + i + "' placeholder='Sélectionner commande' readonly>";
      html += "<span class='input-group-btn'><a class='btn btn-default btn-sm bt_selectCmd' data-input='cmd_custom_" + i + "'><i class='fas fa-list-alt'></i></a></span>";
      html += "</div></td></tr>";


      // Ligne 3 : Température intérieure
      html += "<tr>";
      html += "<td>{{Température intérieure}}</td>";
      html += "<td><div class='input-group'>";
      html += "<input type='text' class='form-control eqLogicAttr' data-l1key='cmd_temp_" + i + "' id='cmd_temp_" + i + "' placeholder='Sélectionner commande' readonly>";
      html += "<span class='input-group-btn'><a class='btn btn-default btn-sm bt_selectCmd' data-input='cmd_temp_" + i + "'><i class='fas fa-list-alt'></i></a></span>";
      html += "</div></td></tr>";

      // Ligne 4 : 3 commandes
      html += "<tr>";
      html += "<td>{{Actions}}</td>";
      html += "<td>";

      // Chauffer
      html += "<label class='label-control'>{{Chauffer}}</label>";
      html += "<div class='input-group' style='margin-bottom:5px'>";
      html += "<input type='text' class='form-control eqLogicAttr' data-l1key='cmd_heat_" + i + "' id='cmd_heat_" + i + "' placeholder='Sélectionner commande' readonly>";
      html += "<span class='input-group-btn'><a class='btn btn-default btn-sm bt_selectCmd' data-input='cmd_heat_" + i + "'><i class='fas fa-list-alt'></i></a></span>";
      html += "</div>";

      // Tout arrêter
      html += "<label class='label-control'>{{Tout arrêter}}</label>";
      html += "<div class='input-group' style='margin-bottom:5px'>";
      html += "<input type='text' class='form-control eqLogicAttr' data-l1key='cmd_stop_" + i + "' id='cmd_stop_" + i + "' placeholder='Sélectionner commande' readonly>";
      html += "<span class='input-group-btn'><a class='btn btn-default btn-sm bt_selectCmd' data-input='cmd_stop_" + i + "'><i class='fas fa-list-alt'></i></a></span>";
      html += "</div>";

      // Changement de consigne
      html += "<label class='label-control'>{{Changement de consigne}}</label>";
      html += "<div class='input-group'>";
      html += "<input type='text' class='form-control eqLogicAttr' data-l1key='cmd_setpoint_" + i + "' id='cmd_setpoint_" + i + "' placeholder='Sélectionner commande' readonly>";
      html += "<span class='input-group-btn'><a class='btn btn-default btn-sm bt_selectCmd' data-input='cmd_setpoint_" + i + "'><i class='fas fa-list-alt'></i></a></span>";
      html += "</div>";

      html += "</td></tr>";

      html += "<tr>";
      html += "<td>{{Zone}}</td>";
      html += "<td><div class='input-group'>";
      html += "<input type='text' class='form-control eqLogicAttr thermostat_zone' data-l1key='zone_" + i + "' id='zone_" + i + "' placeholder='Sélectionner un équipement' readonly>";
      html += "<span class='input-group-btn'><a class='btn btn-default btn-sm bt_selectEqLogic' data-input='zone_" + i + "'><i class='fas fa-list-alt'></i></a></span>";
      html += "</div></td></tr>";

    }

    html += "</tbody></table>";
    container.innerHTML = html;

    $(document).off('click', '.bt_selectEqLogic').on('click', '.bt_selectEqLogic', function () {

      var inputId = $(this).data('input');

      jeedom.eqLogic.getSelectModal({}, function (result) {

        if (result) {
          $('#' + inputId)
            .val(result.human)                 // ✅ on stocke l'ID
            .attr('data-eqlogic-id', result.id) // facultatif
            .trigger('change');

          // On affiche le nom lisible
          $('#' + inputId).val(result.human);
        }

      });

    });
    $(document).off('click', '.bt_selectCmd').on('click', '.bt_selectCmd', function () {
      var inputId = $(this).data('input');

      jeedom.cmd.getSelectModal({}, function (result) {
        console.log('result cmd:', result);
        if (result) {
          $('#' + inputId)
            .val(result.human)
            .attr('data-cmd-id', result.cmd.id)
            .trigger('change');
        }
      });
    });

  }
}

function log() {

  fetch("plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "log"
    }),
  })
    .then((response) => response.text())
    .then((data) => {
      if (data.state === "ok") {
        alert("Les logs sont affichés !");
      } else {
        alert(data.result);
      }
    })
    .catch((error) => {
      console.error("Erreur:", error);
      alert("Erreur lors de l'affichage des logs :");
    });
}

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    let _cmd = { configuration: {} };
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {};
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td class="hidden-xs">';
  tr += '<span class="cmdAttr" data-l1key="id"></span>';
  tr += "</td>";
  tr += "<td>";
  tr += '<div class="input-group">';
  tr +=
    '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">';
  tr +=
    '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>';
  tr +=
    '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>';
  tr += "</div>";
  tr +=
    '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">';
  tr += '<option value="">{{Aucune}}</option>';
  tr += "</select>";
  tr += "</td>";
  tr += "<td>";
  tr +=
    '<span class="type" type="' +
    init(_cmd.type) +
    '">' +
    jeedom.cmd.availableType() +
    "</span>";
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
  tr += "</td>";
  tr += "<td>";
  tr +=
    '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> ';
  tr +=
    '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> ';
  tr +=
    '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> ';
  tr += '<div style="margin-top:7px;">';
  tr +=
    '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">';
  tr +=
    '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">';
  tr +=
    '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">';
  tr += "</div>";
  tr += "</td>";
  tr += "<td>";
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
  tr += "</td>";
  tr += "<td>";
  if (is_numeric(_cmd.id)) {
    tr +=
      '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
    tr +=
      '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
  }
  tr +=
    '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>';
  tr += "</tr>";
  $("#table_cmd tbody").append(tr);
  var tr = $("#table_cmd tbody tr").last();
  jeedom.eqLogic.buildSelectCmd({
    id: $(".eqLogicAttr[data-l1key=id]").value(),
    filter: { type: "info" },
    error: function (error) {
      $("#div_alert").showAlert({ message: error.message, level: "danger" });
    },
    success: function (result) {
      tr.find(".cmdAttr[data-l1key=value]").append(result);
      tr.setValues(_cmd, ".cmdAttr");
      jeedom.cmd.changeType(tr, init(_cmd.subType));
    },
  });
}
