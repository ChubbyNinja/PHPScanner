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
<div class="info-box">
    <h1>PHPSC Vault</h1>
    <table width="100%" cellpadding="0" cellspacing="0">
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
                    <td><a href="#" class="info-icon">i</a></td>
                </tr>
            <?php

            }
        }
        ?>

        </tbody>
    </table>
</div>
