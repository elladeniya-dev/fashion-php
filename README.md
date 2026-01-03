# 🧵 Supplier–Customer Coordination Portal

A full-stack PHP & MySQL web application designed for small fashion retail and wholesale operations, enabling inventory-aware ordering, admin-controlled approval workflows, and audit-logged operations—without the complexity of a full ERP system.


🚀 What This System Does
👑 Admin Capabilities

            📊 “Today View” dashboard – low-stock alerts, pending supplier products, today’s orders, and weekly order counts

            ✅ Approve / reject supplier products before they become visible to customers

            📦 Order control – approve, cancel, or auto-expire stale orders (SLA-based)

            🚨 Inventory monitoring with low-stock warnings

            🧾 Audit logging – all critical actions (approvals, cancellations, auto-expiry) are recorded

🧵 Supplier Capabilities

            ➕ Submit products for admin approval

            📦 Manage inventory (stock levels & pricing)

            ⚠️ Low-stock alerts for timely restocking

            📈 Supplier insights – top-selling items, pending approvals, low-stock count

👤 Customer Capabilities

            🛍️ Browse only approved & in-stock products

            🛒 Place stock-validated orders (prevents over-ordering or out-of-stock requests)

            📜 Order history & tracking with clear, human-readable status messages

            🧑‍💼 Profile management

🔐 Business Rules & Governance

            🔒 Products require admin approval before customer visibility

            ❌ Customers cannot order out-of-stock or excess quantities

            ⏳ Orders auto-cancel after SLA if not processed

            🧹 Product deletion respects pending order constraints

            🧾 All critical state changes are audit-logged for traceability

🛠️ Tech Stack

            🐘 Backend: PHP 8

            🗄️ Database: MySQL (InnoDB)

            🔐 Auth: Session-based authentication with password hashing

            🐳 Deployment: Docker & docker-compose (App + DB)

            🎨 Frontend: HTML & CSS (responsive tables and cards)

            🧩 Architecture: Modular PHP pages with centralized configuration

🎯 Real-World Use Cases

            👗 Small fashion boutiques

            🧵 Tailoring shops

            🧶 Fabric wholesalers

            🏪 Supplier-driven retail stores

            🧑‍🤝‍🧑 Small teams needing lightweight workflow control without heavy ERP systems

💡 Why This Project

            🧠 Focuses on real business workflows, not just CRUD

            ⚖️ Enforces inventory integrity and approval gates

            🔍 Emphasizes auditability and accountability

            🚀 Designed to be lightweight, maintainable, and extensible
