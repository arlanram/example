<?php

class CreditCardFactory
{
    protected static $cards = [
        Maestro::class,
        AmericanExpress::class,
        Mastercard::class,
        UnionPay::class,
        Visa::class,
    ];

    public static function makeFromNumber(string $cardNumber)
    {
        return self::determineCardByNumber($cardNumber);
    }

    protected static function determineCardByNumber(string $cardNumber)
    {
        foreach (self::$cards as $card) {
            if (preg_match($card::$pattern, $cardNumber)) {
                return new $card($cardNumber);
            }
        }

        throw new CreditCardException('Card not found.');
    }
}