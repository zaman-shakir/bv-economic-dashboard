<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Feel free to add more as needed.
    |
    */

    // Most common validation messages
    'required' => ':attribute skal udfyldes.',
    'email' => ':attribute skal være en gyldig e-mailadresse.',
    'confirmed' => ':attribute bekræftelsen matcher ikke.',
    'min' => [
        'string' => ':attribute skal være mindst :min tegn.',
    ],
    'max' => [
        'string' => ':attribute må højst være :max tegn.',
    ],
    'unique' => ':attribute er allerede i brug.',
    'password' => 'Adgangskoden er forkert.',
    'current_password' => 'Adgangskoden er forkert.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'email' => 'e-mailadresse',
        'password' => 'adgangskode',
        'password_confirmation' => 'bekræft adgangskode',
        'name' => 'navn',
    ],

];
