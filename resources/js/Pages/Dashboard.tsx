import { useState, useEffect, useRef } from "react";
import { Sidebar } from "@/components/sidebar";
import { QuestionCard } from "../components/question-card";
import { RaiseIssueModal } from "../components/raise-issue-modal";
import { AdminPostModal } from "../components/admin-post-modal";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Search, Plus, Users, CheckCircle, Hourglass, TrendingUp } from "lucide-react";
import { usePage, router } from "@inertiajs/react";
import '../../css/solves.css';

export interface User {
  id: number;
  first_name?: string;
  last_name?: string;
  username: string;
  role: string;
}

export interface QuestionData {
  id: string;
  title: string;
  description: string;
  category: 'ibml' | 'softtrac' | 'omniscan';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  created_by: string;
  created_at: string;
  status: 'pending' | 'approved' | 'rejected';
  views: number;
  is_final: boolean;
  attachment?: string | null;
  author?: User;
}

export interface PageProps {
  solves: {
    user: User | null;
  };
  questions?: QuestionData[];
  filters?: {
    search?: string;
    category?: string;
    status?: string;
    sort?: string;
  };
  userCount: number;
  pendingApprovals: number;
  [key: string]: unknown;
}

export default function Dashboard() {
  const { solves, questions = [], userCount, pendingApprovals = 0, filters = {} } = usePage<PageProps>().props;
  const user = solves?.user;

  // Modal State
  const [showRaiseIssueModal, setShowRaiseIssueModal] = useState(false);
  const [showAdminPostModal, setShowAdminPostModal] = useState(false);

  // Form Filters State
 const [search, setSearch] = useState(filters?.search || "");
  const [category, setCategory] = useState(filters?.category || "all");
  const [status, setStatus] = useState(filters?.status || "all");
  const [sort, setSort] = useState(filters?.sort || "recent");

  // Track mounting to prevent search effect firing on first load
  const isInitialMount = useRef(true);

  // Central Router Dispatcher
  const applyFilters = (overrides: Record<string, string> = {}) => {
    const currentParams = {
      search,
      category,
      status,
      sort,
      ...overrides,
    };

    const queryParams: Record<string, string> = {};

    if (currentParams.search.trim()) queryParams.search = currentParams.search.trim();
    if (currentParams.category && currentParams.category !== "all") queryParams.category = currentParams.category;
    if (currentParams.status && currentParams.status !== "all") queryParams.status = currentParams.status;
    if (currentParams.sort && currentParams.sort !== "recent") queryParams.sort = currentParams.sort;

    router.get(window.location.pathname, queryParams, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  };

  // Debounced search trigger
  useEffect(() => {
    if (isInitialMount.current) {
      isInitialMount.current = false;
      return;
    }

    const timer = setTimeout(() => {
      applyFilters({ search });
    }, 400);

    return () => clearTimeout(timer);
  }, [search]);

  // Dropdown Handlers
  const handleCategoryChange = (val: string | null) => {
    const nextCategory = val ?? "all";
    setCategory(nextCategory);
    applyFilters({ category: nextCategory });
  };

  const handleStatusChange = (val: string | null) => {
    const nextStatus = val ?? "all";
    setStatus(nextStatus);
    applyFilters({ status: nextStatus });
  };

  const handleSortChange = (val: string | null) => {
    const nextSort = val ?? "recent";
    setSort(nextSort);
    applyFilters({ sort: nextSort });
  };

  return (
    <div className="min-h-screen bg-background">
      <Sidebar />

      <main className="ml-64 min-h-screen">
        {/* Header */}
        <header className="bg-card border-b border-border p-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              <h2 className="text-2xl font-bold text-foreground">Dashboard</h2>
              <div className="flex items-center space-x-2">
                <span className="text-sm text-muted-foreground">Welcome back,</span>
                <span className="text-sm font-medium text-primary">
                  {user?.first_name && user?.last_name
                    ? `${user.first_name} ${user.last_name}`
                    : user?.username || "User"}
                </span>
              </div>
            </div>

            {/* Search Input */}
            <div className="flex items-center space-x-4">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                <Input
                  placeholder="Search issues..."
                  className="pl-10 w-80"
                  data-testid="input-search"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
              </div>
            </div>
          </div>
        </header>

        <div className="p-6">
          {/* Stats Overview */}
          {user?.role === "admin" && (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">Total Questions</p>
                      <p className="text-3xl font-bold text-foreground">{questions.length}</p>
                    </div>
                    <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                      <TrendingUp className="text-blue-600 dark:text-blue-400 h-6 w-6" />
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">Pending Approvals</p>
                      <p className="text-3xl font-bold text-orange-600 dark:text-orange-400">{pendingApprovals}</p>
                    </div>
                    <div className="w-12 h-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                      <Hourglass className="text-orange-600 dark:text-orange-400 h-6 w-6" />
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">Active Users</p>
                      <p className="text-3xl font-bold text-foreground">{userCount}</p>
                    </div>
                    <div className="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                      <Users className="text-green-600 dark:text-green-400 h-6 w-6" />
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">Resolution Rate</p>
                      <p className="text-3xl font-bold text-foreground">100%</p>
                    </div>
                    <div className="w-12 h-12 bg-lime-100 dark:bg-lime-900/20 rounded-lg flex items-center justify-center">
                      <CheckCircle className="text-lime-600 dark:text-lime-400 h-6 w-6" />
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          )}

          {/* Interactive Filters */}
          <Card className="mb-6">
            <CardContent className="p-6">
              <div className="flex flex-wrap items-center justify-between gap-4">
                <h3 className="text-lg font-semibold text-foreground">Filter Questions</h3>

                <div className="flex flex-wrap items-center gap-3">
                  <div className="flex items-center space-x-2">
                    <label className="text-sm font-medium text-foreground">Category:</label>
                    <Select value={category} onValueChange={handleCategoryChange}>
                      <SelectTrigger className="w-40">
                        <SelectValue placeholder="All Categories" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Categories</SelectItem>
                        <SelectItem value="ibml">IBML Scanners</SelectItem>
                        <SelectItem value="softtrac">SoftTrac</SelectItem>
                        <SelectItem value="omniscan">OmniScan</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="flex items-center space-x-2">
                    <label className="text-sm font-medium text-foreground">Status:</label>
                    <Select value={status} onValueChange={handleStatusChange}>
                      <SelectTrigger className="w-40">
                        <SelectValue placeholder="All Status" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="approved">Approved</SelectItem>
                        <SelectItem value="rejected">Rejected</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div className="flex items-center space-x-2">
                    <label className="text-sm font-medium text-foreground">Sort by:</label>
                    <Select value={sort} onValueChange={handleSortChange}>
                      <SelectTrigger className="w-40">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="recent">Most Recent</SelectItem>
                        <SelectItem value="trending">Trending</SelectItem>
                        <SelectItem value="views">Most Viewed</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Questions List */}
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="text-xl font-semibold text-foreground">Questions</h3>
              <div className="flex space-x-2">
                <Button
                  onClick={() => setShowRaiseIssueModal(true)}
                  className="bg-lime-green text-dark-green hover:bg-lime-green/90"
                  data-testid="button-raise-issue-main"
                >
                  <Plus className="mr-2 h-4 w-4" />
                  Raise Issue
                </Button>

                {user?.role === 'admin' && (
                  <Button
                    onClick={() => setShowAdminPostModal(true)}
                    className="bg-lime-green text-dark-green hover:bg-lime-green/90"
                    data-testid="button-post-issue-answer-main"
                  >
                    <Plus className="mr-2 h-4 w-4" />
                    Post Issue + Answer
                  </Button>
                )}
              </div>
            </div>

            <div className="space-y-4">
              {questions.length > 0 ? (
                questions.map((question) => (
                  <QuestionCard
                    onTap={() => router.get(`/disi-solves/${question.id}/details`)}
                    key={question.id}
                    question={{
                      id: question.id,
                      title: question.title,
                      description: question.description,
                      category: question.category,
                      status: question.status,
                      createdAt: question.created_at,
                      authorName: question.author
                        ? `${question.author.first_name ?? ''} ${question.author.last_name ?? ''}`.trim() || question.author.username
                        : 'Anonymous',
                    } as any}
                  />
                ))
              ) : (
                <Card>
                  <CardContent className="p-8 text-center text-muted-foreground">
                    No questions found matching your filter criteria.
                  </CardContent>
                </Card>
              )}
            </div>
          </div>
        </div>
      </main>

      <RaiseIssueModal
        open={showRaiseIssueModal}
        onOpenChange={setShowRaiseIssueModal}
      />

      <AdminPostModal
        open={showAdminPostModal}
        onOpenChange={setShowAdminPostModal}
      />
    </div>
  );
}
