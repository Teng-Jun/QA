<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = $request->input('query');
        $users = [];
        $randomUsers = [];

        if ($query) {
            $users = User::where('name', 'like', "%{$query}%")
                ->where('id', '!=', $user->id)
                ->get();
        } else {
            // Fetch random users if query is empty
            $randomUsers = User::where('id', '!=', $user->id)
                ->inRandomOrder()
                ->limit(5) // limit to 5 random users
                ->get();
        }

        return view('search.searchresults', [
            'users' => $users,
            'query' => $query,
            'randomUsers' => $randomUsers,
        ]);
    }
}
