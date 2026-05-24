"use client";

import { useState, useEffect } from "react";
import { formatCurrency, formatDate } from "@/lib/utils";
import { Wallet, Plus, Loader2, ArrowUpRight, ArrowDownRight } from "lucide-react";
import { toast } from "@/components/Toast";

interface Transaction {
  id: number;
  transactionId: string;
  type: string;
  amount: number;
  balanceAfter: number;
  description: string | null;
  createdAt: string;
}

const gateways = [
  { id: "paypal", name: "PayPal", color: "from-blue-500 to-blue-600" },
  { id: "stripe", name: "Stripe", color: "from-purple-500 to-purple-600" },
  { id: "razorpay", name: "Razorpay", color: "from-cyan-500 to-cyan-600" },
  { id: "crypto", name: "Crypto", color: "from-amber-500 to-amber-600" },
];

export default function WalletPage() {
  const [balance, setBalance] = useState(0);
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [amount, setAmount] = useState("");
  const [gateway, setGateway] = useState("paypal");
  const [loading, setLoading] = useState(false);
  const [showDeposit, setShowDeposit] = useState(false);

  useEffect(() => {
    fetch("/api/wallet")
      .then((r) => r.json())
      .then((data) => {
        setBalance(data.balance);
        setTransactions(data.transactions);
      });
  }, []);

  const handleDeposit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await fetch("/api/wallet", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ amount: Number(amount), gateway }),
      });
      const data = await res.json();
      if (!res.ok) return toast("error", data.error);
      toast("success", `Deposited ${formatCurrency(Number(amount))}`);
      setBalance(data.balance);
      setAmount("");
      setShowDeposit(false);
      fetch("/api/wallet").then((r) => r.json()).then((d) => setTransactions(d.transactions));
    } catch {
      toast("error", "Deposit failed");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Wallet</h1>
          <p className="text-muted">Manage your balance and transactions</p>
        </div>
        <button
          onClick={() => setShowDeposit(!showDeposit)}
          className="inline-flex items-center gap-2 px-4 py-2 rounded-xl gradient-primary text-white text-sm font-medium hover:opacity-90 transition-opacity shadow-lg shadow-indigo-500/25"
        >
          <Plus className="w-4 h-4" />
          Add Funds
        </button>
      </div>

      <div className="glass rounded-2xl p-6">
        <div className="flex items-center gap-4">
          <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg">
            <Wallet className="w-7 h-7 text-white" />
          </div>
          <div>
            <p className="text-sm text-muted">Available Balance</p>
            <p className="text-3xl font-bold text-emerald-400">{formatCurrency(balance)}</p>
          </div>
        </div>
      </div>

      {showDeposit && (
        <form onSubmit={handleDeposit} className="glass rounded-2xl p-6 space-y-4">
          <h3 className="font-semibold">Add Funds</h3>
          <div>
            <label className="block text-sm text-muted mb-2">Amount (USD)</label>
            <input
              type="number"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              className="w-full px-4 py-3 rounded-xl bg-white/5 border border-border text-foreground focus:outline-none focus:border-primary"
              placeholder="Enter amount"
              min={5}
              step="0.01"
              required
            />
          </div>
          <div>
            <label className="block text-sm text-muted mb-2">Payment Gateway</label>
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
              {gateways.map((g) => (
                <button
                  key={g.id}
                  type="button"
                  onClick={() => setGateway(g.id)}
                  className={`p-3 rounded-xl border text-sm font-medium transition-all ${
                    gateway === g.id
                      ? `bg-gradient-to-r ${g.color} text-white border-transparent shadow-lg`
                      : "border-border text-muted hover:text-foreground hover:bg-surface-hover"
                  }`}
                >
                  {g.name}
                </button>
              ))}
            </div>
          </div>
          <button
            type="submit"
            disabled={loading}
            className="w-full py-3 rounded-xl gradient-primary text-white font-semibold hover:opacity-90 disabled:opacity-50 flex items-center justify-center gap-2"
          >
            {loading ? <Loader2 className="w-5 h-5 animate-spin" /> : `Deposit ${amount ? formatCurrency(Number(amount)) : ""}`}
          </button>
        </form>
      )}

      <div className="glass rounded-2xl p-6">
        <h3 className="font-semibold mb-4">Transaction History</h3>
        <div className="space-y-3">
          {transactions.length === 0 ? (
            <p className="text-muted text-sm py-8 text-center">No transactions yet</p>
          ) : transactions.map((t) => (
            <div key={t.id} className="flex items-center justify-between p-3 rounded-xl hover:bg-surface-hover transition-colors">
              <div className="flex items-center gap-3">
                <div className={`w-10 h-10 rounded-xl flex items-center justify-center ${
                  t.amount > 0
                    ? "bg-emerald-500/15 text-emerald-400"
                    : "bg-red-500/15 text-red-400"
                }`}>
                  {t.amount > 0 ? <ArrowDownRight className="w-5 h-5" /> : <ArrowUpRight className="w-5 h-5" />}
                </div>
                <div>
                  <p className="text-sm font-medium">{t.description || t.type}</p>
                  <p className="text-xs text-muted">{formatDate(t.createdAt)}</p>
                </div>
              </div>
              <div className="text-right">
                <p className={`text-sm font-semibold ${t.amount > 0 ? "text-emerald-400" : "text-red-400"}`}>
                  {t.amount > 0 ? "+" : ""}{formatCurrency(t.amount)}
                </p>
                <p className="text-xs text-muted">Bal: {formatCurrency(t.balanceAfter)}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
