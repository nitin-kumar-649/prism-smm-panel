"use client";

import { useState, useEffect } from "react";
import { formatDate, getStatusColor } from "@/lib/utils";
import { Plus, Send, Loader2, MessageSquare } from "lucide-react";
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
  messages: Message[];
}

export default function TicketsPage() {
  const [tickets, setTickets] = useState<Ticket[]>([]);
  const [selectedTicket, setSelectedTicket] = useState<Ticket | null>(null);
  const [showNew, setShowNew] = useState(false);
  const [subject, setSubject] = useState("");
  const [message, setMessage] = useState("");
  const [priority, setPriority] = useState("medium");
  const [reply, setReply] = useState("");
  const [loading, setLoading] = useState(false);

  const loadTickets = () => {
    fetch("/api/tickets").then((r) => r.json()).then(setTickets);
  };

  useEffect(() => { loadTickets(); }, []);

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await fetch("/api/tickets", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ subject, message, priority }),
      });
      if (!res.ok) { const d = await res.json(); return toast("error", d.error); }
      toast("success", "Ticket created");
      setShowNew(false);
      setSubject("");
      setMessage("");
      loadTickets();
    } catch {
      toast("error", "Failed to create ticket");
    } finally {
      setLoading(false);
    }
  };

  const handleReply = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedTicket) return;
    setLoading(true);
    try {
      const res = await fetch("/api/tickets", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ticketId: selectedTicket.id, message: reply }),
      });
      if (!res.ok) { const d = await res.json(); return toast("error", d.error); }
      toast("success", "Reply sent");
      setReply("");
      loadTickets();
    } catch {
      toast("error", "Failed to send reply");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Support Tickets</h1>
          <p className="text-muted">Get help from our support team</p>
        </div>
        <button
          onClick={() => { setShowNew(true); setSelectedTicket(null); }}
          className="inline-flex items-center gap-2 px-4 py-2 rounded-xl gradient-primary text-white text-sm font-medium hover:opacity-90 shadow-lg shadow-indigo-500/25"
        >
          <Plus className="w-4 h-4" /> New Ticket
        </button>
      </div>

      {showNew && (
        <form onSubmit={handleCreate} className="glass rounded-2xl p-6 space-y-4">
          <h3 className="font-semibold">Create New Ticket</h3>
          <div className="grid sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm text-muted mb-1">Subject</label>
              <input value={subject} onChange={(e) => setSubject(e.target.value)} className="w-full px-4 py-3 rounded-xl bg-white/5 border border-border text-foreground focus:outline-none focus:border-primary" required />
            </div>
            <div>
              <label className="block text-sm text-muted mb-1">Priority</label>
              <select value={priority} onChange={(e) => setPriority(e.target.value)} className="w-full px-4 py-3 rounded-xl bg-white/5 border border-border text-foreground focus:outline-none focus:border-primary">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>
          <div>
            <label className="block text-sm text-muted mb-1">Message</label>
            <textarea value={message} onChange={(e) => setMessage(e.target.value)} rows={4} className="w-full px-4 py-3 rounded-xl bg-white/5 border border-border text-foreground focus:outline-none focus:border-primary resize-none" required />
          </div>
          <div className="flex gap-3">
            <button type="submit" disabled={loading} className="px-6 py-2.5 rounded-xl gradient-primary text-white text-sm font-medium disabled:opacity-50 flex items-center gap-2">
              {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : "Submit"}
            </button>
            <button type="button" onClick={() => setShowNew(false)} className="px-6 py-2.5 rounded-xl border border-border text-sm hover:bg-surface-hover">Cancel</button>
          </div>
        </form>
      )}

      <div className="grid lg:grid-cols-3 gap-6">
        <div className="lg:col-span-1 space-y-2">
          {tickets.length === 0 ? (
            <div className="glass rounded-2xl p-8 text-center text-muted text-sm">No tickets yet</div>
          ) : tickets.map((t) => (
            <button
              key={t.id}
              onClick={() => { setSelectedTicket(t); setShowNew(false); }}
              className={`w-full text-left p-4 rounded-xl transition-all ${
                selectedTicket?.id === t.id ? "glass-strong border border-primary/50" : "glass hover:bg-surface-hover"
              }`}
            >
              <div className="flex items-center justify-between mb-1">
                <span className="text-xs font-mono text-muted">{t.ticketId}</span>
                <span className={`px-2 py-0.5 rounded-lg text-xs font-medium border ${getStatusColor(t.status)}`}>{t.status}</span>
              </div>
              <p className="text-sm font-medium truncate">{t.subject}</p>
              <p className="text-xs text-muted mt-1">{formatDate(t.createdAt)}</p>
            </button>
          ))}
        </div>

        <div className="lg:col-span-2">
          {selectedTicket ? (
            <div className="glass rounded-2xl p-6 space-y-4">
              <div className="flex items-center justify-between">
                <div>
                  <h3 className="font-semibold">{selectedTicket.subject}</h3>
                  <p className="text-xs text-muted">{selectedTicket.ticketId} · {selectedTicket.priority} priority</p>
                </div>
                <span className={`px-2.5 py-1 rounded-lg text-xs font-medium border ${getStatusColor(selectedTicket.status)}`}>{selectedTicket.status}</span>
              </div>

              <div className="space-y-3 max-h-96 overflow-y-auto">
                {selectedTicket.messages.map((m) => (
                  <div key={m.id} className={`p-4 rounded-xl ${m.isAdmin ? "bg-indigo-500/10 border border-indigo-500/20 ml-8" : "bg-white/5 border border-border mr-8"}`}>
                    <div className="flex items-center justify-between mb-2">
                      <span className={`text-xs font-medium ${m.isAdmin ? "text-indigo-400" : "text-muted"}`}>
                        {m.isAdmin ? "Support Team" : "You"}
                      </span>
                      <span className="text-xs text-muted">{formatDate(m.createdAt)}</span>
                    </div>
                    <p className="text-sm">{m.message}</p>
                  </div>
                ))}
              </div>

              {selectedTicket.status !== "closed" && (
                <form onSubmit={handleReply} className="flex gap-3">
                  <input
                    value={reply}
                    onChange={(e) => setReply(e.target.value)}
                    className="flex-1 px-4 py-2.5 rounded-xl bg-white/5 border border-border text-foreground text-sm focus:outline-none focus:border-primary"
                    placeholder="Type your reply..."
                    required
                  />
                  <button type="submit" disabled={loading} className="px-4 py-2.5 rounded-xl gradient-primary text-white disabled:opacity-50">
                    <Send className="w-4 h-4" />
                  </button>
                </form>
              )}
            </div>
          ) : (
            <div className="glass rounded-2xl p-12 text-center text-muted">
              <MessageSquare className="w-12 h-12 mx-auto mb-3 opacity-50" />
              <p className="text-sm">Select a ticket to view conversation</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
