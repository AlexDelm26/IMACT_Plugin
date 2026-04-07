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
                <tr>
                    <td>{{Equipement Cible :}}</td>
                    <td>

                        <div class='input-group'>

                            <input type='text' class='form-control eqLogicAttr' data-l1key='equipementCible'
                                id='equipementCible' placeholder='Sélectionner équipement' readonly>

                            <span class='input-group-btn'>
                                <a class='btn btn-default btn-sm bt_selectEqLogic' data-input="equipementCible">
                                    <i class='fas fa-list-alt'></i>
                                </a>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        Copier toutes les commandes ?
                    </td>
                    <td>
                        <input type="checkbox" id="copierAllCommandes">
                    </td>
                </tr>
                <tr id="commandesContenantInput">
                    <td>{{Commandes contenant uniquement :}}</td>
                    <td>
                        <input type='text' class='form-control' data-l1key='commandesContenant' id='commandesContenant'>
                    </td>
                </tr>
                <tr id="exclureCommandesInput">
                    <td>{{Exclure les commandes contenant :}}</td>
                    <td>
                        <div class="row">
                            <div class="col-xs-4">
                                <input type='text' class='form-control ' data-l1key='exclureCommande1'
                                    id='exclureCommande1'>

                            </div>
                            <div class="col-xs-4">
                                <input type='text' class='form-control ' data-l1key='exclureCommande2'
                                    id='exclureCommande2'>

                            </div>
                            <div class="col-xs-4">
                                <input type='text' class='form-control ' data-l1key='exclureCommande3'
                                    id='exclureCommande3'>
                            </div>
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