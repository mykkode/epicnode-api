<?php

namespace App\Exceptions;

use Exception;

class badAuthenticationException extends Exception
{
     /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        return response()->json([
        	"success" => false,
        	"error" => [
        		"code" => 401,
        		"message" => $this->getMessage()
        	]]);
    }
}
