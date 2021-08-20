<?php

namespace Moova\Settings;

use Moova\Settings\Fields\DescriptionField;
use Moova\Settings\Fields\NumberField;
use Moova\Settings\Fields\SelectField;
use Moova\Settings\Fields\TextField;

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
    public function create($fields)
    {
        switch ($fields['type']) {
            case 'text':
                $field = new TextField($fields);
                break;
            case 'select':
                $field = new SelectField($fields);
                break;
            case 'number':
                $field = new NumberField($fields);
                break;
            case 'description':
                $field = new DescriptionField($fields);
                break;
            default:
                $field = false;
        }
        return $field;
    }
}
