"use client";

import { useState, useEffect } from "react";
import { formatDate, getStatusColor } from "@/lib/utils";
import { Send, Loader2, MessageSquare } from "lucide-react";
import { toast } from "@/components/Toast";

interface Message {
  id: number;
  message: string;
  isAdmin: boolean;
  createdAt: string;
}

interface Ticket {
  id: number;
  ticketId: string;
  subject: string;
  priority: string;
  status: string;
  createdAt: string;
  user: { username: string; email: string };
  messages: Message[];
}

export default function AdminTicketsPage() {
  const [tickets, setTickets] = useState<Ticket[]>([]);
  const [selected, setSelected] = useState<Ticket | null>(null);
  const [reply, setReply] = useState("");
  const [loading, setLoading] = useState(false);

  const load = () => {
    fetch("/api/admin/tickets").then((r) => r.json()).then(setTickets);
  };

  useEffect(() => { load(); }, []);

  const handleReply = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selected) return;
    setLoading(true);
    try {
      await fetch("/api/admin/tickets", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ticketId: selected.id, message: reply }),
      });
      toast("success", "Reply sent");
      setReply("");
      load();
    } catch {
      toast("error", "Failed");
    } finally {
      setLoading(false);
    }
  };

  const closeTicket = async (id: number) => {
    await fetch("/api/admin/tickets", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ ticketId: id, status: "closed" }),
    });
    toast("success", "Ticket closed");
    load();
  };

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Support Tickets</h1>

      <div className="grid lg:grid-cols-3 gap-6">
        <div className="lg:col-span-1 space-y-2 max-h-[70vh] overflow-y-auto">
          {tickets.length === 0 ? (
            <div className="glass rounded-2xl p-8 text-center text-muted text-sm">No tickets</div>
          ) : tickets.map((t) => (
            <button
              key={t.id}
              onClick={() => setSelected(t)}
              className={`w-full text-left p-4 rounded-xl transition-all ${
                selected?.id === t.id ? "glass-strong border border-primary/50" : "glass hover:bg-surface-hover"
              }`}
            >
              <div className="flex items-center justify-between mb-1">
                <span className="text-xs font-mono text-muted">{t.ticketId}</span>
                <span className={`px-2 py-0.5 rounded-lg text-xs font-medium border ${getStatusColor(t.status)}`}>{t.status}</span>
              </div>
              <p className="text-sm font-medium truncate">{t.subject}</p>
              <p className="text-xs text-muted mt-1">{t.user.username} · {formatDate(t.createdAt)}</p>
            </button>
          ))}
        </div>

        <div className="lg:col-span-2">
          {selected ? (
            <div className="glass rounded-2xl p-6 space-y-4">
              <div className="flex items-center justify-between">
                <div>
                  <h3 className="font-semibold">{selected.subject}</h3>
                  <p className="text-xs text-muted">{selected.user.username} ({selected.user.email}) · {selected.priority}</p>
                </div>
                <button
                  onClick={() => closeTicket(selected.id)}
                  className="px-3 py-1.5 rounded-lg border border-border text-xs hover:bg-surface-hover"
                >
                  Close Ticket
                </button>
              </div>

              <div className="space-y-3 max-h-96 overflow-y-auto">
                {selected.messages.map((m) => (
                  <div key={m.id} className={`p-4 rounded-xl ${m.isAdmin ? "bg-indigo-500/10 border border-indigo-500/20 ml-8" : "bg-white/5 border border-border mr-8"}`}>
                    <div className="flex items-center justify-between mb-2">
                      <span className={`text-xs font-medium ${m.isAdmin ? "text-indigo-400" : "text-muted"}`}>
                        {m.isAdmin ? "Admin" : selected.user.username}
                      </span>
                      <span className="text-xs text-muted">{formatDate(m.createdAt)}</span>
                    </div>
                    <p className="text-sm">{m.message}</p>
                  </div>
                ))}
              </div>

              {selected.status !== "closed" && (
                <form onSubmit={handleReply} className="flex gap-3">
                  <input
                    value={reply}
                    onChange={(e) => setReply(e.target.value)}
                    className="flex-1 px-4 py-2.5 rounded-xl bg-white/5 border border-border text-sm focus:outline-none focus:border-primary"
                    placeholder="Type admin reply..."
                    required
                  />
                  <button type="submit" disabled={loading} className="px-4 py-2.5 rounded-xl gradient-primary text-white disabled:opacity-50">
                    {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
                  </button>
                </form>
              )}
            </div>
          ) : (
            <div className="glass rounded-2xl p-12 text-center text-muted">
              <MessageSquare className="w-12 h-12 mx-auto mb-3 opacity-50" />
              <p className="text-sm">Select a ticket</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
