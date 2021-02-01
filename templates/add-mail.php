<?php 
BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/ClassesModel.php');
// $test = BincomAutomatedMails::add([
//     'name' => 'Academy Mails',
//    'status' => 'publish',
//    'title' => 'Mails to send to academy students',
//    'content' => 'single',
//    'input_to_check' => 'role',
//    'form_to_check_slug' => 'meeting'
// ]);
if(array_key_exists('add_mail', $_POST)){
    $nonce = esc_attr($_POST['add_mail']);
    if(! wp_verify_nonce($nonce,'add_mail')){
        die( 'Go get a life script dear' );
    };
    $mail = BincomAutomatedMails::add($_POST['newMail']);
    if($mail){
        ?><div style="border-color:green; color:darkgreen; background-color:rgba(144,238,144,0.2);padding: 20px"
    id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
    <strong> Mail  added </strong>
</div>
<?php
    }else{
        ?><div style="border-color:red; color:darkred; background-color:rgba(220,50,50,0.2); padding: 20px"
    id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
    <strong> Mail was not added </strong>
</div><?php
    }

}
function addInput($name, $label, $required = true){
    ?>
<label for="newMail[<?= $name ?>]" class="label_input">
    <span style="padding:10px 2px; font-size:1.2em; color:darkblue"><?= $label ?></span>
</label>
<input style="margin: 5px 0px" type="text" class="large-text" name="newMail[<?= $name ?>]" id="" <?php if($required) {echo" required"; } ?>>

<?php
}
function addTextInput($name, $label , $details = ''){
    ?>
<label for="newMail[<?= $name ?>]" class="label_input column-2:">
    <span style="padding:10px 0px; font-size:1.2em; color:darkblue"> <?= $label ?></span>
    <span style="margin:20px; font-size:1em;">
        <?=  htmlspecialchars($details)?>
    </span>
    <textarea style="margin: 5px 0px" rows="3" cols='50' class="large-text" name="newMail[<?= $name ?>]"
        id=""></textarea>
</label>
<?php
}

function addSelect($name, $label , $options){
    ?>
    <div style="display:block; margin: 10px 0px" >
        <label for="newMail[<?= $name ?>]" class="label_input">
            <span style="padding:10px 2px; font-size:1.2em; color:darkblue"><?= $label ?></span>
        </label>
        <select name="newMail[<?= $name ?>]" >
    <?php
        foreach($options as $key => $value){
            ?>
                <option  value="<?= $key ?>">
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
    <h2>Add a new Mail</h2>

    <div class='metabox-holder'
        style="display: flex; flex-flow:row wrap; justify-content:start; background-color:rgba(255,255,255,0.2); padding:20px">
        <form method="post" class='form' style="flex: 0 0 80%; ">
            <?php addInput('name','Mail Name') ?>
            <?php addInput('title','Mail titile ') ?>
            <?php addTextInput('additional_header','Additional Mail Headers', "eg Bcc : Sample  <sample@mail.com> \n Cc : carbon <carbon@gmail.com>  ") ?>
            <?php addSelect('content','Mail Template type',['multiple' => 'Multiple','single' => 'Single']) ?>
            <?php addInput('form_to_check_slug','Form slug to check') ?>
            <?php addInput('input_to_check','Input to Check',false) ?>
            <button type="submit" class="button button-primary" name='add_mail'
                value="<?= wp_create_nonce('add_mail')?>"> Add Mail </button>
        </form>
    </div>
</div>