import { useRef } from "react";
import { useForm } from "@inertiajs/react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Send, Plus, Upload, X } from "lucide-react";

interface AdminPostModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export function AdminPostModal({ open, onOpenChange }: AdminPostModalProps) {
  const questionFileInputRef = useRef<HTMLInputElement | null>(null);
  const answerFileInputRef = useRef<HTMLInputElement | null>(null);

  const { data, setData, post, processing, errors, reset } = useForm({
    title: "",
    description: "",
    category: "" as string | null,
    answer_text: "",
    question_attachment: null as File | null,
    answer_attachment: null as File | null,
  });

  const handleFileSelect = (field: 'question_attachment' | 'answer_attachment') => (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] || null;
    setData(field, file);
  };

const removeFile = (field: 'question_attachment' | 'answer_attachment', ref: React.RefObject<HTMLInputElement | null>) => () => {
  setData(field, null);
  if (ref.current) ref.current.value = "";
};

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    post("/disi-solves/admin/post", {
      forceFormData: true,
      onSuccess: () => {
        reset();
        onOpenChange(false);
      },
    });
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-3xl w-[90vw] max-h-[85vh] overflow-y-auto p-6" data-testid="admin-post-modal">
        <DialogHeader className="mb-2">
          <DialogTitle className="flex items-center space-x-2 text-xl font-bold">
            <Plus className="h-5 w-5 text-dark-green" />
            <span>Post Issue + Answer</span>
          </DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Question Section */}
          <Card className="shadow-sm">
            <CardHeader className="pb-3">
              <CardTitle className="text-lg">Question Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="title" className="text-sm font-semibold">Issue Title *</Label>
                <Input
                  id="title"
                  data-testid="input-question-title"
                  placeholder="Brief description of the issue..."
                  value={data.title}
                  onChange={(e) => setData("title", e.target.value)}
                  required
                />
                {errors.title && <p className="text-xs text-red-500">{errors.title}</p>}
              </div>

              <div className="space-y-2">
                <Label htmlFor="description" className="text-sm font-semibold">Detailed Description *</Label>
                <Textarea
                  id="description"
                  data-testid="textarea-question-description"
                  placeholder="Provide detailed information about the issue, steps to reproduce, and any relevant context..."
                  rows={4}
                  className="resize-none"
                  value={data.description}
                  onChange={(e) => setData("description", e.target.value)}
                  required
                />
                {errors.description && <p className="text-xs text-red-500">{errors.description}</p>}
              </div>

              <div className="space-y-2">
                <Label htmlFor="category" className="text-sm font-semibold">Category *</Label>
                <Select value={data.category} onValueChange={(val) => setData("category", val)}>
                  <SelectTrigger data-testid="select-question-category">
                    <SelectValue placeholder="Select a category" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="ibml">IBML Scanners</SelectItem>
                    <SelectItem value="softtrac">SoftTrac</SelectItem>
                    <SelectItem value="omniscan">OmniScan</SelectItem>
                  </SelectContent>
                </Select>
                {errors.category && <p className="text-xs text-red-500">{errors.category}</p>}
              </div>

              {/* Question Image Attachment */}
              <div className="space-y-2">
                <Label className="text-sm font-semibold">Attach Image to Question (Optional)</Label>
                <input
                  ref={questionFileInputRef}
                  type="file"
                  accept="image/*"
                  onChange={handleFileSelect('question_attachment')}
                  className="hidden"
                />
                {!data.question_attachment ? (
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => questionFileInputRef.current?.click()}
                    className="w-full h-16 border-dashed border-2 border-muted-foreground/30 hover:border-primary/50 transition-colors"
                  >
                    <div className="flex items-center space-x-2">
                      <Upload className="h-4 w-4 text-muted-foreground" />
                      <span className="text-sm text-muted-foreground">Upload question image</span>
                    </div>
                  </Button>
                ) : (
                  <div className="flex items-center justify-between p-2 bg-muted/40 rounded-lg border">
                    <span className="text-sm truncate">{data.question_attachment.name}</span>
                    <Button type="button" variant="ghost" size="sm" onClick={removeFile('question_attachment', questionFileInputRef)}>
                      <X className="h-4 w-4" />
                    </Button>
                  </div>
                )}
                {errors.question_attachment && <p className="text-xs text-red-500">{errors.question_attachment}</p>}
              </div>
            </CardContent>
          </Card>

          {/* Answer Section */}
          <Card className="shadow-sm">
            <CardHeader className="pb-3">
              <CardTitle className="text-lg">Solution/Answer</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="answer" className="text-sm font-semibold">Provide Solution *</Label>
                <Textarea
                  id="answer"
                  data-testid="textarea-answer-text"
                  placeholder="Provide the solution or answer to this issue..."
                  rows={5}
                  className="resize-none"
                  value={data.answer_text}
                  onChange={(e) => setData("answer_text", e.target.value)}
                  required
                />
                {errors.answer_text && <p className="text-xs text-red-500">{errors.answer_text}</p>}
              </div>

              {/* Answer Image Attachment */}
              <div className="space-y-2">
                <Label className="text-sm font-semibold">Attach Image to Answer (Optional)</Label>
                <input
                  ref={answerFileInputRef}
                  type="file"
                  accept="image/*"
                  onChange={handleFileSelect('answer_attachment')}
                  className="hidden"
                />
                {!data.answer_attachment ? (
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => answerFileInputRef.current?.click()}
                    className="w-full h-16 border-dashed border-2 border-muted-foreground/30 hover:border-primary/50 transition-colors"
                  >
                    <div className="flex items-center space-x-2">
                      <Upload className="h-4 w-4 text-muted-foreground" />
                      <span className="text-sm text-muted-foreground">Upload answer image</span>
                    </div>
                  </Button>
                ) : (
                  <div className="flex items-center justify-between p-2 bg-muted/40 rounded-lg border">
                    <span className="text-sm truncate">{data.answer_attachment.name}</span>
                    <Button type="button" variant="ghost" size="sm" onClick={removeFile('answer_attachment', answerFileInputRef)}>
                      <X className="h-4 w-4" />
                    </Button>
                  </div>
                )}
                {errors.answer_attachment && <p className="text-xs text-red-500">{errors.answer_attachment}</p>}
              </div>
            </CardContent>
          </Card>

          <div className="flex justify-end space-x-3 pt-4 border-t border-border">
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)} disabled={processing}>
              Cancel
            </Button>
            <Button
              type="submit"
              disabled={processing}
              className="bg-lime-green text-dark-green hover:bg-lime-green/90 font-bold px-6"
            >
              <Send className="mr-2 h-4 w-4" />
              {processing ? "POSTING..." : "POST QUESTION + ANSWER"}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
