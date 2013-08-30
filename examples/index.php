<?php

error_reporting(E_ALL | E_STRICT);

require_once __DIR__ . '/bootstrap.php';

$send = false;
$reporter = new Mute\Pinba\Reporter();

for ($i=0; $i < 20; $i++) {
    $collector = new Mute\Pinba\DataCollector('script', 'host');
    $collector->serverName = "server";
    $collector->start();
    $collector->documentSize = 1024;
    for ($j=0; $j<10; $j++) {
        $timer1 = $collector->timer(array('foo' => 'bar'))->start();
        $timer2 = $collector->timer(array('baz' => 'qux'))->start();
        $timer1->stop();
    }
    $collector->stop();

    $msg = $reporter->prepare($collector);
    print "request #$i " . bin2hex($msg) . PHP_EOL;
    if ($send) {
        print "sending...";
        $reporter->send($msg);
        print "done" . PHP_EOL;
    }
    print PHP_EOL;
}
