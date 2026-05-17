<?php
// Discounts we receive from Peyflex as an API user (in %)
// These are the percentages OFF the standard price that we pay.
return [
    'data' => [
        'mtn'       => 5,   // we pay 95% of standard price
        'glo'       => 2,
        'airtel'    => 5,
        '9mobile'   => 5,
    ],
    'airtime' => [
        'mtn'       => 1,   // we pay 99% of face value
        'glo'       => 2,
        'airtel'    => 1.4,
        '9mobile'   => 2,
    ],
];
