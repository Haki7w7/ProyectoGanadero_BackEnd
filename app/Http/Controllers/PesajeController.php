<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesajeRequest;
use App\Http\Requests\UpdatePesajeRequest;
use App\Services\PesajeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesajeController extends Controller
{
    public function __construct(private readonly PesajeService $pesajeService)
    {
    }

    // GET /api/pesajes
    public function index(Request $request): JsonResponse
    {
        $pesajes = $this->pesajeService->listarPesajes($request->query());

        return response()->json([
            'message' => 'Listado de pesajes obtenido correctamente.',
            'data'    => $pesajes,
        ], 200);
    }

    // GET /api/pesajes/{id}
    public function show(int $id): JsonResponse
    {
        $pesaje = $this->pesajeService->obtenerPorId($id);

        return response()->json([
            'message' => 'Pesaje obtenido correctamente.',
            'data'    => $pesaje,
        ], 200);
    }

    // POST /api/pesajes
    public function store(StorePesajeRequest $request): JsonResponse
    {
        $pesaje = $this->pesajeService->crearPesaje($request->validated());

        return response()->json([
            'message' => 'Pesaje registrado correctamente.',
            'data'    => $pesaje,
        ], 201);
    }

    // PUT/PATCH /api/pesajes/{id}
    public function update(UpdatePesajeRequest $request, int $id): JsonResponse
    {
        $pesaje = $this->pesajeService->actualizarPesaje($id, $request->validated());

        return response()->json([
            'message' => 'Pesaje actualizado correctamente.',
            'data'    => $pesaje,
        ], 200);
    }

    // DELETE /api/pesajes/{id}
    public function destroy($id): JsonResponse
    {
        $this->pesajeService->eliminarPesaje((int) $id);

        return response()->json([
            'message' => 'Pesaje eliminado correctamente.',
        ], 200);
    }
}