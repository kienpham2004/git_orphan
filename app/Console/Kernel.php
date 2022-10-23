<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		//'App\Console\Commands\Inspire',
		//'App\Console\Commands\UserRegister',
		// Original
		'App\Console\Commands\CsvLongUpdate',
		'App\Console\Commands\CsvShortUpdate',
		'App\Console\Commands\ExtractTargetCars',
		'App\Console\Commands\ExtractResultCars',
		'App\Console\Commands\ExtractDmHanyou',
		'App\Console\Commands\ExtractDmSyaken',
		'App\Console\Commands\ExtractIkou',
		'App\Console\Commands\CheckTargetCars',
		'App\Console\Commands\CheckManageInfo',
		'App\Console\Commands\CheckResult',
		'App\Console\Commands\CheckResultCars',
		'App\Console\Commands\CleanRecords',
        'App\Console\Commands\ConvertCodeToID',
		'App\Console\Commands\ExportLogToZipFileOfDay',
		'App\Console\Commands\ExportLogToZipFileOfMonth'
		#'App\Console\Commands\ExtractDmEvent'
	];
	
	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule( Schedule $schedule ){
		$schedule->command('inspire');
	}
	
}
