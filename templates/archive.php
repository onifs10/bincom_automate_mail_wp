<?php

if(array_key_exists('submit_scripts_update', $_POST)){
            update_option('BMA_header_script', trim($_POST['headerScripts']));
            update_option('BMA_footer_script', trim($_POST['footerScripts']));
            ?>
<div id='setting-error-settings-updated' class="updated_settings_error notice is-dismissible">
    <strong>Settings has been saved</strong>
</div>
<?php   
    }
        $header_script = get_option('BMA_header_script','none');
        $footer_script = get_option('BMA_footer_script','none')
        ?>
<div class='wrap'>
    <form class="form" method='post' action="">
        <h2>this is the page to show forms </h2>
        <label for="HeaderScripts">Header Scripts</label>
        <textarea name="headerScripts" class="large-text">
                <?php echo trim($header_script) ?>
            </textarea>
        <label for="footerScripts">Footer Scripts</label>
        <textarea name='footerScripts' class="large-text">
                <?php echo trim($footer_script) ?>
            </textarea>
        <input type="submit" class="button button-primary" name='submit_scripts_update' value="UPDATE SCRIPTS">
    </form>
</div>