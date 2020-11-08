<?php namespace App\Models;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    public function permissions(){
        return $this->belongsToMany(Permission::class);
    }
    public static function getRoleByName($role) {
        return self::where(['name'=>$role])->first();
    }
}