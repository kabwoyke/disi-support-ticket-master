<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/** Helpers over config('solves.categories') for validating and filtering category/subcategory. */
class SolvesCategories
{
    public static function slugs(): array
    {
        return array_keys(config('solves.categories'));
    }

    public static function subcategorySlugs(?string $category): array
    {
        return array_keys(config("solves.categories.{$category}.subcategories", []));
    }

    /** Validation rules for a request carrying `category` and `subcategory`. */
    public static function rules(): array
    {
        return [
            'category' => ['required', Rule::in(self::slugs())],
            'subcategory' => ['required', 'string', fn ($attr, $value, $fail) => in_array($value, self::subcategorySlugs(request('category')), true)
                ?: $fail('The selected subcategory does not belong to that category.')],
        ];
    }
}
