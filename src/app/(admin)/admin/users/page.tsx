"use client";

import { useState, useEffect } from "react";
import { formatCurrency, formatDate, getStatusColor } from "@/lib/utils";
import { DollarSign, Ban, CheckCircle, Loader2 } from "lucide-react";
import { toast } from "@/components/Toast";

interface User {
  id: number;
  username: string;
  email: string;
  role: string;
  status: string;
  balance: number;
  totalSpent: number;
  createdAt: string;
  _count: { orders: number };
}

export default function AdminUsersPage() {
  const [users, setUsers] = useState<User[]>([]);
  const [addFundsUser, setAddFundsUser] = useState<number | null>(null);
  const [amount, setAmount] = useState("");
  const [loading, setLoading] = useState(false);

  const loadUsers = () => {
    fetch("/api/admin/users").then((r) => r.json()).then(setUsers);
  };

  useEffect(() => { loadUsers(); }, []);

  const handleAction = async (userId: number, action: string, amt?: number) => {
    setLoading(true);
    try {
      const res = await fetch("/api/admin/users", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ userId, action, amount: amt }),
      });
      if (res.ok) {
        toast("success", `User ${action === "addFunds" ? "funds added" : action + "d"}`);
        setAddFundsUser(null);
        setAmount("");
        loadUsers();
      }
    } catch {
      toast("error", "Action failed");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Users</h1>

      <div className="glass rounded-2xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="text-left text-muted border-b border-border">
                <th className="px-4 py-3 font-medium">ID</th>
                <th className="px-4 py-3 font-medium">User</th>
                <th className="px-4 py-3 font-medium">Email</th>
                <th className="px-4 py-3 font-medium">Role</th>
                <th className="px-4 py-3 font-medium">Balance</th>
                <th className="px-4 py-3 font-medium">Spent</th>
                <th className="px-4 py-3 font-medium">Orders</th>
                <th className="px-4 py-3 font-medium">Status</th>
                <th className="px-4 py-3 font-medium">Joined</th>
                <th className="px-4 py-3 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {users.map((u) => (
                <tr key={u.id} className="hover:bg-surface-hover transition-colors">
                  <td className="px-4 py-3">{u.id}</td>
                  <td className="px-4 py-3 font-medium">{u.username}</td>
                  <td className="px-4 py-3 text-xs text-muted">{u.email}</td>
                  <td className="px-4 py-3">
                    <span className={`px-2 py-0.5 rounded-lg text-xs font-medium ${u.role === "admin" ? "bg-purple-500/20 text-purple-400" : "bg-blue-500/20 text-blue-400"}`}>
                      {u.role}
                    </span>
                  </td>
                  <td className="px-4 py-3">{formatCurrency(u.balance)}</td>
                  <td className="px-4 py-3">{formatCurrency(u.totalSpent)}</td>
                  <td className="px-4 py-3">{u._count.orders}</td>
                  <td className="px-4 py-3">
                    <span className={`px-2 py-0.5 rounded-lg text-xs font-medium border ${getStatusColor(u.status)}`}>{u.status}</span>
                  </td>
                  <td className="px-4 py-3 text-xs text-muted whitespace-nowrap">{formatDate(u.createdAt)}</td>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-1">
                      <button onClick={() => setAddFundsUser(addFundsUser === u.id ? null : u.id)} className="p-1.5 rounded-lg hover:bg-emerald-500/10 text-muted hover:text-emerald-400" title="Add funds">
                        <DollarSign className="w-3.5 h-3.5" />
                      </button>
                      {u.status === "active" ? (
                        <button onClick={() => handleAction(u.id, "suspend")} className="p-1.5 rounded-lg hover:bg-red-500/10 text-muted hover:text-red-400" title="Suspend">
                          <Ban className="w-3.5 h-3.5" />
                        </button>
                      ) : (
                        <button onClick={() => handleAction(u.id, "activate")} className="p-1.5 rounded-lg hover:bg-emerald-500/10 text-muted hover:text-emerald-400" title="Activate">
                          <CheckCircle className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>
                    {addFundsUser === u.id && (
                      <div className="mt-2 flex gap-2">
                        <input
                          type="number"
                          value={amount}
                          onChange={(e) => setAmount(e.target.value)}
                          className="w-24 px-2 py-1 rounded-lg bg-white/5 border border-border text-xs focus:outline-none"
                          placeholder="Amount"
                          min={1}
                        />
                        <button
                          onClick={() => handleAction(u.id, "addFunds", Number(amount))}
                          disabled={loading || !amount}
                          className="px-3 py-1 rounded-lg gradient-primary text-white text-xs disabled:opacity-50"
                        >
                          {loading ? <Loader2 className="w-3 h-3 animate-spin" /> : "Add"}
                        </button>
                      </div>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
