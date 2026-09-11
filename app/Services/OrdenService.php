<?php

namespace App\Services;

use App\Models\Orden;
use Illuminate\Support\Facades\DB;  

class OrdenService
{
  /*  public function createOrden(array $data): Orden
    {
        return DB::transaction(function () use ($data) {
           $orden = Orden::create($data);


            return $orden;
        });
    }

    public function updateOrden(Orden $orden, array $data): Orden
    {
        /*return DB::transaction(function () use ($orden, $data) {
            $orden->update($data);

            return $orden;
        });
    } */
}