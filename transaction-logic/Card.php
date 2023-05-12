<?php

abstract class Card
{
    public static $pattern;

    protected $type;

    protected $name;

    protected $brand;

    protected $numberLength;

    protected $cvcLength;

    protected $checksumTest;

    private $cardNumber;

    public function __construct(string $cardNumber = '')
    {
        $this->checkImplementation();

        if ($cardNumber) {
            $this->setCardNumber($cardNumber);
        }
    }

    public function setCardNumber(string $cardNumber)
    {
        $this->cardNumber = preg_replace('/\s+/', '', $cardNumber);

        $this->isValidCardNumber();

        if (!$this->validPattern()) {
            throw new CreditCardPatternException(
                sprintf('Wrong "%s" card pattern', $this->cardNumber)
            );
        }

        return $this;
    }

    public function isValidCardNumber()
    {
        if (!$this->cardNumber) {
            throw new CreditCardException('Card number is not set');
        }

        if (!is_numeric(preg_replace('/\s+/', '', $this->cardNumber))) {
            throw new CreditCardCharactersException(
                sprintf('Card number "%s" contains invalid characters', $this->cardNumber)
            );
        }

        if (!$this->validLength()) {
            throw new CreditCardLengthException(
                sprintf('Incorrect "%s" card length', $this->cardNumber)
            );
        }

        if (!$this->validChecksum()) {
            throw new CreditCardChecksumException(
                sprintf('Invalid card number: "%s". Checksum is wrong', $this->cardNumber)
            );
        }

        return true;
    }

    public function type()
    {
        return $this->type;
    }

    public function name()
    {
        return $this->name;
    }

    public function brand()
    {
        return $this->brand;
    }

    public function isValidCvc($cvc)
    {
        return is_numeric($cvc) && self::isValidCvcLength($cvc, $this->cvcLength);
    }

    public static function isValidCvcLength($cvc, array $availableLengths = [3, 4])
    {
        return is_numeric($cvc) && in_array(strlen($cvc), $availableLengths, true);
    }

    protected function checkImplementation()
    {
        if (!$this->type || !is_string($this->type) || !in_array($this->type, ['debit', 'credit'])) {
            throw new CreditCardTypeException('Credit card type is missing');
        }

        if (!$this->name || !is_string($this->name)) {
            throw new CreditCardNameException('Credit card name is missing or is not a string');
        }

        if (!static::$pattern || !is_string(static::$pattern)) {
            throw new CreditCardPatternException(
                'Credit card number recognition pattern is missing or is not a string'
            );
        }

        if (empty($this->numberLength) || !is_array($this->numberLength)) {
            throw new CreditCardLengthException(
                'Credit card number length is missing or is not an array'
            );
        }

        if (empty($this->cvcLength) || !is_array($this->cvcLength)) {
            throw new CreditCardCvcException(
                'Credit card cvc code length is missing or is not an array'
            );
        }

        if ($this->checksumTest === null || !is_bool($this->checksumTest)) {
            throw new CreditCardChecksumException(
                'Credit card checksum test is missing or is not a boolean'
            );
        }
    }

    protected function validPattern()
    {
        return (bool)preg_match(static::$pattern, $this->cardNumber);
    }

    protected function validLength()
    {
        return in_array(strlen($this->cardNumber), $this->numberLength, true);
    }

    protected function validChecksum()
    {
        return ! $this->checksumTest || $this->checksumTest();
    }

    protected function checksumTest()
    {
        $checksum = 0;
        $len = strlen($this->cardNumber);

        for ($i = 2 - ($len % 2); $i <= $len; $i += 2) {
            $checksum += (int)$this->cardNumber[$i - 1];
        }

        for ($i = $len % 2 + 1; $i < $len; $i += 2) {
            $digit = $this->cardNumber[$i - 1] * 2;
            if ($digit < 10) {
                $checksum += $digit;
            } else {
                $checksum += $digit - 9;
            }
        }

        return ($checksum % 10) === 0;
    }
}