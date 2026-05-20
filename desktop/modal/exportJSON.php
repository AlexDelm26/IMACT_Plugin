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
        <button class="btn btn-success" onclick="copyCommandes()">{{Valider}}</button>
    </div>
</div>
<script>
    $(document).off('click', '.bt_selectEqLogic').on('click', '.bt_selectEqLogic', function () {
        var inputId = $(this).data('input');
        var index = $(this).data('index');

        jeedom.eqLogic.getSelectModal({}, function (result) {
            if (!result) return;

            $('#' + inputId)
                .val(result.human)
                .attr('data-eqlogic-id', result.id);

            const extraTbody = document.querySelector('#extra_volet_' + index);
        });
    });
</script>