<?php

require_once dirname(__FILE__) . '/Container.php';

class ConfigContainer extends Container {

    protected $config;
    protected $save;

    public function __construct( $title, $config, $save = false ) {
        parent::__construct( $title, array( $this, 'RenderContent' ) );
        $this->config = $config;
        $this->save = $save;
    }

    protected function RenderContent() { ?>
        <table width="100%">
        <?php foreach ( $this->config as $label => $details ) {
            $hint = '';
            if ( is_array( $details ) && isSet( $details[ 'type' ] ) ) {
                if ( $details[ 'type' ] != 'test' ) {
                    $type = $details[ 'type' ];
                    $name = $details[ 'name' ];
                    $value = $details[ 'value' ];
                    if ( isSet( $details[ 'hint' ] ) ) {
                        $hint = $details[ 'hint' ];
                    }
                } else {
                    $type = $details[ 'type' ];
                    $name = $details[ 'name' ];
                    $value = $details[ 'args' ];
                }
            } else {
                $type = 'text';
                $name = $details;
                $value = '';
            }
            call_user_func_array( array( 'wpalBox', 'Render' . $type . 'Option' ), array(
                $label, $name, $value, $hint
            ));
        }
        if ( $this->save ) { ?>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" class="button" value="Save" /></td>
            </tr>
        <?php } ?>
        </table>
    <?php }

}