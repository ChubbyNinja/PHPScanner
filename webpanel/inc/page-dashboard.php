<?php
$filter = false;

if (isset($_GET['phpsc_filter'])) {
    $filter_request = trim($_GET['phpsc_filter']);

    if (in_array($filter_request, array('blocked','pending','unbanned'))) {
        $filter = $filter_request;
    }
}

$items = $Webpanel->get_vault($filter);
?>

<div class="large-12 column">
<div class="info-box">
    <div class="row">
        <div class="large-5 columns">
            <h1>PHPSC Vault - <?php echo $Webpanel->get_vault_size(); ?> items</h1>
        </div>
        <div class="large-7 columns">
            <ul class="menu">
                <li class="<?php echo((!$filter) ? 'active' : null)?>"><a href="?phpsc&phpsc_action=dashboard">Show All</a></li>
                <li class="<?php echo(($filter == 'blocked') ? 'active' : null); ?>"><a href="?phpsc&phpsc_action=dashboard&phpsc_filter=blocked">Banned</a></li>
                <li class="<?php echo(($filter == 'pending') ? 'active' : null); ?>"><a href="?phpsc&phpsc_action=dashboard&phpsc_filter=pending">Pending</a></li>
                <li class="<?php echo(($filter == 'unbanned') ? 'active' : null); ?>"><a href="?phpsc&phpsc_action=dashboard&phpsc_filter=unbanned">Not Banned</a></li>
            </ul>
        </div>
    </div>
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


        if ($items) {
            foreach ($items as $item) {
                $date = new DateTime($item['date']);

                $file = json_decode($item['file']);
                $threat = json_decode($item['threat']);
                ?>
                <tr>
                    <td><?php echo $date->format('r');
                ?></td>
                    <td>
                        <a href="http://geomaplookup.net/?ip=<?php echo $item['ip'];
                ?>" target="_blank"><?php echo $item['ip'];
                ?> <i class="fa fa-external-link"></i></a>
                    </td>
                    <td class="text-center">
                        <?php
                        if ($item['status'] == 'pending') {
                            ?>
                            <i class="fa fa-clock-o" title="Waiting for cronjob to block this IP"></i>
                            <?php

                        } elseif ($item['status'] == 'blocked') {
                            ?>
                            <i class="fa fa-check" title="IP Blocked"></i>
                            <?php

                        } else {
                            ?>
                            <a href="?phpsc&phpsc_action=banip&phpsc_ip=<?php echo $item['ip'];
                            ?>" class="ban-ip-address" data-ip="<?php echo $item['ip'];
                            ?>"><i
                                    class="fa fa-ban"></i></a>
                            <?php

                        }
                ?>
                    </td>
                    <td><?php echo $file->name;
                ?></td>
                    <td><?php echo $threat[0]->vun_string;
                ?></td>
                    <td>
                        <a data-open="info-icon-modal-<?php echo $item['id'];
                ?>" class="fa fa-info-circle"></a>

                        <div class="reveal large" id="info-icon-modal-<?php echo $item['id'];
                ?>" data-reveal>
                            <h1>Details</h1>
                            <p class="lead">Viewing threat log for <?php echo $file->name;
                ?></p>
                            <button class="close-button" data-close aria-label="Close modal" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>

<pre class="modal-code" onclick="selectText(this);">
THREAT DETAILS
-=-=-=-=-=-=-=-
<?php
foreach ($threat as $key=>$val) {
    foreach ($val as $tkey=>$t) {
        echo $tkey . ':' . $t . "\n";
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
    echo $key .':' . $val . "\n";
}
                ?>


SERVER DETAILS
-=-=-=-=-=-=-=-
<?php
echo $item['server_details'];
                ?>
</pre>
                        </div>
                    </td>
                    <td class="text-center"><a href="?phpsc&phpsc_action=download&phpsc_id=<?php echo $item['id'];
                ?>" class="fa fa-download" title="Download infected file"></a></td>
                </tr>
            <?php

            }
        }
        ?>

        </tbody>

        <tfoot>
            <tr>
                <td colspan="20">
                    <div class="">
                        <ul class="pagination text-center" role="navigation" aria-label="Pagination">

                        <?php


                        if ($Webpanel->get_page() > 1) {
                            ?>
                            <li class="page-prev"><a href="?phpsc&phpsc_action=dashboard&phpsc_filter=<?php echo $filter;
                            ?>&phpsc_page=<?php echo $Webpanel->get_page()-1;
                            ?>"><i class="fa fa-angle-double-left" aria-hidden="true"></i> Previous page</a></li>
                            <?php

                        } else {
                            ?>
                            <li class="page-prev disabled"><a href="#"><i class="fa fa-angle-double-left" aria-hidden="true"></i> Previous page</a></li>
                            <?php

                        }

                        $i = 1;
                        while ($i <= $Webpanel->get_total_pages()) {
                            ?>
                            <li class="<?php echo(($i == $Webpanel->get_page()) ? 'current-page' : null);
                            ?>"><a href="?phpsc&phpsc_action=dashboard&phpsc_filter=<?php echo $filter;
                            ?>&phpsc_page=<?php echo $i;
                            ?>"><?php echo $i;
                            ?></a></li>
                            <?php

                            $i++;
                        }

                        if ($Webpanel->get_page() < $Webpanel->get_total_pages()) {
                            ?>
                            <li class="page-next"><a href="?phpsc&phpsc_action=dashboard&phpsc_filter=<?php echo $filter;
                            ?>&phpsc_page=<?php echo $Webpanel->get_page()+1;
                            ?>">Next page <i class="fa fa-angle-double-right" aria-hidden="true"></i></a></li>
                            <?php

                        } else {
                            ?>
                            <li class="page-next disabled"><a href="#">Next page <i class="fa fa-angle-double-right" aria-hidden="true"></i></a></li>
                            <?php

                        }
                        ?>
                        </ul>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>



</div>
</div>