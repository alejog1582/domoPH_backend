<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar el comando para ejecutarse el último día de cada mes a las 23:59
// Se ejecuta los días 28, 29, 30 y 31 a las 23:59 (cubre todos los posibles últimos días del mes)
Schedule::command('cartera:actualizar-cuentas-vencidas')
    ->cron('59 23 28-31 * *')
    ->when(function () {
        // Verificar que sea el último día del mes
        $hoy = Carbon\Carbon::now();
        $mañana = $hoy->copy()->addDay();
        return $hoy->month !== $mañana->month;
    })
    ->timezone('America/Bogota')
    ->description('Actualizar cuentas de cobro pendientes a vencidas y ajustar carteras');

// Programar el comando para ejecutarse diariamente a las 00:00
Schedule::command('sorteo:asignar-parqueaderos')
    ->daily()
    ->at('00:00')
    ->timezone('America/Bogota')
    ->description('Asignar parqueaderos a participantes favorecidos en sorteos del día');