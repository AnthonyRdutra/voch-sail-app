<?php

namespace App\Http\Controllers;

use App\Models\Bandeira;
use Illuminate\Http\Request;

class BandeiraController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bandeiras = Bandeira::with('grupoEconomico', 'unidades')->get();
        return response()->json($bandeiras, 200);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:bandeiras,nome',
            'grupo_economico_id' => 'required|exists:grupo_economico,id'
        ]);

        $bandeira = Bandeira::create($validated);

        return response()->json([
            'message' => 'bandeira cadastrada com sucesso',
            'data' => $bandeira
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bandeira = Bandeira::with('grupoEconomico', 'unidades')->findOrFail($id);

        return response()->json($bandeira, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bandeira = Bandeira::findOrfail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:bandeiras,nome,' . $bandeira->id,
            'grupo_economico_id' => 'required|exists:grupo_economico,id',
        ]);

        $bandeira->update($validated);

        return response()->json([
            'message' => 'bandeira atualizada com sucesso',
            'data' => $bandeira
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bandeira = Bandeira::findOrFail($id);
        $bandeira->delete();

        return response()->json([
            'message' => 'bandeira removida'
        ], 200);
    }
}
