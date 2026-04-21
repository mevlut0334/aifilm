<?php

return [
    'required' => ':attribute alanı zorunludur.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'unique' => ':attribute daha önce alınmış.',
    'min' => [
        'string' => ':attribute en az :min karakter olmalıdır.',
    ],
    'confirmed' => ':attribute onayı eşleşmiyor.',
    'max' => [
        'string' => ':attribute en fazla :max karakter olabilir.',
    ],

    'attributes' => [
        'first_name' => 'ad',
        'last_name' => 'soyad',
        'email' => 'e-posta',
        'phone' => 'telefon',
        'country_code' => 'ülke kodu',
        'password' => 'şifre',
    ],
];
