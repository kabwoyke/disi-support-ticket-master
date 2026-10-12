import { usePage } from "@inertiajs/react";
import { Badge } from "@/components/ui/badge";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

export interface CategoryOption {
  value: string;
  label: string;
  subcategories: { value: string; label: string }[];
}

/** Category taxonomy shared by the server (config/solves.php) on every page. */
export function useCategories(): CategoryOption[] {
  return (usePage().props.categories as CategoryOption[] | undefined) ?? [];
}

/** Human labels for a category/subcategory pair; falls back to the raw slug for legacy values. */
function useCategoryLabels(category?: string | null, subcategory?: string | null) {
  const categories = useCategories();
  const cat = categories.find((c) => c.value === category);
  const sub = cat?.subcategories.find((s) => s.value === subcategory);
  return {
    category: category ? (cat?.label ?? category) : null,
    subcategory: subcategory ? (sub?.label ?? subcategory) : null,
  };
}

/** Badges showing e.g. "Hardware" and "IBML Scanners" for a question. */
export function CategoryBadges({ category, subcategory }: { category?: string | null; subcategory?: string | null }) {
  const labels = useCategoryLabels(category, subcategory);
  return (
    <>
      {labels.category && (
        <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">{labels.category}</Badge>
      )}
      {labels.subcategory && <Badge variant="secondary">{labels.subcategory}</Badge>}
    </>
  );
}

interface PickerProps {
  category: string | null;
  subcategory: string | null;
  onChange: (next: { category: string; subcategory: string }) => void;
  errors?: { category?: string; subcategory?: string };
}

/** Two linked selects for forms: choosing a category resets and narrows the subcategory list. */
export function CategoryPicker({ category, subcategory, onChange, errors = {} }: PickerProps) {
  const categories = useCategories();
  const subs = categories.find((c) => c.value === category)?.subcategories ?? [];

  return (
    <div className="grid gap-4 sm:grid-cols-2">
      <div className="space-y-2">
        <Label className="text-sm font-semibold">Category *</Label>
        <Select value={category || ""} onValueChange={(v) => onChange({ category: v ?? "", subcategory: "" })}>
          <SelectTrigger data-testid="select-question-category">
            <SelectValue placeholder="Select a category" />
          </SelectTrigger>
          <SelectContent>
            {categories.map((c) => (
              <SelectItem key={c.value} value={c.value}>{c.label}</SelectItem>
            ))}
          </SelectContent>
        </Select>
        {errors.category && <p className="text-xs text-red-500">{errors.category}</p>}
      </div>

      <div className="space-y-2">
        <Label className="text-sm font-semibold">Subcategory *</Label>
        <Select
          value={subcategory || ""}
          disabled={!category}
          onValueChange={(v) => onChange({ category: category ?? "", subcategory: v ?? "" })}
        >
          <SelectTrigger data-testid="select-question-subcategory">
            <SelectValue placeholder={category ? "Select a subcategory" : "Pick a category first"} />
          </SelectTrigger>
          <SelectContent>
            {subs.map((s) => (
              <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
            ))}
          </SelectContent>
        </Select>
        {errors.subcategory && <p className="text-xs text-red-500">{errors.subcategory}</p>}
      </div>
    </div>
  );
}

interface FilterProps {
  category: string;
  subcategory: string;
  onChange: (next: { category: string; subcategory: string }) => void;
}

/** Linked "All ..." selects for list and search filters. */
export function CategoryFilter({ category, subcategory, onChange }: FilterProps) {
  const categories = useCategories();
  const subs = categories.find((c) => c.value === category)?.subcategories ?? [];

  return (
    <>
      <div className="flex items-center space-x-2">
        <label className="text-sm font-medium text-foreground">Category:</label>
        <Select value={category} onValueChange={(v) => onChange({ category: v ?? "all", subcategory: "all" })}>
          <SelectTrigger className="w-40">
            <SelectValue placeholder="All Categories" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Categories</SelectItem>
            {categories.map((c) => (
              <SelectItem key={c.value} value={c.value}>{c.label}</SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      {category !== "all" && (
        <div className="flex items-center space-x-2">
          <label className="text-sm font-medium text-foreground">Subcategory:</label>
          <Select value={subcategory} onValueChange={(v) => onChange({ category, subcategory: v ?? "all" })}>
            <SelectTrigger className="w-44">
              <SelectValue placeholder="All Subcategories" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Subcategories</SelectItem>
              {subs.map((s) => (
                <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      )}
    </>
  );
}
