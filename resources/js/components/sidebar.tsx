import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Wrench,
  Moon,
  Sun,
  Home,
  Search,
  Plus,
  Users,
  History,
  UserCircle,
  Ticket,
  Headset,
  LogOut
} from "lucide-react";


import { useEffect, useState } from "react";
import {Form, Link, usePage} from "@inertiajs/react"
import { PageProps } from "@/Pages/Dashboard";

export function Sidebar() {
    const { solves, questions = [] } = usePage<PageProps>().props;
      const user = solves?.user;
      const currentUrl = usePage().url; // e.g. "/disi-solves/dashboard"

      const isActive = (path: string) =>
        currentUrl === path || currentUrl.startsWith(`${path}/`);

      const navButtonClass = (path: string, extra = "") =>
        `w-full justify-start h-11 px-4 ${
          isActive(path)
            ? "bg-lime-green/10 text-dark-green dark:text-lime-green font-medium"
            : ""
        } ${extra}`;
  const flash = (usePage().props as { flash?: { success?: string | null; error?: string | null } }).flash;
  const [notice, setNotice] = useState<{ kind: "success" | "error"; text: string } | null>(null);

  // Surface server flash messages (deleted / approved / errors) as a dismissing toast.
  useEffect(() => {
    const next = flash?.error
      ? { kind: "error" as const, text: flash.error }
      : flash?.success
      ? { kind: "success" as const, text: flash.success }
      : null;
    setNotice(next);
    if (!next) return;
    const t = setTimeout(() => setNotice(null), 4000);
    return () => clearTimeout(t);
  }, [flash?.success, flash?.error, currentUrl]);

  return (
    <>
    {notice && (
      <div
        role="status"
        onClick={() => setNotice(null)}
        className={`fixed right-4 top-4 z-50 max-w-sm cursor-pointer rounded-lg border px-4 py-3 text-sm shadow-lg ${
          notice.kind === "error"
            ? "border-red-200 bg-red-50 text-red-800 dark:bg-red-900/40 dark:text-red-200"
            : "border-green-200 bg-green-50 text-green-800 dark:bg-green-900/40 dark:text-green-200"
        }`}
      >
        {notice.text}
      </div>
    )}
    <div className="fixed left-0 top-0 h-full w-64 bg-card border-r border-border shadow-lg z-30">
      {/* Logo Section */}
      <div className="flex items-center justify-between p-6 border-b border-border">
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 bg-gradient-to-br from-dark-green to-lime-green rounded-lg flex items-center justify-center">
            <Wrench className="text-white h-5 w-5" />
          </div>
          <div>
            <h1 className="text-xl font-bold text-dark-green dark:text-lime-green">DisiSolves</h1>
            <p className="text-xs text-muted-foreground">Internal Q&A</p>
          </div>
        </div>

        {/* Theme Toggle */}
        <Button
          variant="ghost"
          size="sm"
          className="p-2"
        >
          <Sun className="h-4 w-4 hidden dark:block" />
          <Moon className="h-4 w-4 block dark:hidden" />
        </Button>
      </div>

      {/* User Info */}
      <div className="p-5 border-b border-border">
        <div className="flex items-center space-x-3">
          <Link href="/disi-solves/profile" title="Edit profile" className="shrink-0">
            <img src={user?.avatar_url} alt="Profile" className="w-8 h-8 rounded-full object-cover" />
          </Link>
          <div>
            <p className="font-medium text-sm text-foreground mb-1">
              {user?.first_name} {user?.last_name}
            </p>
            <Badge className="bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300">
              {user?.role}
            </Badge>
          </div>
        </div>
      </div>

      {/* Navigation Menu */}
      <nav className="p-4 space-y-3">
        <Link href={"/disi-solves/dashboard"} className="block">
        <Button
          variant="ghost"
          className={navButtonClass("/disi-solves/dashboard")}
          data-testid="button-dashboard"
        >
          <Home className="mr-3 h-4 w-4" />
          Dashboard
        </Button>
        </Link>

        <Link href={"/disi-solves/search"} className="block">
        <Button
          variant="ghost"
          className={navButtonClass("/disi-solves/search")}
          data-testid="button-search"
        >
          <Search className="mr-3 h-4 w-4" />
          Search Issues
        </Button>
        </Link>


    {
        user?.role === "admin" &&
        <Link href={"/disi-solves/admin/user-management"} className="block">
        <Button
          variant="ghost"
          className={navButtonClass("/disi-solves/admin/user-management")}
          data-testid="button-user-management"
        >
          <Users className="mr-3 h-4 w-4" />
          User Management
        </Button>
        </Link>
}

        <Link href="/disi-solves/activity" className="block w-full">
        <Button

          variant="ghost"
          className={navButtonClass("/disi-solves/activity")}
          data-testid="button-my-activity"
        >
          <History className="mr-3 h-4 w-4" />
          My Activity
        </Button>
        </Link>

        <Link href="/disi-solves/profile" className="block w-full">
        <Button
          variant="ghost"
          className={navButtonClass("/disi-solves/profile")}
        >
          <UserCircle className="mr-3 h-4 w-4" />
          My Profile
        </Button>
        </Link>

        <Link href="/disi-solves/dashboard?raise=1" className="block w-full">
        <Button
          variant="ghost"
          className="w-full justify-start h-11 px-4 bg-lime-green/10 text-lime-green hover:bg-lime-green/20"
          data-testid="button-raise-issue"
        >
          <Plus className="mr-3 h-4 w-4" />
          Raise Issue
        </Button>
        </Link>
      </nav>

      {/* Logout */}
      <div className="absolute bottom-4 left-4 right-4">
        {/* Jump to the other module (separate login, so these are full page loads) */}
        <div className="mb-3 border-t border-border pt-3">
          <p className="mb-1 px-4 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
            Other modules
          </p>
          <a
            href="/tickets/create"
            className="flex h-10 items-center rounded-md px-4 text-sm text-foreground hover:bg-muted"
          >
            <Ticket className="mr-3 h-4 w-4" />
            Support Tickets
          </a>
          <a
            href="/support/dashboard"
            className="flex h-10 items-center rounded-md px-4 text-sm text-foreground hover:bg-muted"
          >
            <Headset className="mr-3 h-4 w-4" />
            Support Team Portal
          </a>
        </div>
        <Form method="post" action={"/disi-solves/auth/logout"}>
        <Button
        type="submit"
          variant="ghost"
          className="w-full justify-start h-11 px-4 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
        >
          <LogOut className="mr-3 h-4 w-4" />
          Logout
        </Button>
        </Form>
      </div>
    </div>
    </>
  );
}
