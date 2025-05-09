# Event Booking API

This is a RESTful API built with **Symfony 7** and **API Platform** to manage events, attendees, and bookings.

---

## 🚀 Features

* Create, update, delete, and list **events**
* Register and manage **attendees**
* **Book events** with attendee registration
* Prevent **duplicate** and **overbooked** bookings
* Designed with **clean architecture**, **validation**, and **error handling**
* Ready for future **authentication/authorization** integration

---

## 🗃️ Database Schema

* **Event**

  * `id`, `name`, `capacity`, `date`, `country`

* **Attendee**

  * `id`, `name`, `email`

* **Booking**

  * `id`, `bookedAt`, `event` (relation), `attendee` (relation)

---

## 🔐 Authentication & Authorization Design

> Authentication is not implemented but designed for future integration.

### Authentication (Planned)

* Use **JWT (JSON Web Tokens)**
* Secure event/attendee management endpoints
* Allow public access to booking endpoints

### Authorization Rules

| Role         | Permissions                                                                     |
| ------------ | ------------------------------------------------------------------------------- |
| `Admin/User` | Create, update, delete, and list events and attendees (requires authentication) |
| `Attendee`   | Can book events anonymously (no authentication required)                        |

### Example Security Configuration (Planned)

```yaml
access_control:
    - { path: ^/api/bookings, roles: IS_AUTHENTICATED_ANONYMOUSLY }
    - { path: ^/api, roles: ROLE_USER }
```

---

## 🐳 Docker Setup

This application supports Docker for easy setup and deployment. Follow the steps below to get started:

1. Clone the repository:

```bash
git clone <your-repo-url>
cd event-booking-api
```

2. Build the Docker containers:

```bash
docker compose build --no-cache
```

3. Start the containers:

```bash
docker compose up --wait
```

4. Run the database migrations:

```bash
docker compose exec php bin/console doctrine:migrations:migrate
```

---

## 📄 API Documentation

Auto-generated and available at:

```
/docs
```

Includes OpenAPI support with custom request examples.

---

### Unit Test Cases

To run all the unit tests together, use the following command:

```bash
docker compose exec php bin/phpunit
```

This will execute all the test cases, including unit and integration tests, for the application.

---

## 🛠️ Tech Stack

* Symfony 7
* API Platform
* Doctrine ORM
* PHP 8.2+
* PHPUnit
* Docker

---

## ✨ Bonus Features

* Custom OpenAPI examples for better developer experience
* Booking validator to enforce event capacity and prevent duplicate bookings

---

## 📫 Contact

For any questions or suggestions, feel free to open an issue or contact the maintainer.
