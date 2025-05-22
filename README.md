# GABAY - Capitol Navigation System

GABAY is a comprehensive office navigation system designed for the Provincial Capitol. It helps visitors locate and navigate to different offices within the Capitol building, view office details, and receive directions through an interactive floor plan.

## Features

- Interactive floor plan visualization with clickable office spaces
- QR code scanning for easy navigation
- Office management system for administrators
- Visitor feedback collection
- Mobile-friendly interface
- Office status tracking (active/inactive)

## Technologies Used

- PHP
- JavaScript
- HTML/CSS
- Tailwind CSS
- SVG for interactive floor plans

## Installation

1. Clone this repository to your web server directory:
   ```
   git clone https://github.com/Excellsus/GABAY-3.git
   ```

2. Ensure you have a web server with PHP support (e.g., XAMPP, WAMP)

3. Create a MySQL database and import the database schema (SQL file to be provided)

4. Configure the database connection in `connect_db.php`

5. Access the application through your web browser

## Project Structure

- `/api` - API endpoints for data operations
- `/floorjs` - JavaScript files for floor plan functionality
- `/mobileScreen` - Mobile-specific views
- `/phpqrcode` - QR code generation library
- `/qrcodes` - Generated QR codes for offices
- `/srcImage` - Image assets

## Usage

### Admin Portal
- Manage offices (add, edit, delete)
- Update office statuses
- View and manage floor plans
- Access visitor feedback

### Visitor Interface
- Scan QR codes to navigate to specific offices
- Explore the interactive floor plan
- Submit feedback about their experience

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributors

- [Excellsus](https://github.com/Excellsus) 