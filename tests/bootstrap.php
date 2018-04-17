<?php
require __DIR__.'/../vendor/autoload.php';


\VCR\VCR::configure()->setCassettePath('tests/fixtures');



\VCR\VCR::turnOn();
\VCR\VCR::turnOff();

//\VCR\VCR::insertCassette('tests');