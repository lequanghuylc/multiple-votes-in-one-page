<?php
require('../../../wp-blog-header.php');

if (isset($_POST['postid']) && isset($_POST['countvalue'])){
    update_post_meta($_POST['postid'],'_vote', $_POST['countvalue']);
}

?>