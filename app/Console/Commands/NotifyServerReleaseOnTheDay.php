<?php

namespace App\Console\Commands;

use App\ServerRequest;
use Illuminate\Console\Command;
use Mail;
use Illuminate\Support\Facades\DB;

class NotifyServerReleaseOnTheDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serverRelease:onTheDay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will check for servers with end date in the current date and send server release notification mail';

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
        $serverRequests = ServerRequest::select(
            'server_requests.id as id',
            'server_requests.user_id as reporting_user',
            'server_requests.project_name as project_name',
            'server_requests.start_date as start_date',
            'server_requests.end_date as end_date',
            'operating_system.name as operating_system',
            'server_requests.cpu_configuration as cpu_configuration',
            'server_requests.ram_configuration as ram_configuration',
            'server_requests.hdd_configuration as hdd_configuration',
            'server_requests.stakeholders as stakeholders',
            'server_requests.admin_comment as comment',
            'server_requests.status as status',
            'server_requests.server_ip as server_ip',
            'server_requests.responsible_person as responsible_person'
        )
        ->where('end_date', '=', Carbon::now()->toDateString())
        ->join('operating_system', 'server_requests.operating_system', '=', 'operating_system.id')
        ->get();
        
        foreach ($serverRequests as $serverRequest) {

            $reportingUser = User::where("id", '=', $serverRequest->reporting_user)->first();
            $responsiblePerson = User::where("id", '=', $serverRequest->responsible_person)->first();

            $user = array (
                'email' => $reportingUser->email,
                'name' => $reportingUser->name,
                'responsiblePersonEmail' => $responsiblePerson->email
            );
            $data = array(
                'reference'=> $serverRequest->id,
                'requester'=> $reportingUser->name,
                'projectName' => $serverRequest->project_name,
                'startDate' => $serverRequest->start_date,
                'endDate' => $serverRequest->end_date,
                'operatingSystem' => $serverRequest->operating_system,
                'cpuConfiguration' => $serverRequest->cpu_configuration,
                'ramConfiguration' => $serverRequest->ram_configuration,
                'hddConfiguration' => $serverRequest->hdd_configuration,
                'stakeholders' => $serverRequest->stakeholders,
                'comment' => $serverRequest->comment,
                'server_ip' => $serverRequest->server_ip,
                'responsible_person' => $responsiblePerson->name,
                'end_message' => "and is going to end today"
            );

            Mail::send("serverReleaseNotification", $data, function ($mail) use ($user) {
                $mail
                ->from(env('MAIL_FROM_ADDRES'))
                ->to($user['email'], $user['name'])
                ->cc([
                    $user['responsiblePersonEmail'],
                    env('MAIL_TO_ADDRESS')
                ])
                ->subject('Server Release Deadline Notification');
            });
            
        } 
        $this->info('Sending on the day server release deadline notification mail');
    }
}
