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

// Pop-up du bouton Ajouter vOLET
$("#btn_add_VOLET").on("click", function () {
  $("#md_modal").dialog({ title: "{{Ajouter Volet}}" });
  $("#md_modal")
    .load("index.php?v=d&plugin=ImactPlugin&modal=addVolet")
    .dialog("open");
});

$("#btn_add_AUTOMATE").on("click", function () {
  $("#md_modal").dialog({ title: "{{Ajouter Automate}}" });
  $("#md_modal")
    .load("index.php?v=d&plugin=ImactPlugin&modal=addAutomate")
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
            .val(result.human)
            .attr('data-eqlogic-id', result.id)
            .trigger('change');

          // On affiche le nom lisible
          $('#' + inputId).val(result.human);
        }

      });

    });


  }
}

async function addLED() {
  let leds = [];
  nbLed = document.querySelector('#LED_number').value
  let success = 0

  for (let i = 1; i <= nbLed; i++) {
    let eclairage = document.getElementById('equipment_' + i).getAttribute('data-eqlogic-id')
    console.log(eclairage);

    leds.push({
      idEquipement: eclairage,
    });
  }
  for (const led of leds) {
    try {
      const response = await fetch("plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "addLEDS",
          leds: JSON.stringify(led),
        }),
      })
      const data = await response.json()
      if (data.state === "ok") {
        success++;
        jeedomUtils.showAlert({ message: `${success}/${leds.length} créé(s)...`, level: 'success' });
      } else {
        // jeedomUtils.showAlert({ message: `Erreur thermostat n°${thermostat.numeroThermostat} : ${data.result}`, level: 'danger' }); // catch l'erreur si doublon dans la DB
        alert('erreur')
      }
    } catch (error) {
      // jeedomUtils.showAlert({ message: `Erreur thermostat n°${thermostat.numeroThermostat}`, level: 'danger' });
      alert(error)
    }
  }
  if (success != 0) {
    jeedomUtils.showAlert({ message: `${success}/${leds.length} éclairage(s) créé(s)`, level: 'success' });
    location.reload();
  }
}

async function addThermostat() {
  const btn = document.querySelector('#btn_valider button')
  if (btn.disabled) return;
  btn.disabled = true;
  btn.textContent = 'Création en cours..';
  let nb_thermostat = parseInt(document.querySelector('#thermostat_number').value)
  const thermostats = []
  const thermostatsInvalides = []
  for (let i = 1; i <= nb_thermostat; i++) {
    let nomThermostat = document.getElementById('nomThermostat_' + i).value.trim()
    if (nomThermostat === 'Thermostat -' || nomThermostat === '') {
      thermostatsInvalides.push({
        numeroThermostat: i,
        nomThermostat: nomThermostat
      })
    }
    let commandePersonnelle = document.getElementById('cmd_custom_' + i)?.getAttribute('data-cmd-id') ?? null
    let temperatureInterieure = document.getElementById('cmd_temp_' + i)?.getAttribute('data-cmd-id') ?? null
    let commandeChauffer = document.getElementById('cmd_heat_' + i)?.getAttribute('data-cmd-id') ?? null
    let commandeArreter = document.getElementById('cmd_stop_' + i)?.getAttribute('data-cmd-id') ?? null
    let commandeConsigne = document.getElementById('cmd_setpoint_' + i)?.getAttribute('data-cmd-id') ?? null
    let consigneZone = document.getElementById('zone_' + i)?.getAttribute('data-eqlogic-id') ?? 440 // 8 sur la template | 440 au bureau


    thermostats.push({
      numeroThermostat: i,
      nomThermostat: nomThermostat,
      commandePersonnelle: commandePersonnelle,
      temperatureInterieure: temperatureInterieure,
      commandeChauffer: commandeChauffer,
      commandeArreter: commandeArreter,
      commandeConsigne: commandeConsigne,
      consigneZone: consigneZone,
    });
  }
  if (thermostatsInvalides.length > 0) {
    btn.disabled = false;
    btn.textContent = 'Valider';
    const thermostatsInvalidesLabels = thermostatsInvalides
      .map(t => `n°${t.numeroThermostat} (${t.nomThermostat || 'nom vide'})`);

    jeedomUtils.showAlert({
      message: `Noms invalides : ${thermostatsInvalidesLabels.join(', ')}`,
      level: 'danger'
    });
    return;
  }

  const noms = thermostats.map(t => t.nomThermostat.toLowerCase());
  const doublonsInternes = noms.filter((nom, index) => noms.indexOf(nom) !== index);

  if (doublonsInternes.length > 0) {
    const thermostatsEnDoublon = thermostats
      .filter(t => doublonsInternes.includes(t.nomThermostat.toLowerCase()))
      .map(t => `n°${t.numeroThermostat} (${t.nomThermostat})`);

    jeedomUtils.showAlert({
      message: `Noms en doublon : ${thermostatsEnDoublon.join(', ')}`,
      level: 'danger'
    });
    return;
  }


  let success = 0;
  for (const thermostat of thermostats) {
    try {
      const response = await fetch("plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "addTHERMOSTAT",
          thermostat: JSON.stringify(thermostat)
        }),
      });
      const data = await response.json();
      if (data.state === "ok") {
        success++;
        jeedomUtils.showAlert({ message: `${success}/${thermostats.length} créé(s)...`, level: 'success' });
      } else {
        jeedomUtils.showAlert({ message: `Erreur thermostat n°${thermostat.numeroThermostat} : ${data.result}`, level: 'danger' }); // catch l'erreur si doublon dans la DB
      }
    } catch (error) {
      jeedomUtils.showAlert({ message: `Erreur thermostat n°${thermostat.numeroThermostat}`, level: 'danger' });
    }
  }
  if (success != 0) {
    jeedomUtils.showAlert({ message: `${success}/${thermostats.length} thermostat(s) créé(s)`, level: 'success' });
    location.reload();
  }
  btn.disabled = false;
  btn.textContent = 'Valider';
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
      html += "<span class='input-group-btn'><a class='btn btn-default btn-sm bt_selectCmdInfo' data-input='cmd_custom_" + i + "'><i class='fas fa-list-alt'></i></a></span>";
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
    $(document).off('click', '.bt_selectCmdInfo').on('click', '.bt_selectCmdInfo', function () {
      var inputId = $(this).data('input');

      jeedom.cmd.getSelectModal({cmd:{type:'info'}}, function (result) {
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
function addChampVolet() {
  let nbVolet = document.querySelector('#volet_number').value;
  if (nbVolet <= 0) {
    alert("Saisissez au moins 1 Volet");
    return;
  }

  document.getElementById('btn_valider').style.display = 'block';
  document.getElementById('btn_cocherDecocher').style.display = 'block';
  let container = document.querySelector("#volet_array");
  let html = '<table class="table table-bordered">';
  html += "<thead><tr>";
  html += "<th style='width:50px;'>{{N°}}</th>";
  html += "<th style='width:200px;'>{{Configuration}}</th>";
  html += "<th>{{Valeur}}</th>";
  html += "<th style='width:120px;'>{{Etat retour ?}}</th>";
  html += "</tr></thead>";

  for (let i = 1; i <= nbVolet; i++) {

    // tbody principal
    html += "<tbody style='border-top: 3px solid #337ab7'>";
    html += "<tr>";
    html += "<td>" + i + "</td>";
    html += "<td>{{Equipement}}</td>";
    html += "<td><div class='input-group'>";
    html += "<input type='text' class='form-control eqLogicAttr equipement_volet' data-l1key='volet_" + i + "' id='volet_" + i + "' placeholder='Sélectionner un équipement' readonly>";
    html += "<span class='input-group-btn'><a class='btn btn-default btn-sm bt_selectEqLogic' data-input='volet_" + i + "' data-index='" + i + "'><i class='fas fa-list-alt'></i></a></span>";
    html += "</div></td>";
    html += "<td><input type='checkbox' style='width:30px; height:20px; cursor:pointer; data-l1key='etatRetour_" + i + "' id='etatRetour_" + i + "' checked ></td>";
    html += "</tr>";
    html += "</tbody>";

    // tbody caché pour les commandes
    html += "<tbody id='extra_volet_" + i + "' style='display:none;'>";

    html += "<tr>";
    html += "<td></td>";
    html += "<td>{{Commande ouverture}}</td>";
    html += "<td><select class='form-control' id='cmd_open_" + i + "'>";
    html += "<option value=''>-- Sélectionner équipement d'abord --</option>";
    html += "</select></td>";
    html += "<td></td>";
    html += "</tr>";

    html += "<tr>";
    html += "<td></td>";
    html += "<td>{{Commande fermeture}}</td>";
    html += "<td><select class='form-control' id='cmd_close_" + i + "'>";
    html += "<option value=''>-- Sélectionner équipement d'abord --</option>";
    html += "</select></td>";
    html += "<td></td>";
    html += "</tr>";

    html += "<tr>";
    html += "<td></td>";
    html += "<td>{{Commande stop}}</td>";
    html += "<td><select class='form-control' id='cmd_stop_" + i + "'>";
    html += "<option value=''>-- Sélectionner équipement d'abord --</option>";
    html += "</select></td>";
    html += "<td></td>";
    html += "</tr>";

    html += "</tbody>";
  }

  html += "</table>";
  container.innerHTML = html;

  // Listener unique pour bt_selectEqLogic
  $(document).off('click', '.bt_selectEqLogic').on('click', '.bt_selectEqLogic', function () {
    var inputId = $(this).data('input');
    var index = $(this).data('index');

    jeedom.eqLogic.getSelectModal({}, function (result) {
      if (!result) return;

      $('#' + inputId)
        .val(result.human)
        .attr('data-eqlogic-id', result.id);

      const extraTbody = document.querySelector('#extra_volet_' + index);

      // Vérifie si RFXCOM
      // fetch('plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      //   body: new URLSearchParams({ action: 'isWithoutLogicalId', id: result.id })
      // })
      //   .then(response => response.json())
      //   .then(data => {
      //     const isRfxcom = data.state === 'ok' && data.result === true;
      //     extraTbody.style.display = isRfxcom ? 'table-row-group' : 'none';

      //     // Stocke si c'est RFXCOM sur l'input pour le listener etatRetour
      //     // document.querySelector('#volet_' + index)?.setAttribute('data-is-rfxcom', isRfxcom);
      //   })
      //   .catch(error => console.error('Erreur isWithoutLogicalId:', error));

      // Récupère les commandes
      fetch('plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'getCmdByEqLogicId', eqLogic_id: result.id })
      })
        .then(response => response.json())
        .then(data => {
          if (data.state !== 'ok') return;
          const selectOpen = document.querySelector('#cmd_open_' + index);
          const selectClose = document.querySelector('#cmd_close_' + index);
          const selectStop = document.querySelector('#cmd_stop_' + index);
          const defaultOpt = '<option value="">Sélectionner...</option>';
          selectOpen.innerHTML = defaultOpt;
          selectClose.innerHTML = defaultOpt;
          selectStop.innerHTML = defaultOpt;
          data.result.forEach(cmd => {
            const opt = `<option value="${cmd.id}">${cmd.name}</option>`;
            selectOpen.innerHTML += opt;
            selectClose.innerHTML += opt;
            selectStop.innerHTML += opt;
          });
        })
        .catch(error => console.error('Erreur getCmdByEqLogicId:', error));
    });
  });

  // Si la case Etat Retour est coché, 
  document.addEventListener('change', function (e) {
    if (e.target.id?.startsWith('etatRetour_')) {
      const index = e.target.id.split('_')[1];
      // const isRfxcom = document.querySelector('#volet_' + index)?.getAttribute('data-is-rfxcom') === 'true';
      const extraVolet = document.querySelector('#extra_volet_' + index);

      if (/**isRfxcom &&**/  extraVolet) {
        extraVolet.style.display = e.target.checked ? 'none' : 'table-row-group';
      } else {
        verifyVoletProp();
      }
    }
  });
}

function cocherDecocher() {
  nbVolet = document.querySelector('#volet_number').value
  for (let i = 1; i <= nbVolet; i++) {
    let checkbox = document.querySelector('#etatRetour_' + i);
    if (checkbox) checkbox.checked = !checkbox.checked;
  }

  verifyVoletProp();
}

function verifyVoletProp() {
  let nbVolet = document.querySelector('#volet_number').value;
  let isVoletProp = false;
  for (let i = 1; i <= nbVolet; i++) {
    let checkbox = document.querySelector('#etatRetour_' + i);
    let extraTbody = document.querySelector('#extra_volet_' + i);
    // const isRfxcom = document.querySelector('#volet_' + i)?.getAttribute('data-is-rfxcom') === 'true';

    if (/**isRfxcom || **/(checkbox && !checkbox.checked)) {
      isVoletProp = true;
      extraTbody.style.display = 'table-row-group';
    } else {
      extraTbody.style.display = 'none';
    }
  }
  return isVoletProp;
}



async function addVolet() {
  const btn = document.querySelector('#btn_valider button')
  if (btn.disabled) return;
  btn.disabled = true;
  btn.textContent = 'Création en cours..';
  nbVolet = document.querySelector('#volet_number').value;
  const volets = []
  const voletsInvalides = []
  let success = 0
  if (verifyVoletProp() == true) {
    const response = await fetch("plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ action: "verifyVolet" })
    });
    const data = await response.json()
    if (data.state === 'ok' && data.result == 0) {
      jeedomUtils.showAlert({ message: `Le plugin Volet Proportionnel est introuvable`, level: 'danger' });
      return;
    }
  }
  for (let i = 1; i <= nbVolet; i++) {
    let idVolet = document.getElementById('volet_' + i).getAttribute('data-eqlogic-id')
    let etatRetour = document.querySelector('#etatRetour_' + i)
    let cmdOpen = document.getElementById('cmd_open_' + i).value
    let cmdClose = document.getElementById('cmd_close_' + i).value
    let cmdStop = document.getElementById('cmd_stop_' + i).value
    if (!idVolet) {
      voletsInvalides.push({
        numeroVolet: i,
        erreur: 'Veuillez sélectionner un équipement'
      })
    }
    if (!etatRetour.checked) {
      if (cmdOpen == '' || cmdClose == '' || cmdStop == '') {
        voletsInvalides.push({
          numeroVolet: i,
          erreur: 'Commande(s) vide(s)'
        })
      }
    }
    volets.push({
      idVolet: idVolet,
      numeroVolet: i,
      etatRetour: etatRetour.checked,
      ...(!etatRetour.checked && { cmdOpen: cmdOpen, cmdClose: cmdClose, cmdStop: cmdStop })
    });
    console.log(volets)
  }
  if (voletsInvalides.length > 0) {
    btn.disabled = false
    btn.textContent = 'Valider'
    const erreurs = voletsInvalides.map(t => `Volet n°${t.numeroVolet} : ${t.erreur}`)
    jeedomUtils.showAlert({
      message: erreurs.join('<br> '),
      level: 'danger'
    })
    return
  }
  for (const volet of volets) {
    try {
      const response = await fetch("plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "addVOLET",
          volet: JSON.stringify(volet)
        }),
      });
      const data = await response.json();
      if (data.state === "ok") {
        success++;
        jeedomUtils.showAlert({ message: `${success}/${volets.length} créé(s)...`, level: 'success' });
      } else {
        jeedomUtils.showAlert({ message: `Erreur volet n°${volet.numeroVolet} : ${data.result}`, level: 'danger' }); // catch l'erreur si doublon dans la DB
      }
    } catch (error) {
      jeedomUtils.showAlert({ message: `Erreur volet n°${volet.numeroVolet}`, level: 'danger' });
    }
  }
  if (success != 0) {
    jeedomUtils.showAlert({ message: `${success}/${volets.length} volet(s) créé(s)`, level: 'success' });
    location.reload();
  }
  btn.disabled = false;
  btn.textContent = 'Valider';
}



document.addEventListener('change', function (e) {
  if (e.target.id?.startsWith('copierAllCommandes')) {
    const commandeContenant = document.getElementById('commandesContenantInput');
    const exclureCommandes = document.getElementById('exclureCommandesInput');

    if (exclureCommandes) {
      exclureCommandes.style.visibility = e.target.checked ? 'hidden' : 'visible';
      commandeContenant.style.visibility = e.target.checked ? 'hidden' : 'visible';
    }
  }
});
async function copyCommandes() {
  
  let equipementSource = document.getElementById('equipementSource').getAttribute('data-eqlogic-id')
  
  let equipementCible = document.getElementById('equipementCible').getAttribute('data-eqlogic-id')
  let copierAllCommandes = document.getElementById('copierAllCommandes').checked
  let commandesContenant = document.getElementById('commandesContenant').value
  let exclureCommandes1 = document.getElementById('exclureCommande1').value
  let exclureCommandes2 = document.getElementById('exclureCommande2').value
  let exclureCommandes3 = document.getElementById('exclureCommande3').value
  if (!equipementSource) {
    jeedomUtils.showAlert({
      message: 'Veuillez sélectionner un équipement source',
      level: 'danger'
    })
    
  }

  if (!equipementCible) {
    jeedomUtils.showAlert({
      message: 'Veuillez sélectionner un équipement cible',
      level: 'danger'
    })
    
  }

  if (!copierAllCommandes) {
    if (!commandesContenant) {
      jeedomUtils.showAlert({
        message: 'Veuillez saisir un mot',
        level: 'danger'
      })
    }
  }
  const automate = {
    equipementSource: equipementSource,
    equipementCible: equipementCible,
    copierAllCommandes: copierAllCommandes,
    ...(!copierAllCommandes && { commandesContenant: commandesContenant, exclureCommandes1: exclureCommandes1, exclureCommandes2: exclureCommandes2, exclureCommandes3: exclureCommandes3 })
  }
  console.log(automate);
  
  try {
    const response = await fetch("plugins/ImactPlugin/core/ajax/ImactPlugin.ajax.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        action: "convertAutomate",
        automate: JSON.stringify(automate)
      }),
    });
    const data = await response.json();
    console.log(data);
    
    if (data.state === "ok") {
      jeedomUtils.showAlert({ message: `${data.result} commande(s) copiée(s)...`, level: 'success'  });
    }
  } catch (error) {
    jeedomUtils.showAlert({ message: error, level: 'danger' });
  }

}


/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} }
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
  tr += '<option value="">{{Aucune}}</option>'
  tr += '</select>'
  tr += '</td>'
  tr += '<td>'
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
  tr += '<div style="margin-top:7px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '</div>'
  tr += '</td>'
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
  tr += '</td>';
  tr += '<td>'
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
  tr += '</tr>'
  $('#table_cmd tbody').append(tr)
  var tr = $('#table_cmd tbody tr').last()
  jeedom.eqLogic.buildSelectCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    filter: { type: 'info' },
    error: function (error) {
      $('#div_alert').showAlert({ message: error.message, level: 'danger' })
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result)
      tr.setValues(_cmd, '.cmdAttr')
      jeedom.cmd.changeType(tr, init(_cmd.subType))
    }
  })
}
