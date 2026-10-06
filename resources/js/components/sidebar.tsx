import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Wrench,
  Moon,
  Sun,
  Home,
  Search,
  Plus,
  BarChart3,
  Users,
  History,
  LogOut
} from "lucide-react";


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
  return (
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
          <div className="w-8 h-8 bg-lime-green rounded-full flex items-center justify-center shrink-0">
            <span className="text-dark-green font-semibold text-sm">
              {user?.first_name && user.first_name[0].toUpperCase()}{user?.last_name && user.last_name[0].toUpperCase()}
            </span>
          </div>
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

        <Link href={"/disi-solves/dashboard"} className="block">
        <Button
          variant="ghost"
          className={navButtonClass("/disi-solves/dashboard/")}
          data-testid="button-browse-all"
        >
          <Search className="mr-3 h-4 w-4" />
          Browse All
        </Button>
        </Link>


        {
            user?.role === "admin" &&
        <Button
          variant="ghost"
          className="w-full justify-start h-11 px-4"
          data-testid="button-analytics"
        >
          <BarChart3 className="mr-3 h-4 w-4" />
          Analytics
        </Button>
}

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

        <Button
          variant="ghost"
          className="w-full justify-start h-11 px-4 bg-lime-green/10 text-lime-green hover:bg-lime-green/20"
          data-testid="button-raise-issue"
        >
          <Plus className="mr-3 h-4 w-4" />
          Raise Issue
        </Button>
      </nav>

      {/* Logout */}
      <div className="absolute bottom-4 left-4 right-4">
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
  );
}
