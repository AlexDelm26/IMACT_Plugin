<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div style="padding: 20px;">
    <div class="form-group">
        <label>{{Nombre de volets à créer}}</label>
        <input type="number" class="form-control" id="volet_number" placeholder="1" min="1" value="1" />
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <button class="btn btn-success" onclick="addChampVolet()">{{Valider Nombre}}</button>
    </div>

    <div style="text-align: end; margin-top: 20px; display:none;" id="btn_cocherDecocher">
        <button class="btn btn-primary" onclick="cocherDecocher()">{{Cocher/Décocher}}</button>
    </div>

    <div id="volet_array" style="margin-top: 30px;"></div>

    <div style="text-align: center; margin-top: 20px; display:none;" id="btn_valider">
        <button class="btn btn-success" onclick="addVolet()">{{Valider}}</button>
    </div>
</div>