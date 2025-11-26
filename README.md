# 🤖 AI-Enabled Support Chatbot Portal — SaaS Platform

AI-powered helpdesk platform where businesses can sign up, upload their documents/FAQs, and deploy a smart chatbot that answers customer queries using their knowledge base.

Think of it as **Zendesk + Intercom + ChatGPT** — for automated customer support.

<p align="center">
  <img src="docs/screenshot-dashboard.png" width="800" alt="Dashboard Screenshot">
</p>

---

## 🚀 Features

| Category | Highlights |
|---------|------------|
| 🧠 AI Chatbot | Conversational support bot trained using company knowledge |
| 📄 Knowledge Base | Upload PDFs, docs, FAQs, and website content |
| 👥 Multi-tenant SaaS | Companies create accounts, manage teams and customers |
| 💬 Live Chat Widget | Embeddable JavaScript widget for any website |
| 💰 Billing | Tiered pricing plans & usage-based billing |
| 🔐 Authentication | Email login, OAuth, 2FA, roles & permissions |
| 📊 Analytics | Conversation logs, feedback, and chatbot accuracy tracking |
| 🧾 REST API / Webhooks | Integrate workflow automations |

---

## 🏗️ Architecture Overview

```mermaid
graph TD
UI[Web + JS Widget] --> API
API --> Auth[Authentication Service]
API --> KB[Knowledge Base Service]
KB --> Storage[(Vector DB)]
API --> AI[LLM + RAG Engine]
AI --> Storage
