<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait ControllerInvoker
{
    /**
     * Chama um método de Controller diretamente, simulando um Request.
     *
     * @param string $controllerClass  Classe do controller (ex: GrupoEconomicoController::class)
     * @param string $method           Método do controller a chamar (ex: 'store', 'update')
     * @param array  $data             Dados do request (ex: ['nome' => 'Rede Teste'])
     * @param string $httpMethod       Método HTTP simulado (POST, PUT, GET, DELETE)
     * @return mixed                   Resposta do controller (geralmente JsonResponse)
     */

    protected function callController(string $controllerClass, string $method, array $data =[], string $httpMethod = 'POST' ){
        $request = Request::create('', strtoupper($httpMethod), $data);

        $controller = App::make($controllerClass); 

        return $controller->$method($request); 
    }
}
