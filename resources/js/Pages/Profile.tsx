import { useRef, useState } from "react";
import { router, useForm, usePage } from "@inertiajs/react";
import { UserCircle } from "lucide-react";
import { Sidebar } from "../components/sidebar";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { PageProps } from "@/Pages/Dashboard";
import "../../css/solves.css";

type ProfilePageProps = PageProps & { flash?: { success?: string | null } };

export default function Profile() {
  const { solves, flash } = usePage<ProfilePageProps>().props;
  const user = solves?.user;
  const fileInput = useRef<HTMLInputElement>(null);
  const [preview, setPreview] = useState<string | null>(null);

  const { data, setData, post, processing, errors, reset } = useForm<{
    first_name: string;
    last_name: string;
    photo: File | null;
  }>({
    first_name: user?.first_name ?? "",
    last_name: user?.last_name ?? "",
    photo: null,
  });

  const onPick = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    setData("photo", file);
    setPreview(file ? URL.createObjectURL(file) : null);
  };

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    post("/disi-solves/profile", {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        reset("photo");
        setPreview(null);
        if (fileInput.current) fileInput.current.value = "";
      },
    });
  };

  const removePicture = () => {
    if (confirm("Remove your profile picture?")) {
      router.delete("/disi-solves/profile/picture", { preserveScroll: true });
    }
  };

  return (
    <div className="min-h-screen bg-background">
      <Sidebar />

      <main className="ml-64 min-h-screen">
        <header className="bg-card border-b border-border p-6">
          <div className="flex items-center space-x-4">
            <UserCircle className="h-8 w-8 text-primary" />
            <div>
              <h2 className="text-2xl font-bold text-foreground">My Profile</h2>
              <p className="text-muted-foreground">Change your name and profile picture</p>
            </div>
          </div>
        </header>

        <div className="p-6 max-w-xl">
          {flash?.success && (
            <div className="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-300">
              {flash.success}
            </div>
          )}

          <Card>
            <CardHeader>
              <CardTitle>Profile details</CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={submit} className="space-y-5">
                <div className="flex items-center gap-4">
                  <img
                    src={preview ?? user?.avatar_url}
                    alt="Profile"
                    className="h-20 w-20 rounded-full object-cover border border-border"
                  />
                  <div className="space-y-2">
                    <input
                      ref={fileInput}
                      type="file"
                      accept="image/*"
                      onChange={onPick}
                      className="block text-sm text-muted-foreground file:mr-3 file:rounded-md file:border-0 file:bg-lime-green/10 file:px-3 file:py-1.5 file:text-sm file:font-medium"
                    />
                    {user?.has_picture && (
                      <button type="button" onClick={removePicture} className="text-xs text-red-600 hover:underline">
                        Remove picture
                      </button>
                    )}
                    {errors.photo && <p className="text-xs text-red-600">{errors.photo}</p>}
                  </div>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div className="space-y-1.5">
                    <Label htmlFor="first_name">First name</Label>
                    <Input id="first_name" value={data.first_name} onChange={(e) => setData("first_name", e.target.value)} />
                    {errors.first_name && <p className="text-xs text-red-600">{errors.first_name}</p>}
                  </div>
                  <div className="space-y-1.5">
                    <Label htmlFor="last_name">Last name</Label>
                    <Input id="last_name" value={data.last_name} onChange={(e) => setData("last_name", e.target.value)} />
                    {errors.last_name && <p className="text-xs text-red-600">{errors.last_name}</p>}
                  </div>
                </div>

                <div className="space-y-1.5">
                  <Label htmlFor="username">Username</Label>
                  <Input id="username" value={user?.username ?? ""} disabled />
                </div>

                <Button type="submit" disabled={processing}>
                  {processing ? "Saving..." : "Save changes"}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </main>
    </div>
  );
}
