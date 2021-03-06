<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function(){
            $ids = DB::table('old_address')
            ->select('address_id')
            ->get();

                $idsJson = json_encode($ids);
                $ids = json_decode($idsJson, true);

                $insertBackup = DB::table('address')
                        ->select('id as address_id', 'name', 'email', 'address_1', 'address_2', 'mobile', 'city', 'state', 'pincode')
                        ->whereNotIn('id', $ids)
                        ->get();


                if(count($insertBackup)>0){
                    DB::table('address')
                        ->whereNotIn('id', $ids)
                        ->update(['is_copied' => 1]);

                    $insertBackupJson = json_encode($insertBackup);
                    $insertBackup = json_decode($insertBackupJson, true);

                    DB::table('old_address')
                        ->insert($insertBackup);
                }

                $updateBackup = DB::table('address')
                        ->select('id as address_id', 'name', 'email', 'address_1', 'address_2', 'mobile', 'city', 'state', 'pincode')
                        ->whereIn('id', $ids)
                        ->where('is_copied', 0)
                        ->get();

                $updateBackupJson = json_encode($updateBackup);
                $updateBackup = json_decode($updateBackupJson, true);

                DB::table('address')
                    ->whereIn('id', $ids)
                    ->where('is_copied', 0)
                    ->update(['is_copied' => 1]);

                foreach ($updateBackup as $toUpdate) {
                    DB::table('old_address')
                        ->where('address_id', $toUpdate['address_id'])
                        ->update($toUpdate);
                }
        })->hourly();
                 
    }
}
