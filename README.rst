Mute Pinba
==========


Implements `Pinba`_ in pure PHP. See `examples`_ for usage.

This library package requires `PHP 5.3`_ or later.


How to use this library
-----------------------

Monitor a script, with a timer::

    <?php
    
    $collector = new \Mute\Pinba\DataCollector(SCRIPT_NAME, HOST_NAME);

    // start monitoring
    $collector->start();

    // monitor a function
    $timer = $collector->timer(array('foo' => 'bar'))->start();
    func();
    $timer->stop();

    // stop monitoring
    $collector->stop();


    // send result to Pinba Server
    $reporter = new \Mute\Pinba\Reporter(ADDRESS_HOST, ADDRESS_PORT);
    $reporter($collector);


.. _Pinba: https://http://pinba.org
.. _examples: https://github.com/johnnoone/php-pinba/tree/master/example
.. _PHP 5.3: http://php.net/releases/5_3_0.php
