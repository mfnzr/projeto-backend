<?php

$json['side_dish'] = [
    'id' => $sideDish->id,
    'name' => $sideDish->name,
    'price' => 'R$ ' . number_format((float) $sideDish->price, 2, ',', '.'),
];
