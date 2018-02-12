<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		\App\Console\Commands\Inspire::class,
		\App\Console\Commands\ActivateAntiRaid::class,
		\App\Console\Commands\ApplicantMembers::class,
		\App\Console\Commands\MemberApplicants::class,
		\App\Console\Commands\SendRecruitmentMessages::class,
		\App\Console\Commands\RegisterIntegration::class,
		\App\Console\Commands\MiniAudits::class,
		\App\Console\Commands\ModifyUserIntegration::class,
		\App\Console\Commands\ActivateIntegration::class,
		\App\Console\Commands\PreventRaids::class,
		\App\Console\Commands\PullNationStats::class,
		\App\Console\Commands\PullTaxes::class,
		\App\Console\Commands\PullAlliances::class,
		\App\Console\Commands\PullWorldMilitaries::class,
		\App\Console\Commands\PullMarketData::class,
		\App\Console\Commands\ReturnAntiRaid::class,
		\App\Console\Commands\Temp::class,
		\App\Console\Commands\TestFunction::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('schedule:sendrecruitmentmessages')->everyMinute();
		$schedule->command('schedule:preventraids')->cron('2,4 */2 * * * *');
		$schedule->command('schedule:pullnationstats')->cron('* * * * * *');
		$schedule->command('schedule:pullworldmilitaries')->cron('5 */2 * * * *');
		//$schedule->command('applicants:add')->cron('54 * * * * *');
		//$schedule->command('applicants:remove')->cron('20 * * * * *');
		$schedule->command('schedule:pulltaxes')->cron('5 */2 * * * *');
		$schedule->command('schedule:pullalliances')->cron('5 */2 * * * *');
		//$schedule->command('schedule:miniaudit')->cron('0 13 8-14,22-28 * 6 *');
		$schedule->command('schedule:temp')->cron('5 */2 * * * *');
		$schedule->command('schedule:pullmarketdata')->cron('0 * * * * *');
	}
}
