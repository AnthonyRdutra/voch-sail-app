<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bandeira;
use App\Models\Unidade;

class UnidadeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $unidade = Unidade::with('bandeira')->get();
        return response()->json([
            $unidade
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_fantasia' => 'required|string|max:255|unique:unidades,nome_fantasia',
            'razao_social' => 'required|string|max:255|unique:unidades,razao_social',
            'cnpj' => 'required|digits:14|unique:unidades,cnpj',
            'bandeira_id' => 'required|exists:bandeiras,id'
        ]);

        $unidade = Unidade::create($validated);

        return response()->json([
            'message' => 'unidade cadastrada com sucesso',
            'data' => $unidade
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unidade = Unidade::with('bandeira')->findOrFail($id);

        return response()->json($unidade);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unidade = Unidade::findOrfail($id);

        $validated = $request->validate([
            'nome_fantasia' => 'required|string|max:255|unique:unidades,nome_fantasia,' . $unidade->id,
            'razao_social' => 'required|string|max:255|unique:unidades,razao_social,' . $unidade->id,
            'cnpj' => 'required|digits:14|unique:unidades,cnpj,' . $unidade->id,
            'bandeira_id' => 'required|exists:bandeiras,id'
        ]);

        $unidade->update($validated);

        return response()->json([
            'message' => 'unidade atualizada com sucesso',
            'data' => $unidade
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unidade = Unidade::findOrfail($id);
        $unidade->delete(); 

        return response()->json([
            'message' => 'unidade removida'
        ]); 
    }
}
