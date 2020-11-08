<?php

namespace App\Http\Controllers\Api;

use App\Events\AdminHelpAlertNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\GeneralError;
use App\Http\Resources\GeneralResponse;
use App\Models\Help;

class HelpController extends Controller
{
    /**
     * Create user
     *
     * @return [string] json data
     */


    public function createHelp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => ['required'],
            'longitude' => ['required'],
        ]);

        if ($validator->fails()) {
            $errors = array();
            foreach ($validator->messages()->getMessages() as $field_name => $messages) {
                $errors[$field_name] = $messages[0];
            }
            return new GeneralError(['status' => 0, 'data' => $errors, 'message' => 'Invalid Data']);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();

            $help = new Help;
            $help->user_id = $request->user()->id;
            $help->latitude = $data['latitude'];
            $help->longitude = $data['longitude'];
            $help->description = isset($data['description']) ? $data['description'] : NULL;
            $help->help_date_time = date('Y-m-d H:i:s');
            $help->save();

            $help = Help::with('user')->where('id', $help->id)->first();

            event(new AdminHelpAlertNotification($help));

            DB::commit();
            return new GeneralResponse(['status' => 1, 'message' => 'success']);
        } catch (\Exception $e) {
            DB::rollback();
            return new GeneralError(['status' => 0, 'data' => [], 'message' => $e->getMessage()]);
        }
    }
}
