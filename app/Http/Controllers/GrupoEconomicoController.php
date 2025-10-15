<?php

namespace App\Http\Controllers;

use App\Models\GrupoEconomico;
use Illuminate\Http\Request;

class GrupoEconomicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grupos = GrupoEconomico::all();

        return response()->json([
            'message' => 'grupos listado com sucesso',
            'data' => $grupos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:grupo_economico,nome'
        ]);

        $grupo = GrupoEconomico::create($validated);

        return response()->json([
            'message' => 'grupo cadastrado com sucesso',
            'data' => $grupo
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $grupo = GrupoEconomico::findOrFail($id); 

        return response()->json([
            'message' => 'grupo encontrado com sucesso',
            'data' => $grupo
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $grupo = GrupoEconomico::findOrFail($id); 

        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:grupo_economico,nome,' . $grupo->id
        ]); 

        $grupo->update($validated); 

        return response()->json([
            'message' => 'grupo economico atualizado com sucesso', 
            'data' => $grupo
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $grupo = GrupoEconomico::findOrfail($id); 
        $grupo->delete(); 

        return response()->json([
            'message' => 'grupo economico removido com sucesso'
        ]);
    }
}
