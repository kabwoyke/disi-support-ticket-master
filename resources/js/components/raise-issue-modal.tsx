import { useRef, useState } from "react";
import { useForm, usePage } from "@inertiajs/react";
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
import { Send, Upload, X, Loader2 } from "lucide-react";
import "../../css/solves.css";
import { PageProps } from "@/Pages/Dashboard";


interface RaiseIssueModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export function RaiseIssueModal({ open, onOpenChange }: RaiseIssueModalProps) {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [filePreview, setFilePreview] = useState<string | null>(null);

  const { solves } = usePage<PageProps>().props;
  const user = solves?.user;

  // Initialize Inertia form
  const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
    title: "",
    category: "" as string | null,
    description: "",
    created_by:user?.id,
    attachment: null as File | null,
  });

  // Handle file selection
  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setData("attachment", file);
      setFilePreview(URL.createObjectURL(file));
    }
  };

  // Remove selected file
  const handleRemoveFile = () => {
    setData("attachment", null);
    if (filePreview) {
      URL.revokeObjectURL(filePreview);
      setFilePreview(null);
    }
    if (fileInputRef.current) {
      fileInputRef.current.value = "";
    }
  };

  // Reset modal state
  const handleClose = () => {
    reset();
    clearErrors();
    handleRemoveFile();
    onOpenChange(false);
  };

  // Submit form data to Laravel
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    post("/disi-solves/questions/store", {
      forceFormData: true,
      onSuccess: () => {
        handleClose();
      },
    });
  };

  return (
    <Dialog open={open} onOpenChange={handleClose}>
      <DialogContent className="sm:max-w-4xl w-[90vw] max-h-[85vh] overflow-y-auto p-8">
        <DialogHeader className="mb-2">
          <DialogTitle className="text-2xl font-bold">Raise New Issue</DialogTitle>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Issue Title */}
          <div className="space-y-2">
            <Label htmlFor="title" className="text-sm font-semibold">Issue Title *</Label>
            <Input
              id="title"
              value={data.title}
              onChange={(e) => setData("title", e.target.value)}
              placeholder="Brief description of your issue..."
              required
              className="h-11"
            />
            {errors.title && <p className="text-sm text-red-500">{errors.title}</p>}
          </div>

          {/* Category */}
          <div className="space-y-2">
            <Label htmlFor="category" className="text-sm font-semibold">Category *</Label>
            <Select
              value={data.category || ""}
              onValueChange={(value) => setData("category", value)}
            >
              <SelectTrigger className="h-11">
                <SelectValue placeholder="Select a category" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="ibml">IBML Scanners</SelectItem>
                <SelectItem value="softtrac">SoftTrac</SelectItem>
                <SelectItem value="omniscan">OmniScan</SelectItem>
              </SelectContent>
            </Select>
            {errors.category && <p className="text-sm text-red-500">{errors.category}</p>}
          </div>

          {/* Description */}
          <div className="space-y-2">
            <Label htmlFor="description" className="text-sm font-semibold">Detailed Description *</Label>
            <Textarea
              id="description"
              rows={5}
              value={data.description}
              onChange={(e) => setData("description", e.target.value)}
              placeholder="Please provide detailed information about the issue, including steps to reproduce, error messages, and any troubleshooting steps you've already tried..."
              required
              className="resize-none"
            />
            {errors.description && <p className="text-sm text-red-500">{errors.description}</p>}
          </div>

          {/* Image Attachment */}
          <div className="space-y-2">
            <Label className="text-sm font-semibold">Attach Image (Optional)</Label>
            <div className="space-y-3">
              <input
                type="file"
                ref={fileInputRef}
                accept="image/*"
                onChange={handleFileChange}
                className="hidden"
              />

              {!data.attachment ? (
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => fileInputRef.current?.click()}
                  className="w-full h-24 border-dashed border-2 border-muted-foreground/30 hover:border-primary/50 transition-colors"
                >
                  <div className="flex flex-col items-center space-y-1">
                    <Upload className="h-6 w-6 text-muted-foreground mb-1" />
                    <span className="text-sm font-medium text-foreground">Click to upload image</span>
                    <span className="text-xs text-muted-foreground">JPEG, PNG, GIF, WebP (max 5MB)</span>
                  </div>
                </Button>
              ) : (
                <div className="relative flex items-center justify-between p-4 border rounded-lg bg-card">
                  <div className="flex items-center space-x-4">
                    {filePreview && (
                      <img
                        src={filePreview}
                        alt="Preview"
                        className="w-12 h-12 object-cover rounded-md border"
                      />
                    )}
                    <div>
                      <p className="text-sm font-medium text-foreground">{data.attachment.name}</p>
                      <p className="text-xs text-muted-foreground">
                        {(data.attachment.size / (1024 * 1024)).toFixed(2)} MB
                      </p>
                    </div>
                  </div>
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={handleRemoveFile}
                    className="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/20"
                  >
                    <X className="h-4 w-4" />
                  </Button>
                </div>
              )}
            </div>
            {errors.attachment && <p className="text-sm text-red-500">{errors.attachment}</p>}
          </div>

          {/* Form Actions */}
          <div className="flex items-center justify-end space-x-4 pt-6 border-t border-border">
            <Button
              type="button"
              variant="outline"
              size="lg"
              disabled={processing}
              onClick={handleClose}
            >
              Cancel
            </Button>
            <Button
              type="submit"
              size="lg"
              disabled={processing}
              className="bg-lime-green text-dark-green hover:bg-lime-green/90 font-bold px-8 shadow-md transition-all duration-200"
            >
              {processing ? (
                <>
                  <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                  SUBMITTING...
                </>
              ) : (
                <>
                  <Send className="mr-2 h-5 w-5" />
                  SUBMIT ISSUE
                </>
              )}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
