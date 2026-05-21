<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div style="padding: 20px;">
    <div class="form-group">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td>{{Equipement Source}}</td>
                    <td>
                        <div class='input-group'>
                            <input type='text' class='form-control eqLogicAttr' data-l1key='equipementSource'
                                id='equipementSource' placeholder='Sélectionner équipement' readonly>
                            <span class='input-group-btn'>
                                <a class='btn btn-default btn-sm bt_selectEqLogic' data-input="equipementSource">
                                    <i class='fas fa-list-alt'></i>
                                </a>
                            </span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="text-align: center; margin-top: 20px;" id="btn_valider">
        <button class="btn btn-success" onclick="exportJson()">{{Valider}}</button>
    </div>

    <div id="json_result_zone" style="display:none; margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <strong>{{Résultat JSON}}</strong>
            <button class="btn btn-default btn-sm" onclick="copyJson()">
                <i class="fas fa-copy"></i> {{Copier}}
            </button>
        </div>
        <textarea id="json_result" class="form-control" rows="15" readonly
            style="font-family: monospace; font-size: 12px;"></textarea>
    </div>
</div>