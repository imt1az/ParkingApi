# Parking API (Laravel 12)

JWT-সুরক্ষিত পার্কিং বুকিং REST API. ফিচার: ইউজার রোল (driver/provider/admin), স্পেস ম্যানেজমেন্ট, অ্যাভেইলেবিলিটি উইন্ডো, লোকেশন-ভিত্তিক সার্চ, বুকিং লাইফসাইকেল (reserve → confirm → check-in → complete/cancel).

## টেক স্ট্যাক
- PHP 8.2+, Laravel 12
- MySQL 8 (spatial ফাংশনসহ) – `POINT`, `SPATIAL INDEX`, `ST_Distance_Sphere` লাগে
- JWT: `tymon/jwt-auth`
- Vite/Tailwind (ফ্রন্টএন্ড scaffold)

## সেটআপ
1) ডিপেন্ডেন্সি ইন্সটল  
```
composer install
npm install
```
2) `.env` তৈরি করুন (`.env.example` থেকে) ও কনফিগ করুন:
   - `APP_KEY` → `php artisan key:generate`
   - `JWT_SECRET` → `php artisan jwt:secret`
   - `DB_*` MySQL ক্রেডেনশিয়াল (MySQL 8 spatial সক্ষম)
3) মাইগ্রেশন ও সিড চালান  
```
php artisan migrate --seed
```
4) লোকাল রান  
```
php artisan serve
```
ফ্রন্টএন্ড ডেভ: `npm run dev`, বিল্ড: `npm run build`.

## ডেমো ইউজার (পাসওয়ার্ড `password`)
- ড্রাইভার: `01700000001`
- প্রোভাইডার: `01700000002`
- অ্যাডমিন: `01700000003`

## মূল API রুট (prefix `/api/v1`)
- Auth: `POST /auth/register`, `/auth/login`, `/auth/refresh`, `/auth/logout`
- Spaces (provider/admin): `POST /spaces`, `GET /spaces/my`, `PATCH /spaces/{id}`  
  Public: `GET /spaces/{id}`
- Availability (provider/admin): `POST /spaces/{space}/availability`
- বুকিং:
  - Driver: `POST /bookings`, `GET /bookings/my`
  - Provider/Admin: `GET /bookings/for-my-spaces`
  - Status updates: `PATCH /bookings/{id}/confirm`, `/cancel`, `/check-in`, `/check-out`
- সার্চ (public): `GET /search?lat&lng&start_ts&end_ts&radius_m`

## বুকিং ফ্লো সারাংশ
1) ড্রাইভার সার্চ → `POST /bookings` দিয়ে বুক করে (ওভারল্যাপ/অ্যাভেইলেবিলিটি চেক হয়)।  
2) প্রোভাইডার/অ্যাডমিন `PATCH /bookings/{id}/confirm` করে।  
3) চেক-ইন/চেক-আউট `PATCH /check-in` → `PATCH /check-out`.  
4) ক্যানসেল (driver/provider) `PATCH /cancel` (reserved/confirmed অবস্থায়)।  
5) My bookings: ড্রাইভার `/bookings/my`, প্রোভাইডার `/bookings/for-my-spaces`.

## ডাটাবেস নোট
- `parking_spaces` টেবিলে `location` POINT ও spatial index; insert/update ট্রিগার lat/lng থেকে location সেট করে।  
- সার্চ কুয়েরি MySQL `ST_Distance_Sphere` ব্যবহার করে রেডিয়াস ফিল্টার করে।

## টেস্ট
- ডিফল্ট উদাহরণ টেস্ট ছাড়া কিছু নেই; নতুন ফিচার যোগ করলে ফিচার টেস্ট লেখার পরামর্শ।
