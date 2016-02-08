<div class="large-12 column">
<div class="info-box">
    <h1>PHPSC Vault</h1>
    <table width="100%" cellpadding="0" cellspacing="0" >
        <thead>
            <th>Date</th>
            <th>IP</th>
            <th>BanIP</th>
            <th>File</th>
            <th>Threat</th>
            <th>Info</th>
            <th class="text-center">Download</th>
        </thead>
        <tbody>
        <?php
        $items = $Webpanel->get_vault();

        if ($items) {
            foreach ($items as $item) {

                $date = new DateTime( $item['date'] );

                $file = json_decode($item['file']);
                $threat = json_decode($item['threat']);
                ?>
                <tr>
                    <td><?=$date->format('r')?></td>
                    <td>
                        <a href="http://geomaplookup.net/?ip=<?=$item['ip']?>" target="_blank"><?=$item['ip']?> <i class="fa fa-external-link"></i></a>
                    </td>
                    <td class="text-center">
                        <?php
                        if( $item['status'] == 'pending' ) {
                            ?>
                            <i class="fa fa-clock-o" title="Waiting for cronjob to block this IP"></i>
                            <?php
                        } elseif( $item['status'] == 'blocked' ) {
                            ?>
                            <i class="fa fa-check" title="IP Blocked"></i>
                            <?php
                        } else {
                            ?>
                            <a href="?phpsc&phpsc_action=banip&phpsc_ip=<?= $item['ip'] ?>" class="ban-ip-address" data-ip="<?=$item['ip']?>"><i
                                    class="fa fa-ban"></i></a>
                            <?php
                        }
                        ?>
                    </td>
                    <td><?=$file->name?></td>
                    <td><?=$threat[0]->vun_string?></td>
                    <td>
                        <a data-open="info-icon-modal-<?=$item['id']?>" class="fa fa-info-circle"></a>

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
foreach ($threat as $key=>$val) {
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
foreach ($file as $key=>$val) {
    if (!is_string($val)) {
        continue;
    }
    ?>
<?=$key?>: <?=$val . "\n"?>
<?php

}
                ?>


SERVER DETAILS
-=-=-=-=-=-=-=-
<?php
$details = json_decode($item['server_details'], true);
                foreach ($details as $key=>$val) {
                    ?>
<?=$key?>: <?=$val . "\n"?>
<?php

                }

                ?>
</pre>
                        </div>
                    </td>
                    <td class="text-center"><a href="?phpsc&phpsc_action=download&phpsc_id=<?=$item['id']?>" class="fa fa-download" title="Download infected file"></a></td>
                </tr>
            <?php

            }
        }
        ?>

        </tbody>
    </table>



</div>
</div>