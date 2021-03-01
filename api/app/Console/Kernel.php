<?php

namespace App\Console;

use App\Console\Commands\AutomaticDelivery;
use App\Console\Commands\CouponExpireDispose;
use App\Console\Commands\CouponStartDispose;
use App\Console\Commands\AutomaticReceiving;
use App\Console\Commands\OrderInvalidationHandling;
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
        CouponStartDispose::class,
        CouponExpireDispose::class,
        AutomaticDelivery::class,
        OrderInvalidationHandling::class,
        AutomaticReceiving::class,
        AutomaticDelivery::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('coupon:expire')->dailyAt('00:00')->withoutOverlapping(10);
        $schedule->command('coupon:start')->dailyAt('00:00')->withoutOverlapping(10);
        if(config('backup.switch')){    //是否开启备份功能
            $schedule->command('backup:clean')->daily()->at(config('backup.clean_time'));
            if (config('backup.db_time') || config('backup.files_time')) {   //设置了数据库备份时间或文件备份时间
                if (config('backup.db_time')) {
                    $schedule->command('backup:run --only-db')->dailyAt(config('backup.db_time'));
                }
                if (config('backup.files_time')) {
                    $schedule->command('backup:run --only-files')->dailyAt(config('backup.files_time'));
                }
            } else {  //设置了备份时间
                $schedule->command('backup:run')->dailyAt(config('backup.time'));
            }
        }

        //以下任务调试可直接删除
        //自动发货
        $schedule->command('automatic:delivery')->everyMinute();
        if (config('dswjcms.automaticReceivingState')) {    //是否开启自动收货
            $schedule->command('automatic:receiving')->dailyAt('00:20');
        }
        if (config('comment.automaticEvaluateState')) {    //是否开启自动好评
            $schedule->command('automatic:evaluate')->everyMinute();
        }
        //订单失效处理
        $schedule->command('order:invalidation')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
