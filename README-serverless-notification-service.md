# Serverless Notification Service Architecture

This document describes a proposed architecture for a **Serverless Event-Driven Notification Service** using AWS components. The goal is to deliver notifications through multiple channels (WebSocket, mobile push, and email) while supporting real-time processing and event enrichment.

---

## 🎯 Objective

Design a serverless notification system that:

* Is event-driven and scalable
* Supports **real-time delivery** to web and mobile users
* Can send **localized emails** using templates
* Includes **event enrichment** before routing
* Uses **AWS native serverless services**

---

## 🧱 Architecture Components

### 🔹 Event Sources

* **Custom App**
* **API Gateway**

### 🔹 Event Ingestion & Routing

* **Amazon EventBridge**: Central event bus

  * Routes events based on type (e.g., `user.signup`, `booking.created`)

### 🔹 Event Enrichment (If Required)

* **Enrichment Lambda**:

  * Checks if additional data is needed
  * Fetches data from external APIs
  * Stores enriched data in **DynamoDB** if applicable

### 🔹 Notification Processor

* **Processor Lambda**:

  * Determines which notification channels to trigger based on the event

### 🔹 Notification Channels

#### 1. **WebSocket Notifications**

* **WebSocket Lambda** sends real-time messages
* **API Gateway (WebSocket)** handles the connection
* **DynamoDB** tracks active WebSocket connections

#### 2. **Mobile Push Notifications**

* **Push Lambda** publishes to **SNS**
* SNS delivers to mobile endpoints

#### 3. **Email Notifications**

* **Email Lambda** prepares messages
* **Amazon SES** sends localized, templated emails

### 🔹 Monitoring & Logging

* **Amazon CloudWatch** collects logs and metrics from all services

---

## 🔄 Event Flow Summary

1. Events are triggered from a Custom App via **API Gateway**
2. Events are sent to **EventBridge**
3. EventBridge routes events:

   * If enrichment is needed → **Enrichment Lambda** → external API → **DynamoDB**
4. Event is passed to **Processor Lambda**
5. Processor triggers:

   * **WebSocket Lambda** → API Gateway WebSocket → Web App
   * **Push Lambda** → SNS → Mobile Devices
   * **Email Lambda** → SES → User Email
6. All services send logs/metrics to **CloudWatch**

---

## ✅ Key Highlights

* Real-time, multi-channel notification delivery
* Event enrichment support
* Fully serverless and scalable
* Clean separation of responsibilities

---

## 🖼️ Diagram Reference

Refer to `Serverless_Notification_Service` diagram below for the visual layout of the system.

![Serverless Notification Service Architecture](./images/Serverless_Notification_Service.drawio.svg)

---

## 📫 Contact

For any questions, please reach out via issue or contact the maintainer.
