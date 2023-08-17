<?php

// Train model

error_reporting(E_ALL);

$command = 'crf_learn rod.template rod.train rod.model';

system($command);


?>
