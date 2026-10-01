<?php

return [
    // Horas máximas de respuesta por prioridad de ticket.
    // 1 = Baja, 2 = Media, 3 = Alta, 4 = Urgente
    'priority_hours' => [
        1 => 72,
        2 => 48,
        3 => 24,
        4 => 8,
    ],

    'business_hours' => [
        'start' => 7,
        'end' => 17,
        'weekdays' => [1, 2, 3, 4, 5],
    ],
];
