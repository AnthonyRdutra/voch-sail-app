<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unidade;
use App\Models\Colaborador;

class ColaboradorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $colaborador = Colaborador::with('unidade')->get();
        return response()->json([
            $colaborador
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:colaboradores,nome',
            'email' => 'required|string|email|max:255|unique:colaboradores,email',
            'cpf' => 'required|digits:11|unique:colaboradores,cpf',
            'unidade_id' => 'required|exists:unidades,id'
        ]);

        $colaborador = Colaborador::create($validated);

        return response()->json([
            'message' => 'colaborador cadastrada com sucesso',
            'data' => $colaborador
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $colaborador = Colaborador::with('unidade')->findOrFail($id);

        return response()->json($colaborador);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $colaborador = Colaborador::findOrfail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:colaboradores,nome,'. $colaborador->id,
            'email' => 'required|string|email|max:255|unique:colaboradores,email,'. $colaborador->id,
            'cpf' => 'required|digits:11|unique:colaboradores,cpf,'. $colaborador->id,
            'unidade_id' => 'required|exists:unidades,id'
        ]);

        $colaborador->update($validated);

        return response()->json([
            'message' => 'colaborador atualizado com sucesso',
            'data' => $colaborador
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $colaborador = Colaborador::findOrfail($id);
        $colaborador->delete();

        return response()->json([
            'message' => 'colaborador removido'
        ]);
    }
}
