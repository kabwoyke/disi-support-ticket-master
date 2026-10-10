import { useState } from "react";
import { Link, router, usePage } from "@inertiajs/react";
import moment from "moment";
import { History, MessageSquare, HelpCircle, Clock, Check, X, Trash2 } from "lucide-react";
import { Sidebar } from "../components/sidebar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import "../../css/solves.css";

type Status = "pending" | "approved" | "rejected";

interface ActivityItem {
  type: "question" | "answer";
  id: number;
  question_id: number;
  title: string;
  category?: string | null;
  status: Status;
  excerpt?: string;
  views?: number;
  answer_count?: number;
  created_at: string;
}

interface ActivityProps {
  items: ActivityItem[];
  stats: { questions: number; answers: number; pending: number; approved: number };
  [key: string]: unknown;
}

const statusStyles: Record<Status, string> = {
  pending: "bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300",
  approved: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300",
  rejected: "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300",
};

const statusIcon = (s: Status) =>
  s === "approved" ? <Check className="mr-1 h-3 w-3" /> : s === "pending" ? <Clock className="mr-1 h-3 w-3" /> : <X className="mr-1 h-3 w-3" />;

const filters = [
  { key: "all", label: "All" },
  { key: "question", label: "Questions" },
  { key: "answer", label: "Answers" },
] as const;

export default function Activity() {
  const { items, stats } = usePage<ActivityProps>().props;
  const [filter, setFilter] = useState<(typeof filters)[number]["key"]>("all");

  const visible = items.filter((i) => filter === "all" || i.type === filter);

  const handleDelete = (item: ActivityItem) => {
    const url = item.type === "question" ? `/disi-solves/questions/${item.id}` : `/disi-solves/answers/${item.id}`;
    if (confirm(`Delete this ${item.type}? This cannot be undone.`)) {
      router.delete(url, { preserveScroll: true });
    }
  };

  return (
    <div className="min-h-screen bg-background">
      <Sidebar />

      <main className="ml-64 min-h-screen">
        <header className="bg-card border-b border-border p-6">
          <div className="flex items-center space-x-4">
            <History className="h-8 w-8 text-primary" />
            <div>
              <h2 className="text-2xl font-bold text-foreground">My Activity</h2>
              <p className="text-muted-foreground">Track your questions and answers</p>
            </div>
          </div>
        </header>

        <div className="p-6 space-y-6">
          <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
            {[
              ["Questions asked", stats.questions],
              ["Answers given", stats.answers],
              ["Awaiting review", stats.pending],
              ["Approved", stats.approved],
            ].map(([label, value]) => (
              <Card key={label as string}>
                <CardContent className="p-5">
                  <p className="text-sm text-muted-foreground">{label}</p>
                  <p className="text-3xl font-bold text-foreground">{value}</p>
                </CardContent>
              </Card>
            ))}
          </div>

          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Recent Activity ({visible.length} {visible.length === 1 ? "item" : "items"})</CardTitle>
                <div className="flex gap-1">
                  {filters.map((f) => (
                    <Button
                      key={f.key}
                      size="sm"
                      variant={filter === f.key ? "default" : "ghost"}
                      onClick={() => setFilter(f.key)}
                    >
                      {f.label}
                    </Button>
                  ))}
                </div>
              </div>
            </CardHeader>
            <CardContent>
              {visible.length === 0 ? (
                <p className="py-8 text-center text-muted-foreground">
                  Nothing here yet. Raise an issue or answer one to see it listed.
                </p>
              ) : (
                <div className="space-y-4">
                  {visible.map((item) => (
                    <div key={`${item.type}-${item.id}`} className="flex items-start space-x-4 rounded-lg border border-border p-4">
                      <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-lime-green/10">
                        {item.type === "question" ? (
                          <HelpCircle className="h-5 w-5 text-lime-green" />
                        ) : (
                          <MessageSquare className="h-5 w-5 text-lime-green" />
                        )}
                      </div>

                      <div className="min-w-0 flex-1">
                        <div className="mb-1 flex items-center space-x-2">
                          <span className="text-sm font-medium text-muted-foreground">
                            {item.type === "question" ? "Posted question" : "Answered"}
                          </span>
                          <Badge className={statusStyles[item.status]}>
                            {statusIcon(item.status)}
                            {item.status}
                          </Badge>
                          {item.category && <Badge variant="secondary">{item.category.toUpperCase()}</Badge>}
                        </div>

                        <Link href={`/disi-solves/${item.question_id}/details`} className="mb-1 block font-medium text-foreground hover:text-primary">
                          {item.title}
                        </Link>

                        {item.excerpt && <p className="mb-2 line-clamp-2 text-sm text-muted-foreground">{item.excerpt}</p>}

                        <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                          <span className="flex items-center space-x-1">
                            <Clock className="h-4 w-4" />
                            <span>{moment(item.created_at).fromNow()}</span>
                          </span>
                          {item.type === "question" && (
                            <span>
                              {item.views ?? 0} views, {item.answer_count ?? 0} answers
                            </span>
                          )}
                        </div>
                      </div>

                      {item.status === "pending" && (
                        <Button
                          variant="ghost"
                          size="sm"
                          className="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                          title={`Delete ${item.type}`}
                          onClick={() => handleDelete(item)}
                        >
                          <Trash2 className="h-4 w-4" />
                        </Button>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </main>
    </div>
  );
}
