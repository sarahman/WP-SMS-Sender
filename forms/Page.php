<?php

class Page {

    protected $title;
    protected $content_boxes;
    protected $side_boxes;
    protected $form = null;

    public function __construct( $title ) {
        $this->title = $title;
        $this->content_boxes = array();
        $this->side_boxes = array();
    }

    public function AddContentBox( $cb ) {
        $this->content_boxes[] = $cb;
    }

    public function AddSideBox( $sb ) {
        $this->side_boxes[] = $sb;
    }

    public function AddForm( $action, $method, $hidden = null ) {
        $this->form = array(
            'action' => $action,
            'method' => $method,
            'hidden' => $hidden
        );
    }

    public function Render() { ?>
        <div class="wrap">
            <div id="icon-plugins" class="icon32"><br /></div>
              <h2><?php echo $this->title; ?></h2>
            <div id="poststuff" class="metabox-holder has-right-sidebar">
                <?php $this->RenderSideBar(); ?>

                <?php if ( $this->form ) { ?>
                    <form action="<?php echo $this->form[ 'action' ]; ?>" method="<?php echo $this->form[ 'method' ]; ?>">
                    <?php if ( $this->form[ 'hidden' ] ) { ?>
                        <input type="hidden" name="<?php echo $this->form[ 'hidden' ]; ?>" />
                    <?php } ?>
                <?php } ?>

                <?php $this->RenderContent(); ?>

                <?php if ( $this->form ) { ?>
                    </form>
                <?php } ?>
            </div> <!--- class="metabox-holder" --->
        </div> <!-- class="wrap" -->
    <?php }

    protected function RenderSideBar() { ?>
        <div id="side-info-column" class="inner-sidebar">
            <div id='side-sortables' class='meta-box-sortables'>
                <?php
                    foreach ( $this->side_boxes as $box ) {
                        $box->Render();
                    }
                ?>
            </div> <!---  class='meta-box-sortables' --->
        </div> <!--- class="inner-sidebar" --->
    <?php }

    protected function RenderContent() { ?>
        <div id="post-body" class="has-sidebar">
            <div id="post-body-content" class="has-sidebar-content">
                <div id="normal-sortables" class="meta-box-sortables">
                    <?php
                        foreach ( $this->content_boxes as $box ) {
                            $box->render();
                        }
                    ?>
                  </div> <!--- class='meta-box-sortables' --->
            </div> <!--- class="has-sidebar-content" ---> 
        </div> <!--- class="has-sidebar" --->
    <?php }
}

?>