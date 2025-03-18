# MystryMenu-Project
recipe organizer web application  
MystryMenu - Online Recipe Application

Introduction

MystryMenu is an innovative online recipe application designed to simplify and
enhance the cooking experience for beginners, chefs, and food enthusiasts. The system provides a comprehensive platform for managing recipes, 
dietary preferences, and cooking techniques. It fosters a culinary community where users can explore, share, and organize their favorite recipes 
effortlessly.



**User Roles & Features**

1. Admin

The administrator of the system has the following capabilities

Add, edit, or remove recipes.

Manage recipe categories.

View and delete users.


2. Users

Registered users of MystryMenu can,

Register/Login to their account.

Add and manage their own recipes.

View and explore a diverse collection of recipes.

Save favorite recipes for easy access.

Provide feedback on recipes.


3. Visitors

Visitors (unregistered users) can,  

Browse available recipes.

View community-shared recipes.




**Installation & Setup**

Prerequisites

Web server (Apache, Nginx, or similar)

Database server (MySQL)
 
Code Editor(Visual Code, Notepad)

PHP, HTML, CSS, Java Script(AJAX)


**Steps to run the application**

Clone the repository to your htdocs or www folder
git clone https://github.com/yourusername/MystryMenu-Project.git

Install dependencies
 1.Install composer

Set up the database

1. Start *Apache* and *MySQL* in XAMPP/WAMP.
2. Open PHPMyAdmin (at http://localhost/phpmyadmin).
3. Create a new database (mystrymenu)
4. Import the database structure(Choose the database_dump.sql file from the project directory)
5. Open the db.php file in the project.   
6. Ensure that the connection parameters are correct

    $host = 'localhost';
    $dbname = 'mystrymenu';    // The database you created above
    $username = 'root';    
    $password = '';       
    
Save the file.

Run the Project

1. Open the project folder in your browser (http://localhost/your-project-folder).




**Contributors**

Project Team- [Group 28.4]

Supervisor- [ Mr.Saliya Wickramasinghe ]

Developers- [ Thilan Nanayakkara, Avindu Niwanthaka, Saara Rizmi, Supuni Geeganage]

License- MIT License

For more information, visit our repository: [https://github.com/Group-28-4/MystryMenu-Project]
