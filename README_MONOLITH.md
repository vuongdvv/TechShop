# TechShop Monolith Laravel

Day la codebase moi duoc tao lai theo mo hinh Laravel monolith (khong tach front-end/back-end).

## Muc tieu

- Mot du an Laravel duy nhat phuc vu ca web route va REST API.
- Tai su dung database `techshop` hien tai.
- Bao toan cac luong nghiep vu da migrate trong API v1.

## Cau truc

- Web routes: routes/web.php
- API routes: routes/api.php
- API controllers: app/Http/Controllers/Api/V1
- Web controllers: app/Http/Controllers/Web
- Views: resources/views
- Assets: public/assets, public/js

## Chay du an

1. Cai dependencies

    composer install

2. Cau hinh .env (da dat san mysql local)

3. Clear config cache

    php artisan config:clear

4. Chay server

    php artisan serve --host=127.0.0.1 --port=8000

5. Truy cap

- Web: http://127.0.0.1:8000
- API: http://127.0.0.1:8000/api/v1

## Ghi chu

- Thu muc cu `front-end/` va `back-end/` duoc giu lai de tham chieu.
- Codebase moi can tiep tuc chuyen cac trang Blade chi tiet va web form handlers vao Laravel web layer.
