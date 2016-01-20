<?php
/**
 * Created for PHPScanner
 * User: Danny Hearnah
 * Author: Skyblue Creations Ltd.
 *
 * Date: 1/19/2016
 * Time: 12:00 PM
 */
?>

<div class="large-12 column">
<div class="info-box">
    <h1>PHPSC Vault</h1>
    <table width="100%" cellpadding="0" cellspacing="0" >
        <thead>
            <th>Date</th>
            <th>IP</th>
            <th>File</th>
            <th>Threat</th>
            <th>Info</th>
        </thead>
        <tbody>
        <?php
        $items = $Webpanel->get_vault();

        if ($items) {
            foreach ($items as $item) {
                $file = json_decode($item['file']);
                $threat = json_decode($item['threat']);
                ?>
                <tr>
                    <td><?=date('jS F Y', strtotime($item['date']))?></td>
                    <td><?=$item['ip']?></td>
                    <td><?=$file->name?></td>
                    <td><?=$threat[0]->vun_string?></td>
                    <td>
                        <a data-open="info-icon-modal-<?=$item['id']?>" class="info-icon">i</a>

                        <div class="reveal large" id="info-icon-modal-<?=$item['id']?>" data-reveal>
                            <h1>Details</h1>
                            <p class="lead">Viewing threat log for <?=$file->name?></p>
                            <button class="close-button" data-close aria-label="Close modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>

<pre class="modal-code" onclick="selectText(this);">
THREAT DETAILS
-=-=-=-=-=-=-=-
<?php
foreach($threat as $key=>$val) {

    foreach ($val as $tkey=>$t) {
?>
<?= $tkey ?>: <?= $t . "\n" ?>
<?php
    }
}
?>


UPLOAD DETAILS
-=-=-=-=-=-=-=-
<?php
foreach($file as $key=>$val){

if( !is_string($val) ){ continue; }
?>
<?=$key?>: <?=$val . "\n"?>
<?php
}
?>


SERVER DETAILS
-=-=-=-=-=-=-=-
<?php
$details = json_decode($item['server_details'],true);
foreach($details as $key=>$val){
?>
<?=$key?>: <?=$val . "\n"?>
<?php
}

?>
</pre>
                        </div>
                    </td>
                </tr>
            <?php

            }
        }
        ?>

        </tbody>
    </table>



</div>
</div>