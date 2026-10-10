import { Fragment, useEffect, useRef, useState } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import moment from "moment";
import { Search as SearchIcon, Eye, MessageCircle, Clock, CheckCircle2, Lightbulb } from "lucide-react";
import { Sidebar } from "../components/sidebar";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import "../../css/solves.css";

interface Result {
  id: number;
  title: string;
  category: string;
  status: string;
  views: number;
  answer_count: number;
  created_at?: string;
  snippet: string;
  highlight: string[];
  matched_in: "title" | "description" | "answers" | "category";
  exact: boolean;
  author?: { first_name?: string | null; last_name?: string | null; username: string } | null;
}

interface SearchProps {
  query: string;
  filters: { category: string; solved: boolean };
  results: Result[];
  total: number;
  suggestion: string | null;
  terms: string[];
  [key: string]: unknown;
}

const escapeRegExp = (s: string) => s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

/** Wrap every highlighted word in <mark>. */
function Highlight({ text, words }: { text: string; words: string[] }) {
  if (words.length === 0) return <>{text}</>;
  const pattern = new RegExp(`(${words.map(escapeRegExp).join("|")})`, "gi");
  return (
    <>
      {text.split(pattern).map((part, i) =>
        i % 2 === 1 ? (
          <mark key={i} className="rounded bg-lime-green/30 px-0.5 text-foreground">
            {part}
          </mark>
        ) : (
          <Fragment key={i}>{part}</Fragment>
        )
      )}
    </>
  );
}

const matchedLabel: Record<Result["matched_in"], string> = {
  title: "Matched in title",
  description: "Matched in description",
  answers: "Matched in a solution",
  category: "Matched category",
};

export default function Search() {
  const { query, filters, results, total, suggestion } = usePage<SearchProps>().props;
  const [q, setQ] = useState(query);
  const [category, setCategory] = useState(filters.category);
  const [solved, setSolved] = useState(filters.solved);
  const first = useRef(true);

  const run = (overrides: Partial<{ q: string; category: string; solved: boolean }> = {}) => {
    const next = { q, category, solved, ...overrides };
    router.get(
      "/disi-solves/search",
      {
        ...(next.q.trim() ? { q: next.q.trim() } : {}),
        ...(next.category !== "all" ? { category: next.category } : {}),
        ...(next.solved ? { solved: 1 } : {}),
      },
      { preserveState: true, preserveScroll: true, replace: true, only: ["query", "filters", "results", "total", "suggestion", "terms"] }
    );
  };

  // Search as you type.
  useEffect(() => {
    if (first.current) {
      first.current = false;
      return;
    }
    const t = setTimeout(() => run({ q }), 350);
    return () => clearTimeout(t);
  }, [q]);

  const authorName = (a?: Result["author"]) =>
    a ? `${a.first_name ?? ""} ${a.last_name ?? ""}`.trim() || a.username : "Unknown";

  return (
    <div className="min-h-screen bg-background">
      <Sidebar />

      <main className="ml-64 min-h-screen">
        <header className="bg-card border-b border-border p-6">
          <div className="flex items-center space-x-4">
            <SearchIcon className="h-8 w-8 text-primary" />
            <div>
              <h2 className="text-2xl font-bold text-foreground">Search Issues</h2>
              <p className="text-muted-foreground">
                Describe the problem in your own words. Typos, plurals and similar terms are understood.
              </p>
            </div>
          </div>
        </header>

        <div className="space-y-6 p-6">
          <Card>
            <CardContent className="space-y-4 p-6">
              <div className="relative">
                <SearchIcon className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground" />
                <Input
                  autoFocus
                  value={q}
                  onChange={(e) => setQ(e.target.value)}
                  placeholder='e.g. "scanner keeps jamming on the feeder" or "blurry images"'
                  className="h-12 pl-11 text-base"
                  data-testid="input-issue-search"
                />
              </div>

              <div className="flex flex-wrap items-center gap-4">
                <div className="flex items-center space-x-2">
                  <label className="text-sm font-medium text-foreground">Category:</label>
                  <Select
                    value={category}
                    onValueChange={(v) => {
                      const next = v ?? "all";
                      setCategory(next);
                      run({ category: next });
                    }}
                  >
                    <SelectTrigger className="w-44">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">All Categories</SelectItem>
                      <SelectItem value="ibml">IBML Scanners</SelectItem>
                      <SelectItem value="softtrac">SoftTrac</SelectItem>
                      <SelectItem value="omniscan">OmniScan</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <label className="flex cursor-pointer items-center space-x-2 text-sm text-foreground">
                  <input
                    type="checkbox"
                    checked={solved}
                    onChange={(e) => {
                      setSolved(e.target.checked);
                      run({ solved: e.target.checked });
                    }}
                  />
                  <span>Only issues with an approved solution</span>
                </label>
              </div>
            </CardContent>
          </Card>

          {query && suggestion && (
            <p className="text-sm text-muted-foreground">
              Did you mean{" "}
              <button
                type="button"
                className="font-semibold text-primary hover:underline"
                onClick={() => {
                  setQ(suggestion);
                  run({ q: suggestion });
                }}
              >
                {suggestion}
              </button>
              ?
            </p>
          )}

          {!query ? (
            <Card>
              <CardContent className="flex flex-col items-center p-10 text-center text-muted-foreground">
                <Lightbulb className="mb-3 h-8 w-8 text-lime-green" />
                <p className="font-medium text-foreground">Start typing to search every issue and solution.</p>
                <p className="mt-1 text-sm">Results are ranked by how well they match, and solved issues come first.</p>
              </CardContent>
            </Card>
          ) : results.length === 0 ? (
            <Card>
              <CardContent className="p-10 text-center text-muted-foreground">
                <p className="font-medium text-foreground">No issues match "{query}".</p>
                <p className="mt-1 text-sm">
                  Try fewer or different words, or{" "}
                  <Link href="/disi-solves/dashboard?raise=1" className="text-primary hover:underline">
                    raise it as a new issue
                  </Link>
                  .
                </p>
              </CardContent>
            </Card>
          ) : (
            <div className="space-y-3">
              <p className="text-sm text-muted-foreground">
                {total} {total === 1 ? "result" : "results"}
                {total > results.length && ` (showing the top ${results.length})`}
              </p>

              {results.map((r) => (
                <Link key={r.id} href={`/disi-solves/${r.id}/details`} className="block">
                  <Card className="transition-shadow hover:shadow-md">
                    <CardContent className="p-5">
                      <div className="mb-2 flex flex-wrap items-center gap-2">
                        <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                          {r.category.toUpperCase()}
                        </Badge>
                        {r.answer_count > 0 && (
                          <Badge className="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                            <CheckCircle2 className="mr-1 h-3 w-3" />
                            Solved
                          </Badge>
                        )}
                        {r.status !== "approved" && <Badge variant="secondary">{r.status}</Badge>}
                        <span className="text-xs text-muted-foreground">{matchedLabel[r.matched_in]}</span>
                      </div>

                      <h3 className="mb-1 text-lg font-semibold text-foreground">
                        <Highlight text={r.title} words={r.highlight} />
                      </h3>
                      <p className="mb-3 text-sm text-muted-foreground">
                        <Highlight text={r.snippet} words={r.highlight} />
                      </p>

                      <div className="flex items-center space-x-4 text-xs text-muted-foreground">
                        <span>{authorName(r.author)}</span>
                        <span className="flex items-center space-x-1">
                          <Clock className="h-3.5 w-3.5" />
                          <span>{moment(r.created_at).fromNow()}</span>
                        </span>
                        <span className="flex items-center space-x-1">
                          <Eye className="h-3.5 w-3.5" />
                          <span>{r.views}</span>
                        </span>
                        <span className="flex items-center space-x-1">
                          <MessageCircle className="h-3.5 w-3.5" />
                          <span>{r.answer_count}</span>
                        </span>
                      </div>
                    </CardContent>
                  </Card>
                </Link>
              ))}
            </div>
          )}
        </div>
      </main>
    </div>
  );
}
