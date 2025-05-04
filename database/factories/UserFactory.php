<?php

/** Factories de base de datos */

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Este directorio debe contener cada una de las definiciones de los factories de modelos para
| su aplicación. Los factories proporcionan una manera conveniente de generar nuevas
| instancias de modelo para probar o cargar la base de datos de la aplicación.
|
*/
class UserFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
        ];
    }
}
