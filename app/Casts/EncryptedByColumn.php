<?php

namespace App\Casts;

use App\ThirdParty\Kaga;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class EncryptedByColumn implements CastsAttributes
{
    public bool $withoutObjectCaching = true;

    public function __construct(
        protected string $column,
    ) {}
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $salt = $key . $attributes[$this->column];
        return Kaga::salt($salt)->decode($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $salt = $key . $attributes[$this->column];
        return Kaga::salt($salt)->encode($value);
    }
}
