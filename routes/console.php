<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('afren:process-subscriptions')->dailyAt('02:00');
