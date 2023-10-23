<?php
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenAIGeneratorController;
use App\Http\Controllers\VoiceChatBotController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
// Route::get('set-locale/{locale}', function(string $locale) {
//     // App::setLocale($locale);
// })->name('set-locale');
Route::get('/simple', [OpenAIGeneratorController::class, 'index']);
Route::post('/write/generate', [OpenAIGeneratorController::class, 'getResponse']);

Route::get('/voice-chat-bot', [VoiceChatBotController::class, 'index']);
Route::post('/voice-chat-bot/interact', [VoiceChatBotController::class, 'getResponse']);
