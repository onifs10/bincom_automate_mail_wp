<?php
if(array_key_exists('submit_bma_settings', $_POST)){
    update_option('bma_settings', $_POST['bma_settings']);
    ?><div style="border-color:green; color:darkgreen; background-color:rgba(144,238,144,0.2);padding: 20px"
    id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
    <strong> Settings Updated </strong>
</div>
<?php
}
$settings = get_option('bma_settings', 'none');

function addInput($name, $label, $value){
    ?>
<label for="settings[<?= $name ?>]" class="label_input">
    <span style="padding:10px 2px; font-size:1.2em; color:darkblue"><?= $label ?></span>
</label>
<input style="margin: 5px 0px" type="text" class="large-text" value="<?= $value ?>" name="bma_settings[<?= $name ?>]"
    id="" required>
<?php
}
?>

<div>
    <form method='POST'>
        <?php addInput('mail_sender','Mail Sender', $settings['mail_sender'] )?>
        <button type="submit" class="button button-primary" name='submit_bma_settings'
            value="<?= wp_create_nonce('update_bma_settings')?>">Update Settings </button>
    </form>
</div>