<?php

namespace Ecomerciar\Moova\Settings\Sections;

/**
 * IntegrationSection class
 */
class IntegrationSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-integration-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Configuración de la integración', 'wc-moova');
        parent::__construct($this->data);
    }

    /**
     * Gets all our fields in this section
     *
     * @return array
     */
    public static function get_fields()
    {
        $fields = [
            'status_processing' => [
                'name' => 'Estado de proceso',
                'slug' => 'status_processing',
                'description' => 'Cuando un pedido tenga este estado, será procesado automáticamente a Moova.',
                'type' => 'select',
                'default' => 'wc-completed',
                'options' => [
                    '0' => 'Desactivar procesamiento automático',
                ]
            ],
            'tracking' => [
                'name' => 'Rastreo',
                'slug' => 'tracking',
                'description' => 'Este plugin provee un formulario de rastreo mediante el shortcode <strong>[moova_tracking_form]</strong> el cual podés agregar a cualquier página de tu sitio donde querés que aparezca.',
                'type' => 'description'
            ],
            'webhooks' => [
                'name' => 'Notificaciones de envíos',
                'slug' => 'webhooks',
                'description' => 'Para recibir notificaciones sobre tus envíos de Moova deberás agregar un Webhook en la configuración de tu cuenta en el panel de Moova, colocá como URL del webhook: <strong>' . get_site_url(null, '/wc-api/wc-moova-orders') . '</strong> mediante el método POST.',
                'type' => 'description'
            ],
            'debug' => [
                'name' => 'Modo Debug',
                'slug' => 'debug',
                'description' => 'Activa el log de debug para desarrolladores. Si no sabes que es esto probablemente no necesitas activarlo',
                'type' => 'select',
                'options' => [
                    '0' => 'No',
                    '1' => 'Si'
                ]
            ]
        ];
        $statuses = wc_get_order_statuses();
        foreach ($statuses as $key => $status) {
            $fields['status_processing']['options'][$key] = $status;
        }
        return $fields;
    }
}
