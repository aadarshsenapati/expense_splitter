# Expense Splitter  View[https://expense.aadarshsenapati.in/]

Expense Splitter is a web-based application that enables users to manage shared expenses efficiently. It allows users to split bills with friends and groups, track payments, manage transactions, and monitor balances.  

---

## Features  

- **User Authentication** – Secure user login and registration with password hashing.  
- **Friend Management** – Users can add friends via Merchant ID or username, send requests, and accept/reject them.  
- **Expense Splitting** – Users can split expenses with friends or within groups.  
- **Group Management** – Users can create groups, add members, and manage pending invitations.  
- **Transaction Tracking** – Users can view balances, pending settlements, and payment history.  
- **Notifications** – The system sends notifications for pending requests and payments.  
- **Payment Integration** – Supports UPI, credit/debit card, and net banking (using a dummy gateway for testing).  

---

## Installation  

### **1. Clone the Repository**  
```sh
git clone https://github.com/yourusername/expense-splitter.git  
cd expense-splitter
```

## Set Up the Database
- Create a MySQL database called expense_splitter.
- Import database.sql using phpMyAdmin or the MySQL CLI:
  ```sh
  git clone https://github.com/yourusername/expense-splitter.git  
  cd expense-splitter
  ```
- Update config.php with your database credentials.

## Install Dependencies
Ensure you have XAMPP, LAMP, or WAMP installed with PHP 8+ and MySQL.

## Start the Server
If using XAMPP, start Apache and MySQL, then access the project at:
  ```sh
    http://localhost/expense_split/
  ```

## Usage

### User Registration and Login
- New users must register with their name, email, password and UPI id.
- Users log in to manage expenses, transactions, and groups.

### Adding Friends
- Users can search for friends using either Merchant ID or Username.
- Friend requests must be accepted before splitting expenses.
- After three rejections, users cannot send additional requests.

### Splitting Expenses
- Select friends or a group to split with.
- Enter the expense title, amount, and optional notes.
- The system automatically divides the bill among participants.
- Pending payments appear in the "Settle Up" section.

### Group Management
- Users can create groups and invite friends.
- Group requests must be accepted before a user can participate.
- Groups remain hidden until the invitation is accepted.

### Settling Payments
- Pending payments are displayed under "Transactions."
- Users can settle dues via UPI, credit/debit card, or net banking.
- Payments update balances in real-time.

## Implementation
### Friend Management
Friend relationships are stored in the friends table.
Users can send, accept, or reject friend requests.
The system prevents excessive requests after three rejections.
### Expense Splitting
Expenses are logged in the expenses table.
The splits table records how an expense is divided among participants.
Transactions are logged in the transactions table, tracking pending and settled amounts.

### Group Handling
Groups are created in the groups table.
Group members are stored in the group_members table with a status (pending or accepted).
Users must accept invitations before accessing group features.

### Notifications
Notifications are stored in the notifications table.
Users receive alerts for pending friend and group requests.
Group creators receive a notification when members accept an invitation.

## Conclusion
The Expense Splitter project provides an efficient and user-friendly solution for managing shared expenses. With features like friend management, group-based splitting, real-time balance tracking, and secure payments, this system simplifies cost-sharing among individuals and groups.

The implementation ensures data integrity and security by enforcing friend and group acceptance rules, preventing unauthorized splits, and handling transactions with proper status tracking. The use of pending requests, rejection limits, and notifications enhances usability and prevents spam.

By integrating group and transaction management, the application enables users to split expenses transparently and settle payments effortlessly. Future improvements may include automated reminders, payment gateway integration, and AI-based spending analytics.

This project demonstrates a well-structured PHP-MySQL application with robust user authentication, database interactions, and seamless UI functionality, making it a practical tool for real-world expense-sharing scenarios.

## Deployment
Visit https://expense.aadarshsenapati.in/ to view
