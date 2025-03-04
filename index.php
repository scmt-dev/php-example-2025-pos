<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to POS</title>
</head>
<body>
    <h1>POS App</h1>
    2+2
    <?php
        echo (1+1).'<br>';
        echo (3*3).'<br>';
        echo (4/2).'<br>';

        $name = 'Mark';
        $age = 21.4;
        $PI = 3.14;

        echo $name;
        echo '<br>';
        echo $age;
        
        $a = 2;
        $b = 3;
        $c = $a + $b;
        $d = $a > $b;
        echo $c;
        echo '<br>';
        echo $d;
        if($a > $b){
            echo 'a ໃຫຍ່ກ່ວາ b';
        }else {
            echo 'b ໃຫຍ່ກ່ວາ a';
        }

    $colors = ['red',1, 'blue', $a, $d];
    echo $colors[0];
    echo $colors[4];   echo '<br>';
    var_dump($colors); echo '<br>';
    var_dump($a);      echo '<br>';
    print_r($colors);
    
     // set time zone
    date_default_timezone_set('Asia/Bangkok');
    echo date('H:i A j M, Y');

    $date = date('H:i A j M, Y');
    $IP = $_SERVER['REMOTE_ADDR'];
    var_dump($_SERVER);
    file_put_contents('log.txt',$IP.' '.$date.PHP_EOL, FILE_APPEND);
    file_put_contents('log-user.txt',$IP.' '.$date.PHP_EOL, FILE_APPEND);

    ?>
    <a href="test.php">Test</a>
</body>
</html>