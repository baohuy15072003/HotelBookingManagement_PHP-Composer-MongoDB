# Hotel Booking Management System

A web-based hotel booking management application developed using **Plain PHP and MongoDB** for a NoSQL database course.

## Technologies

- PHP, MongoDB, Composer
- Bootstrap 5, HTML, CSS
- PHP dotenv
- MVC Architecture
- Apache HTTP Server

## Features

- **Database Design:** Four collections — `users`, `rooms`, `categories`, and `bookings`.
- **NoSQL Operations:** Embedded/Reference data modeling, indexes, and aggregation pipelines.
- **CRUD:** Create, read, update, and delete records.
- **Search & Filtering:** Filter records by multiple criteria.
- **Statistics:** Revenue by month/year and Top 5 room categories and customers by booking count.
- **PDF Reports:** Generate and export statistical reports as PDF files.
- **Web Interface:** Bootstrap 5 interface organized using the MVC pattern.

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/baohuy15072003/HotelBookingManagement_PHP-Composer-MongoDB.git
   cd HotelBookingManagement_PHP-Composer-MongoDB
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Configure MongoDB connection settings in `.env` using `.env.example` as a reference.

4. Start MongoDB Server and initialize the database using the provided MongoDB script.

5. Configure Apache to serve the `public/` directory and open the application in your browser.

**Prerequisites:** PHP with the MongoDB extension, Composer, MongoDB Server, and Apache HTTP Server.

## Author

**Nguyen Bao Huy**

- GitHub: [GitHub Profile](https://github.com/baohuy15072003/)
