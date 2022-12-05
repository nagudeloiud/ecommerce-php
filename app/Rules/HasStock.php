<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

use App\Models\Product;
use App\Models\OrderProduct;

class HasStock implements Rule, DataAwareRule
{

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    protected $data = [];
    var $prueba;
 
    // ...
 
    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
 
        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $partes = explode(".", $attribute);
        $data = $this->data["products"][$partes[1]];
        $producto = Product::findOrFail($data["id"]);
        return $data["quantity"] <= $producto["inventory"];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "No hay suficiente inventario";
    }
}
