<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\{
    LookbookController,
    CategoryController,
    MatchGameController,
    TeamController,
    PlayerController,
    LineupController,
    StatsController,
    OverviewController,
    ProductController,
    UploadStorageController,
    AuthController,
    CartController,
    ShippingController,
    RegionController,
    UserAddressController
};
use App\Models\Cart;
use Symfony\Component\Mime\Address;

Route::prefix('v1')->group(function () {
    // =====================
    // HEALTH CHECK
    // =====================
    Route::get('health', function () {
        return response()->json([
            'status'  => 'running',
            'service' => 'DUB API',
            'version' => '1.0',
        ]);
    });

    // =====================
    // MATCHES
    // =====================
    Route::get('matches/upcoming', [MatchGameController::class, 'nextUpcomingMatch']);
    Route::get('matches/history', [MatchGameController::class, 'matchHistory']);
    Route::get('matches', [MatchGameController::class, 'index']);
    Route::post('matches', [MatchGameController::class, 'store']);
    Route::get('matches/{id}', [MatchGameController::class, 'getMatchByID']);
    Route::put('matches/{id}', [MatchGameController::class, 'update']);
    Route::delete('matches/{id}', [MatchGameController::class, 'destroy']);


    // =====================
    // STATS
    // =====================
    Route::get('stats', [StatsController::class, 'index']);
    Route::post('stats', [StatsController::class, 'store']);
    Route::get('stats/{id}', [StatsController::class, 'show']);
    Route::put('stats/{id}', [StatsController::class, 'update']);
    Route::delete('stats/{id}', [StatsController::class, 'destroy']);
    Route::get('matches/{matchId}/stats', [StatsController::class, 'getByMatch']);
    Route::get('matches/{matchId}/stat', [StatsController::class, 'getByMatchID']);

    // =====================
    // LINEUPS
    // =====================
    Route::get('lineup', [LineupController::class, 'index']);
    Route::post('lineup', [LineupController::class, 'store']);
    Route::get('lineup/{id}', [LineupController::class, 'show']);
    Route::put('lineup/{id}', [LineupController::class, 'update']);
    Route::delete('lineup/{id}', [LineupController::class, 'destroy']);
    Route::get('matches/{matchId}/lineup', [LineupController::class, 'getByMatch']);
    Route::delete('matches/{matchId}/lineup', [LineupController::class, 'deleteByMatch']);

    // =====================
    // OVERVIEW
    // =====================
    Route::get('overview', [OverviewController::class, 'index']);
    Route::get('overview/{id}', [OverviewController::class, 'show']);
    Route::delete('overview/{id}', [OverviewController::class, 'destroy']);
    Route::post('matches/{matchId}/overview', [OverviewController::class, 'storeByMatch']);
    Route::get('matches/{matchId}/overview', [OverviewController::class, 'getByMatch']);

    // =====================
    // TEAMS
    // =====================
    Route::get('team', [TeamController::class, 'index']);
    Route::post('team', [TeamController::class, 'store']);
    Route::get('team/{id}', [TeamController::class, 'show']);
    Route::put('team/{id}', [TeamController::class, 'update']);
    Route::delete('team/{id}', [TeamController::class, 'destroy']);

    // =====================
    // PLAYERS
    // =====================
    Route::get('player', [PlayerController::class, 'index']);
    Route::post('player', [PlayerController::class, 'store']);
    Route::get('player/{id}', [PlayerController::class, 'show']);
    Route::put('player/{id}', [PlayerController::class, 'update']);
    Route::delete('player/{id}', [PlayerController::class, 'destroy']);
    Route::get('player/team/{teamId}', [PlayerController::class, 'getByTeam']);

    /**
     * E-Catalog
     */
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product:slug}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete']);

    // ============================
    // Upload Storage / Media 
    // ============================

    Route::get('/media', [UploadStorageController::class, 'index']);
    Route::post('/media/upload', [UploadStorageController::class, 'store']);
    Route::post('/assign/{id}', [UploadStorageController::class, 'assignToProduct']);
    Route::delete('/media/{id}', [UploadStorageController::class, 'destroy']);


    // =============================
    // Category Product
    // =============================

    Route::get('/category', [CategoryController::class, 'index']);
    Route::post('/category', [CategoryController::class, 'store']);

    // =============================
    // LOOKBOOK (PUBLIC)
    // =============================
    Route::get('/lookbooks', [LookbookController::class, 'index']);
    Route::get('/lookbooks/{lookbook:slug}', [LookbookController::class, 'show']);

    // =============================
    // LOOKBOOK
    // =============================
    Route::post('/lookbooks', [LookbookController::class, 'store']);
    Route::get('/lookbooks/{lookbook}', [LookbookController::class, 'showById']);
    Route::put('/lookbooks/{lookbook}', [LookbookController::class, 'update']);
    Route::delete('/lookbooks/{lookbook}', [LookbookController::class, 'destroy']);
    Route::post('/lookbooks/{lookbook}/publish', [LookbookController::class, 'publish']);
});


/*
|--------------------------------------------------------------------------
| API V2
|--------------------------------------------------------------------------
| - Public
| - Authenticated User
| - Admin (CMS)
|--------------------------------------------------------------------------
*/

Route::prefix('v2')->group(function () {

    // =====================
    // HEALTH
    // =====================
    Route::get('health', fn() => response()->json([
        'status' => 'running',
        'service' => 'DUB API',
        'version' => '2.0',
    ]));
    Route::get('sanctum/csrf-cookie', [\Laravel\Sanctum\Http\Controllers\CsrfCookieController::class, 'show']);
    // AUTH LOGIN
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    // =====================
    // PUBLIC API
    // =====================
    Route::get('matches', [MatchGameController::class, 'index']);
    Route::get('matches/upcoming', [MatchGameController::class, 'nextUpcomingMatch']);
    Route::get('matches/history', [MatchGameController::class, 'matchHistory']);
    Route::get('matches/{id}', [MatchGameController::class, 'getMatchByID']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product:slug}', [ProductController::class, 'show']);

    Route::get('team', [TeamController::class, 'index']);
    Route::get('team/{id}', [TeamController::class, 'show']);

    Route::get('player', [PlayerController::class, 'index']);
    Route::get('player/{id}', [PlayerController::class, 'show']);
    Route::get('player/team/{teamId}', [PlayerController::class, 'getByTeam']);

    // LOOKBOOK PUBLIC
    Route::get('lookbooks', [LookbookController::class, 'index']);
    Route::get('lookbooks/{lookbook}', [LookbookController::class, 'show']);

    // API GET REGIONS
    Route::get('/provinces', [RegionController::class, 'provinces']);
    Route::get('/regencies', [RegionController::class, 'regencies']);
    Route::get('/districts', [RegionController::class, 'districts']);
    Route::get('/villages', [RegionController::class, 'villages']);
    // SHIPPING
    Route::post('/shipping/rates', [ShippingController::class, 'rates']);

    // =====================
    // USER
    // =====================
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/me/update', [AuthController::class, 'updateProfile']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('/address', [UserAddressController::class, 'index']);
        Route::post('/address', [UserAddressController::class, 'store']);

        Route::get('stats', [StatsController::class, 'index']);
        Route::get('stats/{id}', [StatsController::class, 'show']);
        Route::get('matches/{matchId}/stats', [StatsController::class, 'getByMatch']);

        Route::get('lineup', [LineupController::class, 'index']);
        Route::get('lineup/{id}', [LineupController::class, 'show']);
        Route::get('matches/{matchId}/lineup', [LineupController::class, 'getByMatch']);

        Route::get('overview', [OverviewController::class, 'index']);
        Route::get('overview/{id}', [OverviewController::class, 'show']);
        Route::get('matches/{matchId}/overview', [OverviewController::class, 'getByMatch']);

        Route::get('media', [UploadStorageController::class, 'index']);
        Route::get('category', [CategoryController::class, 'index']);

        // CART
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart', [CartController::class, 'addToCart']);
        Route::put('cart/items/{itemId}', [CartController::class, 'updateQuantity']);
        Route::delete('cart/items/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('cart/clear', [CartController::class, 'clear']);
    });

    // =====================
    // ADMIN
    // =====================
    Route::middleware(['auth:sanctum', 'role:' . User::ADMIN])
        ->group(function () {

            // MATCHES
            Route::post('matches', [MatchGameController::class, 'store']);
            Route::put('matches/{id}', [MatchGameController::class, 'update']);
            Route::delete('matches/{id}', [MatchGameController::class, 'destroy']);

            // STATS
            Route::post('stats', [StatsController::class, 'store']);
            Route::put('stats/{id}', [StatsController::class, 'update']);
            Route::delete('stats/{id}', [StatsController::class, 'destroy']);

            // LINEUP
            Route::post('lineup', [LineupController::class, 'store']);
            Route::put('lineup/{id}', [LineupController::class, 'update']);
            Route::delete('lineup/{id}', [LineupController::class, 'destroy']);
            Route::delete('matches/{matchId}/lineup', [LineupController::class, 'deleteByMatch']);

            // OVERVIEW
            Route::post('matches/{matchId}/overview', [OverviewController::class, 'storeByMatch']);
            Route::delete('overview/{id}', [OverviewController::class, 'destroy']);

            // TEAM
            Route::post('team', [TeamController::class, 'store']);
            Route::put('team/{id}', [TeamController::class, 'update']);
            Route::delete('team/{id}', [TeamController::class, 'destroy']);

            // PLAYER
            Route::post('player', [PlayerController::class, 'store']);
            Route::put('player/{id}', [PlayerController::class, 'update']);
            Route::delete('player/{id}', [PlayerController::class, 'destroy']);

            // PRODUCTS
            Route::post('products', [ProductController::class, 'store']);
            Route::put('products/{product}', [ProductController::class, 'update']);
            Route::delete('products/{product}', [ProductController::class, 'destroy']);
            Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
            Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete']);

            // MEDIA
            Route::post('media/upload', [UploadStorageController::class, 'store']);
            Route::post('media/assign/{id}', [UploadStorageController::class, 'assignToProduct']);
            Route::delete('media/{id}', [UploadStorageController::class, 'destroy']);

            // CATEGORY
            Route::post('category', [CategoryController::class, 'store']);

            // LOOKBOOK
            Route::get('lookbooks/{id}', [LookbookController::class, 'show']);
            Route::post('lookbooks', [LookbookController::class, 'store']);
            Route::put('lookbooks/{id}', [LookbookController::class, 'update']);
            Route::delete('lookbooks/{id}', [LookbookController::class, 'destroy']);
        });
});
