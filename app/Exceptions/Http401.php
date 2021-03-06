<?php

namespace App\Exceptions;

use Exception;

class Http401 extends Exception
{
    protected $bindings;

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
        		"message" => $this->getMessage(),
                "bindings" => $this->getBindings(),
        	]]);
    }

    public function getBindings() {
        return $this->bindings;
    }

    public function __construct($bindings, $message, $code=401, Exception $previous = NULL)
    {
        parent::__construct($message, $code, $previous);

        $this->bindings = $bindings;
    }
}
