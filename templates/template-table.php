<?php
// BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/bma-tableMail.php');

$table = new BincomTamplateTable();

?>
<div class="wrap">
    <h2>Templates <a class="button" href=<?php menu_page_url('bma_add_template') ?>>Add Template</a> </h2>

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