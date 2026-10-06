import { useState } from "react";
import { Sidebar } from "@/components/sidebar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Users,
  UserPlus,
  Shield,
  Search,
  MoreVertical,
  UserCheck,
  Edit,
  Trash2,
  X,
  Loader2,
} from "lucide-react";
import "../../css/solves.css";
import { router, useForm, usePage } from "@inertiajs/react";
import { PageProps as InertiaPageProps } from "@inertiajs/core";
import moment from "moment";

export interface UserItem {
  id: number;
  username: string;
  first_name: string | null;
  last_name: string | null;
  role: "admin" | "supervisor" | "user";
  supervisor_type?: string | null;
  created_at: string;
}

interface PageProps extends InertiaPageProps {
  users: UserItem[];
  solves?: {
    user?: {
      id: number;
      username: string;
      role: string;
    };
  };
}

export default function UserManagement() {
  const { users = [] } = usePage<PageProps>().props;
  const [searchQuery, setSearchQuery] = useState("");

  // Modal states
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState<UserItem | null>(null);
  const [activeMenuId, setActiveMenuId] = useState<number | null>(null);

  // Search filter
  const filteredUsers = users.filter((u) => {
    const fullName = `${u.first_name ?? ""} ${u.last_name ?? ""}`.toLowerCase();
    const query = searchQuery.toLowerCase();
    return (
      u.username.toLowerCase().includes(query) ||
      fullName.includes(query) ||
      u.role.toLowerCase().includes(query)
    );
  });

  // Inertia Form hook for Creating a user
  const createForm = useForm({
    username: "",
    first_name: "",
    last_name: "",
    role: "user" as "admin" | "supervisor" | "user",
    supervisor_type: "",
    password: "",
  });

  // Inertia Form hook for Updating a user
  const editForm = useForm({
    username: "",
    first_name: "",
    last_name: "",
    role: "user" as "admin" | "supervisor" | "user",
    supervisor_type: "",
    password: "",
  });

  // Handle Add User Form Submission
  const handleCreateUser = (e: React.FormEvent) => {
    e.preventDefault();
    createForm.post("/disi-solves/admin/add/users", {
      preserveScroll: true,
      onSuccess: () => {
        createForm.reset();
        setIsAddModalOpen(false);
      },
    });
  };

  // Open Edit Modal & Populate Form Data
  const handleOpenEditModal = (user: UserItem) => {
    setEditingUser(user);
    editForm.setData({
      username: user.username,
      first_name: user.first_name ?? "",
      last_name: user.last_name ?? "",
      role: user.role,
      supervisor_type: user.supervisor_type ?? "",
      password: "", // Leave blank unless changing
    });
    setActiveMenuId(null);
  };

  // Handle Edit User Form Submission
  const handleUpdateUser = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingUser) return;

    editForm.put(`/disi-solves/admin/${editingUser.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        editForm.reset();
        setEditingUser(null);
      },
    });
  };

  // Handle Delete User
  const handleDeleteUser = (userId: number) => {
    setActiveMenuId(null);
    if (confirm("Are you sure you want to delete this user account?")) {
      router.delete(`/disi-solves/admin/users/${userId}`, {
        preserveScroll: true,
      });
    }
  };

  const getRoleBadge = (role: string) => {
    switch (role.toLowerCase()) {
      case "admin":
        return (
          <Badge className="bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-red-200">
            <Shield className="mr-1 h-3 w-3" /> Admin
          </Badge>
        );
      case "supervisor":
        return (
          <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200">
            <UserCheck className="mr-1 h-3 w-3" /> Supervisor
          </Badge>
        );
      default:
        return (
          <Badge className="bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200">
            <Users className="mr-1 h-3 w-3" /> Standard User
          </Badge>
        );
    }
  };

  return (
    <div className="min-h-screen bg-background flex">
      <Sidebar />

      <main className="ml-64 flex-1 min-h-screen">
        {/* Top Header */}
        <header className="bg-card border-b border-border p-6 flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 bg-lime-green/20 rounded-lg flex items-center justify-center">
              <Users className="text-lime-green h-5 w-5" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-foreground">User Management</h1>
              <p className="text-sm text-muted-foreground">
                Manage DISI team permissions, roles, and user access controls
              </p>
            </div>
          </div>

          <Button
            onClick={() => setIsAddModalOpen(true)}
            className="bg-lime-green text-dark-green hover:bg-lime-green/90 font-semibold"
          >
            <UserPlus className="mr-2 h-4 w-4" /> Add New User
          </Button>
        </header>

        <div className="p-6 space-y-6">
          {/* User Stats Grid */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card className="bg-card border-border">
              <CardContent className="p-4 flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Total Accounts</p>
                  <h3 className="text-2xl font-bold text-foreground mt-1">{users.length}</h3>
                </div>
                <div className="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center text-blue-600">
                  <Users className="h-5 w-5" />
                </div>
              </CardContent>
            </Card>

            <Card className="bg-card border-border">
              <CardContent className="p-4 flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">Supervisors</p>
                  <h3 className="text-2xl font-bold text-foreground mt-1">
                    {users.filter((u) => u.role === "supervisor").length}
                  </h3>
                </div>
                <div className="w-10 h-10 bg-lime-green/20 rounded-full flex items-center justify-center text-dark-green dark:text-lime-green">
                  <UserCheck className="h-5 w-5" />
                </div>
              </CardContent>
            </Card>

            <Card className="bg-card border-border">
              <CardContent className="p-4 flex items-center justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">System Admins</p>
                  <h3 className="text-2xl font-bold text-foreground mt-1">
                    {users.filter((u) => u.role === "admin").length}
                  </h3>
                </div>
                <div className="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center text-red-600">
                  <Shield className="h-5 w-5" />
                </div>
              </CardContent>
            </Card>
          </div>

          {/* User Table Card */}
          <Card className="border-border">
            <CardHeader className="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-4 gap-4">
              <CardTitle className="text-xl">System Accounts</CardTitle>
              <div className="relative w-full sm:w-72">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search user or role..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-9"
                />
              </div>
            </CardHeader>
            <CardContent>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm">
                  <thead className="bg-muted/50 border-b border-border text-muted-foreground">
                    <tr>
                      <th className="p-3 font-semibold">User</th>
                      <th className="p-3 font-semibold">Role</th>
                      <th className="p-3 font-semibold">Supervisor Specialization</th>
                      <th className="p-3 font-semibold">Joined Date</th>
                      <th className="p-3 font-semibold text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-border">
                    {filteredUsers.length > 0 ? (
                      filteredUsers.map((user) => (
                        <tr key={user.id} className="hover:bg-muted/30 transition-colors relative">
                          <td className="p-3">
                            <div className="flex items-center space-x-3">
                              <div className="w-8 h-8 rounded-full bg-dark-green text-lime-green font-bold flex items-center justify-center text-xs">
                                {user.username.slice(0, 2).toUpperCase()}
                              </div>
                              <div>
                                <p className="font-semibold text-foreground">{user.username}</p>
                                <p className="text-xs text-muted-foreground">
                                  {user.first_name || user.last_name
                                    ? `${user.first_name ?? ""} ${user.last_name ?? ""}`
                                    : "No name attached"}
                                </p>
                              </div>
                            </div>
                          </td>
                          <td className="p-3">{getRoleBadge(user.role)}</td>
                          <td className="p-3 text-muted-foreground">
                            {user.supervisor_type ? (
                              <Badge variant="outline" className="font-mono text-xs">
                                {user.supervisor_type}
                              </Badge>
                            ) : (
                              <span className="text-xs italic">N/A</span>
                            )}
                          </td>
                          <td className="p-3 text-muted-foreground text-xs">
                            {moment(user.created_at).format("MMM D, YYYY")}
                          </td>
                          <td className="p-3 text-right relative">
                            <Button
                              variant="ghost"
                              size="sm"
                              className="h-8 w-8 p-0"
                              onClick={() =>
                                setActiveMenuId(activeMenuId === user.id ? null : user.id)
                              }
                            >
                              <MoreVertical className="h-4 w-4" />
                            </Button>

                            {/* Dropdown Action Menu */}
                            {activeMenuId === user.id && (
                              <div className="absolute right-3 top-10 w-36 bg-card border border-border rounded-lg shadow-lg z-50 py-1 text-left">
                                <button
                                  onClick={() => handleOpenEditModal(user)}
                                  className="w-full flex items-center px-3 py-2 text-sm text-foreground hover:bg-muted transition-colors"
                                >
                                  <Edit className="mr-2 h-4 w-4 text-blue-500" /> Edit
                                </button>
                                <button
                                  onClick={() => handleDeleteUser(user.id)}
                                  className="w-full flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors"
                                >
                                  <Trash2 className="mr-2 h-4 w-4 text-red-500" /> Delete
                                </button>
                              </div>
                            )}
                          </td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td colSpan={5} className="p-8 text-center text-muted-foreground">
                          No users found matching your criteria.
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>
        </div>
      </main>

      {/* CREATE USER MODAL */}
      {isAddModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="bg-card border border-border w-full max-w-md rounded-lg shadow-xl overflow-hidden">
            <div className="flex items-center justify-between p-4 border-b border-border">
              <h3 className="font-bold text-lg text-foreground">Create New User</h3>
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setIsAddModalOpen(false)}
                className="h-8 w-8 p-0"
              >
                <X className="h-4 w-4" />
              </Button>
            </div>
            <form onSubmit={handleCreateUser} className="p-4 space-y-4">
              <div>
                <Label htmlFor="username">Username</Label>
                <Input
                  id="username"
                  required
                  value={createForm.data.username}
                  onChange={(e) => createForm.setData("username", e.target.value)}
                />
                {createForm.errors.username && (
                  <p className="text-xs text-red-500 mt-1">{createForm.errors.username}</p>
                )}
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <Label htmlFor="first_name">First Name</Label>
                  <Input
                    id="first_name"
                    value={createForm.data.first_name}
                    onChange={(e) => createForm.setData("first_name", e.target.value)}
                  />
                </div>
                <div>
                  <Label htmlFor="last_name">Last Name</Label>
                  <Input
                    id="last_name"
                    value={createForm.data.last_name}
                    onChange={(e) => createForm.setData("last_name", e.target.value)}
                  />
                </div>
              </div>

              <div>
                <Label htmlFor="role">Account Role</Label>
                <select
                  id="role"
                  className="w-full mt-1 p-2 bg-background border border-border rounded-md text-sm text-foreground"
                  value={createForm.data.role}
                  onChange={(e) =>
                    createForm.setData("role", e.target.value as "admin" | "supervisor" | "user")
                  }
                >
                  <option value="user">Standard User</option>
                  <option value="supervisor">Supervisor</option>
                  <option value="admin">Admin</option>
                </select>
              </div>

              {createForm.data.role === "supervisor" && (
                <div>
                  <Label htmlFor="supervisor_type">Supervisor Specialization</Label>
                  <Input
                    id="supervisor_type"
                    placeholder="e.g., IBML Scanner, SoftTrac"
                    value={createForm.data.supervisor_type}
                    onChange={(e) => createForm.setData("supervisor_type", e.target.value)}
                  />
                </div>
              )}

              <div>
                <Label htmlFor="password">Password</Label>
                <Input
                  id="password"
                  type="password"
                  required
                  value={createForm.data.password}
                  onChange={(e) => createForm.setData("password", e.target.value)}
                />
                {createForm.errors.password && (
                  <p className="text-xs text-red-500 mt-1">{createForm.errors.password}</p>
                )}
              </div>

              <div className="flex justify-end space-x-2 pt-2">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setIsAddModalOpen(false)}
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  disabled={createForm.processing}
                  className="bg-lime-green text-dark-green hover:bg-lime-green/90 font-semibold"
                >
                  {createForm.processing && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                  Create Account
                </Button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* EDIT USER MODAL */}
      {editingUser && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="bg-card border border-border w-full max-w-md rounded-lg shadow-xl overflow-hidden">
            <div className="flex items-center justify-between p-4 border-b border-border">
              <h3 className="font-bold text-lg text-foreground">Edit User Account</h3>
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setEditingUser(null)}
                className="h-8 w-8 p-0"
              >
                <X className="h-4 w-4" />
              </Button>
            </div>
            <form onSubmit={handleUpdateUser} className="p-4 space-y-4">
              <div>
                <Label htmlFor="edit_username">Username</Label>
                <Input
                  id="edit_username"
                  required
                  value={editForm.data.username}
                  onChange={(e) => editForm.setData("username", e.target.value)}
                />
                {editForm.errors.username && (
                  <p className="text-xs text-red-500 mt-1">{editForm.errors.username}</p>
                )}
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <Label htmlFor="edit_first_name">First Name</Label>
                  <Input
                    id="edit_first_name"
                    value={editForm.data.first_name}
                    onChange={(e) => editForm.setData("first_name", e.target.value)}
                  />
                </div>
                <div>
                  <Label htmlFor="edit_last_name">Last Name</Label>
                  <Input
                    id="edit_last_name"
                    value={editForm.data.last_name}
                    onChange={(e) => editForm.setData("last_name", e.target.value)}
                  />
                </div>
              </div>

              <div>
                <Label htmlFor="edit_role">Account Role</Label>
                <select
                  id="edit_role"
                  className="w-full mt-1 p-2 bg-background border border-border rounded-md text-sm text-foreground"
                  value={editForm.data.role}
                  onChange={(e) =>
                    editForm.setData("role", e.target.value as "admin" | "supervisor" | "user")
                  }
                >
                  <option value="user">Standard User</option>
                  <option value="supervisor">Supervisor</option>
                  <option value="admin">Admin</option>
                </select>
              </div>

              {editForm.data.role === "supervisor" && (
                <div>
                  <Label htmlFor="edit_supervisor_type">Supervisor Specialization</Label>
                  <Input
                    id="edit_supervisor_type"
                    placeholder="e.g., IBML Scanner, SoftTrac"
                    value={editForm.data.supervisor_type}
                    onChange={(e) => editForm.setData("supervisor_type", e.target.value)}
                  />
                </div>
              )}

              <div>
                <Label htmlFor="edit_password">
                  Password <span className="text-xs text-muted-foreground">(Leave blank to keep unchanged)</span>
                </Label>
                <Input
                  id="edit_password"
                  type="password"
                  value={editForm.data.password}
                  onChange={(e) => editForm.setData("password", e.target.value)}
                />
                {editForm.errors.password && (
                  <p className="text-xs text-red-500 mt-1">{editForm.errors.password}</p>
                )}
              </div>

              <div className="flex justify-end space-x-2 pt-2">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setEditingUser(null)}
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  disabled={editForm.processing}
                  className="bg-lime-green text-dark-green hover:bg-lime-green/90 font-semibold"
                >
                  {editForm.processing && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                  Save Changes
                </Button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
