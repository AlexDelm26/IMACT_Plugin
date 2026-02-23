<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div style="padding: 20px;">
    <div class="form-group">
        <label>{{Nombre de thermostats à créer}}</label>
        <input type="number" class="form-control" id="thermostat_number" placeholder="1" min="1" value="1" />
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <button class="btn btn-success" onclick="addChampThermostat()">{{Valider Nombre}}</button>
    </div>

    <div id="thermostat_array" style="margin-top: 30px;"></div>

    <div style="text-align: center; margin-top: 20px; display:none;" id="btn_valider">
        <button class="btn btn-success" onclick="addThermostat()">{{Valider}}</button>
    </div>
    <div style="text-align: center; margin-top: 20px;" id="btn_log">
        <button class="btn btn-success" onclick="log()">{{Log}}</button>
    </div>
</div>