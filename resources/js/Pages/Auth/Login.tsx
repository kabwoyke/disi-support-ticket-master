import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Shield, Users, TrendingUp, ArrowLeft, Home } from "lucide-react";
import '../../../css/solves.css';
import { Form } from "@inertiajs/react";

import disilogo from "../../images/DISI-logo.png"

export default function Login() {
  const [loginForm, setLoginForm] = useState({ username: "", password: "" });

  // Go back if there is history to return to, otherwise fall back to the home page.
  const goBack = () => {
    if (window.history.length > 1) {
      window.history.back();
    } else {
      window.location.href = "/";
    }
  };
  return (
    <div className="min-h-screen flex">

      {/* Left side - Forms */}
      <div className="flex-1 flex items-center justify-center p-8 bg-background">
        <div className="w-full max-w-md">
          <div className="flex items-center justify-between mb-6 text-sm">
            <button
              type="button"
              onClick={goBack}
              className="inline-flex items-center gap-1 text-muted-foreground hover:text-foreground"
              data-testid="button-back"
            >
              <ArrowLeft className="h-4 w-4" /> Back
            </button>
            <a
              href="/"
              className="inline-flex items-center gap-1 text-muted-foreground hover:text-foreground"
              data-testid="link-home"
            >
              <Home className="h-4 w-4" /> Home
            </a>
          </div>
          <div className="text-center mb-8">
            <div className="flex items-center justify-center mb-4">
              <a href="/" aria-label="Go to home page">
                <img
                  src={disilogo}
                  alt="DISI Logo"
                  className="h-16 w-auto object-contain"
                />
              </a>
            </div>
            <h1 className="text-3xl font-bold text-dark-green dark:text-lime-green">DisiSolves</h1>
            <p className="text-gray-600 dark:text-gray-400 mt-2">Solve all DISI IT issues</p>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Welcome</CardTitle>
              <p className="text-sm text-muted-foreground mt-2">
                Please enter username and password
              </p>
            </CardHeader>
            <CardContent>
              <Form method="post" action={"/disi-solves/auth/login"} className="space-y-4">
                <div>
                  <Label htmlFor="username">Username</Label>
                  <Input
                    id="username"
                    name="username"
                    type="text"
                    value={loginForm.username}
                    onChange={(e) => setLoginForm({ ...loginForm, username: e.target.value })}
                    required
                    data-testid="input-username"
                  />
                </div>
                <div>
                  <Label htmlFor="password">Password</Label>
                  <Input
                    id="password"
                    name="password"
                    type="password"
                    value={loginForm.password}
                    onChange={(e) => setLoginForm({ ...loginForm, password: e.target.value })}
                    required
                    data-testid="input-password"
                  />
                </div>
                <Button
                  type="submit"
                  className="w-full bg-lime-green text-dark-green hover:bg-lime-green/90 font-bold text-lg py-6"
                //   disabled={loginMutation.isPending}
                  data-testid="button-login"
                >
                 Sign In
                </Button>
              </Form>
            </CardContent>
          </Card>

          <div className="mt-6 text-center text-sm text-muted-foreground space-y-1">
            <p>
              Need help from the ICT team?{" "}
              <a href="/auth/login" className="font-semibold text-dark-green dark:text-lime-green hover:underline" data-testid="link-raise-ticket">
                Raise a support ticket
              </a>
            </p>
            <p>
              ICT support staff?{" "}
              <a href="/support/auth/login" className="font-semibold text-dark-green dark:text-lime-green hover:underline" data-testid="link-support-login">
                Support team login
              </a>
            </p>
          </div>
        </div>
      </div>

      {/* Right side - Hero section */}
      <div
        className="flex-1 text-white p-8 flex items-center justify-center relative"
        style={{
          backgroundImage: 'url(\assets\imagetrac-6400.png)',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundRepeat: 'no-repeat'
        }}
      >
        <div className="absolute inset-0 bg-gradient-to-br  from-dark-green/80 to-dark-green/70"></div>
        <div className="max-w-lg text-center relative z-10">
          <h2 className="text-4xl font-bold mb-6">Solve IT Issues Together</h2>
          <p className="text-lg mb-8 text-gray-200">
           For solving IBML Scanner, SoftTrac, and OmniScan issues.
            {/* Share knowledge, get answers, and help your team stay productive. */}
          </p>

          <div className="grid grid-cols-1 gap-6">
            <div className="flex items-center space-x-4">
              <div className="w-12 h-12 bg-lime-green/20 rounded-lg flex items-center justify-center">
                <Users className="text-lime-green" />
              </div>
              <div className="text-left">
                <h3 className="font-semibold text-lime-green">Role-based Access</h3>
                <p className="text-sm text-gray-300">Different permissions for Users, Supervisors, and Admins</p>
              </div>
            </div>

            <div className="flex items-center space-x-4">
              <div className="w-12 h-12 bg-lime-green/20 rounded-lg flex items-center justify-center">
                <Shield className="text-lime-green" />
              </div>
              <div className="text-left">
                <h3 className="font-semibold text-lime-green">Approval Workflow</h3>
                <p className="text-sm text-gray-300">Users submit questions that has to be approved by admin and supervisors can answer questions  with admin approval</p>
              </div>
            </div>

            <div className="flex items-center space-x-4">
              <div className="w-12 h-12 bg-lime-green/20 rounded-lg flex items-center justify-center">
                <TrendingUp className="text-lime-green" />
              </div>
              <div className="text-left">
                <h3 className="font-semibold text-lime-green">Trending Issues</h3>
                <p className="text-sm text-gray-300">Stay updated with the most relevant problems and solutions at DISI</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
