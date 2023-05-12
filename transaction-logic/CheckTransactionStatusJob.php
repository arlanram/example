<?php

class CheckTransactionStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Transaction $transaction;

    public const arrayNextTime = [
        0 => 60, // 1 minute
        1 => 60, // 1 minute
        2 => 120, // 2 minutes
        3 => 300, // 5 minutes
        4 => 600, // 10 minutes
        5 => 1200, // 20 minutes
        6 => 1800, // 30 minutes
        7 => 3600, // 60 minutes
        8 => 86700, // 24 hours 5 minutes
        9 => 86700, // 24 hours 5 minutes
        10 => 86700, // 24 hours 5 minutes
        11 => 86700 // 24 hours 5 minutes
    ];

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;

        if($this->transaction->status_check_count >= $this->getMaxAttemptValue() OR $this->transaction->status_check_next_time >= Carbon::now()->toDateTimeString() ) {
            return;
        }

        $this->upAttempt();
        $this->transaction->refresh();
    }

    public function handle()
    {
        switch ($this->transaction->provider->name) {
            case TransactionProviderEnum::PROVIDER_NAME_1()->getValue():
                $refId = $this->transaction->individualPayment()->first();

                $dto = new RequestDto('', '', '', '', '', $refId->uuid, '');

                $service = new Service();
                $service->setTransactionMerchantId($this->transaction->merchant_id);
                $service->callbackResponse($dto);

                $this->transaction->refresh();

                break;
            case TransactionProviderEnum::PROVIDER_NAME_2()->getValue():
                try {
                    $service = new Service(new Repository());
                    $service->getStatusForTransaction($this->transaction);
                }
                catch (Exception $e) {
                    Log::error($e->getMessage());
                } catch (GuzzleException $e) {
                    Log::error($e->getMessage());
                }

                break;
        }

        $this->transaction->refresh();

        switch ($this->transaction->status->name) {
            case TransactionStatusEnum::APPROVED()->getValue():
                CallbackNotifyJob::dispatch($this->transaction);
                AddFeeJob::dispatch($this->transaction);
                break;
            case TransactionStatusEnum::DECLINED()->getValue():
                CallbackNotifyJob::dispatch($this->transaction);
                break;
        }
    }

    private function upAttempt()
    {
        $this->transaction->status_check_count += 1;

        $nextAttemptTime = Carbon::now()->addSeconds($this->getNextAttempTime($this->transaction->status_check_count));
        $this->transaction->status_check_next_time = $nextAttemptTime;

        $this->transaction->save();
    }

    private function getNextAttempTime($attempt)
    {
        $maxAttempt = $this->getMaxAttemptValue();

        if ($attempt > $maxAttempt) {
            $nextAttempt = $this::arrayNextTime[$maxAttempt];
        } else {
            $nextAttempt = $this::arrayNextTime[$attempt];
        }

        return $nextAttempt;
    }

    private function getMaxAttemptValue(): int
    {
        return count($this::arrayNextTime) - 1;
    }
}