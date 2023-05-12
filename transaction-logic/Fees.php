<?php

class Fees
{
    protected $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    private function getFeeValue(string $type)
    {
        $value = ConfigService::getByMerchant($this->transaction->merchant_id, "params.{$this->transaction->provider->name}.{$type}");

        if ($value == null) {
            $value = ConfigService::getCommon("params.{$this->transaction->provider->name}.{$type}", 0);
        }

        return $value;
    }

    public function add($additionalAttributes = [])
    {
        $additionalAttributes = is_array($additionalAttributes) ? $additionalAttributes : [$additionalAttributes];

        $attributes = [
            'mdr'             => $this->getMDR(),
            'transaction_fee' => TransactionFeeService::getFeeForTransaction($this->transaction),
            'flat_fee'        => TransactionFeeService::getFeeFlatValueForTransaction($this->transaction),
            'percentage_fee'  => TransactionFeeService::getFeePercentValueForTransaction($this->transaction),
        ];

        $optionalAttributes = [
            'refund_fee' => $this->getRefundFee(),
        ];

        foreach ($optionalAttributes as $optionalAttribute => $optionalFeeValue) {
            if (!in_array($optionalAttribute, $additionalAttributes)) {
                continue;
            }

            $attributes[$optionalAttribute] = $optionalFeeValue;
        }

        if (array_sum($attributes) === 0) {
            return;
        }

        $fee = TransactionFee::where('transaction_id', $this->transaction->id)->first();
        
        if (!$fee) {
            $fee = new TransactionFee();
        }

        $fee->transaction_id = $this->transaction->id;
        $fee->forceFill($attributes);
        $fee->save();
    }

    public function getMDR(): float
    {
        return $this->transaction->amount_eur * ($this->getFeeValue('merchant_discount_rate') / 100);
    }

    public function getTransactionFeeFlat(): float
    {
        $transaction_fee = 0;
        $transaction_fee_percent = 0;
        $currency = strtolower($this->transaction->currency);
        $transactionCountryIso = '';

        $transactionCountry = Country::where('id', $this->transaction->country_id)->first();

        if (!empty($transactionCountry)) {
            $transactionCountryIso = $transactionCountry->iso_3;
        }

        $transaction_fee_value_with_country = (float)$this->getFeeValue($typeForTransactionFeeFlat);
        $transaction_fee_value = $transaction_fee_value_with_country > 0 ? $transaction_fee_value_with_country : (float)$this->getFeeValue('transaction_fee_flat.' . $currency);

        if (!empty($transaction_fee_value)) {
            $transaction_fee = $transaction_fee_value;
        }

        $transaction_fee_percent_value_with_country = $this->getFeeValue($typeForTransactionFeePercent);
        $transaction_fee_percent_value = $transaction_fee_percent_value_with_country > 0 ? $transaction_fee_percent_value_with_country : $this->getFeeValue('transaction_fee_percent');

        if (!empty($transaction_fee_percent_value)) {
            $transaction_fee_percent = $this->transaction->amount * ($transaction_fee_percent_value / 100);
        }

        return (float)($transaction_fee + $transaction_fee_percent);
    }

    public function getTransactionFee(): float
    {
        $transaction_fee = 0;
        $transaction_fee_percent = 0;
        $transactionCountry = Country::where('id', $this->transaction->id)->first();
        $transactionCountryIso = '';

        if (!empty($transactionCountry)) {
            $transactionCountryIso = $transactionCountry->iso_3;
        }

        $transaction_fee_value_with_country = (float)$this->getFeeValue($typeForTransactionFee);
        $transaction_fee_value = $transaction_fee_value_with_country > 0 ? $transaction_fee_value_with_country : (float)$this->getFeeValue('transaction_fee');

        if (!empty($transaction_fee_value)) {
            $fee_rate = $this->getFeeRate();
            $transaction_fee = $transaction_fee_value * $fee_rate;
        }

        $transaction_fee_percent_value_with_country = $this->getFeeValue($typeForTransactionFeePercent);
        $transaction_fee_percent_value = $transaction_fee_percent_value_with_country > 0 ? $transaction_fee_percent_value_with_country : $this->getFeeValue('transaction_fee_percent');

        if (!empty($transaction_fee_percent_value)) {
            $transaction_fee_percent = $this->transaction->amount * ($transaction_fee_percent_value / 100);
        }

        return (float)($transaction_fee + $transaction_fee_percent);
    }

    public function getRefundFee(): float
    {
        $transactionCountry = Country::where('id', $this->transaction->id)->first();
        $transactionCountryIso = '';

        if (!empty($transactionCountry)) {
            $transactionCountryIso = $transactionCountry->iso_3;
        }

        $refund_fee_value_with_country = (float)$this->getFeeValue($typeForRefundFee);
        $refund_fee_value = $refund_fee_value_with_country > 0 ? $refund_fee_value_with_country : (float)$this->getFeeValue('refund_fee');
        $refund_fee = 0;

        if (!empty($refund_fee_value)) {
            $fee_rate = $this->getFeeRate();
            $refund_fee = $refund_fee_value * $fee_rate;
        }

        return (float)$refund_fee;
    }

    public function getFeeRate(): float
    {
        $fee_rate = $this->transaction->amount / $this->transaction->amount_eur;

        return (float)$fee_rate;
    }
}
