# AI Study Assistant

Mobile study app built with Vue 3, Capacitor, and SQLite, with a Laravel API for online services.

## Run locally

Start the Laravel API in one terminal:

```powershell
cd backend
php artisan serve
```

Start the Vue app in another terminal:

```powershell
cd mobile
npm run dev
```

The API health check is available at `http://127.0.0.1:8000/api/health`.

## Build the Android app

Install the Android SDK and a compatible Android Studio toolchain, then run:

```powershell
cd mobile
npm run build
npx cap sync android
npx cap open android
```

The Android SDK and `adb` are not currently available on PATH in this environment.