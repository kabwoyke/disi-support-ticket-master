<?php

namespace App\Http\Controllers;

use App\Support\SolvesCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

/** Admin CRUD for categories and their subcategories. Questions reference them by slug. */
class CategoryController extends Controller
{
    private function db()
    {
        return DB::connection('mysql_solves');
    }

    public function index()
    {
        $counts = $this->db()->table('questions')->selectRaw('category, subcategory, COUNT(*) as total')->groupBy('category', 'subcategory')->get();

        $tree = collect(SolvesCategories::tree())->map(function ($c) use ($counts) {
            $c['question_count'] = (int) $counts->where('category', $c['value'])->sum('total');
            $c['subcategories'] = collect($c['subcategories'])->map(function ($s) use ($c, $counts) {
                $s['question_count'] = (int) $counts->where('category', $c['value'])->where('subcategory', $s['value'])->sum('total');

                return $s;
            })->all();

            return $c;
        })->all();

        return Inertia::render('CategoryManagement', ['managedCategories' => $tree]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['label' => 'required|string|max:100']);
        $slug = $this->uniqueSlug('solve_categories', $data['label']);

        $this->db()->table('solve_categories')->insert(['slug' => $slug, 'label' => trim($data['label']), 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate(['label' => 'required|string|max:100']);
        abort_unless($this->db()->table('solve_categories')->where('id', $id)->exists(), 404);

        // The slug is what questions store, so only the display label changes.
        $this->db()->table('solve_categories')->where('id', $id)->update(['label' => trim($data['label']), 'updated_at' => now()]);

        return back()->with('success', 'Category updated.');
    }

    public function destroy($id)
    {
        $category = $this->db()->table('solve_categories')->where('id', $id)->first();
        abort_unless($category, 404);

        if ($this->db()->table('questions')->where('category', $category->slug)->exists()) {
            return back()->with('error', 'This category has questions. Move or delete them first.');
        }

        $this->db()->table('solve_categories')->where('id', $id)->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function storeSub(Request $request, $categoryId)
    {
        $data = $request->validate(['label' => 'required|string|max:100']);
        abort_unless($this->db()->table('solve_categories')->where('id', $categoryId)->exists(), 404);

        $slug = $this->uniqueSlug('solve_subcategories', $data['label'], ['category_id' => $categoryId]);
        $this->db()->table('solve_subcategories')->insert(['category_id' => $categoryId, 'slug' => $slug, 'label' => trim($data['label']), 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Subcategory created.');
    }

    public function updateSub(Request $request, $id)
    {
        $data = $request->validate(['label' => 'required|string|max:100']);
        abort_unless($this->db()->table('solve_subcategories')->where('id', $id)->exists(), 404);

        $this->db()->table('solve_subcategories')->where('id', $id)->update(['label' => trim($data['label']), 'updated_at' => now()]);

        return back()->with('success', 'Subcategory updated.');
    }

    public function destroySub($id)
    {
        $sub = $this->db()->table('solve_subcategories')
            ->join('solve_categories', 'solve_categories.id', '=', 'solve_subcategories.category_id')
            ->where('solve_subcategories.id', $id)
            ->select('solve_subcategories.slug as sub_slug', 'solve_categories.slug as cat_slug')
            ->first();
        abort_unless($sub, 404);

        if ($this->db()->table('questions')->where('category', $sub->cat_slug)->where('subcategory', $sub->sub_slug)->exists()) {
            return back()->with('error', 'This subcategory has questions. Move or delete them first.');
        }

        $this->db()->table('solve_subcategories')->where('id', $id)->delete();

        return back()->with('success', 'Subcategory deleted.');
    }

    private function uniqueSlug(string $table, string $label, array $scope = []): string
    {
        $base = Str::slug($label) ?: 'item';
        $slug = $base;
        $i = 2;
        while ($this->db()->table($table)->where($scope)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
