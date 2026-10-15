<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        return response()->json(Author::withCount('books')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:authors',
            'bio' => 'nullable|string',
        ]);

        $author = Author::create($validated);

        return response()->json($author, 201);
    }

    public function show(Author $author)
    {
        return response()->json($author->load('books'));
    }

    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:authors,email,' . $author->id,
            'bio' => 'nullable|string',
        ]);

        $author->update($validated);

        return response()->json($author);
    }

    public function destroy(Request $request, Author $author)
    {
        $total = $author->books()->count();

        if (! $request->boolean('confirm')) {
            return response()->json([
                'message' => 'Penulis ini punya ' . $total . ' buku yang ikut terhapus. Kirim ulang dengan ?confirm=true untuk melanjutkan.',
            ], 422);
        }

        $author->delete();

        return response()->json([
            'message' => 'Penulis dan ' . $total . ' buku terkait berhasil dihapus',
        ]);
    }
}