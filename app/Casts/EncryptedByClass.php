<?php

namespace App\Casts;

use App\ThirdParty\Kaga;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class EncryptedByClass implements CastsAttributes
{
    public bool $withoutObjectCaching = true;

    public function __construct(
        protected string $class,
        protected string $column,
        protected ?string $property = null,
    ) {}

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($this->property)) {
            $this->property = $this->class::find($attributes[$this->column])->getKeyName();
        }
        $salt = $key . $this->class::find($attributes[$this->column])->{$this->property};
        return Kaga::salt($salt)->decode($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($this->property)) {
            $this->property = $this->class::find($attributes[$this->column])->getKeyName();
        }
        $salt = $key . $this->class::find($attributes[$this->column])->{$this->property};
        return Kaga::salt($salt)->encode($value);
    }
}
