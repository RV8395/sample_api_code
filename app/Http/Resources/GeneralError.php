<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GeneralError extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return ([
            'status' => 0,
            'message' => $this['message'],
            'data' => isset($this['data']) ? encryptData(getPassphrase(), $this['data']) : array(),
            // 'data' => isset($this['data']) ? $this['data'] : array(),
        ]);
    }

    /*
     * Modify response for a request
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\JsonResponse  $response
     * @return \Illuminate\Http\JsonResponse $response with Error code 400
     */
    public function withResponse($request, $response)
    {
        return ($response)->setStatusCode(200);
    }
}
