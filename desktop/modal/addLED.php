<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div style="padding: 20px;">
    <div class="form-group">
        <label>{{Nombre de LED à créer}}</label>
        <input type="number" class="form-control" id="LED_number" placeholder="1" min="1" value="1" />
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <button class="btn btn-success" onclick="addChampLED('#LED_number')">{{Valider Nombre}}</button>
    </div>

    <div id="led_array" style="margin-top: 30px;"></div>

    <div style="text-align: center; margin-top: 20px; display:none;" id="btn_valider">
        <button class="btn btn-success" onclick="addLED('#LED_number')">{{Valider}}</button>
    </div>
</div>