<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResourceWrapper extends JsonResource
{
    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function with($request)
    {
        return [
            'status' => 'success',
            'message' =>  isset($this['message']) ? $this['message'] : '',
        ];
    }
}
