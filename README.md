# ShopRight Inventory System
## Product Description


This app makes it easy for users to place orders by selecting a product and entering the desired quantity. You can see real-time updates of product availability in stock while placing your order.

If a product's stock level gets too low, you’ll receive a notification. If there’s not enough stock to fulfill your order, the app will let you know right away.

In case of incorrect input or any issues during the ordering process, helpful messages will guide you. You can also check the history of your orders on the "Order History" page.

The app has a flexible structure and can be expanded with more features if needed in the future.

## Requirements

- **PHP 8.0** or higher
- **Web server** (Apache, Nginx)

## Installation

1. **Clone the repository**
   
Clone the project, for example, into the /var/www/html folder.

   ```
   git clone https://github.com/artemkuchma/shopright-inventory.git 
   ```
   
2. **Configure a Virtual Host for Application (if not already configured)**

### 2.1 Example for Apache2

2.1.1 Configure Virtual Host

Create a new configuration file for the project in /etc/apache2/sites-available/(you can base it on the default one)

```
sudo nano /etc/apache2/sites-available/shopright-inventory.conf
```
Add the following configuration:

````
<VirtualHost *:80>
    ServerName shopright-inventory
    DocumentRoot /var/www/html/shopright-inventory
    ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
````
2.1.2. Enable the site

Activate the virtual host

````
sudo a2ensite shopright-inventory.conf
````

2.1.3. Configure the hosts File

````
sudo nano /etc/hosts
````
Add the following line:
````
127.0.0.1 shopright-inventory
````

2.1.4. Restart Apache

Restart Apache to apply the changes:
````
sudo systemctl restart apache2
````

### 2.2 Example for Eginx

2.2.1 Configure Virtual Host

Create a new configuration file for the project in /etc/nginx/sites-available/(you can base it on the default one)

```
sudo nano /etc/nginx/sites-available/shopright-inventory.conf
```
Add the following configuration:

````
server {
    listen 80;
    server_name shopright-inventory;

    root /var/www/html/shopright-inventory;
    index index.php;

    access_log /var/log/nginx/shopright-inventory-access.log;
    error_log /var/log/nginx/shopright-inventory-error.log;

    location / {
        try_files $uri $uri/ /index.php?$args;;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

}

````
2.1.2. Enable the site 

Create a symbolic link in the sites-enabled directory to enable the site

````
sudo ln -s /etc/nginx/sites-available/shopright-inventory /etc/nginx/sites-enabled/
````


2.1.3. Configure the hosts File

````
sudo nano /etc/hosts
````
Add the following line:
````
127.0.0.1 shopright-inventory
````

2.1.4. Restart Nginx

Restart Apache to apply the changes:
````
sudo systemctl restart nginx
````
3. **Set permissions for js files**

Run the script 
````
sudo ./set_data_owner.sh. 
````
This script will set the necessary permissions for the JSON files responsible for storing data.

Note: As per the task, I understood that all the JSON files should already exist in the final project, 
so I did not implement a process for creating them during the application deployment 
(similar to how database migrations are applied). However, for proper data writing to the files, 
it is necessary for their owner to match the server user under which the application will run. 
This script sets the file owner to be the same as the server user.

4. **Access the Project**

Your project will now be available in the browser at:
http://shopright-inventory



## Design

The application is based on the **MVC (Model-View-Controller)** approach, implemented in a simplified form. It serves as a minimalistic version of an MVC framework.

---

## Functionality

### User Interface
The application provides a minimal user interface with **three pages** and a **sidebar menu**, using **Bootstrap** for layout and styling.

1. **History Page**
    - Displays a table with a list of all orders.

2. **Order Page**
    - Contains a form for placing orders with:
        - A dropdown to select products.
        - A field for specifying quantity.

3. **Products Page**
    - Displays a table listing all products.

---

### Features

#### Real-Time Data Updates
To ensure real-time updates for product quantities and notifications about low stock levels, **Server-Sent Events (SSE)** were used.
- **Why SSE?**
    - Provides lightweight, one-way communication from server to client.
    - Suitable for moderate server load.
    - Simpler implementation compared to alternatives.

- **Alternatives Considered**:
    - **WebSockets**: Overkill for this use case, requiring additional ports, two-way communication, and more complex implementation.
    - **Polling**: Overburdens the server due to constant requests.
    - **Page Reloading**: Periodic page reloads were deemed unacceptable for user experience.

- **Implementation Details**:
    1. **Approach 1**: A dedicated page with its own controller and route (e.g., `/sse`).
    2. **Approach 2**: A separate `sse.php` file.
        - This was chosen as the final implementation.
        - Logic is handled by the helper `SseHelper`.

- **Optimization**:
    - Real-time updates for product quantities are implemented using JavaScript for efficiency.
    - A more advanced version was explored, aiming to send updates only on changes within a limited time interval (triggered by `sse.json`). However, this remains in development.

- **Challenges with Sessions**:
    - Displaying session-based messages faced issues with outdated data during the SSE loop.
    - Attempts to resolve these by clearing session caches and managing session start/stop cycles were unsuccessful.

---

## Messaging

### Types of Messages
1. **Notifications**:
    - For low stock levels (< 5 units).
    - No notifications are generated when stock reaches zero (considered "out of stock" rather than "low").
    - Duplicate notifications are avoided by overwriting messages for the same product.
    - Timestamps are included and displayed to ensure the user recognizes new messages.

2. **Flash Messages**:
    - Displayed only once.
    - Used for validator outputs, checks, and warnings.

### Integration
- Both types of messages are integrated into the **layout.html** and displayed on all pages.

---

## Validation and Logging

1. **Form Validation**:
    - Before saving an order, data is validated and sanitized using the `FormValidator` helper.
    - Custom validation rules are defined to block invalid actions and display appropriate messages.

2. **Model Validation**:
    - Data is validated before saving to files using model-specific methods.
    - Messages are displayed, and invalid actions are blocked.

3. **Error Logging**:
    - Errors encountered during file operations are logged to `log.json` using `LogHelper`.

4. **Exception Handling**:
    - Critical exceptions are caught in `index.php` and displayed as simple messages.

5. **Edge Case Handling**:
    - Edge cases for product orders are validated using both `FormValidator` and `InventoryManager`.

---

## Models

The application implements a minimal version of **Active Record**:
- Each file is treated as a separate table.
- Each table has a corresponding model defining:
    - Attributes.
    - Validation rules.

- **Base Model**:
    - All models inherit from a base model providing methods analogous to those in frameworks like Yii and Laravel:
        - `save()`, `update()`, `load()`, `add()`.e.c.
    - These methods are tailored to the scope of this task.

---

## Controllers

- Products and Orders Controllers inherit from a **Controller**.
- Responsibilities include:
    - Determining the view to load.
    - Passing parameters to views (similar to standard frameworks).
    - And other specific tasks

- **Controller Features**:
    - Methods for parsing views and managing messages.
    - A `before()` method for preprocessing:
        - Includes `checkLowStock`, which:
            - Validates low-stock messages.
            - Removes outdated messages.
            - Adds new messages if needed.
        - Ensures low-stock updates remain consistent even when products are modified outside the order form.

---

## Routing

The application uses a simple routing system:
- Allows aliases for URL paths.
- Does not yet support GET parameters or templates (these could be added easily).
- Routes are defined in the `routes.php` configuration file.





