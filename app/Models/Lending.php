<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lending extends Model
{
    //protected $primarykey = no;
    //protected $timestamps = false;
    //protected $table = 'inbound_stuffs';
    use SoftDeletes; 
    protected $fillable = [
    "stuff_id", 
    "date_time",
    "name" ,
    "user_id",
    "notes",
    "total_stuff"
];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function stuff(){
        return $this->belongsTo(Stuff::class);
    }
    public function restoration(){
        return $this->hasOne(Restoration::class);
    }

    //return $this->belongsTo(Stuff::class, 'kolom fk' , 'kolom pk');
    //saat penulisan column tidak sesuai dengan aturan menulis
}
