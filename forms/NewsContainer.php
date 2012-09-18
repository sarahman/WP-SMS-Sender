<?php

require_once dirname(__FILE__) . '/Container.php';

class NewsContainer extends Container {

    protected $feed;

    public function __construct( $title, $feed ) {
        parent::__construct( $title, array( $this, 'RenderContent' ) );
        $this->feed = $feed;
    }

    public function RenderContent() {
        $http = new WP_Http;
        if($http) {
            $reply = $http->request( $this->feed );
            if($reply && is_array($reply)) {
                $news = array();
                $news_count = preg_match_all('/<title>(.*?)<\/title>.*?<link>(.*?)<\/link>/is', $reply['body'], $news);
                if($news_count > 1) {
                    $idx = 1;
                    while($idx < count($news[0])) {
                        echo '<li><a href="' . $news[2][$idx] . '">' . $news[1][$idx]. '</a></li>';
                        $idx++;
                    }
                }
            }
        }
    }

}

?>