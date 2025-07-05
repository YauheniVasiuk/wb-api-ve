# WB API Importer (Laravel 12 + MySQL)

Laravel-приложение для импорта данных из внешнего API и сохранения их в удалённую базу данных MySQL.

---

## Что делает проект

- Подключается к API с авторизацией через токен
- Загружает данные по 4 сущностям:
  - **sales** (продажи)
  - **orders** (заказы)
  - **stocks** (остатки на складах)
  - **incomes** (доходы)
- Использует пагинацию, обрабатывает все страницы
- Сохраняет данные в JSON-формате в базу данных
- Работает с удалённой БД на бесплатном хостинге

---

## Установка и настройка


1. **Клонируйте репозиторий:**

```bash
git clone https://github.com/YauheniVasiuk/wb-api-ve.git

---

2. **Установите зависимости:**

```bash
composer install

---

3. **Скопируйте .env и сгенерируйте ключ:**

```bash
cp .env.example .env
php artisan key:generate

---

4. **Настройте .env:**

APP_NAME=Laravel
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=sql308.infinityfree.com
DB_PORT=3306
DB_DATABASE=if0_39395224_wb_api_ve
DB_USERNAME=if0_39395224
DB_PASSWORD=xFNtbhnZhz

API_PROTOCOL=http
API_HOST=109.73.206.144
API_PORT=6969
API_TOKEN=E6kUTYrYwZq2tN4QEtyzsbEBk3ie

---

5. **Доступы к БД:**

База данных размещена на бесплатном хостинге InfinityFree.

Доступы к БД:
Параметр	Значение
Хост	sql308.infinityfree.com
Порт	3306
База данных	if0_39395224_wb_api_ve
Имя пользователя	if0_39395224
Пароль	xFNtbhnZhz

Как подключиться:
Можно использовать phpMyAdmin:

🔗 https://sql308.infinityfree.com/phpmyadmin

--- 

6. **Работа приложения**

Для импорта данных используется Artisan-команда api:import, которая:

Формирует URL с параметрами (dateFrom, dateTo, page, limit, key)

Выполняет запросы к каждому из 4 API-эндпоинтов

Сохраняет данные в соответствующую таблицу

Обрабатывает пагинацию

7. **Как вызвать импорт данных**

php artisan api:import

Импортируются данные за последние 7 дней по следующим сущностям:

Сущность	Путь запроса	Описание
sales	/api/sales	Продажи
orders	/api/orders	Заказы
stocks	/api/stocks	Остатки (только dateFrom)
incomes	/api/incomes	Доходы

8. **Структура таблиц**

Каждая сущность (sales, orders, stocks, incomes) сохраняется в отдельную таблицу с одинаковой структурой:

sql
Копировать
Редактировать
CREATE TABLE `sales` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `data` JSON NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
);
Примечание: Таблицы orders, stocks, incomes идентичны по структуре.