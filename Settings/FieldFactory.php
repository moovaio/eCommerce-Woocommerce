<?php

namespace Ecomerciar\Moova\Settings;

use Ecomerciar\Moova\Settings\Fields\DescriptionField;
use Ecomerciar\Moova\Settings\Fields\NumberField;
use Ecomerciar\Moova\Settings\Fields\SelectField;
use Ecomerciar\Moova\Settings\Fields\TextField;

/**
 * This factory creates a FieldInterface
 */
class FieldFactory
{

    /**
     * Creates a Field
     *
     * @param string $slug
     * @return FieldInterface|false
     */
    public function create(string $slug)
    {
        $fields = Main::get_settings_fields();
        if (empty($fields[$slug])) {
            return false;
        }
        switch ($fields[$slug]['type']) {
            case 'text':
                $field = new TextField($fields[$slug]);
                break;
            case 'select':
                $field = new SelectField($fields[$slug]);
                break;
            case 'number':
                $field = new NumberField($fields[$slug]);
                break;
            case 'description':
                $field = new DescriptionField($fields[$slug]);
                break;
            default:
                $field = false;
        }
        return $field;
    }
}
