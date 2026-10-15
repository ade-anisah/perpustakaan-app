<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('books/low-stock', [BookController::class, 'lowStock']);

Route::get('stats', function () {
    return response()->json([
        'total_books' => Book::count(),
        'total_authors' => Author::count(),
        'total_categories' => Category::count(),
    ]);
});

Route::apiResource('authors', AuthorController::class);
Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
Route::apiResource('books', BookController::class);