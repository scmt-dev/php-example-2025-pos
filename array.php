<?php

// example array
$fruits = array("apple", "banana", "orange");
echo $fruits[0];

// array key value
$colors = array(
    "red" => "#ff0000",
    "green" => "#00ff00",
    "yellow" => "#ffff00"
);
echo $colors["red"];
// array nested
$person = array(
    "name" => "John Doe",
    "age" => 30,
    "address" => [
        "street" => "123 Main St",
        "city" => "New York",
        "state" => "NY"
    ]
);
echo $person["address"]["city"];

$customers = array(
    "customer1" => array(
        "name" => "John Doe",
        "email" => "q7d7j@example.com",
        "phone" => "123-456-7890"
    ),
    "customer2" => array(
        "name" => "Jane Doe",
        "email" => "l0D0y@example.com",
        "phone" => "987-654-3210"
    )
);
echo '<hr>';
// loop array
echo "<table class='table' border='1'>";
echo "<tr><th>Name</th><th>Email</th><th>Phone</th></tr>";
foreach ($customers as $customer) {
    echo "<tr>";
        echo "<td>".$customer["name"]."</td>";
        echo "<td>".$customer["email"]."</td>";
        echo "<td>".$customer["phone"]."</td>";
    echo "</tr>";
}
echo "</table>";