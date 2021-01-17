<?php 
BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/ClassesModel.php');


if(array_key_exists('add_class', $_POST)){
    $nonce = esc_attr($_POST['add_class']);
    if(! wp_verify_nonce($nonce,'add_class')){
        die( 'Go get a life script dear' );
    };
    $class = BincomClasses::insert($_POST['newClass']);
    if($class){
        ?><div style="border-color:green; color:darkgreen; background-color:rgba(144,238,144,0.2);padding: 20px"
    id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
    <strong> Class has been added </strong>
</div>
<?php
    }else{
        ?><div style="border-color:red; color:darkred; background-color:rgba(220,50,50,0.2); padding: 20px"
    id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
    <strong> Class was not added </strong>
</div><?php
    }

}

function addInput($name, $label){
    ?>
<label for="newClass[<?= $name ?>]" class="label_input">
    <span style="padding:10px 2px; font-size:1.2em; color:darkblue"><?= $label ?></span>
</label>
<input style="margin: 5px 0px" type="text" class="large-text" name="newClass[<?= $name ?>]" id="" required>

<?php
}
function addTextInput($name, $label){
    ?>
<label for="newClass[<?= $name ?>]" class="label_input column-2:">
    <span style="padding:10px 0px; font-size:1.2em; color:darkblue"> <?= $label ?></span>
    <textarea style="margin: 5px 0px" rows="20" cols='50' class="large-text" name="newClass[<?= $name ?>]"
        id=""></textarea>
</label>
<?php
}
?>

<div class='wrap'>
    <h2>Add a new class</h2>

    <div class='metabox-holder'
        style="display: flex; flex-flow:row wrap; justify-content:start; background-color:rgba(255,255,255,0.2); padding:20px">
        <form method="post" class='form' style="flex: 0 0 80%; ">
            <?php addInput('class_name','Class Name') ?>
            <?php addInput('class_code','Class Code ') ?>
            <?php addInput('class_days','Class Days') ?>
            <?php addInput('class_time','Class Time') ?>
            <?php addInput('class_starts','Class Starts') ?>
            <?php addInput('class_link','Class link') ?>
            <?php addInput('class_duration','Class Duration') ?>
            <?php addTextInput('mail_template','Mail Template') ?>
            <button type="submit" class="button button-primary" name='add_class'
                value="<?= wp_create_nonce('add_class')?>"> Add Class </button>
        </form>
    </div>