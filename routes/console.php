<?php

use Illuminate\Support\Facades\Schedule;

/* Payment verification */
Schedule::command('payments:reconcile --limit=100')
    ->everyFiveMinutes()
    ->withoutOverlapping();

/* Front Desk */
Schedule::command('hotel:arrival-reminders --hours=2')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('hotel:departure-reminders --hours=1')
    ->hourly()
    ->withoutOverlapping();

/* Inventory */
Schedule::command('inventory:low-stock-alerts')
    ->hourly()
    ->withoutOverlapping();

/* Management */
Schedule::command('hotel:management-snapshot')
    ->dailyAt('05:30')
    ->withoutOverlapping();

/* Health */
Schedule::command('hotel:health-check --store')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

/* Queue maintenance */
Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('04:15')
    ->withoutOverlapping();

/* Staff task reminders */
Schedule::command('tasks:send-due-reminders')
    ->everyMinute()
    ->withoutOverlapping();
