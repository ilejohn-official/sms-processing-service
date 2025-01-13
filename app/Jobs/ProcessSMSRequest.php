<?php

namespace App\Jobs;

use App\Enums\SMSStatus;
use App\Models\SmsRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProcessSMSRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of retry attempts on failure
     */
    public $tries = 1; 

    /**
     * Timeout for the job
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(protected SmsRequest $smsRequest)
    {
        //
    }

    /**
     * Execute the job to process SMS request.
     */
    public function handle(): void
    {
        // Skip invalid request
        if (! $this->isValidInput()){
            return;
        }

        // Stop processing if it's a duplicate
        if ($this->isDuplicateRequest()) {
            Log::info('Duplicate SMS request skipped', [
                'phone_number' => $this->smsRequest->phone_number,
                'sms_request_id' => $this->smsRequest->id,
            ]);
            return;
        }

        // Send a mock SMS API request (simulating ISP delivery)
        $response = $this->sendMockSms();

        // Update the SMS request status based on the response
        if ($response['status'] === 'success') {
            $this->smsRequest->status = SMSStatus::DELIVERED->value;
            Log::info('SMS sent successfully', [
                'phone_number' => $this->smsRequest->phone_number,
                'sms_request_id' => $this->smsRequest->id,
            ]);
        } else {
            $this->smsRequest->status = SMSStatus::FAILED->value;
            Log::error('SMS delivery failed', [
                'phone_number' => $this->smsRequest->phone_number,
                'sms_request_id' => $this->smsRequest->id,
                'error_message' => $response['message'] ?? 'Unknown error',
            ]);
        }

        // Save SMS request status
        $this->smsRequest->save();
    }

    /**
     * Simulate sending an SMS to an ISP (Mock API Call).
     *
     * @return array
     */
    private function sendMockSms()
    {
         // Log the start of the SMS processing
         Log::info("Processing SMS request for phone number: {$this->smsRequest->phone_number}");

         //Mock code for testing process. Real code will replace the api
         Http::fake([
            'https://mock-isp.com/sms' => Http::response(['status' => 'success'], 200),
            
            // 'https://mock-isp.com/sms' => Http::response(['status' => 'failure'], 500),
        ]);

        try {
            // Simulate a mock API call to an ISP's SMS delivery service
            $response = Http::post('https://mock-isp.com/sms', [
                'phone_number' => $this->smsRequest->phone_number,
                'message' => $this->smsRequest->message,
            ]);

            $responseBody = $response->json();

            if ($response->successful()) {
                return ['status' => 'success'];
            } else {
                return ['status' => 'failed', 'message' => $responseBody['error'] ?? 'Unknown error'];
            }
        } catch (\Exception $e) {
            Log::error('Error during SMS delivery simulation', [
                'exception' => $e->getMessage(),
                'sms_request_id' => $this->smsRequest->id,
            ]);

            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    /**
     * 
     * Validate input
     * 
     * @return bool
     */
    private function isValidInput(): bool
    {
        $validator = Validator::make(
            ['message' => $this->smsRequest->message, 'phone_number' => $this->smsRequest->phone_number],
            [
                'message' => 'required|string|min:1',
                'phone_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
            ]
        );

        if ($validator->fails()) {
            Log::error('Invalid SMS request', [
                'errors' => $validator->errors(),
                'sms_request_id' => $this->smsRequest->id,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Check if the SMS request is a duplicate.
     *
     * @return bool
     */
    private function isDuplicateRequest(): bool
    {
        return SmsRequest::withStatus(SMSStatus::DELIVERED)
            ->where([
                'phone_number' => $this->smsRequest->phone_number,
                'message' => $this->smsRequest->message
            ])
            ->exists();
    }
}
