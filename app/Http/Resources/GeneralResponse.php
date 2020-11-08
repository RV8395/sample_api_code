<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralResponse extends JsonResource
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
            'status' => isset($this['status']) ? $this['status'] : 1,
            'message' => isset($this['message']) ? $this['message'] : '',
            'data' => isset($this['data']) ? encryptData(getPassphrase(), $this['data']) : array(),
            // 'data' => isset($this['data']) ? $this['data'] : array(),
        ]);
    }
}
