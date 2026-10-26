<?php

namespace Webkul\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Webkul\Core\Contracts\CoreConfig as CoreConfigContract;
use Webkul\Core\Database\Factories\CoreConfigFactory;

class CoreConfig extends Model implements CoreConfigContract
{
    use HasFactory;

    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = ($this->attributes['code'] ?? '') === 'emails.configure.smtp.password' && filled($value)
            ? 'smtp:v1:'.Crypt::encryptString($value) : $value;
    }

    public function getValueAttribute($value)
    {
        return ($this->attributes['code'] ?? '') === 'emails.configure.smtp.password' && str_starts_with($value ?? '', 'smtp:v1:')
            ? Crypt::decryptString(substr($value, 8)) : $value;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'core_config';

    /**
     * Fillable for mass assignment
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'value',
        'channel_code',
        'locale_code',
    ];

    /**
     * Hidden properties
     *
     * @var array
     */
    protected $hidden = ['token'];

    /**
     * Create a new factory instance for the model
     */
    protected static function newFactory(): Factory
    {
        return CoreConfigFactory::new();
    }
}
