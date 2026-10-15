<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with('author', 'categories')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('author', function ($a) use ($search) {
                            $a->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->whereHas('categories', function ($c) use ($categoryId) {
                    $c->where('categories.id', $categoryId);
                });
            })
            ->when($request->boolean('in_stock'), function ($query) {
                $query->where('stock', '>', 0);
            })
            ->get();

        return response()->json($books);
    }

    public function lowStock()
    {
        return response()->json(
            Book::with('author', 'categories')->where('stock', '<', 5)->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'published_year' => 'required|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'author_id' => 'required|exists:authors,id',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book = Book::create(Arr::except($validated, ['category_ids']));

        if (isset($validated['category_ids'])) {
            $book->categories()->sync($validated['category_ids']);
        }

        return response()->json($book->load('author', 'categories'), 201);
    }

    public function show(Book $book)
    {
        return response()->json($book->load('author', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book->update(Arr::except($validated, ['category_ids']));

        if (isset($validated['category_ids'])) {
            $book->categories()->sync($validated['category_ids']);
        }

        return response()->json($book->load('author', 'categories'));
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}
