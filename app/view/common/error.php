<?php
/**
* @var $title string
* @var $error_text string
* @var $trace string
*/
?>
<h1><?=$title?></h1>
<h3><?=$error_text?></h3>
<?php if($trace){ ?>
    <p><?=nl2br($trace)?></p>
<?php } ?>
