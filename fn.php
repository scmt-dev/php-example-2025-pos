<?php

    function sum($a, $b) {
        return $a + $b;
    }
    function minus($a, $b) {
        return $a - $b;
    }
    function sayHi($name) {
        echo 'Hi '.$name;
    }
    sayHi('Mark');
    echo date('Y');
    echo sum(1, 2);
    echo sum(3, 4);
    echo sum(100, 6) + 2 ;
    echo sum(1, 2) + sum(3,4);

    // function
    echo 'Hello fn.php';
?>
