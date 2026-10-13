<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/** Category/subcategory taxonomy (solve_categories / solve_subcategories) for validation, filters and forms. */
class SolvesCategories
{
    /** Taxonomy as [{id, value, label, subcategories: [{id, value, label}]}], falling back to config before the migration runs. */
    public static function tree(): array
    {
        $db = DB::connection('mysql_solves');

        if (! Schema::connection('mysql_solves')->hasTable('solve_categories')) {
            return collect(config('solves.categories'))->map(fn ($c, $slug) => [
                'id' => null,
                'value' => $slug,
                'label' => $c['label'],
                'subcategories' => collect($c['subcategories'])->map(fn ($label, $value) => ['id' => null, 'value' => $value, 'label' => $label])->values()->all(),
            ])->values()->all();
        }

        $subs = $db->table('solve_subcategories')->orderBy('label')->get()->groupBy('category_id');

        return $db->table('solve_categories')->orderBy('label')->get()->map(fn ($c) => [
            'id' => $c->id,
            'value' => $c->slug,
            'label' => $c->label,
            'subcategories' => ($subs[$c->id] ?? collect())->map(fn ($s) => ['id' => $s->id, 'value' => $s->slug, 'label' => $s->label])->values()->all(),
        ])->all();
    }

    public static function slugs(): array
    {
        return array_column(self::tree(), 'value');
    }

    public static function subcategorySlugs(?string $category): array
    {
        foreach (self::tree() as $c) {
            if ($c['value'] === $category) {
                return array_column($c['subcategories'], 'value');
            }
        }

        return [];
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
