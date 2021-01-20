<?php 
BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/ClassesModel.php');

$class = BincomAutomatedClasses::find($_REQUEST['class']);

if(array_key_exists('edit_class', $_POST)){
    $nonce = esc_attr($_POST['edit_class']);
    if(! wp_verify_nonce($nonce,'edit_class')){
        die( 'Go get a life script dear' );
    };
    $details = $_REQUEST['updateClass'];
    $details['ID'] = $_REQUEST['class'];
    $class = new BincomAutomatedClasses($details);
    if($class->update()){
        ?><div style="border-color:green; color:darkgreen; background-color:rgba(144,238,144,0.2);padding: 20px"
    id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
    <strong> Class updated been added </strong>
</div>
<?php
    }else{
        ?><div style="border-color:red; color:darkred; background-color:rgba(220,50,50,0.2); padding: 20px"
    id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
    <strong> Class was not added </strong>
</div><?php
    }

}

function addInput($name, $label, $value){
    ?>
<label for="newClass[<?= $name ?>]" class="label_input">
    <span style="padding:10px 2px; font-size:1.2em; color:darkblue"><?= $label ?></span>
</label>
<input style="margin: 5px 0px" type="text" class="large-text" value="<?= $value ?>" name="updateClass[<?= $name ?>]"
    id="" required>

<?php
}
function addTextInput($name, $label, $value){
    ?>
<label for="newClass[<?= $name ?>]" class="label_input column-2:">
    <span style="padding:10px 0px; font-size:1.2em; color:darkblue"> <?= $label ?></span>
    <span style="margin:20px; font-size:1em;">
        you can use this in the template to fill in needeed details from above <br> [class-name] [class-duration]
        [class-days] [class-time] [class-link] [class-starts] [recipient-name]
    </span>
    <textarea style="margin: 5px 0px" rows="20" cols='50' class="large-text" name="updateClass[<?= $name ?>]"
        id=""><?= $value ?></textarea>
</label>
<?php
}
?>

<div class='wrap'>
    <h2>Edit Class : <span style="font-size: 0.7em; color:darkblue"><?php echo($class->name) ?><span></h2>

    <div class='metabox-holder'
        style="display: flex; flex-flow:row wrap; justify-content:start; background-color:rgba(255,255,255,0.2); padding:20px">
        <form method="post" class='form' style="flex: 0 0 80%; ">
            <?php addInput('class_name','Class Name', $class->name) ?>
            <?php addInput('class_code','Class Code ', $class->code ) ?>
            <?php addInput('class_days','Class Days', $class->days) ?>
            <?php addInput('class_time','Class Time', $class->time) ?>
            <?php addInput('class_starts','Class Starts', $class->starts) ?>
            <?php addInput('class_link','Class link', $class->link) ?>
            <?php addInput('class_duration','Class Duration', $class->duration) ?>
            <?php addTextInput('mail_template','Mail Template', $class->mail_template) ?>
            <button type="submit" class="button button-primary" name='edit_class'
                value="<?= wp_create_nonce('edit_class')?>"> Edit Class </button>
        </form>
    </div>