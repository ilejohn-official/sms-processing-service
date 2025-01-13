<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSMSRequest;
use App\Models\SmsRequest;
use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;
use Illuminate\Console\Command;

class PollSQSQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sqs-poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll AWS SQS queue for SMS requests and process them';

    protected $sqsClient;

    protected $queueUrl;

    public function __construct()
    {
        parent::__construct();

        $this->sqsClient = new SqsClient([
            'version' => 'latest',
            'region'  => config('services.sqs.region'),
            'credentials' => [
                'key'    => config('services.sqs.key'),
                'secret' => config('services.sqs.secret'),
            ],
        ]);

        $this->queueUrl = config('services.sqs.sqs_queue_url');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        while (true) {
            try {
                // Receive messages from the queue
                $result = $this->sqsClient->receiveMessage([
                    'QueueUrl' => $this->queueUrl,
                    'MaxNumberOfMessages' => 10,
                    'WaitTimeSeconds' => 20,
                ]);

                // No messages, continue polling
                if (empty($result->get('Messages'))) {
                    continue; 
                }

                foreach ($result->get('Messages') as $message) {
                    $this->info("Processing SMS request: {$message['MessageId']}");

                    $smsData = json_decode($message['Body'], true);

                    // Create a new SmsRequest and dispatch job
                    $smsRequest = SmsRequest::create([
                        'phone_number' => $smsData['phone_number'],
                        'message' => $smsData['message'],
                    ]);

                    ProcessSMSRequest::dispatch($smsRequest);

                    // Delete the message from the queue after processing
                    $this->sqsClient->deleteMessage([
                        'QueueUrl' => $this->queueUrl,
                        'ReceiptHandle' => $message['ReceiptHandle'],
                    ]);
                }
            } catch (AwsException $e) {
                $this->error("Error receiving message: " . $e->getMessage());
            }
        }
    
    }
}
