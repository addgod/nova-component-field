<?php

namespace Addgod\ComponentField;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use stdClass;

class ComponentField extends Field
{
    /**
     * Do not show this field on the index page.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'component-field';

    /**
     * The registered sections.
     *
     * @var array
     */
    private $sections = [];

    /**
     * ComponentField constructor.
     *
     * @param      $name
     * @param null $attribute
     * @param null $resolveCallback
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);
    }

    /**
     * Determine if the field is required.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return bool
     */
    public function isRequired(NovaRequest $request)
    {
        return false;
    }

    /**
     * Add a section for this component.
     *
     * @param string $title
     * @param string $attribute
     * @param array  $fields
     * @param int    $limit
     *
     * @return $this
     */
    public function addSection(string $title, string $attribute, array $fields, int $limit = 0)
    {
        $this->sections[] = [
            'attribute' => $attribute,
            'name'      => $title,
            'limit'     => $limit,
            'fields'    => $fields,
        ];

        return $this;
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param object                                  $model
     * @param string                                  $attribute
     * @param string|null                             $requestAttribute
     *
     * @return mixed
     */
    public function fillInto(NovaRequest $request, $model, $attribute, $requestAttribute = null)
    {
        $requestAttribute = $requestAttribute ?? $this->attribute;

        // Data needs to be resolved with the value of this field, and send to the component.....
        $order = collect(data_get($request, $requestAttribute . '._order', []));
        $model->{$attribute} = $order->map(function ($id) use ($request, $requestAttribute) {
            $values = new stdClass();
            $section = collect($this->sections)->first(function ($section) use ($request, $id, $requestAttribute) {
                return data_get($request, "{$requestAttribute}.{$id}.attribute") === $section['attribute'];
            });

            collect($section['fields'])->each(function ($field) use ($id, $request, $requestAttribute, $values) {
                $fieldRequestAttribute = "{$requestAttribute}.{$id}.fields.{$field->attribute}";
                $field->fillInto($request, $values, $field->attribute, $fieldRequestAttribute);
            });

            return [
                'attribute' => $section['attribute'],
                'fields'    => (array) $values,
            ];
        });
    }

    /**
     * Resolve the given attribute from the given resource.
     *
     * @param mixed  $resource
     * @param string $attribute
     *
     * @return mixed
     */
    public function resolve($resource, $attribute = null)
    {
        $attribute = $attribute ?? $this->attribute;

        $sections = collect($this->sections);

        parent::resolve($resource, $attribute);

        $this->value = empty($this->value) ? [] : $this->value;

        $this->value = collect($this->value)->map(function ($sectionsData) use ($sections) {
            $section = $sections->first(function ($section) use ($sectionsData) {
                return $section['attribute'] === $sectionsData['attribute'];
            });

            $section['fields'] = collect($section['fields'])->map(function ($field, $attribute) use ($sectionsData) {
                $resolvedField = clone $field;
                $resolvedField->resolve($sectionsData['fields']);

                return $resolvedField;
            });

            return $section;
        });
    }

    /**
     * Get the validation rules for this field.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function getRules(NovaRequest $request)
    {
        $order = collect(data_get($request, $this->attribute . '._order'));

        return $order->mapWithKeys(function ($id) use ($request) {
            $section = collect($this->sections)->first(function ($section) use ($request, $id) {
                return data_get($request, "{$this->attribute}.{$id}.attribute") === $section['attribute'];
            });

            return collect($section['fields'])->mapWithKeys(function ($field) use ($request, $id) {
                return collect($field->getRules($request))->mapWithKeys(function ($rules, $key) use ($id) {
                    return ["{$this->attribute}.{$id}.fields.{$key}" => $rules];
                })->toArray();
            });
        });
    }

    /**
     * Get the creation rules for this field.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array|string
     */
    public function getCreationRules(NovaRequest $request)
    {
        $order = collect(data_get($request, $this->attribute . '._order'));

        return $order->mapWithKeys(function ($id) use ($request) {
            $section = collect($this->sections)->first(function ($section) use ($request, $id) {
                return data_get($request, "{$this->attribute}.{$id}.attribute") === $section['attribute'];
            });

            return collect($section['fields'])->mapWithKeys(function ($field) use ($request, $id) {
                return collect($field->getCreationRules($request))->mapWithKeys(function ($rules, $key) use ($id) {
                    return ["{$this->attribute}.{$id}.fields.{$key}" => $rules];
                })->toArray();
            });
        });
    }

    /**
     * Get the update rules for this field.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function getUpdateRules(NovaRequest $request)
    {
        $order = collect(data_get($request, $this->attribute . '._order'));

        return $order->mapWithKeys(function ($id) use ($request) {
            $section = collect($this->sections)->first(function ($section) use ($request, $id) {
                return data_get($request, "{$this->attribute}.{$id}.attribute") === $section['attribute'];
            });

            return collect($section['fields'])->mapWithKeys(function ($field) use ($request, $id) {
                return collect($field->getRules($request))->mapWithKeys(function ($rules, $key) use ($id) {
                    return ["{$this->attribute}.{$id}.fields.{$key}" => $rules];
                })->toArray();
            });
        });
    }

    /**
     * Set the layout of the component fields to horizontal.
     *
     * @param bool $horizontal
     *
     * @return \Addgod\ComponentField\ComponentField
     */
    public function horizontal($horizontal = true)
    {
        return $this->withMeta([
            'horizontal' => $horizontal,
        ]);
    }

    /**
     * Get additional meta information to merge with the field payload.
     *
     * @return array
     */
    public function meta()
    {
        return array_merge([
            'sections' => collect($this->sections),
        ], $this->meta);
    }

}
