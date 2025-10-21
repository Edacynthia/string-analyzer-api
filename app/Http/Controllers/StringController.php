<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\AnalyzedString;
use Illuminate\Support\Facades\Validator;

class StringController extends Controller
{
    // Analyze and store a string

    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'value' => 'required|string'
        ]);

        // 🔹 Normalize the string (convert to lowercase)
        $value = strtolower($validated['value']);

        // Compute hash
        $hash = hash('sha256', $value);

        $file = storage_path('app/strings.json');
        $store = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

        // Check if it already exists
        if (isset($store[$hash])) {
            return response()->json(['error' => 'String already exists in the system'], 409);
        }

        // Compute string properties
        $properties = [
            'length' => strlen($value),
            'is_palindrome' => strtolower(strrev($value)) === strtolower($value),
            'unique_characters' => count(array_unique(str_split($value))),
            'word_count' => str_word_count($value),
            'sha256_hash' => $hash,
            'character_frequency_map' => array_count_values(str_split($value))
        ];

        $data = [
            'id' => $hash,
            'value' => $value,
            'properties' => $properties,
            'created_at' => now()->toISOString()
        ];

        // Save
        $store[$hash] = $data;
        file_put_contents($file, json_encode($store, JSON_PRETTY_PRINT));

        return response()->json($data, 201);
    }

    // Retrieve a specific string
    public function show($value)
    {
        $file = storage_path('app/strings.json');

        if (!file_exists($file)) {
            return response()->json(['error' => 'String does not exist in the system'], 404);
        }

        $store = json_decode(file_get_contents($file), true);

        // 🔹 Normalize input for consistent comparison
        $normalized = strtolower(trim(urldecode($value)));

        // ✅ Option 1: Find by hash (fast lookup)
        $sha256 = hash('sha256', $normalized);

        if (isset($store[$sha256])) {
            return response()->json($store[$sha256], 200);
        }

        // ✅ Option 2: Fallback in case of any mismatch (more forgiving)
        foreach ($store as $item) {
            if (strtolower(trim($item['value'])) === $normalized) {
                return response()->json($item, 200);
            }
        }

        // If still not found
        return response()->json(['error' => 'String does not exist in the system'], 404);
    }



    // Delete a string
    public function destroy($value)
    {
        $file = storage_path('app/strings.json');

        if (!file_exists($file)) {
            return response()->json(['error' => 'String does not exist in the system'], 404);
        }

        $store = json_decode(file_get_contents($file), true);

        // Normalize input for case-insensitive match
        $keyToDelete = null;
        foreach ($store as $hash => $item) {
            if (strcasecmp($item['value'], $value) === 0) {
                $keyToDelete = $hash;
                break;
            }
        }

        if (!$keyToDelete) {
            return response()->json(['error' => 'String does not exist in the system'], 404);
        }

        unset($store[$keyToDelete]);

        // Save updated data
        file_put_contents($file, json_encode($store, JSON_PRETTY_PRINT));

        // Return 204 No Content (empty body)
        return response()->noContent();
    }


    // Filtering endpoint
    public function index(Request $request)
    {
        $file = storage_path('app/strings.json');

        if (!file_exists($file)) {
            return response()->json([
                'data' => [],
                'count' => 0,
                'filters_applied' => (object) []
            ], 200);
        }

        // ✅ Validate query parameters
        $validator = Validator::make($request->all(), [
            'is_palindrome' => 'nullable|boolean',
            'min_length' => 'nullable|integer|min:0',
            'max_length' => 'nullable|integer|min:0',
            'word_count' => 'nullable|integer|min:0',
            'contains_character' => 'nullable|string|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid query parameter values or types',
                'details' => $validator->errors()
            ], 400);
        }

        // ✅ Safe to use validated data now
        $filters = $validator->validated();

        $store = json_decode(file_get_contents($file), true);
        $results = array_values($store);

        $filtered = array_filter($results, function ($item) use ($filters) {
            $p = $item['properties'];

            if (isset($filters['is_palindrome']) && $p['is_palindrome'] != (bool)$filters['is_palindrome']) {
                return false;
            }

            if (isset($filters['min_length']) && $p['length'] < $filters['min_length']) {
                return false;
            }

            if (isset($filters['max_length']) && $p['length'] > $filters['max_length']) {
                return false;
            }

            if (isset($filters['word_count']) && $p['word_count'] != $filters['word_count']) {
                return false;
            }

            if (isset($filters['contains_character'])) {
                $char = strtolower($filters['contains_character']);
                if (stripos($item['value'], $char) === false) {
                    return false;
                }
            }

            return true;
        });

        return response()->json([
            'data' => array_values($filtered),
            'count' => count($filtered),
            'filters_applied' => $filters
        ], 200);
    }


    // Simple natural language filter example
    public function filterByNaturalLanguage(Request $request)
    {
        $query = strtolower($request->query('query', ''));

        if (empty($query)) {
            return response()->json(['error' => 'Query parameter is required'], 400);
        }

        $file = storage_path('app/strings.json');
        if (!file_exists($file)) {
            return response()->json(['data' => [], 'count' => 0]);
        }

        $store = json_decode(file_get_contents($file), true);

        $filtered = array_filter($store, function ($item) use ($query) {
            $p = $item['properties'];
            $text = $item['value'];

            // Keywords: interpret user query
            $match = true;

            // Handle palindrome
            if (str_contains($query, 'palindrome') || str_contains($query, 'palindromic')) {
                $match = $match && $p['is_palindrome'];
            }

            // Handle multi-word / single word
            if (str_contains($query, 'single word')) {
                $match = $match && ($p['word_count'] === 1);
            } elseif (str_contains($query, 'multiple words') || str_contains($query, 'multi word')) {
                $match = $match && ($p['word_count'] > 1);
            }

            // Handle "containing the letter X"
            if (preg_match('/containing (?:the )?letter ([a-zA-Z])/', $query, $m)) {
                $char = strtolower($m[1]);
                $match = $match && str_contains(strtolower($text), $char);
            }

            // Handle length conditions (e.g., "longer than 5", "shorter than 10")
            if (preg_match('/longer than (\d+)/', $query, $m)) {
                $match = $match && ($p['length'] > (int)$m[1]);
            }
            if (preg_match('/shorter than (\d+)/', $query, $m)) {
                $match = $match && ($p['length'] < (int)$m[1]);
            }

            // Handle unique characters condition
            if (preg_match('/unique characters (?:more than|greater than) (\d+)/', $query, $m)) {
                $match = $match && ($p['unique_characters'] > (int)$m[1]);
            }

            return $match;
        });

        return response()->json([
            'data' => array_values($filtered),
            'count' => count($filtered),
            'interpreted_query' => [
                'original' => $query,
                'parsed_filters' => []
            ]
        ]);
    }
}