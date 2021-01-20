<?php
BMA()->load_files(BMA()->get_vars('PATH') . 'includes/classes/ClassesModel.php');

if (!array_key_exists('mail', $_REQUEST)) {
    die('pls select mail to edit');
}
$mail = new BincomAutomatedMails($_REQUEST['mail']);
// die(var_dump($mail));
if (array_key_exists('edit_mail', $_POST)) {
    $nonce = esc_attr($_POST['edit_mail']);
    if (!wp_verify_nonce($nonce, 'edit_mail')) {
        die('Go get a life script dear');
    }
    $details = $_REQUEST['updateMail'];
    $mail = BincomAutomatedMails::update($_REQUEST['mail'], $details);
    if ($mail) {
        ?>
        <div style="border-color:green; color:darkgreen; background-color:rgba(144,238,144,0.2);padding: 20px"
             id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
            <strong> Mail Updated </strong>
        </div>
        <?php
    } else {
        ?>
        <div style="border-color:red; color:darkred; background-color:rgba(220,50,50,0.2); padding: 20px"
             id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
            <strong> Class Was Not Updated</strong>
        </div><?php
    }

}

function addInput($name, $label, $value, $required = true)
{
    ?>
    <label for="updateMail[<?= $name ?>]" class="label_input">
        <span style="padding:10px 2px; font-size:1.2em; color:darkblue"><?= $label ?></span>
    </label>
    <input value=<?= $value ?> style="margin: 5px
           0px" type="text" class="large-text" name="updateMail[<?= $name ?>]" id="" <?php if ($required) {
    echo " required";
} ?>>

    <?php
}

function addSelect($name, $label, $options)
{
    ?>
    <div style="display:block; margin: 10px 0px">
        <label for="updateMail[<?= $name ?>]" class="label_input">
            <span style="padding:10px 2px; font-size:1.2em; color:darkblue"><?= $label ?></span>
        </label>
        <select name="updateMail[<?= $name ?>]">
            <?php
            foreach ($options as $key => $value) {
                ?>
                <option value="<?= $key ?>">
                    <?= $value ?>
                </option>
                <?php
            }
            ?>
        </select>
    </div>
    <?php
}

?>


<div class='wrap'>
    <h2>Edit Mail</h2>

    <div class='metabox-holder'
         style="display: flex; flex-flow:row wrap; justify-content:start; background-color:rgba(255,255,255,0.2); padding:20px">
        <form method="post" class='form' style="flex: 0 0 80%; ">
            <?php addInput('name', 'Mail Name', $mail->name) ?>
            <?php addInput('title', 'Mail titile ', $mail->title) ?>
            <?php addSelect('content', 'Mail Template type', ['multiple' => 'Multiple', 'single' => 'Single']) ?>
            <?php addInput('form_to_check_slug', 'Form slug to check', $mail->form_to_check_slug) ?>
            <?php addInput('input_to_check', 'Input to Check', $mail->input_to_check, false,) ?>
            <button type="submit" class="button button-primary" name='edit_mail'
                    value="<?= wp_create_nonce('edit_mail') ?>"> Edit Mail
            </button>
        </form>
    </div>
</div>