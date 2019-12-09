<?php

namespace Ecomerciar\Moova\Settings\Sections;

/**
 * SellerSection class
 */
class SellerSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-seller-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Configuración del Remitente', 'wc-moova');
        parent::__construct($this->data);
    }

    /**
     * Gets all our fields in this section
     *
     * @return array
     */
    public static function get_fields()
    {
        return [
            'first_name' => [
                'name' => 'Nombre',
                'slug' => 'first_name',
                'type' => 'text'
            ],
            'last_name' => [
                'name' => 'Apellido',
                'slug' => 'last_name',
                'type' => 'text'
            ],
            'email' => [
                'name' => 'Email',
                'slug' => 'email',
                'type' => 'text'
            ],
            'phone' => [
                'name' => 'Teléfono',
                'slug' => 'phone',
                'type' => 'text'
            ],
            'street' => [
                'name' => 'Calle',
                'slug' => 'street',
                'type' => 'text',
                'description' => 'Ejemplo: Av. Belgrano'
            ],
            'street_number' => [
                'name' => 'Altura de la calle',
                'slug' => 'street_number',
                'type' => 'text',
                'description' => 'Ejemplo: 520'
            ],
            'floor' => [
                'name' => 'Piso',
                'slug' => 'floor',
                'type' => 'text',
                'description' => 'Ejemplo: 2'
            ],
            'apartment' => [
                'name' => 'Nº de Departamento (Opcional)',
                'slug' => 'apartment',
                'type' => 'text',
                'description' => 'Ejemplo: A'
            ],
            'locality' => [
                'name' => 'Localidad',
                'slug' => 'locality',
                'type' => 'text',
                'description' => 'Ejemplo: Palermo'
            ],
            'province' => [
                'name' => 'Provincia',
                'slug' => 'province',
                'type' => 'text',
                'description' => 'Ejemplo: Capital Federal'
            ],
            'zipcode' => [
                'name' => 'Código Postal',
                'slug' => 'zipcode',
                'type' => 'text',
                'description' => 'Ejemplo: 1040'
            ],
            'observations' => [
                'name' => 'Observaciones de la dirección',
                'slug' => 'observations',
                'type' => 'text',
                'description' => 'Ejemplo: No funciona el timbre. Llamar antes'
            ]
        ];
    }
}
