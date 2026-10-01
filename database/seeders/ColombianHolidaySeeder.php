<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ColombianHolidaySeeder extends Seeder
{
    public function run(): void
    {
        foreach (range((int) date('Y'), (int) date('Y') + 4) as $year) {
            foreach ($this->holidaysForYear($year) as $holiday) {
                Holiday::updateOrCreate(
                    ['holiday_date' => $holiday['date']],
                    [
                        'name' => $holiday['name'],
                        'scope' => 'national',
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function holidaysForYear(int $year): array
    {
        $holidays = [
            ["{$year}-01-01", 'Año Nuevo'],
            ["{$year}-05-01", 'Día del Trabajo'],
            ["{$year}-07-20", 'Día de la Independencia'],
            ["{$year}-08-07", 'Batalla de Boyacá'],
            ["{$year}-12-08", 'Inmaculada Concepción'],
            ["{$year}-12-25", 'Navidad'],
        ];

        $easter = Carbon::create($year, 3, 21)->addDays(easter_days($year));
        $mondayAfter = function (int $days, string $name) use ($easter): array {
            $date = $easter->copy()->addDays($days);
            while (!$date->isMonday()) {
                $date->addDay();
            }

            return [$date->startOfDay()->toDateString(), $name];
        };

        $holidays = array_merge($holidays, [
            $mondayAfter(-3, 'Jueves Santo'),
            $mondayAfter(-2, 'Viernes Santo'),
            $mondayAfter(43, 'Ascensión del Señor'),
            $mondayAfter(64, 'Corpus Christi'),
            $mondayAfter(71, 'Sagrado Corazón de Jesús'),
        ]);

        foreach ([
            [1,  6,  'Día de los Reyes Magos'],
            [3, 19, 'Día de San José'],
            [6, 29, 'San Pedro y San Pablo'],
            [8, 15, 'Asunción de la Virgen'],
            [10, 12, 'Día de la Raza'],
            [11, 2,  'Todos los Santos'],
            [11, 11, 'Independencia de Cartagena'],
        ] as [$month, $day, $name]) {
            $date = Carbon::create($year, $month, $day);
            while (!$date->isMonday()) {
                $date->addDay();
            }
            $holidays[] = [$date->toDateString(), $name];
        }

        return collect($holidays)->map(fn ($holiday) => [
            'date' => $holiday[0],
            'name' => $holiday[1],
        ])->unique('date')->all();
    }
}
