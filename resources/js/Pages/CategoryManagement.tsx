import { useState } from "react";
import { router, usePage } from "@inertiajs/react";
import { Sidebar } from "@/components/sidebar";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Edit, Plus, Tags, Trash2, Check, X } from "lucide-react";
import "../../css/solves.css";

interface Sub {
  id: number;
  value: string;
  label: string;
  question_count: number;
}

interface Category {
  id: number;
  value: string;
  label: string;
  question_count: number;
  subcategories: Sub[];
}

/** Inline add/rename input used for both categories and subcategories. */
function LabelForm({
  initial = "",
  placeholder,
  onSubmit,
  onCancel,
}: {
  initial?: string;
  placeholder: string;
  onSubmit: (label: string) => void;
  onCancel: () => void;
}) {
  const [label, setLabel] = useState(initial);
  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    if (label.trim()) onSubmit(label.trim());
  };
  return (
    <form onSubmit={submit} className="flex items-center gap-2">
      <Input autoFocus value={label} onChange={(e) => setLabel(e.target.value)} placeholder={placeholder} className="h-8" />
      <Button type="submit" size="sm" className="h-8 w-8 p-0" title="Save"><Check className="h-4 w-4" /></Button>
      <Button type="button" variant="ghost" size="sm" className="h-8 w-8 p-0" onClick={onCancel} title="Cancel"><X className="h-4 w-4" /></Button>
    </form>
  );
}

export default function CategoryManagement() {
  const { managedCategories = [], errors = {} } = usePage<{ managedCategories: Category[]; errors?: Record<string, string> }>().props as any;
  const categories = managedCategories as Category[];

  // "new-cat" | "cat-<id>" (rename) | "sub-new-<catId>" | "sub-<id>" (rename)
  const [editing, setEditing] = useState<string | null>(null);
  const done = { preserveScroll: true, onSuccess: () => setEditing(null) };

  const remove = (url: string, what: string) => {
    if (confirm(`Delete ${what}?`)) router.delete(url, { preserveScroll: true });
  };

  return (
    <div className="min-h-screen bg-background flex">
      <Sidebar />

      <main className="ml-64 flex-1 min-h-screen">
        <header className="bg-card border-b border-border p-6 flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 bg-lime-green/20 rounded-lg flex items-center justify-center">
              <Tags className="text-lime-green h-5 w-5" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-foreground">Categories</h1>
              <p className="text-sm text-muted-foreground">Manage the categories and subcategories used to classify issues</p>
            </div>
          </div>
          <Button onClick={() => setEditing("new-cat")} className="bg-lime-green text-dark-green hover:bg-lime-green/90 font-semibold">
            <Plus className="mr-2 h-4 w-4" /> Add Category
          </Button>
        </header>

        <div className="p-6 space-y-4">
          {errors.label && <p className="text-sm text-red-500">{errors.label}</p>}

          {editing === "new-cat" && (
            <Card className="border-border">
              <CardContent className="p-4">
                <LabelForm
                  placeholder="Category name"
                  onCancel={() => setEditing(null)}
                  onSubmit={(label) => router.post("/disi-solves/admin/categories", { label }, done)}
                />
              </CardContent>
            </Card>
          )}

          {categories.map((c) => (
            <Card key={c.id} className="border-border">
              <CardContent className="p-4 space-y-3">
                <div className="flex items-center justify-between gap-3">
                  {editing === `cat-${c.id}` ? (
                    <LabelForm
                      initial={c.label}
                      placeholder="Category name"
                      onCancel={() => setEditing(null)}
                      onSubmit={(label) => router.put(`/disi-solves/admin/categories/${c.id}`, { label }, done)}
                    />
                  ) : (
                    <div className="flex items-center gap-2">
                      <h2 className="text-lg font-semibold text-foreground">{c.label}</h2>
                      <Badge variant="outline" className="font-mono text-xs">{c.value}</Badge>
                      <span className="text-xs text-muted-foreground">{c.question_count} question{c.question_count === 1 ? "" : "s"}</span>
                    </div>
                  )}
                  <div className="flex items-center gap-1">
                    <Button variant="ghost" size="sm" onClick={() => setEditing(`sub-new-${c.id}`)}>
                      <Plus className="mr-1 h-4 w-4" /> Subcategory
                    </Button>
                    <Button variant="ghost" size="sm" className="h-8 w-8 p-0" title="Rename" onClick={() => setEditing(`cat-${c.id}`)}>
                      <Edit className="h-4 w-4 text-blue-500" />
                    </Button>
                    <Button variant="ghost" size="sm" className="h-8 w-8 p-0" title="Delete" onClick={() => remove(`/disi-solves/admin/categories/${c.id}`, `category "${c.label}" and all its subcategories`)}>
                      <Trash2 className="h-4 w-4 text-red-500" />
                    </Button>
                  </div>
                </div>

                <ul className="divide-y divide-border rounded-md border border-border">
                  {editing === `sub-new-${c.id}` && (
                    <li className="p-2">
                      <LabelForm
                        placeholder="Subcategory name"
                        onCancel={() => setEditing(null)}
                        onSubmit={(label) => router.post(`/disi-solves/admin/categories/${c.id}/subcategories`, { label }, done)}
                      />
                    </li>
                  )}
                  {c.subcategories.length === 0 && editing !== `sub-new-${c.id}` && (
                    <li className="p-3 text-sm italic text-muted-foreground">No subcategories yet.</li>
                  )}
                  {c.subcategories.map((s) => (
                    <li key={s.id} className="flex items-center justify-between gap-3 p-2 pl-3">
                      {editing === `sub-${s.id}` ? (
                        <LabelForm
                          initial={s.label}
                          placeholder="Subcategory name"
                          onCancel={() => setEditing(null)}
                          onSubmit={(label) => router.put(`/disi-solves/admin/subcategories/${s.id}`, { label }, done)}
                        />
                      ) : (
                        <div className="flex items-center gap-2 text-sm">
                          <span className="text-foreground">{s.label}</span>
                          <Badge variant="outline" className="font-mono text-xs">{s.value}</Badge>
                          <span className="text-xs text-muted-foreground">{s.question_count} question{s.question_count === 1 ? "" : "s"}</span>
                        </div>
                      )}
                      <div className="flex items-center gap-1">
                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0" title="Rename" onClick={() => setEditing(`sub-${s.id}`)}>
                          <Edit className="h-4 w-4 text-blue-500" />
                        </Button>
                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0" title="Delete" onClick={() => remove(`/disi-solves/admin/subcategories/${s.id}`, `subcategory "${s.label}"`)}>
                          <Trash2 className="h-4 w-4 text-red-500" />
                        </Button>
                      </div>
                    </li>
                  ))}
                </ul>
              </CardContent>
            </Card>
          ))}

          {categories.length === 0 && (
            <p className="p-8 text-center text-muted-foreground">No categories yet. Add one to get started.</p>
          )}
        </div>
      </main>
    </div>
  );
}
