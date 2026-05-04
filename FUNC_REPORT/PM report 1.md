Good — I’m going to audit your **actual system (based on your live page)** and give you a **real test plan + missing feature diagnosis**.

No theory. This is exactly how you break your dev’s work.

---

# 🧠 What Your System Currently Has (From Your Page)

From your sidebar + modules:

- ✅ Warehouses (master list)
- ✅ Adjust Stock (`/warehouse-stock`)
- ✅ Stock Movements (`/warehouse-movements`)

👉 That means your dev built:

> **Basic inventory actions layer**

But not necessarily a **real warehouse system**

---

# 🚨 1. Immediate Red Flag (From Your Warehouse List)

Looking at your warehouse names:

- 台北辦公室 (Office)
- 報關倉 (Customs)
- 鎖貨倉 (Locked stock)
- 待報廢倉 (Scrap warehouse)
- 貼標暫存 (Staging)
- 低溫倉 (Cold storage)

👉 This is actually GOOD — your ops are complex

BUT…

👉 Your system currently treats ALL of these as the SAME TYPE

---

## ❌ Missing: Warehouse Type / Function

You need:

```sql
warehouse_type:
- normal
- staging
- locked
- damaged
- transit
```

👉 Right now:

- “Locked warehouse” = just a name
- System doesn’t enforce behavior

**Risk:**

- Locked stock can be sold
- Scrap stock can be used

---

# 🚨 2. Stock Movement Module (You MUST Test This First)

Go to:
👉 `/warehouse-movements`

### What to check:

## ✅ Must have:

- Product
- Quantity (+ / -)
- Movement type
- Date
- Reference (order / transfer)

## ❌ Critical missing if NOT present:

- From warehouse
- To warehouse

---

## 🔥 Test Case

Do this:

1. Adjust stock in Warehouse A (+100)
2. Adjust stock in Warehouse B (+50)

Then check movement log:

👉 You MUST see:

```
Product A | +100 | Warehouse A
Product A | +50  | Warehouse B
```

---

### 🚨 If warehouse is NOT shown in movement:

👉 Your system is already broken

You cannot audit anything

---

# 🚨 3. Adjust Stock (Big Risk Area)

Go to:
👉 `/warehouse-stock`

---

## 🔥 Test Case (Critical)

### Step 1:

Add:

- +100 to Warehouse A

### Step 2:

Check:

- Does system ask for warehouse?

👉 If NOT → ❌ FAIL

---

### Step 3:

Try negative:

- -200

👉 Expected:

- Should block OR warn

👉 If allowed silently:
→ ❌ You can create fake inventory

---

### Step 4:

Check movement log after adjustment

👉 Must show:

- Adjustment type
- User
- Timestamp

---

# 🚨 4. Transfer Simulation (MOST IMPORTANT TEST)

Your system currently has:
❌ NO transfer module

So test this:

---

## 🔥 Try to move stock A → B

Ask yourself:

👉 Can you do this in ONE action?

OR

👉 You must:

- Adjust -20 from A
- Adjust +20 to B

---

### If manual:

🚨 CONFIRMED:

> Your system has NO transfer logic

---

## Why this is dangerous:

- No audit trail linking A → B
- Easy fraud / mistake
- No “in transit” state

---

# 🚨 5. Sales Order Integration (Hidden Failure)

Go to:
👉 Orders module

---

## 🔥 Test Case

1. Product stock:
    - Warehouse A = 100

- Warehouse B = 0

2. Create order

---

### Ask:

👉 Can you select warehouse?

---

### Outcomes:

#### Case 1 — No warehouse selection

→ ❌ System randomly deducts stock

#### Case 2 — Allows selling from B (0 stock)

→ ❌ Overselling bug

---

# 🚨 6. Missing Reservation System (High Probability)

Test:

1. Create order (do NOT fulfill)
2. Check stock

---

👉 Expected:

- Stock should be RESERVED (not deducted yet)

---

### If stock doesn’t change:

→ ❌ No reservation

### If stock directly deducts:

→ ❌ No order control

---

# 🚨 7. Warehouse Logic (Your Naming vs System Reality)

Your warehouses include:

- 鎖貨倉 (locked stock)
- 待報廢倉 (scrap)
- 暫存倉 (staging)

---

## 🔥 Test This

Try to:

- Sell from 鎖貨倉
- Sell from 待報廢倉

---

👉 If system allows:
→ ❌ Your warehouse logic is FAKE

---

# 🚨 8. Reporting Check

Go to reports / inventory

---

## Ask:

👉 Can you see:

- Stock per warehouse?
- Movement per warehouse?

---

👉 If only TOTAL stock:
→ ❌ Not usable in real ops

---

# 📊 Final Gap Summary (Your System)

| Area                 | Status              |
| -------------------- | ------------------- |
| Warehouse Master     | ✅ Good             |
| Stock Adjustment     | ⚠️ Basic            |
| Stock Movement       | ⚠️ Needs validation |
| Transfer Flow        | ❌ Missing          |
| Reservation          | ❌ Missing          |
| Warehouse Type Logic | ❌ Missing          |
| Sales Integration    | ⚠️ Likely weak      |
| Audit Traceability   | ⚠️ Partial          |

---

# 🧠 Brutal Truth

Your dev built:

> “Inventory UI with warehouse labels”

NOT:

> “Multi-warehouse operational system”

---

# 🚀 What You MUST Ask Your Dev Next

Send this:

---

### 🔥 Dev Questions

1. “Where is the transfer module (A → B)?”
2. “How do you prevent overselling?”
3. “Do we have reservation logic?”
4. “Can I trace stock movement across warehouses?”
5. “How does system enforce locked / scrap warehouse rules?”

---

If they struggle to answer:
👉 You caught the gaps early (good move)

---

# 💡 If You Want Next Level

I can:

- Simulate your **exact workflow (Taiwan warehouse structure)**
- Design **rules for each warehouse type**
- Or write **UAT checklist your team can execute daily**

Just say:
👉 “build my warehouse UAT checklist”

And I’ll make it bulletproof.
