import { JSX, useRef } from "react";
import { Sidebar } from "@/components/sidebar";
import { RaiseIssueModal } from "@/components/raise-issue-modal";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { ArrowLeft, Clock, Eye, User, MessageCircle, Send, Check, X, Loader2, Trash2 } from "lucide-react";
import '../../css/solves.css'
import { router, useForm, usePage } from "@inertiajs/react";
import { PageProps as InertiaPageProps, PageProps as Page } from "@inertiajs/core";
import moment from 'moment'

export interface Author {
  id: number;
  username: string;
  first_name?: string | null;
  last_name?: string | null;
}

export interface Answer {
  id?: number;
  question_id?: number;
  created_by?: number;
  answer_text: string;
  status: "pending" | "approved" | "rejected";
  attachment?: string | null;
  author:Author;
  created_at?: string;
}

export interface Question {
  id: number;
  title: string;
  description: string;
  category: string;
  priority?: string;
  status: "pending" | "approved" | "rejected";
  views: number;
  is_final: boolean;
  attachment?: string | null;
  created_by: number;
  created_at?: string;
  author?: Author;
  answer?: Answer[];
}

interface PageProps extends InertiaPageProps {
  question: Question;
  solves?: {
    user?: {
      id: number;
      username: string;
      role: string;
    };
  };
  errors?: Record<string, string>;
}

// Matches the Answer model's fillable fields (question_id/created_by/status are
// set server-side; the client only sends what the person typed/attached).
interface AnswerFormData {
  answer_text: string;
  attachment: File | null;
}

const statusStyles: Record<Answer["status"], string> = {
  pending: "bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300",
  approved: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300",
  rejected: "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300",
};

const statusIcons: Record<Answer["status"], JSX.Element> = {
  pending: <Clock className="mr-1 h-3 w-3" />,
  approved: <Check className="mr-1 h-3 w-3" />,
  rejected: <X className="mr-1 h-3 w-3" />,
};

export default function QuestionDetail() {
    const q = usePage<PageProps>().props
    const fileInputRef = useRef<HTMLInputElement>(null)
    const isAdmin = q.solves?.user?.role === "admin"
    const currentUserId = q.solves?.user?.id

    const displayName = (a?: Author | null) =>
      a ? `${a.first_name ?? ""} ${a.last_name ?? ""}`.trim() || a.username : "Unknown";

    // Admins can delete anything; authors can delete their own items while still pending.
    const canDeleteQuestion =
      isAdmin || (q.question.created_by === currentUserId && q.question.status === "pending");
    const canDeleteAnswer = (a: Answer) =>
      isAdmin || (a.created_by === currentUserId && a.status === "pending");

    const handleDeleteQuestion = () => {
      if (confirm("Delete this question and all of its answers? This cannot be undone.")) {
        router.delete(`/disi-solves/questions/${q.question.id}`);
      }
    };

    const handleDeleteAnswer = (answerId?: number) => {
      if (!answerId) return;
      if (confirm("Delete this answer? This cannot be undone.")) {
        router.delete(`/disi-solves/answers/${answerId}`, { preserveScroll: true });
      }
    };

    const answerForm = useForm<AnswerFormData>({
      answer_text: "",
      attachment: null,
    })

    // Adjust these routes to match whatever your web.php defines for
    // moderating an individual answer, e.g.:
    // Route::patch('/disi-solves/answers/{answer}/approve', ...)
    // Route::patch('/disi-solves/answers/{answer}/reject', ...)
    const handleApprove = (answerId?: number) => {
      if (!answerId) return;
      router.patch(`/disi-solves/answers/${answerId}/approve`, {}, { preserveScroll: true });
    };

    const handleReject = (answerId?: number) => {
      if (!answerId) return;
      router.patch(`/disi-solves/answers/${answerId}/reject`, {}, { preserveScroll: true });
    };

    const handleAnswerSubmit = (e: React.FormEvent) => {
      e.preventDefault()

      // Adjust this URL to match whatever route your web.php defines for
      // storing an answer against this question, e.g.:
      // Route::post('/disi-solves/questions/{question}/answers', [AnswerController::class, 'store'])
      answerForm.post(`/disi-solves/questions/${q.question.id}/answers`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
          answerForm.reset();
          if (fileInputRef.current) fileInputRef.current.value = "";
        },
      });
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      answerForm.setData("attachment", e.target.files?.[0] ?? null);
    };

  return (
    <div className="min-h-screen bg-background">
      <Sidebar />

      <main className="ml-64 min-h-screen">
        {/* Header */}
        <header className="bg-card border-b border-border p-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              <Button variant="outline" size="sm" onClick={() => router.get("/disi-solves/dashboard")}>
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Dashboard
              </Button>
              <h2 className="text-2xl font-bold text-foreground">Question Details</h2>
            </div>
          </div>
        </header>

        <div className="p-6 space-y-6">
          {/* Question Card */}
          <Card>
            <CardHeader>
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <div className="flex items-center space-x-3 mb-3">
                    <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                      {q.question.category}
                    </Badge>
                    <Badge className={statusStyles[q.question.status]}>
                      {statusIcons[q.question.status]}
                      {q.question.status}
                    </Badge>

                      {q.question.is_final &&
                      <Badge variant="destructive" className="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">FINAL ANSWER</Badge>}

                  </div>
                  <CardTitle className="text-2xl mb-4">{q.question.title}</CardTitle>
                </div>
                {canDeleteQuestion && (
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                    onClick={handleDeleteQuestion}
                    title="Delete question"
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                )}
              </div>
            </CardHeader>
            <CardContent>
              <div className="prose dark:prose-invert mb-6">
                <p className="text-foreground whitespace-pre-wrap">
                  {q.question.description}
                </p>

                <div className="mt-4">
                 {q?.question?.attachment && (
  <img
    src={
      q.question.attachment.startsWith("http")
        ? q.question.attachment
        : `/storage/${q.question.attachment}`
    }
    alt="Question attachment"
    className="max-w-full h-auto rounded-lg border max-h-[300px]"
  />
)}
                </div>
              </div>

              <div className="flex items-center justify-between pt-4 border-t border-border">
                <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                  <div className="flex items-center space-x-1">
                    <User className="h-4 w-4" />
                    <span>{displayName(q.question.author)}</span>
                  </div>
                  <div className="flex items-center space-x-1">
                    <Clock className="h-4 w-4" />
                    <span>{moment(q.question.created_at).fromNow()}</span>
                  </div>
                  <div className="flex items-center space-x-1">
                    <Eye className="h-4 w-4" />
                    <span>{q.question.views} views</span>
                  </div>
                </div>

                <Badge variant="secondary" className="flex items-center">
                  <MessageCircle className="mr-1 h-3 w-3" />
                  {q.question.answer?.length ?? 0} {q.question.answer?.length === 1 ? "answer" : "answers"}
                </Badge>
              </div>
            </CardContent>
          </Card>

          {/* Answers Section */}
          <Card>
            <CardHeader>
              <CardTitle>Answers ({q.question.answer?.length ?? 0})</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {q.question.answer && q.question.answer.length > 0 ? (
                  q.question.answer.map((answer) => (
                    <div
                      key={answer.id ?? Math.random()}
                      className="border border-border rounded-lg p-4"
                    >
                      <div className="flex items-start justify-between mb-3">
                        <Badge className={statusStyles[answer.status]}>
                          {statusIcons[answer.status]}
                          {answer.status.charAt(0).toUpperCase() + answer.status.slice(1)}
                        </Badge>

                        {/* Admin Action Controls — only shown for answers awaiting review */}
                        <div className="flex items-center space-x-2">
                          {isAdmin && answer.status !== "approved" && (
                            <Button
                              variant="ghost"
                              size="sm"
                              className="text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20"
                              onClick={() => handleApprove(answer.id)}
                              title="Approve answer"
                            >
                              <Check className="h-4 w-4" />
                            </Button>
                          )}
                          {isAdmin && answer.status !== "rejected" && (
                            <Button
                              variant="ghost"
                              size="sm"
                              className="text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20"
                              onClick={() => handleReject(answer.id)}
                              title="Reject answer"
                            >
                              <X className="h-4 w-4" />
                            </Button>
                          )}
                          {canDeleteAnswer(answer) && (
                            <Button
                              variant="ghost"
                              size="sm"
                              className="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                              onClick={() => handleDeleteAnswer(answer.id)}
                              title="Delete answer"
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          )}
                        </div>
                      </div>

                      <div className="prose dark:prose-invert mb-4">
                        <p className="text-foreground whitespace-pre-wrap">
                          {answer.answer_text}
                        </p>

                        {answer.attachment && (
                          <div className="mt-4">
                            <img
                              src={
                                answer.attachment.startsWith("http")
                                  ? answer.attachment
                                  : `/storage/${answer.attachment}`
                              }
                              alt="Answer attachment"
                              className="max-w-full h-auto rounded-lg border max-h-[300px]"
                            />
                          </div>
                        )}
                      </div>

                      <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                        <div className="flex items-center space-x-1">
                          <User className="h-4 w-4" />
                          <span>{displayName(answer.author)}</span>
                        </div>
                        <div className="flex items-center space-x-1">
                          <Clock className="h-4 w-4" />
                          <span>{moment(answer.created_at).fromNow()}</span>
                        </div>
                      </div>
                    </div>
                  ))
                ) : (
                  <p className="text-center text-muted-foreground py-6">
                    No answers yet.
                  </p>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Answer Form UI */}
          <Card>
            <CardHeader>
              <CardTitle>Submit an Answer</CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleAnswerSubmit} className="space-y-4">
                <div>
                  <Label htmlFor="answer">Your Answer</Label>
                  <Textarea
                    id="answer"
                    rows={6}
                    placeholder="Provide a detailed answer to help solve this issue..."
                    value={answerForm.data.answer_text}
                    onChange={(e) => answerForm.setData("answer_text", e.target.value)}
                  />
                  {answerForm.errors.answer_text && (
                    <p className="text-sm text-red-600 mt-1">{answerForm.errors.answer_text}</p>
                  )}
                </div>

                <div>
                  <Label htmlFor="answer-image">Attach Image (Optional)</Label>
                  <div className="mt-2">
                    <input
                      ref={fileInputRef}
                      type="file"
                      id="answer-image"
                      accept="image/jpeg,image/png,image/gif,image/webp"
                      onChange={handleFileChange}
                      className="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100"
                    />
                    {answerForm.errors.attachment && (
                      <p className="text-sm text-red-600 mt-1">{answerForm.errors.attachment}</p>
                    )}
                  </div>

                  {answerForm.data.attachment && (
                    <div className="mt-4 flex items-center space-x-3">
                      <img
                        src={URL.createObjectURL(answerForm.data.attachment)}
                        alt="Preview"
                        className="max-w-full h-auto rounded-lg border max-h-[200px]"
                      />
                      <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="text-red-600"
                        onClick={() => {
                          answerForm.setData("attachment", null);
                          if (fileInputRef.current) fileInputRef.current.value = "";
                        }}
                      >
                        Remove Image
                      </Button>
                    </div>
                  )}
                </div>

                <div className="flex justify-end">
                  <Button
                    type="submit"
                    className="bg-lime-green text-dark-green hover:bg-lime-green/90"
                    disabled={answerForm.processing || !answerForm.data.answer_text.trim()}
                  >
                    {answerForm.processing ? (
                      <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    ) : (
                      <Send className="mr-2 h-4 w-4" />
                    )}
                    Submit Answer
                  </Button>
                </div>
              </form>
            </CardContent>
          </Card>
        </div>
      </main>

      <RaiseIssueModal
        open={false}
        onOpenChange={() => {}}
      />
    </div>
  );
}
