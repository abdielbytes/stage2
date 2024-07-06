<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Organisation extends Model
{
    use HasFactory;

    protected $primaryKey = 'orgId';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'orgId', 'name', 'description',
    ];


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organisation_user','orgId', 'userId');
    }
}
