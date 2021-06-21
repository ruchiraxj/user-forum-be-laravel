<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'user_id',
        'product_id',
        'approved_user_id',
        'status'
    ];

    public $timestamps = true;


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function searchByUser($key){
        $posts = $this->where("status", 1)->where("title", 'like', '%'.$key.'%')->orWhereHas('user', function (Builder $query) use($key) {
            $query->where('name', 'like', '%'.$key.'%');
        })->with('product')->with('user')->orderByDesc('id')->get();

        return $posts;
    }
}
