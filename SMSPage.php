<?php

require_once dirname(__FILE__) . '/forms/Page.php';
require_once dirname(__FILE__) . '/forms/NewsContainer.php';

class SMSPage extends Page
{
    public function __construct( $title ) {
        parent::__construct( "Sender - $title" );
    }
}