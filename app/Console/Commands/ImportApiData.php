<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportApiData extends Command
{
    protected $signature = 'api:import';
    protected $description = 'Импорт данных из внешнего API по 4 эндпоинтам с пагинацией';

    // Конфигурация API
    protected $protocol;
    protected $host;
    protected $port;
    protected $token;

    // Максимальное число записей за запрос (лимит)
    protected $limit = 500;

    public function __construct()
    {
        parent::__construct();

        $this->protocol = config('services.api.protocol', 'http');
        $this->host = config('services.api.host');
        $this->port = config('services.api.port');
        $this->token = config('services.api.token');
    }

    public function handle()
    {
        $this->info('Начинаем импорт данных из API');

        // Задаём диапазон дат (пример — последние 7 дней)
        $dateFrom = now()->subDays(7)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        // Импортируем по всем эндпоинтам
        $this->importEndpoint('sales', $dateFrom, $dateTo);
        $this->importEndpoint('orders', $dateFrom, $dateTo);
        $this->importEndpoint('incomes', $dateFrom, $dateTo);
        // stocks — выгрузка только за текущий день, dateTo не нужен
        $this->importEndpoint('stocks', now()->format('Y-m-d'), null);

        $this->info('Импорт завершён');
    }

    /**
     * Импорт данных с конкретного эндпоинта с пагинацией
     */
    protected function importEndpoint(string $endpoint, string $dateFrom, ?string $dateTo)
    {
        $this->info("Импорт данных: {$endpoint}");

        $page = 1;

        do {
            // Собираем параметры запроса
            $params = [
                'dateFrom' => $dateFrom,
                'page' => $page,
                'limit' => $this->limit,
                'key' => $this->token,
            ];
            if ($dateTo !== null) {
                $params['dateTo'] = $dateTo;
            }

            // Формируем URL
            $url = "{$this->protocol}://{$this->host}:{$this->port}/api/{$endpoint}?" . http_build_query($params);

            // Делаем запрос
            $response = Http::timeout(10)->get($url);

            if (!$response->ok()) {
                $this->error("Ошибка запроса к {$url}: " . $response->status());
                Log::error("API import error for {$endpoint} at page {$page}: " . $response->body());
                break;
            }

            $data = $response->json('data');

            if (empty($data)) {
                $this->info("Данных для импорта по {$endpoint} на странице {$page} нет. Выход.");
                break;
            }

            // Вставляем в БД
            foreach ($data as $item) {
                DB::table($endpoint)->insert([
                    'data' => json_encode($item),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->info("Страница {$page} по {$endpoint} импортирована, записей: " . count($data));

            $page++;

            // Если данных меньше лимита — значит последняя страница
        } while (count($data) === $this->limit);
    }
}
