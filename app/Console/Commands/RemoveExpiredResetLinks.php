<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RemoveExpiredResetLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passwordReset:cleanUp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will remove 30 minutes old password reset links';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Getting reset links created before 30 minutes");
        $entries = DB::table('password_resets')
            ->select('email')
            ->where('created_at', '<=', Carbon::now()->subMinutes(30)->toDateTimeString())
            ->delete();
        $this->info("All entries before 30 minutes from now, deleted.");
    }
}
