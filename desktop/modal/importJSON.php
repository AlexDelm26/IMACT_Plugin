<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div style="padding: 20px;">
    <div id="json_form_zone" style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <strong>{{JSON}}</strong>
        </div>
        <textarea id="json_form" class="form-control" rows="15"
            style="font-family: monospace; font-size: 12px;"></textarea>
    </div>

    <div style="text-align: center; margin-top: 20px;" id="btn_valider">
        <button class="btn btn-success" onclick="importJson()">{{Valider}}</button>
    </div>

</div>