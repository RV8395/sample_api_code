<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Help extends Model
{
    protected $table = "help";
    use SoftDeletes {
        restore as private restoreB;
    }
    
    protected $fillable = [
        'user_id', 'assignee_id', 'latitude', 'longitude', 'description', 'status', 'help_date_time'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
