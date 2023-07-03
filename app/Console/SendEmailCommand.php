<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\MailQueue;
use App\Models\Parcel;
use App\Models\Receivers;
use App\Services\EmailService;
use Exception;
use Illuminate\Console\Command;
use Throwable;

class SendEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $emails = MailQueue::query()
            ->where('sent', 0)
            ->take(10)
            ->get();

        /** @var EmailService $emailService */
        $emailService = app()->make(EmailService::class);

        foreach ($emails as $email) {
            try {
                $parcel = Parcel::query()->where('ParcelNumber', $email->ParcelNumber)->first();
                echo $email->ParcelNumber;
                $receiver = Receivers::find($parcel->Receiver);
                if ($receiver) {
                    $client = Client::find($parcel->clientId);
                    $mailStatus = false;
                    if (!empty($receiver->email)) {
                        $mailStatus = $emailService->sendEmail(
                            $receiver,
                            $email->statusId,
                            $email->ParcelNumber,
                            $client
                        );
                    }
                    var_dump($mailStatus);
                    if ($mailStatus) {
                        $email->sent = 1;
                        $email->save();
                    }
                }
            } catch (Throwable $exception) {
                dump($exception->getLine());
                dump($exception->getFile());
                dump($exception->getMessage());
                $email->delete();
            }
        }

        dd('1');
    }
}
