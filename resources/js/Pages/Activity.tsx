import {Sidebar} from "../components/sidebar";
import { RaiseIssueModal } from "@/components/raise-issue-modal";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { History, MessageSquare, HelpCircle, Calendar } from "lucide-react";
import { Link } from "wouter";
import { useState } from "react";
import "../../css/solves.css"

export default function Activity() {

    const [isOpen , setIsOpen] = useState(false)
    const [showRaiseIssueModal, setShowRaiseIssueModal] = useState(false);
    const [showAdminPostModal, setShowAdminPostModal] = useState(false);
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

        <div className="p-6">
          <Card>
            <CardHeader>
              <CardTitle>Recent Activity (2 items)</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {/* Sample Question Item */}
                <div className="flex items-start space-x-4 p-4 border border-border rounded-lg">
                  <div className="w-10 h-10 bg-lime-green/10 rounded-full flex items-center justify-center flex-shrink-0">
                    <HelpCircle className="h-5 w-5 text-lime-green" />
                  </div>

                  <div className="flex-1 min-w-0">
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <div className="flex items-center space-x-2 mb-1">
                          <span className="text-sm font-medium text-muted-foreground">
                            Posted Question
                          </span>
                          <Badge className="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                            approved
                          </Badge>
                        </div>

                        <h3 className="font-medium text-foreground mb-1">
                          Sample Question Title
                        </h3>

                        <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                          <div className="flex items-center space-x-1">
                            <Calendar className="h-4 w-4" />
                            <span>8/31/2026</span>
                          </div>
                          <Link href="/questions/1" className="text-primary hover:underline">
                            View Details
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Sample Answer Item */}
                <div className="flex items-start space-x-4 p-4 border border-border rounded-lg">
                  <div className="w-10 h-10 bg-lime-green/10 rounded-full flex items-center justify-center flex-shrink-0">
                    <MessageSquare className="h-5 w-5 text-lime-green" />
                  </div>

                  <div className="flex-1 min-w-0">
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <div className="flex items-center space-x-2 mb-1">
                          <span className="text-sm font-medium text-muted-foreground">
                            Posted Answer
                          </span>
                          <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300">
                            pending
                          </Badge>
                        </div>

                        <h3 className="font-medium text-foreground mb-1">
                          Answer to: Sample Question Title
                        </h3>

                        <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                          <div className="flex items-center space-x-1">
                            <Calendar className="h-4 w-4" />
                            <span>8/31/2026</span>
                          </div>
                          <Link href="/questions/1" className="text-primary hover:underline">
                            View Question
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </main>

       <RaiseIssueModal
        open={showRaiseIssueModal}
        onOpenChange={setShowRaiseIssueModal}
      />
    </div>
  );
}
