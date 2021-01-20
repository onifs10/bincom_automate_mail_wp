<?php
BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/bma-table.php');

$table = new AutomationMailDetailsTable();

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