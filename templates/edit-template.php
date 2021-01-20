<?php
BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/ClassesModel.php');
$template = new BincomAutomatedMailsTemplates($_REQUEST['template']);
if(!$template->id()){
    die('template not found');
}
if(array_key_exists('edit_template', $_POST)){
    $nonce = esc_attr($_POST['edit_template']);
    if(! wp_verify_nonce($nonce,'edit_template')){
        die( 'Go get a life script dear' );
    };
    $template = BincomAutomatedMailsTemplates::update($_REQUEST['template'],$_POST['newTemplate']);
    if($template){
        ?><div style="border-color:green; color:darkgreen; background-color:rgba(144,238,144,0.2);padding: 20px"
               id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
            <strong> Template  Update </strong>
        </div>
        <?php
    }else{
        ?><div style="border-color:red; color:darkred; background-color:rgba(220,50,50,0.2); padding: 20px"
               id='setting-error-settings-success' class="updated_settings_error notice is-dismissible">
            <strong> Template not Update </strong>
        </div><?php
    }

}
function addInput($name, $label, $value, $required = true){
    ?>
    <label for="newTemplate[<?= $name ?>]" class="label_input">
        <span style="padding:10px 2px; font-size:1.2em; color:darkblue"><?= $label ?></span>
    </label>
    <input value="<?php echo $value; ?>" style="margin: 5px 0px" type="text" class="large-text" name="newTemplate[<?= $name ?>]"  <?php if($required) {echo" required"; } ?>>

    <?php
}
function addTextInput($name, $label, $value){
    ?>
    <label for="newTemplate[<?= $name ?>]" class="label_input column-2:">
        <span style="padding:10px 0px; font-size:1.2em; color:darkblue"> <?= $label ?></span>
        <span style="margin:20px; font-size:1em;">
       <br> use this to add template feilds  <br> eg [field-name] <span style="font-size:0.9em; color:darkblue" >replace field-name with the name of the fields you specified above</span> <br> [recipient-name]
    </span>
        <textarea style="margin: 5px 0px" rows="20" cols='50' class="large-text" name="newTemplate[<?= $name ?>]"
                  id=""><?=$value?></textarea>
    </label>
    <?php
}
function addMailSelect($parent = null){
    if(array_key_exists('mail', $_REQUEST))
    {
        $mail = new BincomAutomatedMails($_REQUEST['mail'])
        ?>
        <input type='hidden' value=<?= $mail->id()?>>
        <?php
    }else{
        $mails = BincomAutomatedMails::find();
        ?>
        Mail:
        <select name='newTemplate[parent_id]'>
            <option value=0>Select Mail </option>
            <?php
            foreach($mails as $mail){
                ?>
                <option value=<?= $mail->id()?> <?php if($parent && ($parent == $mail->id())){ echo 'selected';} ?>>  <?= $mail->name ?> </option>
                <?php
            }
            ?>
        </select>
        <?php
    }
}
function addSelect($name, $label , $options){
    ?>
    <div style="display:block; margin: 10px 0px" >
        <label for="newTemplate[<?= $name ?>]" class="label_input">
            <span style="padding:10px 2px; font-size:1.2em; color:darkblue"><?= $label ?></span>
        </label>
        <select name="newTemplate[<?= $name ?>]" >
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
    <h2>Edit Template</h2>

    <div class='metabox-holder'
         style="display: flex; flex-flow:row wrap; justify-content:start; background-color:rgba(255,255,255,0.2); padding:20px">
        <form method="post" class='form' style="flex: 0 0 80%; ">
            <div style='display:block; padding:10px 0px'><?php addMailSelect($template->parent_id)?> </div>
            <?php addInput('name','Template Name',$template->name) ?>
            <?php addInput('title','Input Value required', $template->title) ?>
            <?php addInput('fields',"input Fields needed  to fill the template and values  separate fields with && eg <strong> field-name||value1&&field2&&value2 </strong>  ", $template->fields) ?>
            <?php addTextInput('content','Template',$template->content) ?>
            <button type="submit" class="button button-primary" name='edit_template'
                    value="<?= wp_create_nonce('edit_template')?>"> Update Template </button>
        </form>
    </div>
</div>