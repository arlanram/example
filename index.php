<?php

declare(strict_types=1);

use Mark\App;

require 'vendor/autoload.php';

class Country {
    private Redis $redis;

    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('redis', 6379);
        $this->redis->auth('123');

        if (! $this->redis->ping()) {
            throw new Exception('connection error');
        }
    }

    public array $allowedCountriesIso2 = [
        'us',
        'ru',
        'cy',
    ];

    public function getStats(): array
    {
        $data = [];

        foreach($this->allowedCountriesIso2 as $countryIso2) {
            $count = $this->redis->get($countryIso2);

            $data[$countryIso2] = (int) $count;
        }

        return $data;
    }

    public function addCountryIso2(string $countryIso2): void
    {
        try {
            $this->redis->incr($countryIso2);
        } catch(Throwable $e) {
            throw new Exception(sprintf("error: %s", $e->getMessage()));
        }
    }
}

function jsonResponse(array|string $data = '', int $code = 200): string {
    header('Accept: application/json');
    header('Content-Type: application/json');

    $response = [
        'data' => $data,
        'ok' => $code > 200 ? false : true,
    ];

    http_response_code($code);

    return json_encode($response);
}

$api = new App('http://0.0.0.0:80');
$api->count = 4;

$api->get('/get', function ($requst) {
    $country = new Country();

    return jsonResponse($country->getStats());
});

$api->get('/set/{countryIso2}', function ($response, $countryIso2) {
    if ($countryIso2 === null) {
        return jsonResponse('countryIso2 required', 400);
    }
    
    $country = new Country();

    if (! in_array($countryIso2, $country->allowedCountriesIso2)) {
        return jsonResponse('invalid country code', 400);
    }
    
    $country->addCountryIso2($countryIso2);

    return jsonResponse('ok');
});

$api->start();