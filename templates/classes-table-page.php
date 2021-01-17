<?php
BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/bma-table.php');

BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/inbound_message_table.php');

$table = new AutomationDetailsTable();
$table2 = new BMA_Inbound_Messages_List_Table();

?>
<div class="wrap">
    <h2>Classes Table <a class="button" href=<?php menu_page_url('bma_add_class') ?>>Add class</a> </h2>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
							$table->prepare_items();
							$table->display(); ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>

<div class="wrap">


    <div id="poststuff">
        <div id="post-body" class="metabox-holder">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
							$table2->prepare_items();
							$table2->display(); ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>