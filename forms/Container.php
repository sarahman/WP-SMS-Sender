<?php

class Container {

    protected $title;
    protected $content_callback;
    protected $subtitle;

    public function __construct( $title, $content_callback ) {
        $this->title = $title;
        $this->content_callback = $content_callback;
    }

    public function SetSubtitle( $subtitle ) {
        $this->subtitle = $subtitle;
    }

    public function Render() { ?>
        <div class="postbox">
        <h3 class="hndle"><span><?php echo $this->title; ?></span> <?php echo $this->subtitle; ?></h3>
        <div class="inside">
            <?php call_user_func( $this->content_callback ); ?>
        </div> <!--- class='inside' ---> 
    </div> <!--- class='postbox ' ---> 
    <?php }

    static function RenderTextOption( $label, $name, $value, $hint = '' ) { ?>
        <tr>
            <td width="20%"><?php echo $label; ?>:</td>
            <td width="80%"><input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $value; ?>" /> <?php echo $hint; ?></td>
        </tr>
    <?php }

    static function RenderCheckOption( $label, $name, $value ) { ?>
        <tr>
            <td width="20%"><?php echo $label; ?>:</td>
            <td width="80%"><input type="checkbox" name="<?php echo $name; ?>" <?php if ( $value ) { echo 'checked="checked"'; } ?> /></td>
        </tr>
    <?php }

    static function RenderTextAreaOption( $label, $name, $value ) { ?>
        <tr>
            <td width="20%"><?php echo $label; ?>:</td>
            <td width="80%"><textarea name="<?php echo $name; ?>" style="width:100%" rows="10"><?php echo $value; ?></textarea></td>
        </tr>
    <?php }

    static function RenderHiddenOption( $label, $name, $value ) { ?>
        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
    <?php }

    static function RenderTestOption( $label, $name, $value ) {
        $help_src = get_bloginfo( 'wpurl' ) . '/wp-content/plugins/energizer/images/help.png';
    ?>
        <tr>
            <td width="20%">&nbsp;</td>
            <td width="80%">
                <input type="button" value="Test" onClick="rli_test_site( <?php
                echo "'" . implode( "', '", $value ) . "', '$name'";
                ?> );" />
                <img src="<?php echo $help_src; ?>" id="<?php echo $name; ?>_test_image" />
            </td>
        </tr>
    <?php }
}